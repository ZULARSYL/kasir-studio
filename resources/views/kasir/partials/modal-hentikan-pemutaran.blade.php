<!-- Modal Hentikan Pemutaran Film (Selesai Lebih Awal) -->
<div class="modal fade" id="modalHentikanPemutaran" tabindex="-1" aria-labelledby="modalHentikanPemutaranLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white text-danger d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-stop-circle-fill fs-5 text-danger"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalHentikanPemutaranLabel">
                            Berhentikan Pemutaran Film
                        </h5>
                        <div class="small text-white-50">Selesaikan penayangan jika penonton telah selesai lebih awal</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <!-- Warning / Info Alert -->
                <div class="alert alert-warning border-0 rounded-3 shadow-sm mb-3 d-flex align-items-start gap-2.5 py-2.5 px-3">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-warning mt-0.5"></i>
                    <div class="small text-dark">
                        <strong>Perhatian:</strong> Tindakan ini akan mengakhiri pemutaran film saat ini. Durasi penayangan akan ditutup pada waktu sekarang, dan <strong>room akan langsung berstatus TERSEDIA kembali</strong> untuk penonton baru.
                    </div>
                </div>

                <input type="hidden" id="hentikanRoomId">

                <!-- Room & Film Card Info -->
                <div class="card border-0 shadow-sm rounded-3 mb-3 bg-white p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small">Ruangan Studio</span>
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold font-monospace" id="hentikanRoomNama">-</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                        <span class="text-muted small">Judul Film</span>
                        <strong class="text-dark small text-truncate" style="max-width: 220px;" id="hentikanFilmJudul">-</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Sisa Durasi Berjalan</span>
                        <span class="fw-bold text-danger font-monospace small" id="hentikanSisaWaktu">-</span>
                    </div>
                </div>

                <!-- Input Alasan Berhenti (Opsional) -->
                <div class="mb-2">
                    <label for="hentikanAlasanInput" class="form-label small fw-semibold text-muted mb-1">
                        Alasan Pemberhentian (Opsional)
                    </label>
                    <input type="text" 
                           id="hentikanAlasanInput" 
                           class="form-control rounded-3" 
                           placeholder="Contoh: Penonton sudah selesai menonton lebih awal">
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 bg-white py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-3 px-3 fw-semibold text-muted" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-danger rounded-3 px-4 fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btnSubmitHentikanPemutaran">
                    <i class="bi bi-stop-circle-fill"></i>
                    <span>Ya, Berhentikan Pemutaran</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalHentikanPemutaran');
    if (!modalEl) return;

    const bsModal = new bootstrap.Modal(modalEl);
    const inputRoomId = document.getElementById('hentikanRoomId');
    const spanRoomNama = document.getElementById('hentikanRoomNama');
    const spanFilmJudul = document.getElementById('hentikanFilmJudul');
    const spanSisaWaktu = document.getElementById('hentikanSisaWaktu');
    const inputAlasan = document.getElementById('hentikanAlasanInput');
    const btnSubmit = document.getElementById('btnSubmitHentikanPemutaran');

    // Buka Modal saat tombol diklik
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-open-hentikan-pemutaran');
        if (!btn) return;

        e.preventDefault();
        const roomId = btn.getAttribute('data-room-id');
        const roomNama = btn.getAttribute('data-room-nama') || 'Room Studio';
        const filmJudul = btn.getAttribute('data-film-judul') || '-';
        const sisaWaktu = btn.getAttribute('data-sisa-waktu') || 'Waktu Berjalan';

        inputRoomId.value = roomId;
        spanRoomNama.textContent = roomNama;
        spanFilmJudul.textContent = filmJudul;
        spanSisaWaktu.textContent = sisaWaktu;
        inputAlasan.value = 'Penonton sudah selesai menonton lebih awal';

        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="bi bi-stop-circle-fill"></i> <span>Ya, Berhentikan Pemutaran</span>';

        bsModal.show();
    });

    // Submit Hentikan Pemutaran
    btnSubmit.addEventListener('click', function () {
        const roomId = inputRoomId.value;
        if (!roomId) return;

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Memproses...';

        fetch(`/kasir/room/${roomId}/hentikan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                alasan: inputAlasan.value.trim()
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status === 200 && body.status === 'success') {
                bsModal.hide();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pemutaran Diberhentikan!',
                        text: body.message || 'Pemutaran film berhasil diberhentikan. Ruangan studio kini telah tersedia kembali.',
                        timer: 2000,
                        showConfirmButton: true,
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(body.message || 'Pemutaran film berhasil diberhentikan.');
                    window.location.reload();
                }
            } else {
                throw new Error(body.message || 'Gagal memberhentikan pemutaran.');
            }
        })
        .catch(err => {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="bi bi-stop-circle-fill"></i> <span>Ya, Berhentikan Pemutaran</span>';
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Pemberhentian Gagal',
                    text: err.message || 'Terjadi kesalahan sistem saat mencoba memberhentikan pemutaran.',
                    confirmButtonColor: '#ef4444'
                });
            } else {
                alert(err.message || 'Gagal memberhentikan pemutaran.');
            }
        });
    });
});
</script>
