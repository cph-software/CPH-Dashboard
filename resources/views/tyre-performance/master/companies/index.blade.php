@extends('layouts.admin')

@section('title', 'Master Tyre Companies')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master /</span> Instansi Proyek Tyre</h4>
         @if(hasPermission('Companies', 'create'))
         <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
            <i class="icon-base ri ri-add-line me-1"></i> Tambah Instansi
         </button>
         @endif
      </div>

      <div class="card">
         <div class="card-datatable table-responsive text-nowrap">
            <table class="datatables-companies table border-top">
               <thead>
                  <tr>
                     <th>Nama Instansi</th>
                     <th>Keterangan</th>
                     <th class="text-center">Total Ban / Jatah</th>
                     <th>Total User</th>
                     <th>Status</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
               <tbody>
                  @foreach ($companies as $company)
                     <tr>
                        <td><strong>{{ $company->company_name }}</strong></td>
                        <td>{{ $company->description ?: '-' }}</td>
                        <td class="text-center">
                           <div class="d-flex flex-column align-items-center">
                              <span class="fw-bold fs-5 text-dark">{{ number_format($company->total_tyres) }}</span>
                              <small class="text-muted" style="font-size: 0.7rem;">Currently:
                                 <span class="text-primary fw-bold">{{ number_format($company->tyres_count) }}</span> /
                                 Limit: {{ number_format($company->total_tyre_capacity) }}
                              </small>
                              @if ($company->tyres_count > $company->total_tyre_capacity && $company->total_tyre_capacity > 0)
                                 <span class="badge bg-label-danger mt-1" style="font-size: 0.6rem;">Over Limit</span>
                              @endif
                           </div>
                        </td>
                        <td><span class="badge bg-label-info">{{ $company->users_count }} Users</span></td>
                        <td>
                           <span class="badge bg-label-{{ $company->status == 'Active' ? 'success' : 'secondary' }}">
                              {{ $company->status }}
                           </span>
                        </td>
                        <td>
                           <div class="d-flex align-items-center">
                              @if (auth()->user()->role_id == 1)
                                 <a href="{{ route('tyre-companies.mapping', $company->id) }}"
                                    class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Whitelist Mapping">
                                    <i class="icon-base ri ri-shield-keyhole-line"></i>
                                 </a>
                              @endif
                              @if(hasPermission('Companies', 'update'))
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-secondary rounded-pill edit-company"
                                 data-id="{{ $company->id }}" data-name="{{ $company->company_name }}"
                                 data-desc="{{ $company->description }}"
                                 data-capacity="{{ $company->total_tyre_capacity }}"
                                 data-status="{{ $company->status }}">
                                 <i class="icon-base ri ri-pencil-line"></i>
                              </button>
                              @endif
                              @if(hasPermission('Companies', 'delete'))
                              <button type="button"
                                 class="btn btn-sm btn-icon btn-text-danger rounded-pill delete-company"
                                 data-id="{{ $company->id }}" data-name="{{ $company->company_name }}">
                                 <i class="icon-base ri ri-delete-bin-line"></i>
                              </button>
                              @endif
                           </div>
                        </td>
                     </tr>
                  @endforeach
               </tbody>
            </table>
         </div>
      </div>
   </div>

   {{-- Modal Add --}}
   <div class="modal fade" id="addCompanyModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Tambah Instansi Baru</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('tyre-companies.store') }}" method="POST">
               @csrf
               <div class="modal-body">
                  <div class="mb-3">
                     <label class="form-label fw-bold">Nama Instansi/Perusahaan <span class="text-danger">*</span></label>
                     <input type="text" name="company_name" class="form-control" required
                        placeholder="E.g. PT Arutmin Indonesia">
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Keterangan (Opsional)</label>
                     <textarea name="description" class="form-control" rows="2"></textarea>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold small">TOTAL BAN (Asset) <span class="text-danger">*</span></label>
                     <input type="number" name="total_tyres" class="form-control" required value="0">
                     <small class="text-muted d-block mt-1 small">Jumlah fisik total ban yang dimiliki instansi.</small>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold small">JATAH BAN (Quota / Limit) <span
                           class="text-danger">*</span></label>
                     <input type="number" name="total_tyre_capacity" class="form-control" required value="0">
                     <small class="text-muted d-block mt-1 small">Batas maksimal penginputan ban di sistem.</small>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Mode Pengukuran (Odometer/Hour Meter) <span class="text-danger">*</span></label>
                     <select name="measurement_mode" class="form-select">
                        <option value="BOTH">Tampilkan Keduanya (KM & HM)</option>
                        <option value="KM">Hanya Kilometer (KM - Truk/Bus)</option>
                        <option value="HM">Hanya Hour Meter (HM - Alat Berat)</option>
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                     <select name="status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                     </select>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   {{-- Modal Edit --}}
   <div class="modal fade" id="editCompanyModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Edit Instansi</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editCompanyForm" method="POST">
               @csrf
               @method('PUT')
               <div class="modal-body">
                  <div class="mb-3">
                     <label class="form-label fw-bold">Nama Instansi/Perusahaan</label>
                     <input type="text" name="company_name" id="edit_company_name" class="form-control" required>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Keterangan (Opsional)</label>
                     <textarea name="description" id="edit_description" class="form-control" rows="2"></textarea>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold small">TOTAL BAN (Asset) <span class="text-danger">*</span></label>
                     <input type="number" name="total_tyres" id="edit_total_tyres" class="form-control" required>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold small">JATAH BAN (Quota / Limit) <span
                           class="text-danger">*</span></label>
                     <input type="number" name="total_tyre_capacity" id="edit_total_tyre_capacity"
                        class="form-control" required>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Mode Pengukuran <span class="text-danger">*</span></label>
                     <select name="measurement_mode" id="edit_measurement_mode" class="form-select">
                        <option value="BOTH">Tampilkan Keduanya (KM & HM)</option>
                        <option value="KM">Hanya Kilometer (KM)</option>
                        <option value="HM">Hanya Hour Meter (HM)</option>
                     </select>
                  </div>
                  <div class="mb-3">
                     <label class="form-label fw-bold">Status</label>
                     <select name="status" id="edit_status" class="form-select">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                     </select>
                  </div>
               </div>
               <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-primary">Perbarui</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <form id="deleteForm" method="POST" style="display: none;">
      @csrf
      @method('DELETE')
   </form>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.datatables-companies').DataTable({
            order: [],
         });

         $(document).on('click', '.edit-company', function() {
            const id = $(this).data('id');
            const btn = $(this);
            btn.prop('disabled', true);

            const baseUrl = '{{ url('master_company') }}';

            $.get(baseUrl + '/' + id, function(data) {
               $('#edit_company_name').val(data.company_name);
               $('#edit_description').val(data.description);
               $('#edit_total_tyres').val(data.total_tyres);
               $('#edit_total_tyre_capacity').val(data.total_tyre_capacity);
               $('#edit_status').val(data.status);
               
               if (data.measurement_mode) {
                   $('#edit_measurement_mode').val(data.measurement_mode);
               } else {
                   $('#edit_measurement_mode').val('BOTH');
               }
               
               $('#editCompanyForm').attr('action', baseUrl + '/' + id);

               $('#editCompanyModal').modal('show');
               btn.prop('disabled', false);
            }).fail(function() {
               alert('Failed to load company data!');
               btn.prop('disabled', false);
            });
         });

         $(document).on('click', '.delete-company', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
               title: 'Hapus Instansi?',
               text: `"${name}" akan dihapus permanen!`,
               icon: 'warning',
               showCancelButton: true,
               confirmButtonText: 'Ya, Hapus!',
               customClass: {
                  confirmButton: 'btn btn-danger me-3',
                  cancelButton: 'btn btn-outline-secondary'
               },
               buttonsStyling: false
            }).then((result) => {
               if (result.isConfirmed) {
                  const form = document.getElementById('deleteForm');
                  form.action = `{{ url('master_company') }}/${id}`;
                  form.submit();
               }
            });
         });
      });
   </script>
@endsection
