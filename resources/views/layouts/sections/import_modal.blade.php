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
                        
                        @if (hasPermission('Master Tyre', 'create') || hasPermission('Master Tyre', 'import'))
                           <option value="Tyre Master" data-template="template_tyre_master.csv">Tyre Master (Aset Ban)</option>
                        @endif

                        @if (hasPermission('Vehicle Master', 'create') || hasPermission('Vehicle Master', 'import'))
                           <option value="Vehicle Master" data-template="template_vehicle_master.csv">Vehicle Master (Unit)</option>
                        @endif

                        @if (hasPermission('Movement History', 'create') || hasPermission('Movement History', 'import'))
                           <option value="Movement History" data-template="template_movement.csv">Tyre Movement (Riwayat)</option>
                        @endif

                        @if (hasPermission('Brands', 'create') || hasPermission('Brands', 'import'))
                           <option value="Tyre Brand" data-template="template_brand.csv">Tyre Brand (Merek Ban)</option>
                        @endif

                        @if (hasPermission('Sizes', 'create') || hasPermission('Sizes', 'import'))
                           <option value="Tyre Size" data-template="template_size.csv">Tyre Size (Ukuran Ban)</option>
                        @endif

                        @if (hasPermission('Patterns', 'create') || hasPermission('Patterns', 'import'))
                           <option value="Tyre Pattern" data-template="template_pattern.csv">Tyre Pattern (Tipe Kembangan)</option>
                        @endif

                        @if (hasPermission('Failure Codes', 'create') || hasPermission('Failure Codes', 'import'))
                           <option value="Failure Codes" data-template="template_failure_codes.csv">Failure Codes (Kamus Kerusakan)</option>
                        @endif

                        @if (hasPermission('Locations', 'create') || hasPermission('Locations', 'import'))
                           <option value="Locations" data-template="template_locations.csv">Tyre Locations (Lokasi Kerja)</option>
                        @endif

                        @if (hasPermission('Segments', 'create') || hasPermission('Segments', 'import'))
                           <option value="Segments" data-template="template_segments.csv">Tyre Segments (Segmen Operasi)</option>
                        @endif
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">2. Pilih File Excel (xlsx)</label>
                     <input type="file" name="file" class="form-control" accept=".xlsx" required>
                     <div class="form-text small">Gunakan format <strong>.xlsx</strong> (Microsoft Excel). Pastikan
                        format kolom sesuai panduan.</div>
                  </div>

                  @if(auth()->user()->role_id == 1 || auth()->user()->tyre_company_id == 1)
                  <div class="mb-3" id="companySelectContainer" style="display: none;">
                     <label class="form-label fw-bold text-danger">3. Pilih Perusahaan Tujuan (Khusus Super Admin)</label>
                     <select name="target_company_id" id="targetCompanySelect" class="form-select">
                        <option value="" selected>-- Gunakan Global / Default --</option>
                        @foreach(\App\Models\TyreCompany::all() as $company)
                           <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                        @endforeach
                     </select>
                     <div class="form-text small text-muted">Untuk modul tertentu (seperti Tyre/Vehicle Master), Anda diwajibkan memilih perusahaan spesifik.</div>
                  </div>
                  @endif
               </div>
               <div class="col-md-5 bg-light p-3">
                  <h6 class="fw-bold mb-2"><i class="ri-guide-line me-1"></i> Panduan Upload:</h6>
                  <div id="importGuideContent" class="small text-muted">
                     <p>Pilih modul terlebih dahulu untuk melihat format kolom yang dibutuhkan. <strong>Jangan mengubah
                           header pada template.</strong></p>
                  </div>
                  <div id="templateDownloadArea" class="mt-3 d-none">
                     <a href="#" id="btnDownloadTemplate" class="btn btn-xs btn-outline-primary w-100">
                        <i class="ri-file-download-line me-1"></i> Download Template Excel
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
               - location_name (Nama Lokasi, misal: GUDANG)<br>
               - segment_name (Nama Segmen, misal: HAULING)<br>
               - price (Harga Ban)<br>
               - status (New/Installed/Scrap/Repaired)`,
         'Vehicle Master': `<strong>Kolom Wajib:</strong><br>
               - kode_kendaraan (No. Lambung)<br>
               - no_polisi (No. Plat)<br>
               - model_kendaraan (Contoh: DT, HD)<br>
               - brand_kendaraan (Merek Truk)<br>
               - site_location (Site Kerja)<br>
               - curb_weight (Berat Kosong, kg)<br>
               - payload_capacity (Kapasitas Muat, ton)<br>
               - segment (Nama/ID Segmen)`,
         'Movement History': `<strong>Kolom Wajib:</strong><br>
               - serial_number (SN Ban)<br>
               - kode_kendaraan (Unit Truk)<br>
               - movement_type (Installation/Removal)<br>
               - movement_date (YYYY-MM-DD)<br>
               - position_code (Konfigurasi Ban)<br>
               - odometer (KM/HM Kendaraan)`,
         'Failure Codes': `<strong>Kolom Wajib:</strong><br>
               - failure_code (Kode Kerusakan)<br>
               - failure_name (Deskripsi)<br>
               - default_category (Category Group)`,
         'Tyre Brand': `<strong>Kolom Wajib:</strong><br>
                - brand_name (Nama Brand)<br>
                - brand_type (Premium/Economy)<br>
                - status (Active/Inactive)`,
         'Tyre Size': `<strong>Kolom Wajib:</strong><br>
                - size (Ukuran Ban, misal: 11.00-20)<br>
                - brand_name (Nama Brand)<br>
                - type (Bias/Radial)<br>
                - std_otd (Standard Original Tread Depth)<br>
                - ply_rating (Angka)`,
         'Tyre Pattern': `<strong>Kolom Wajib:</strong><br>
                - pattern_name (Nama Pattern)<br>
                - brand (Nama Brand)<br>
                - status (Active/Inactive)`,
         'Locations': `<strong>Kolom Wajib:</strong><br>
               - location_name (Nama Lokasi)<br>
               - location_type (Warehouse/Service/Disposal)<br>
               - capacity (Kapasitas Ban, angka)`,
         'Segments': `<strong>Kolom Wajib:</strong><br>
                - segment_id (ID Segmen, unik/kode pendek)<br>
                - segment_name (Nama Lengkap Segmen)<br>
                - location_name (Nama Lokasi/Site Kerja)<br>
                - terrain_type (Muddy/Rocky/Asphalt)<br>
                - status (Active/Inactive)`
      };

      if (moduleSelect) {
         moduleSelect.addEventListener('change', function() {
            const selected = this.value;
            guideContent.innerHTML = guides[selected] || '<p>Pilih modul terlebih dahulu.</p>';

            if (selected) {
               downloadArea.classList.remove('d-none');
               const templateUrl =
                  `{{ route('master_data.download-template') }}?module=${encodeURIComponent(selected)}`;
               downloadBtn.setAttribute('href', templateUrl);
               downloadBtn.setAttribute('target', '_blank');

               // Tampilkan dropdown perusahaan jika modul membutuhkan spesifik perusahaan
               const companyContainer = document.getElementById('companySelectContainer');
               const companySelect = document.getElementById('targetCompanySelect');
               if (companyContainer && companySelect) {
                  const scopedModules = ['Tyre Master', 'Master Tyre', 'Vehicle Master', 'Master Vehicle', 'Movement History', 'Tyre Examination'];
                  if (scopedModules.includes(selected)) {
                     companyContainer.style.display = 'block';
                     companySelect.setAttribute('required', 'required');
                  } else {
                     companyContainer.style.display = 'block'; // Tetap tampil untuk opsional
                     companySelect.removeAttribute('required');
                  }
               }

            } else {
               downloadArea.classList.add('d-none');
               downloadBtn.setAttribute('href', '#');
               
               const companyContainer = document.getElementById('companySelectContainer');
               if (companyContainer) companyContainer.style.display = 'none';
            }
         });
      }
   });
</script>
