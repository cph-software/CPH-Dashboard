{{-- Modal Import Data Global --}}
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data" class="modal-content">
         @csrf
         <div class="modal-header">
            <h5 class="modal-title"><i class="ri-upload-2-line me-1"></i> Import/Request Data</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-md-7 border-end">
                  <div class="alert alert-info py-2 small mb-3">
                     <i class="ri-information-line me-1"></i> Data yang diupload akan masuk ke antrean
                     <strong>Approval</strong> sebelum diproses ke database.
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">1. Pilih Modul Tujuan</label>
                     <select name="module" id="importModuleSelect" class="form-select" required>
                        <option value="" selected disabled>-- Pilih Modul --</option>
                        <option value="Tyre Master" data-template="template_tyre_master.csv">Tyre Master (Aset Ban)
                        </option>
                        <option value="Vehicle Master" data-template="template_vehicle_master.csv">Vehicle Master (Unit)
                        </option>
                        <option value="Movement History" data-template="template_movement.csv">Tyre Movement (Riwayat)
                        </option>
                        <option value="Failure Codes" data-template="template_failure_codes.csv">Failure Codes (Kamus
                           Kerusakan)</option>
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">2. Pilih File CSV</label>
                     <input type="file" name="file" class="form-control" accept=".csv" required>
                     <div class="form-text small">Gunakan format <strong>.csv</strong> (Comma Separated Values).</div>
                  </div>
               </div>
               <div class="col-md-5 bg-light p-3">
                  <h6 class="fw-bold mb-2"><i class="ri-guide-line me-1"></i> Panduan Upload:</h6>
                  <div id="importGuideContent" class="small text-muted">
                     <p>Pilih modul terlebih dahulu untuk melihat format kolom yang dibutuhkan.</p>
                  </div>
                  <div id="templateDownloadArea" class="mt-3 d-none">
                     <a href="#" id="btnDownloadTemplate" class="btn btn-xs btn-outline-primary w-100">
                        <i class="ri-file-download-line me-1"></i> Download Template CSV
                     </a>
                  </div>
               </div>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Upload & Kirim Request</button>
         </div>
      </form>
   </div>
</div>

<script>
   document.addEventListener('DOMContentLoaded', function() {
            const moduleSelect = document.getElementById('importModuleSelect');
            const guideContent = document.getElementById('importGuideContent');
            const downloadArea = document.getElementById('templateDownloadArea');
            const downloadBtn = document.getElementById('btnDownloadTemplate');

            const guides = {
               'Tyre Master': `<strong>Kolom Wajib:</strong><br>
               - serial_number (SN Ban)<br>
               - brand_name (Nama Brand)<br>
               - size_name (Contoh: 11.00-20)<br>
               - pattern_name (Nama Pattern)<br>
               - initial_rtd (OTD Awal)<br>
               - price (Harga Ban)`,
               'Vehicle Master': `<strong>Kolom Wajib:</strong><br>
               - kode_kendaraan (No. Lambung)<br>
               - no_polisi (No. Plat)<br>
               - model_kendaraan (Contoh: DT, HD)<br>
               - brand_kendaraan (Merek Truk)<br>
               - site_location (Site Kerja)`,
               'Movement History': `<strong>Kolom Wajib:</strong><br>
               - serial_number (SN Ban)<br>
               - kode_kendaraan (Unit Truk)<br>
               - movement_type (Installation/Removal)<br>
               - movement_date (YYYY-MM-DD)<br>
               - position_code (Posisi Ban)<br>
               - odometer (KM/HM Kendaraan)`,
               'Failure Codes': `<strong>Kolom Wajib:</strong><br>
               - failure_code (Kode Kerusakan)<br>
               - failure_name (Deskripsi)<br>
               - default_category (Category Group)`
            };

            if (moduleSelect) {
               moduleSelect.addEventListener('change', function() {
                        const selected = this.value;
                        guideContent.innerHTML = guides[selected] || '<p>Pilih modul terlebih dahulu.</p>';

                        if (selected) {
                           downloadArea.classList.remove('d-none');
                           downloadBtn.href = \`{{ route('master_data.download-template') }}?module=\${encodeURIComponent(selected)}\`;
               } else {
                   downloadArea.classList.add('d-none');
               }
           });
       }
   });
</script>
