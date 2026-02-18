@extends('layouts.admin')

@section('title', 'Edit Tyre')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master / Tyres /</span> Edit</h4>
         <a href="{{ route('tyre-master.index') }}" class="btn btn-outline-secondary">
            <i class="icon-base ri ri-arrow-left-line me-1"></i> Back to List
         </a>
      </div>

      <div class="card">
         <div class="card-header">
            <h5 class="card-title mb-0">Edit Tyre Data</h5>
         </div>
         <div class="card-body">
            <form action="{{ route('tyre-master.update', $tyre->id) }}" method="POST">
               @csrf
               @method('PUT')
               
               <div class="row">
                  <div class="col-md-12 mb-3">
                     <label for="serial_number" class="form-label">Serial Number</label>
                     <input type="text" id="serial_number" name="serial_number" class="form-control" 
                        value="{{ old('serial_number', $tyre->serial_number) }}" required>
                  </div>
               </div>

               <div class="row g-3">
                  <div class="col-md-6 mb-3">
                     <label for="tyre_size_id" class="form-label">Size</label>
                     <select name="tyre_size_id" id="tyre_size_id" class="form-select select2" data-placeholder="Select Size" required>
                        <option value="">Select Size</option>
                        @foreach ($sizes as $size)
                           <option value="{{ $size->id }}" 
                                 data-type="{{ $size->type }}"
                                 data-brand-id="{{ $size->tyre_brand_id }}" 
                                 data-pattern-id="{{ $size->tyre_pattern_id }}"
                                 data-std-otd="{{ $size->std_otd }}"
                                 {{ old('tyre_size_id', $tyre->tyre_size_id) == $size->id ? 'selected' : '' }}>
                              {{ $size->size }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="tyre_brand_id" class="form-label">Brand</label>
                     <select id="tyre_brand_id" name="tyre_brand_id" class="form-select select2" data-placeholder="Select Brand" required>
                        <option value="">Select Brand</option>
                        @foreach ($brands as $brand)
                           <option value="{{ $brand->id }}" {{ old('tyre_brand_id', $tyre->tyre_brand_id) == $brand->id ? 'selected' : '' }}>
                              {{ $brand->brand_name }}
                           </option>
                        @endforeach
                     </select>
                  </div>
               </div>

               <div class="row g-3">
                  <div class="col-md-6 mb-3">
                     <label for="tyre_pattern_id" class="form-label">Pattern</label>
                     <select id="tyre_pattern_id" name="tyre_pattern_id" class="form-select select2" data-placeholder="Select Pattern">
                        <option value="">Select Pattern</option>
                        @foreach ($patterns as $pattern)
                           <option value="{{ $pattern->id }}" {{ old('tyre_pattern_id', $tyre->tyre_pattern_id) == $pattern->id ? 'selected' : '' }}>
                              {{ $pattern->name }}
                           </option>
                        @endforeach
                     </select>
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="tyre_segment_id" class="form-label">Segment</label>
                     <select name="tyre_segment_id" id="tyre_segment_id" class="form-select select2" data-placeholder="Select Segment">
                        <option value="">Select Segment</option>
                        @foreach ($segments as $segment)
                           <option value="{{ $segment->id }}" {{ old('tyre_segment_id', $tyre->tyre_segment_id) == $segment->id ? 'selected' : '' }}>
                              {{ $segment->segment_name }}
                           </option>
                        @endforeach
                     </select>
                  </div>
               </div>

               <div class="row g-3">
                  <div class="col-md-12 mb-3">
                     <label for="work_location_id" class="form-label">Location</label>
                     <select name="work_location_id" id="work_location_id" class="form-select select2" data-placeholder="Select Location" required>
                        <option value="">Select Location</option>
                        @foreach ($locations as $loc)
                           <option value="{{ $loc->id }}" {{ old('work_location_id', $tyre->work_location_id) == $loc->id ? 'selected' : '' }}>
                              {{ $loc->location_name }}
                           </option>
                        @endforeach
                     </select>
                  </div>
               </div>

               <div class="row g-3">
                  <div class="col-md-6 mb-3">
                     <label for="price" class="form-label">Harga Beli (IDR)</label>
                     <input type="text" id="price" name="price" class="form-control currency-input" 
                        value="{{ old('price', $tyre->price ? number_format($tyre->price, 0, ',', '.') : '') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="retread_count" class="form-label">Retread Count</label>
                     <select name="retread_count" id="retread_count" class="form-select">
                        <option value="0" {{ old('retread_count', $tyre->retread_count) == 0 ? 'selected' : '' }}>New (R0)</option>
                        <option value="1" {{ old('retread_count', $tyre->retread_count) == 1 ? 'selected' : '' }}>R1</option>
                        <option value="2" {{ old('retread_count', $tyre->retread_count) == 2 ? 'selected' : '' }}>R2</option>
                        <option value="3" {{ old('retread_count', $tyre->retread_count) == 3 ? 'selected' : '' }}>R3</option>
                     </select>
                  </div>
               </div>

               <div class="row g-3">
                  <div class="col-md-6 mb-3">
                     <label for="initial_tread_depth" class="form-label">OTD - Ketebalan Awal (mm)</label>
                     <input type="number" id="initial_tread_depth" name="initial_tread_depth" class="form-control" step="0.01" 
                        value="{{ old('initial_tread_depth', $tyre->initial_tread_depth) }}">
                  </div>
                  <div class="col-md-6 mb-3">
                     <label for="current_tread_depth" class="form-label">RTD - Sisa Kembang (mm)</label>
                     <input type="number" id="current_tread_depth" name="current_tread_depth" class="form-control" step="0.01" 
                        value="{{ old('current_tread_depth', $tyre->current_tread_depth) }}">
                  </div>
               </div>

               <div class="row g-3">
                  <div class="col-md-12 mb-3">
                     <label for="status" class="form-label">Status</label>
                     <select name="status" id="status" class="form-select" required>
                        <option value="New" {{ old('status', $tyre->status) == 'New' ? 'selected' : '' }}>New</option>
                        <option value="Installed" {{ old('status', $tyre->status) == 'Installed' ? 'selected' : '' }}>Installed</option>
                        <option value="Repaired" {{ old('status', $tyre->status) == 'Repaired' ? 'selected' : '' }}>Repaired</option>
                        <option value="Retread" {{ old('status', $tyre->status) == 'Retread' ? 'selected' : '' }}>Retread</option>
                        <option value="Scrap" {{ old('status', $tyre->status) == 'Scrap' ? 'selected' : '' }}>Scrap</option>
                     </select>
                  </div>
               </div>

               <div class="mt-4">
                  <button type="submit" class="btn btn-primary me-2">Update Tyre</button>
                  <a href="{{ route('tyre-master.index') }}" class="btn btn-outline-secondary">Cancel</a>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         // Initialize Select2
         $('.select2').each(function () {
            $(this).wrap('<div class="position-relative"></div>').select2({
               placeholder: $(this).data('placeholder'),
               dropdownParent: $(this).parent()
            });
         });

         // Currency Formatting Logic
         function formatCurrency(input) {
            let value = input.value.replace(/\D/g, ''); // Remove non-digits
            if (value) {
               value = parseInt(value, 10).toLocaleString('id-ID'); // Format to 1.000.000
               input.value = value;
            } else {
               input.value = '';
            }
         }

         $(document).on('input', '.currency-input', function () {
            formatCurrency(this);
         });

         // Unformat currency before submit
         $('form').on('submit', function () {
            $('.currency-input').each(function () {
               let value = $(this).val().replace(/\./g, ''); // Remove dots
               $(this).val(value);
            });
         });

         // Auto-fill logic when selecting Size
         function autoFillBySize(sizeId) {
            const selectedOption = $(`#tyre_size_id option:selected`);
            if (!selectedOption.val()) return;

            const brandId = selectedOption.data('brand-id');
            const patternId = selectedOption.data('pattern-id');
            const stdOtd = selectedOption.data('std-otd');

            // Always update Brand if available from Size
            if (brandId) {
               $('#tyre_brand_id').val(brandId).trigger('change');
            }

            // Always update Pattern if available from Size
            if (patternId) {
               $('#tyre_pattern_id').val(patternId).trigger('change');
            }
            
            // Always update OTD if available from Size
            if (stdOtd) {
               $('#initial_tread_depth').val(stdOtd);
            }
         }

         $('#tyre_size_id').on('change', function () {
            autoFillBySize($(this).val());
         });

         @if (session('success'))
            Swal.fire({
               icon: 'success',
               title: 'Success!',
               text: '{{ session('success') }}',
               timer: 2000,
               showConfirmButton: false
            });
         @endif
      });
   </script>
@endsection
