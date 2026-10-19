<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ImageCompressionService
{
    /**
     * Target maksimal ukuran berkas setelah kompresi (500 KB).
     */
    public const MAX_SIZE_BYTES = 512000; // 500 KB (500 * 1024 = 512,000 bytes)

    /**
     * Validasi berkas secara ketat (mencegah file palsu yang di-rename).
     *
     * @param UploadedFile $file
     * @throws ValidationException
     */
    public function validateRealImage(UploadedFile $file): void
    {
        if (!$file->isValid()) {
            throw ValidationException::withMessages([
                'poster' => 'Unggahan berkas gagal atau berkas rusak.',
            ]);
        }

        $realPath = $file->getRealPath();
        if (!$realPath || !file_exists($realPath)) {
            throw ValidationException::withMessages([
                'poster' => 'Berkas gambar tidak ditemukan di sistem penyimpanan sementara.',
            ]);
        }

        // 1. Cek MIME type binary melalui finfo (bukan header yang dikirim browser)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $realMime = finfo_file($finfo, $realPath);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($realMime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'poster' => 'File ditolak! Berkas yang diunggah bukan gambar asli (MIME terdeteksi: ' . ($realMime ?: 'unknown') . '). Hanya berkas gambar asli (JPG, JPEG, PNG, WEBP) yang diizinkan.',
            ]);
        }

        // 2. Cek integritas struktur gambar dengan getimagesize
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false || empty($imageInfo[0]) || empty($imageInfo[1])) {
            throw ValidationException::withMessages([
                'poster' => 'File ditolak! Berkas yang diunggah terdeteksi palsu (berkas non-gambar yang di-rename menjadi gambar) atau rusak.',
            ]);
        }

        // 3. Cek ekstensi file ordinary
        $clientExt = strtolower($file->getClientOriginalExtension());
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($clientExt, $allowedExts, true)) {
            throw ValidationException::withMessages([
                'poster' => 'Ekstensi berkas tidak valid. Hanya berkas dengan ekstensi .jpg, .jpeg, .png, .webp yang diperbolehkan.',
            ]);
        }

        // 4. Pastikan ekstensi selaras dengan MIME
        $mimeToExt = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png'  => ['png'],
            'image/webp' => ['webp'],
        ];

        if (!isset($mimeToExt[$realMime]) || !in_array($clientExt, $mimeToExt[$realMime], true)) {
            throw ValidationException::withMessages([
                'poster' => 'Ketidakcocokan berkas terdeteksi! Ekstensi berkas (.' . $clientExt . ') tidak sesuai dengan konten aslinya (' . $realMime . ').',
            ]);
        }

        // 5. Cek konten teks berbahaya tersembunyi (misal PHP script di dalam payload gambar)
        $content = @file_get_contents($realPath, false, null, 0, 4096);
        if ($content !== false && (stripos($content, '<?php') !== false || stripos($content, '<script') !== false)) {
            throw ValidationException::withMessages([
                'poster' => 'Berkas gambar mengandung kode yang tidak diizinkan dan ditolak oleh sistem keamanan.',
            ]);
        }
    }

    /**
     * Simpan gambar cover film dengan aturan autokompres maksimal 500KB.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @return string Path relatif berkas yang tersimpan
     */
    public function compressAndStore(UploadedFile $file, string $directory = 'films'): string
    {
        // Jalankan validasi ketat terlebih dahulu
        $this->validateRealImage($file);

        $realPath = $file->getRealPath();
        $fileSize = filesize($realPath);
        $imageInfo = getimagesize($realPath);
        $mime = $imageInfo['mime'];

        // Jika ukuran file sudah <= 500 KB, re-encode untuk membersihkan metadata dan simpan
        // Atau jika > 500 KB, lakukan autokompresi bertingkat
        $imageResource = $this->createImageResource($realPath, $mime);
        if (!$imageResource) {
            throw ValidationException::withMessages([
                'poster' => 'Gagal memproses gambar cover film.',
            ]);
        }

        $origWidth = imagesx($imageResource);
        $origHeight = imagesy($imageResource);

        // Resize proporsional jika resolusi terlalu raksasa (misal di atas 1200px lebar)
        $maxDimension = 1200;
        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            $ratio = min($maxDimension / $origWidth, $maxDimension / $origHeight);
            $newWidth = (int) round($origWidth * $ratio);
            $newHeight = (int) round($origHeight * $ratio);

            $resizedResource = imagecreatetruecolor($newWidth, $newHeight);

            // Pertahankan transparansi untuk PNG
            if ($mime === 'image/png') {
                imagealphablending($resizedResource, false);
                imagesavealpha($resizedResource, true);
            }

            imagecopyresampled($resizedResource, $imageResource, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($imageResource);
            $imageResource = $resizedResource;
        }

        // Generate nama file unik
        $extension = ($mime === 'image/png') ? 'png' : (($mime === 'image/webp') ? 'webp' : 'jpg');
        $filename = uniqid('film_cover_', true) . '.' . $extension;
        $savePath = $directory . '/' . $filename;

        // Proses kompresi jika > 500KB atau convert ke JPG/WebP terkompresi
        $compressedData = $this->compressToTargetSize($imageResource, $mime, self::MAX_SIZE_BYTES);

        // Simpan ke storage publik Laravel
        Storage::disk('public')->put($savePath, $compressedData);

        // Bersihkan memori GD
        imagedestroy($imageResource);

        return $savePath;
    }

    /**
     * Buat GD image resource berdasarkan MIME asli.
     */
    protected function createImageResource(string $path, string $mime)
    {
        return match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default      => null,
        };
    }

    /**
     * Kompresi gambar secara iteratif hingga ukurannya <= targetSizeBytes (500KB).
     */
    protected function compressToTargetSize($resource, string $mime, int $targetSizeBytes): string
    {
        $quality = 85;
        $step = 10;
        $minQuality = 30;

        // Untuk format WebP / JPG, kompresi kualitas sangat efektif
        do {
            ob_start();
            if ($mime === 'image/png') {
                // Untuk PNG kompresi tingkat 0-9
                // Konversi PNG ke JPEG jika ukurannya melebihi 500KB untuk efisiensi kompresi
                $currentSizeBytes = strlen(ob_get_contents());
                imagepng($resource, null, 8);
                $data = ob_get_clean();

                if (strlen($data) > $targetSizeBytes) {
                    // Konversi ke truecolor JPEG berkualitas tinggi yang terkompresi
                    $w = imagesx($resource);
                    $h = imagesy($resource);
                    $jpgCanvas = imagecreatetruecolor($w, $h);
                    $white = imagecolorallocate($jpgCanvas, 255, 255, 255);
                    imagefilledrectangle($jpgCanvas, 0, 0, $w, $h, $white);
                    imagecopy($jpgCanvas, $resource, 0, 0, 0, 0, $w, $h);

                    ob_start();
                    imagejpeg($jpgCanvas, null, $quality);
                    $data = ob_get_clean();
                    imagedestroy($jpgCanvas);
                }
            } elseif ($mime === 'image/webp' && function_exists('imagewebp')) {
                imagewebp($resource, null, $quality);
                $data = ob_get_clean();
            } else {
                imagejpeg($resource, null, $quality);
                $data = ob_get_clean();
            }

            $currentSize = strlen($data);
            if ($currentSize <= $targetSizeBytes || $quality <= $minQuality) {
                break;
            }

            $quality -= $step;
        } while ($quality >= $minQuality);

        return $data;
    }
}
