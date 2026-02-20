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
            <a href="{{ route('master_data.export', ['type' => 'examinations']) }}" class="btn btn-outline-primary">
               <i class="ri-download-2-line me-1"></i> Export CSV
            </a>
            @if (hasPermission('Examination', 'create'))
               <a href="{{ route('examination.create') }}" class="btn btn-primary shadow-sm"><i
                     class="ri-add-line me-1"></i>
                  Input Pemeriksaan Baru</a>
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
                     <th>Odometer (KM)</th>
                     <th>Pemeriksa (Tyre Man)</th>
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
                  data: 'status',
                  render: function(data) {
                     let badge = 'bg-label-secondary';
                     if (data === 'Verified') badge = 'bg-label-info';
                     if (data === 'Approved') badge = 'bg-label-success';
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
                  next: '<i class="ri-arrow-right-s-line"></i>',
                  previous: '<i class="ri-arrow-left-s-line"></i>'
               }
            }
         });
      });
   </script>
@endsection
