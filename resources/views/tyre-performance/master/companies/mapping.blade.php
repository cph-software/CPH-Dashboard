@extends('layouts.admin')

@section('title', 'Data Mapping - ' . $company->company_name)

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Master / <a href="{{ route('tyre-companies.index') }}">Instansi</a> /</span>
            Mapping Data: {{ $company->company_name }}
         </h4>
      </div>

      <form action="{{ route('tyre-companies.update-mapping', $company->id) }}" method="POST">
         @csrf
         <div class="row">
            {{-- Brands Section --}}
            <div class="col-12 mb-4">
               <div class="card">
                  <div class="card-header d-flex justify-content-between align-items-center">
                     <h5 class="mb-0"><i class="ri-shield-check-line me-1"></i> Whitelist Brands</h5>
                     <button type="button" class="btn btn-xs btn-outline-primary select-all"
                        data-target="brands-select">Select All</button>
                  </div>
                  <div class="card-body">
                     <select name="brands[]" id="brands-select" class="form-select select2" multiple>
                        @foreach ($allBrands as $brand)
                           <option value="{{ $brand->id }}"
                              {{ $company->brands->contains($brand->id) ? 'selected' : '' }}>
                              {{ $brand->brand_name }}
                           </option>
                        @endforeach
                     </select>
                     <p class="text-muted small mt-2">Pilih merk ban yang diperbolehkan untuk digunakan oleh user di
                        instansi ini.</p>
                  </div>
               </div>
            </div>

            {{-- Patterns Section --}}
            <div class="col-md-6 mb-4">
               <div class="card h-100">
                  <div class="card-header d-flex justify-content-between align-items-center">
                     <h5 class="mb-0"><i class="ri-layout-grid-line me-1"></i> Whitelist Patterns</h5>
                     <button type="button" class="btn btn-xs btn-outline-primary select-all"
                        data-target="patterns-select">Select All</button>
                  </div>
                  <div class="card-body">
                     <select name="patterns[]" id="patterns-select" class="form-select select2" multiple>
                        @foreach ($allPatterns as $pattern)
                           <option value="{{ $pattern->id }}"
                              {{ $company->patterns->contains($pattern->id) ? 'selected' : '' }}>
                              {{ $pattern->brand->brand_name ?? 'N/A' }} - {{ $pattern->name }}
                           </option>
                        @endforeach
                     </select>
                  </div>
               </div>
            </div>

            {{-- Sizes Section --}}
            <div class="col-md-6 mb-4">
               <div class="card h-100">
                  <div class="card-header d-flex justify-content-between align-items-center">
                     <h5 class="mb-0"><i class="ri-ruler-2-line me-1"></i> Whitelist Sizes</h5>
                     <button type="button" class="btn btn-xs btn-outline-primary select-all"
                        data-target="sizes-select">Select All</button>
                  </div>
                  <div class="card-body">
                     <select name="sizes[]" id="sizes-select" class="form-select select2" multiple>
                        @foreach ($allSizes as $size)
                           <option value="{{ $size->id }}"
                              {{ $company->sizes->contains($size->id) ? 'selected' : '' }}>
                              {{ $size->brand->brand_name ?? 'N/A' }} - {{ $size->size }}
                           </option>
                        @endforeach
                     </select>
                  </div>
               </div>
            </div>
         </div>

         <div class="d-flex justify-content-end gap-2 mt-2">
            <a href="{{ route('tyre-companies.index') }}" class="btn btn-outline-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan Mapping</button>
         </div>
      </form>
   </div>
@endsection

@section('vendor-script')
   <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
@endsection

@section('page-script')
   <script>
      $(document).ready(function() {
         $('.select2').each(function() {
            var $this = $(this);
            $this.wrap('<div class="position-relative"></div>').select2({
               placeholder: 'Pilih data...',
               dropdownParent: $this.parent()
            });
         });

         $('.select-all').on('click', function() {
            var target = $(this).data('target');
            var $select = $('#' + target);

            // If all are already selected, deselect all. Otherwise, select all.
            var allSelected = $select.find('option').length === $select.find('option:selected').length;

            if (allSelected) {
               $select.val(null).trigger('change');
               $(this).text('Select All');
            } else {
               var allValues = $select.find('option').map(function() {
                  return this.value;
               }).get();
               $select.val(allValues).trigger('change');
               $(this).text('Deselect All');
            }
         });
      });
   </script>
@endsection
