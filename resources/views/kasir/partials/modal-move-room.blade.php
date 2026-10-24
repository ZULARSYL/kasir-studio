<!-- Modal Move Room (Pindah Room Studio) -->
<div class="modal fade" id="modalMoveRoom" tabindex="-1" aria-labelledby="modalMoveRoomLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white text-info d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                        <i class="bi bi-arrow-left-right fs-5 text-primary"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalMoveRoomLabel">
                            Pindah Room Studio (Move Room)
                        </h5>
                        <div class="small text-white-50">Pindahkan penayangan aktif ke ruangan studio lain yang sedang kosong</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-light">
                <!-- Info Notice Banner (Aturan Move Room) -->
                <div class="alert alert-info border-0 rounded-4 shadow-sm mb-3 d-flex align-items-start gap-2.5 py-2.5 px-3">
                    <i class="bi bi-info-circle-fill fs-5 text-info mt-0.5"></i>
                    <div class="small text-dark">
                        <strong>Aturan Perpindahan:</strong> Waktu mulai transaksi, durasi berjalan, film yang dipesan, dan pesanan kafe tetap tersimpan utuh. Ruangan asal akan langsung berstatus <em>Tersedia</em>, dan ruangan tujuan akan langsung berstatus <em>Terisi</em> melanjutkan sisa waktu pemutaran.
                    </div>
                </div>

                <!-- Loading Spinner State -->
                <div id="moveRoomLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Memuat data...</span>
                    </div>
                    <div class="text-muted small mt-2">Memeriksa ketersediaan ruangan...</div>
                </div>

                <!-- Content State -->
                <div id="moveRoomContent" style="display: none;">
                    <input type="hidden" id="moveRoomTransaksiId">
                    <input type="hidden" id="moveRoomAsalId">

                    <!-- Ringkasan Penayangan Saat Ini (Room Asal) -->
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-header bg-white border-0 py-2.5 px-3.5 fw-bold small text-muted text-uppercase d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-tv me-1.5 text-primary"></i> Informasi Penayangan Saat Ini</span>
                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1" id="moveRoomBadgeAsal">Ruangan 01</span>
                        </div>
                        <div class="card-body p-3.5">
                            <div class="row g-2.5 small">
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted">No. Transaksi</div>
                                    <div class="fw-bold font-monospace text-emerald text-truncate" id="moveRoomTrxNomor">-</div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted">Pelanggan</div>
                                    <div class="fw-bold text-dark text-truncate" id="moveRoomPelanggan">-</div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted">Waktu Mulai</div>
                                    <div class="fw-bold text-dark" id="moveRoomWaktuMulai">-</div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-3">
                                    <div class="text-muted">Sisa Waktu</div>
                                    <div class="fw-bold text-danger font-monospace" id="moveRoomSisaWaktu">-</div>
                                </div>
                            </div>

                            <hr class="my-2.5 text-muted opacity-25">

                            <div class="row g-2.5 small align-items-center">
                                <div class="col-12 col-md-6">
                                    <div class="text-muted">Film Yang Sedang Ditonton:</div>
                                    <div class="fw-bold text-dark fs-6 text-truncate" id="moveRoomFilmJudul">-</div>
                                    <div class="text-muted font-sm">
                                        Durasi Total: <span id="moveRoomDurasiTotal">0</span> mnt &bull; Berjalan: <span id="moveRoomDurasiBerjalan">0</span> mnt
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 text-md-end">
                                    <div class="text-muted">Pesanan Kafe / F&amp;B:</div>
                                    <div class="fw-bold text-emerald" id="moveRoomCafeSummary">-</div>
                                    <div class="text-muted font-sm">Tetap tersimpan pada pesanan utama</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pilih Ruangan Studio Tujuan -->
                    <div class="card border-0 shadow-sm rounded-4 mb-3">
                        <div class="card-header bg-white border-0 py-2.5 px-3.5 fw-bold small text-muted text-uppercase">
                            <i class="bi bi-door-open-fill me-1.5 text-emerald"></i> Pilih Ruangan Studio Tujuan
                        </div>
                        <div class="card-body p-3.5">
                            <div class="mb-3">
                                <label for="selectRoomTujuan" class="form-label fw-bold small text-heading">
                                    Ruangan Baru Yang Tersedia <span class="text-danger">*</span>
                                </label>
                                <select class="form-select form-select-lg rounded-3 fs-6" id="selectRoomTujuan" required>
                                    <option value="" disabled selected>-- Pilih Ruangan Tujuan --</option>
                                </select>
                                <div class="form-text small" id="helpRoomTujuan">
                                    Hanya menampilkan studio room di cabang ini yang saat ini berstatus kosong/tersedia.
                                </div>
                            </div>

                            <!-- Alert jika tidak ada room tersedia -->
                            <div class="alert alert-warning border-0 rounded-3 p-3 mb-0" id="alertNoAvailableRooms" style="display: none;">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                                    <div>
                                        <div class="fw-bold">Tidak ada ruangan lain yang tersedia saat ini</div>
                                        <div class="small">Semua ruangan studio di cabang ini sedang terpakai atau tidak tersedia.</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="inputAlasanPindah" class="form-label fw-bold small text-heading mt-2">
                                    Alasan Perpindahan <span class="text-muted fw-normal">(Opsional)</span>
                                </label>
                                <input type="text" class="form-control rounded-3" id="inputAlasanPindah" 
                                       placeholder="Contoh: Kendala AC ruangan lama, upgrade ruangan, permintaan pelanggan..." maxlength="255">
                            </div>
                        </div>
                    </div>

                    <!-- Ringkasan Konfirmasi Perpindahan -->
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 text-center border-start border-4 border-info">
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <div class="text-end">
                                <div class="small text-muted">Ruangan Asal</div>
                                <div class="fw-bold text-danger fs-6" id="previewRoomAsal">-</div>
                            </div>
                            <div class="px-2">
                                <div class="bg-info-subtle text-info rounded-circle p-2 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px;">
                                    <i class="bi bi-arrow-right fs-5"></i>
                                </div>
                            </div>
                            <div class="text-start">
                                <div class="small text-muted">Ruangan Tujuan</div>
                                <div class="fw-bold text-emerald fs-6" id="previewRoomTujuan">(Pilih Ruangan)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 bg-white py-3 px-4 d-flex justify-content-between">
                <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold text-muted" data-bs-dismiss="modal">
                    Batal
                </button>
                <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold d-flex align-items-center gap-2 shadow-sm" id="btnConfirmMoveRoom" disabled>
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Konfirmasi Pindah Room</span>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('modalMoveRoom');
        if (!modalElement) return;

        const moveRoomModal = new bootstrap.Modal(modalElement);
        const loadingBox = document.getElementById('moveRoomLoading');
        const contentBox = document.getElementById('moveRoomContent');
        const selectRoomTujuan = document.getElementById('selectRoomTujuan');
        const alertNoRooms = document.getElementById('alertNoAvailableRooms');
        const btnConfirm = document.getElementById('btnConfirmMoveRoom');
        const inputAlasan = document.getElementById('inputAlasanPindah');
        const previewTujuan = document.getElementById('previewRoomTujuan');

        // Event listener saat tombol "Pindah Room" diklik
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-open-move-room');
            if (!btn) return;

            e.preventDefault();
            const roomId = btn.dataset.roomId;
            const roomNama = btn.dataset.roomNama || 'Ruangan';

            // Reset modal state
            loadingBox.style.display = 'block';
            contentBox.style.display = 'none';
            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Konfirmasi Pindah Room</span>';
            selectRoomTujuan.innerHTML = '<option value="" disabled selected>-- Pilih Ruangan Tujuan --</option>';
            alertNoRooms.style.display = 'none';
            inputAlasan.value = '';
            previewTujuan.innerText = '(Pilih Ruangan)';
            previewTujuan.className = 'fw-bold text-muted fs-6';

            moveRoomModal.show();

            // Fetch active transaction & available rooms data
            fetch(`/kasir/move-room/rooms/${roomId}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success') {
                        const trx = res.transaksi;
                        const roomAsal = res.room_asal;
                        const availableRooms = res.available_rooms;

                        document.getElementById('moveRoomTransaksiId').value = trx.id;
                        document.getElementById('moveRoomAsalId').value = roomAsal.id;
                        document.getElementById('moveRoomBadgeAsal').innerText = roomAsal.nama;
                        document.getElementById('moveRoomTrxNomor').innerText = '#' + trx.nomor_transaksi;
                        document.getElementById('moveRoomPelanggan').innerText = trx.nama_pelanggan || 'Umum';
                        document.getElementById('moveRoomWaktuMulai').innerText = trx.waktu_mulai;
                        document.getElementById('moveRoomSisaWaktu').innerText = trx.sisa_waktu_teks;
                        document.getElementById('moveRoomFilmJudul').innerText = trx.film_judul;
                        document.getElementById('moveRoomDurasiTotal').innerText = trx.durasi_total_menit;
                        document.getElementById('moveRoomDurasiBerjalan').innerText = trx.durasi_berjalan_menit;
                        
                        if (trx.cafe_items_count > 0) {
                            document.getElementById('moveRoomCafeSummary').innerText = `${trx.cafe_items_count} Item Menu (Rp ${Number(trx.subtotal_cafe).toLocaleString('id-ID')})`;
                        } else {
                            document.getElementById('moveRoomCafeSummary').innerText = 'Tidak ada pesanan cafe';
                        }

                        document.getElementById('previewRoomAsal').innerText = roomAsal.nama;

                        // Populate select options
                        if (availableRooms && availableRooms.length > 0) {
                            selectRoomTujuan.innerHTML = '<option value="" disabled selected>-- Pilih Ruangan Tujuan --</option>';
                            availableRooms.forEach(r => {
                                const opt = document.createElement('option');
                                opt.value = r.id;
                                opt.innerText = `${r.nama} (Kapasitas: ${r.kapasitas} Orang) - Tersedia`;
                                selectRoomTujuan.appendChild(opt);
                            });
                            selectRoomTujuan.disabled = false;
                            alertNoRooms.style.display = 'none';
                        } else {
                            selectRoomTujuan.innerHTML = '<option value="" disabled selected>Tidak ada ruangan lain yang tersedia</option>';
                            selectRoomTujuan.disabled = true;
                            alertNoRooms.style.display = 'block';
                            btnConfirm.disabled = true;
                        }

                        loadingBox.style.display = 'none';
                        contentBox.style.display = 'block';
                    } else {
                        alert(res.message || 'Gagal memuat data ruangan.');
                        moveRoomModal.hide();
                    }
                })
                .catch(err => {
                    console.error('Error fetching move room data:', err);
                    alert('Terjadi kesalahan saat memuat data ruangan.');
                    moveRoomModal.hide();
                });
        });

        // Event listener saat memilih ruangan tujuan di select dropdown
        selectRoomTujuan.addEventListener('change', function () {
            const selectedText = selectRoomTujuan.options[selectRoomTujuan.selectedIndex].text;
            const roomNameOnly = selectedText.split('(')[0].trim();
            previewTujuan.innerText = roomNameOnly;
            previewTujuan.className = 'fw-bold text-emerald fs-6';
            btnConfirm.disabled = !selectRoomTujuan.value;
        });

        // Event listener konfirmasi perpindahan room
        btnConfirm.addEventListener('click', function () {
            const transaksiId = document.getElementById('moveRoomTransaksiId').value;
            const roomTujuanId = selectRoomTujuan.value;
            const alasanPindah = inputAlasan.value;
            const roomTujuanText = previewTujuan.innerText;

            if (!roomTujuanId) {
                alert('Silakan pilih ruangan studio tujuan.');
                return;
            }

            const confirmMsg = `Konfirmasi perpindahan ruangan:\nApakah Anda yakin ingin memindahkan penayangan ke ${roomTujuanText}?`;
            if (!confirm(confirmMsg)) {
                return;
            }

            btnConfirm.disabled = true;
            btnConfirm.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Memproses...';

            fetch('/kasir/move-room', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    transaksi_id: transaksiId,
                    room_tujuan_id: roomTujuanId,
                    alasan_pindah: alasanPindah
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Perpindahan Berhasil!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        alert(data.message);
                        window.location.reload();
                    }
                } else {
                    alert(data.message || 'Gagal memindahkan ruangan.');
                    btnConfirm.disabled = false;
                    btnConfirm.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Konfirmasi Pindah Room</span>';
                }
            })
            .catch(err => {
                console.error('Error executing move room:', err);
                alert('Terjadi kesalahan jaringan saat memproses perpindahan ruangan.');
                btnConfirm.disabled = false;
                btnConfirm.innerHTML = '<i class="bi bi-check-circle-fill"></i> <span>Konfirmasi Pindah Room</span>';
            });
        });
    });
</script>
@endpush
