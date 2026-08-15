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

                  @if(\App\Helpers\SessionCompanyHelper::isSuperAdmin() || \App\Helpers\SessionCompanyHelper::isWorkshopAdmin())
                  <div class="mb-3" id="companySelectContainer" style="display: none;">
                     <label class="form-label fw-bold text-danger">3. Pilih Perusahaan Tujuan</label>
                     <select name="target_company_id" id="targetCompanySelect" class="form-select">
                        <option value="" selected>-- Gunakan Global / Default --</option>
                        @php
                           if (\App\Helpers\SessionCompanyHelper::isSuperAdmin()) {
                              $companies = \App\Models\TyreCompany::all();
                           } else {
                              $userCompany = auth()->user()->tyreCompany;
                              $companies = $userCompany ? collect([$userCompany])->concat($userCompany->children) : collect();
                           }
                        @endphp
                        @foreach($companies as $company)
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
         'Tyre Master': `<strong><i class="ri-check-double-line text-primary me-1"></i>Struktur Template (.xlsx):</strong><br>
               <span class="badge bg-label-primary mb-1">Sheet 1: Import Data</span><br>
               <span class="badge bg-label-info mb-1">Sheet 2: Panduan & Validasi</span><br>
               <span class="badge bg-label-success mb-2">Sheet 3: Referensi Master (Brand/Size/Pattern)</span><br>
               <strong>Kolom Utama:</strong><br>
               • <code>serial_number</code> (Nomor Seri Ban)<br>
               • <code>brand</code> (Merek Ban)<br>
               • <code>size</code> (Ukuran Ban, misal: 11.00-20)<br>
               • <code>initial_rtd</code> (OTD Awal, mm)<br>
               • <code>status</code> (New / Repaired / Scrap)<br>
               • <code>in_warehouse</code> (Yes / No)`,
         'Vehicle Master': `<strong><i class="ri-check-double-line text-success me-1"></i>Struktur Template (.xlsx):</strong><br>
               <span class="badge bg-label-primary mb-1">Sheet 1: Import Data</span><br>
               <span class="badge bg-label-info mb-1">Sheet 2: Panduan & Validasi</span><br>
               <span class="badge bg-label-success mb-2">Sheet 3: Referensi Layout & Lokasi</span><br>
               <strong>Kolom Utama:</strong><br>
               • <code>kode_kendaraan</code> (No. Lambung Unit)<br>
               • <code>model_kendaraan</code> (DUMP TRUCK, HD)<br>
               • <code>layout</code> (Konfigurasi Axle Roda)<br>
               • <code>site_location</code> (Lokasi Site)`,
         'Movement History': `<strong><i class="ri-check-double-line text-info me-1"></i>Struktur Template (.xlsx):</strong><br>
               <span class="badge bg-label-primary mb-1">Sheet 1: Import Data (Siklus Lengkap)</span><br>
               <span class="badge bg-label-info mb-1">Sheet 2: Panduan Lengkap</span><br>
               <span class="badge bg-label-success mb-2">Sheet 3: Referensi Unit & Kerusakan</span><br>
               <strong>Kolom Utama:</strong><br>
               • <code>no_seri</code> & <code>unit</code><br>
               • <code>pemasangan_tanggal</code> & <code>pemasangan_km</code><br>
               • <code>pelepasan_tanggal</code> & <code>pelepasan_km</code><br>
               • <code>keterangan</code> (BUANG / Kosong)<br>
               • <code>tebal_telapak</code> (Sisa RTD mm)`,
         'Failure Codes': `<strong><i class="ri-check-double-line text-danger me-1"></i>Format Kolom:</strong><br>
               • <code>failure_code</code> (Kode Kerusakan Unik)<br>
               • <code>failure_name</code> (Deskripsi Kerusakan)<br>
               • <code>default_category</code> (Repair / Scrap / Claim)`,
         'Tyre Brand': `<strong><i class="ri-check-double-line text-primary me-1"></i>Format Kolom:</strong><br>
               • <code>brand_name</code> (Nama Merek Ban)<br>
               • <code>status</code> (Active / Inactive)`,
         'Tyre Size': `<strong><i class="ri-check-double-line text-info me-1"></i>Format Kolom:</strong><br>
               • <code>size</code> (Ukuran Ban, misal: 11.00-20)<br>
               • <code>brand_name</code> (Merek Terkait)<br>
               • <code>type</code> (Bias / Radial)<br>
               • <code>std_otd</code> (Standar OTD mm)<br>
               • <code>ply_rating</code> (Ply Rating)`,
         'Tyre Pattern': `<strong><i class="ri-check-double-line text-warning me-1"></i>Format Kolom:</strong><br>
               • <code>pattern_name</code> (Nama Kembangan/Pattern)<br>
               • <code>brand</code> (Merek Terkait)<br>
               • <code>status</code> (Active / Inactive)`,
         'Locations': `<strong><i class="ri-check-double-line text-success me-1"></i>Format Kolom:</strong><br>
               • <code>location_name</code> (Nama Lokasi Gudang/Workshop)<br>
               • <code>location_type</code> (Warehouse / Service / Disposal)<br>
               • <code>capacity</code> (Kapasitas Maksimal Ban)`,
         'Segments': `<strong><i class="ri-check-double-line text-teal me-1"></i>Format Kolom:</strong><br>
               • <code>segment_id</code> (Kode ID Segmen)<br>
               • <code>segment_name</code> (Nama Lengkap Segmen)<br>
               • <code>location_name</code> (Lokasi Terkait)<br>
               • <code>terrain_type</code> (Muddy / Rocky / Asphalt)`
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
