@extends('layouts.admin')

@section('title', 'Daftar Pemeriksaan Ban')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Tyre Performance /</span> Examination</h4>
         <div class="d-flex gap-2">
            @if (hasPermission('Examination', 'export') || auth()->user()->role_id == 1)
            <a href="{{ route('master_data.export', ['type' => 'examinations', 'format' => 'excel']) }}"
               class="btn btn-outline-primary">
               <i class="icon-base ri ri-file-excel-2-line me-1"></i> Export Excel
            </a>
            @endif
            @if (hasPermission('Examination', 'create'))
               <a href="{{ route('examination.create') }}" class="btn btn-primary shadow-sm">
                  <i class="icon-base ri ri-add-line me-1"></i> Input Pemeriksaan Baru
               </a>
            @endif
         </div>
      </div>

      <div class="card shadow-sm border-0">
         <div class="card-datatable table-responsive pt-0">
            <table class="table table-hover" id="exam-table">
               <thead class="bg-light">
                  <tr>
                     <th>Tanggal</th>
                     <th>Unit/Kendaraan</th>
                     <th>Odometer / HM</th>
                     <th>Pemeriksa (Tyre Man)</th>
                     <th>Tipe Input</th>
                     <th>Status</th>
                     <th>Aksi</th>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(function() {
         var table = $('#exam-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('examination.data') }}",
            columns: [{
                  data: 'date',
                  name: 'examination_date'
               },
               {
                  data: 'vehicle',
                  name: 'vehicle_id'
               },
               {
                  data: 'odometer',
                  name: 'odometer'
               },
               {
                  data: 'tyre_man',
                  name: 'tyre_man'
               },
               {
                  data: 'type',
                  render: function(data) {
                     let badge = 'bg-label-primary';
                     if (data === 'Sales') badge = 'bg-label-warning';
                     return '<span class="badge ' + badge + '">' + data + '</span>';
                  }
               },
               {
                  data: 'status',
                  render: function(data) {
                     let badge = 'bg-label-secondary';
                     if (data === 'Pending') badge = 'bg-label-warning';
                     if (data === 'Approved') badge = 'bg-label-success';
                     if (data === 'Rejected') badge = 'bg-label-danger';
                     return '<span class="badge ' + badge + '">' + data + '</span>';
                  }
               },
               {
                  data: 'action',
                  orderable: false,
                  searchable: false
               }
            ],
            order: [
               [0, 'desc']
            ],
            displayLength: 10,
            lengthMenu: [10, 25, 50, 75, 100],
            language: {
               paginate: {
                  next: '<i class="ri ri-arrow-right-s-line"></i>',
                  previous: '<i class="ri ri-arrow-left-s-line"></i>'
               }
            }
         });
      });

      function deleteExam(id) {
         Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data pemeriksaan yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e0284f',
            cancelButtonColor: '#8c98a4',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
         }).then((result) => {
            if (result.isConfirmed) {
               $.ajax({
                  url: "{{ url('examination') }}/" + id,
                  type: 'DELETE',
                  data: {
                     _token: '{{ csrf_token() }}'
                  },
                  success: function(response) {
                     if (response.success) {
                        Swal.fire('Terhapus!', response.message, 'success');
                        $('#exam-table').DataTable().ajax.reload();
                     } else {
                        Swal.fire('Gagal!', response.message, 'error');
                     }
                  },
                  error: function(err) {
                     Swal.fire('Error!', 'Terjadi kesalahan pada server.', 'error');
                  }
               });
            }
         });
      }
   </script>
@endsection
