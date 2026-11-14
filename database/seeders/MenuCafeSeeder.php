<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\FakturBarang;
use App\Models\FakturBarangDetail;
use App\Models\MenuCafe;
use App\Models\SatuanBarang;
use App\Models\TokoCabang;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MenuCafeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Master Kategori Menu Cafe
        $categories = [
            'Makanan' => 'Makanan berat dan makanan utama studio',
            'Minuman' => 'Kopi, teh, jus, dan aneka minuman segar',
            'Snack'   => 'Camilan ringan, popcorn, dan finger foods',
            'Dessert' => 'Hidangan penutup manis dan es krim',
        ];

        $kategoriModels = [];
        foreach ($categories as $nama => $desc) {
            $kategoriModels[$nama] = MenuCafe::firstOrCreate(['nama' => $nama]);
        }

        // 2. Master Satuan Barang
        $satuans = [
            'Cup',
            'Porsi',
            'Pcs',
            'Botol',
            'Kaleng',
            'Pack',
        ];

        $satuanModels = [];
        foreach ($satuans as $satuan) {
            $satuanModels[$satuan] = SatuanBarang::firstOrCreate(['nama_satuan' => $satuan]);
        }

        // 3. Katalog Menu Cafe Lengkap (Nama, Kategori, Satuan Standar, Harga Modal HPP, Harga Jual POS, Stok per Cabang)
        $catalog = [
            // --- MINUMAN ---
            [
                'nama' => 'Kopi Susu Gula Aren Pressent',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 8000,
                'harga_jual' => 20000,
                'stok' => 250,
            ],
            [
                'nama' => 'Iced Americano Signature',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 6000,
                'harga_jual' => 18000,
                'stok' => 200,
            ],
            [
                'nama' => 'Cafe Latte Caramel Macchiato',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 9500,
                'harga_jual' => 24000,
                'stok' => 200,
            ],
            [
                'nama' => 'Matcha Green Tea Creamy Latte',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 10000,
                'harga_jual' => 25000,
                'stok' => 180,
            ],
            [
                'nama' => 'Chocolate Hazelnut Blended',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 9500,
                'harga_jual' => 24000,
                'stok' => 180,
            ],
            [
                'nama' => 'Ice Lychee Tea with Nata De Coco',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 7000,
                'harga_jual' => 18000,
                'stok' => 220,
            ],
            [
                'nama' => 'Lemon Tea Segar Dingin',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 5000,
                'harga_jual' => 15000,
                'stok' => 250,
            ],
            [
                'nama' => 'Teh Tarik Special Dingin',
                'kategori' => 'Minuman',
                'satuan' => 'Cup',
                'harga_awal' => 6000,
                'harga_jual' => 16000,
                'stok' => 180,
            ],
            [
                'nama' => 'Air Mineral Aqua 600ml',
                'kategori' => 'Minuman',
                'satuan' => 'Botol',
                'harga_awal' => 3500,
                'harga_jual' => 8000,
                'stok' => 300,
            ],
            [
                'nama' => 'Coca-Cola Kaleng 330ml',
                'kategori' => 'Minuman',
                'satuan' => 'Kaleng',
                'harga_awal' => 6000,
                'harga_jual' => 14000,
                'stok' => 200,
            ],
            [
                'nama' => 'Sprite Kaleng 330ml',
                'kategori' => 'Minuman',
                'satuan' => 'Kaleng',
                'harga_awal' => 6000,
                'harga_jual' => 14000,
                'stok' => 200,
            ],

            // --- MAKANAN ---
            [
                'nama' => 'Popcorn Caramel Studio Jumbo',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 10000,
                'harga_jual' => 28000,
                'stok' => 250,
            ],
            [
                'nama' => 'Popcorn Butter Salty Regular',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 8000,
                'harga_jual' => 22000,
                'stok' => 250,
            ],
            [
                'nama' => 'French Fries Crispy BBQ Seasoning',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 9000,
                'harga_jual' => 22000,
                'stok' => 200,
            ],
            [
                'nama' => 'Crispy Chicken Strips with Mayo',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 13000,
                'harga_jual' => 28000,
                'stok' => 180,
            ],
            [
                'nama' => 'Sosis Panggang Jumbo Mozzarella',
                'kategori' => 'Makanan',
                'satuan' => 'Pcs',
                'harga_awal' => 11000,
                'harga_jual' => 24000,
                'stok' => 180,
            ],
            [
                'nama' => 'Mie Goreng Spesial Telur Kornet',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 9000,
                'harga_jual' => 20000,
                'stok' => 200,
            ],
            [
                'nama' => 'Mie Kuah Kari Ayam Telur Rebus',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 9000,
                'harga_jual' => 20000,
                'stok' => 200,
            ],
            [
                'nama' => 'Spaghetti Carbonara Creamy Smoked Beef',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 15000,
                'harga_jual' => 32000,
                'stok' => 150,
            ],
            [
                'nama' => 'Dimsum Ayam Keju Kukus (4 pcs)',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 10000,
                'harga_jual' => 24000,
                'stok' => 180,
            ],
            [
                'nama' => 'Nasi Goreng Spesial Pressent Studio',
                'kategori' => 'Makanan',
                'satuan' => 'Porsi',
                'harga_awal' => 12000,
                'harga_jual' => 27000,
                'stok' => 150,
            ],

            // --- SNACK & DESSERT ---
            [
                'nama' => 'Nachos with Melted Cheese Sauce',
                'kategori' => 'Snack',
                'satuan' => 'Porsi',
                'harga_awal' => 11000,
                'harga_jual' => 25000,
                'stok' => 180,
            ],
            [
                'nama' => 'Singkong Krispi Keju Parut',
                'kategori' => 'Snack',
                'satuan' => 'Porsi',
                'harga_awal' => 7000,
                'harga_jual' => 18000,
                'stok' => 180,
            ],
            [
                'nama' => 'Roti Bakar Coklat Keju Crunchy',
                'kategori' => 'Snack',
                'satuan' => 'Porsi',
                'harga_awal' => 8000,
                'harga_jual' => 20000,
                'stok' => 180,
            ],
            [
                'nama' => 'Pisang Coklat Crispy (Piscok Lumer)',
                'kategori' => 'Snack',
                'satuan' => 'Porsi',
                'harga_awal' => 7500,
                'harga_jual' => 18000,
                'stok' => 180,
            ],
            [
                'nama' => 'Potato Wedges Spicy Seasoned',
                'kategori' => 'Snack',
                'satuan' => 'Porsi',
                'harga_awal' => 9500,
                'harga_jual' => 23000,
                'stok' => 180,
            ],
            [
                'nama' => 'Keripik Kentang Original Gurih',
                'kategori' => 'Snack',
                'satuan' => 'Pack',
                'harga_awal' => 6000,
                'harga_jual' => 15000,
                'stok' => 200,
            ],
            [
                'nama' => 'Churros with Chocolate Dipping',
                'kategori' => 'Dessert',
                'satuan' => 'Porsi',
                'harga_awal' => 9000,
                'harga_jual' => 22000,
                'stok' => 150,
            ],
            [
                'nama' => 'Waffle Ice Cream Vanilla Double Scoop',
                'kategori' => 'Dessert',
                'satuan' => 'Porsi',
                'harga_awal' => 12000,
                'harga_jual' => 26000,
                'stok' => 150,
            ],
        ];

        // 4. Create or update Barangs
        $itemMetaMap = [];
        foreach ($catalog as $item) {
            $catId = $kategoriModels[$item['kategori']]->id ?? null;
            $barang = Barang::updateOrCreate(
                ['nama_barang' => $item['nama']],
                ['menu_cafe_id' => $catId]
            );

            $itemMetaMap[$barang->id] = [
                'satuan_id' => $satuanModels[$item['satuan']]->id,
                'harga_awal' => $item['harga_awal'],
                'harga_jual' => $item['harga_jual'],
                'stok' => $item['stok'],
            ];
        }

        // Ambil semua barang yang ada di sistem
        $allBarangs = Barang::with('menuCafe')->get();

        // 5. Inisialisasi Faktur Pembelian Stok Barang per Cabang
        $cabangs = TokoCabang::all();
        $adminUser = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $adminUser?->id;

        foreach ($cabangs as $cabangIndex => $cabang) {
            $nomorFaktur = 'FKT-' . Carbon::now()->subDays(30)->format('Ymd') . '-000' . ($cabangIndex + 1);

            $faktur = FakturBarang::updateOrCreate(
                [
                    'nomor_faktur' => $nomorFaktur,
                ],
                [
                    'toko_cabang_id' => $cabang->id,
                    'tanggal_faktur' => Carbon::now()->subDays(30)->format('Y-m-d'),
                    'pemasok' => $cabangIndex === 0 ? 'PT Mitra Kuliner Nusantara' : 'CV Distribusi Pangan Riau',
                    'catatan' => "Pengadaan awal stok menu cafe & snack outlet {$cabang->nama_cabang}",
                    'total_nominal' => 0,
                    'user_id' => $adminId,
                ]
            );

            $totalNominal = 0;
            foreach ($allBarangs as $barang) {
                // Tentukan data harga dan stok
                if (isset($itemMetaMap[$barang->id])) {
                    $meta = $itemMetaMap[$barang->id];
                    $satuanId = $meta['satuan_id'];
                    $hargaAwal = $meta['harga_awal'];
                    $hargaJual = $meta['harga_jual'];
                    $stokQty = $meta['stok'];
                } else {
                    $kategoriNama = $barang->menuCafe?->nama ?? 'Makanan';
                    $satuanId = match ($kategoriNama) {
                        'Minuman' => $satuanModels['Cup']->id,
                        'Snack'   => $satuanModels['Porsi']->id,
                        'Dessert' => $satuanModels['Porsi']->id,
                        default   => $satuanModels['Pcs']->id,
                    };
                    $hargaAwal = match ($kategoriNama) {
                        'Minuman' => 7000,
                        'Snack'   => 8000,
                        'Dessert' => 9000,
                        default   => 10000,
                    };
                    $hargaJual = match ($kategoriNama) {
                        'Minuman' => 18000,
                        'Snack'   => 18000,
                        'Dessert' => 20000,
                        default   => 22000,
                    };
                    $stokQty = 150;
                }

                $subtotal = $stokQty * $hargaAwal;
                $totalNominal += $subtotal;

                FakturBarangDetail::updateOrCreate(
                    [
                        'faktur_barang_id' => $faktur->id,
                        'barang_id' => $barang->id,
                    ],
                    [
                        'satuan_barang_id' => $satuanId,
                        'qty' => $stokQty,
                        'harga_awal' => $hargaAwal,
                        'harga_jual' => $hargaJual,
                        'subtotal' => $subtotal,
                    ]
                );
            }

            $faktur->update(['total_nominal' => $totalNominal]);
        }
    }
}
