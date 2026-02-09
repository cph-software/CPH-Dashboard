@extends('layouts.admin')

@section('title', 'Buat Konfigurasi Posisi Ban')

@section('page-style')
   <style>
      .mini-chassis-preview {
         background: #f8f9fa;
         border-radius: 12px;
         padding: 15px;
         margin-top: 15px;
         border: 1px dashed #ced4da;
         min-height: 200px;
         display: flex;
         flex-direction: column;
         align-items: center;
      }

      .mini-cabin {
         width: 60px;
         height: 30px;
         background: #dee2e6;
         border-radius: 5px 5px 2px 2px;
         margin-bottom: 20px;
         border: 1px solid #ced4da;
         font-size: 8px;
         display: flex;
         justify-content: center;
         align-items: center;
         color: #6c757d;
      }

      .mini-axle {
         width: 100%;
         display: flex;
         justify-content: space-between;
         margin-bottom: 15px;
         position: relative;
      }

      .mini-axle::after {
         content: '';
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         width: 60%;
         height: 2px;
         background: #dee2e6;
         z-index: 1;
      }

      .mini-tyre {
         width: 12px;
         height: 22px;
         background: #495057;
         border-radius: 2px;
         z-index: 2;
      }

      .mini-tyre-group {
         display: flex;
         gap: 3px;
      }

      .mini-spare-area {
         margin-top: 10px;
         padding-top: 10px;
         border-top: 1px solid #dee2e6;
         width: 100%;
         display: flex;
         justify-content: center;
         gap: 5px;
         flex-wrap: wrap;
      }

      .mini-tyre.spare {
         width: 18px;
         height: 10px;
         background: #6c757d;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master / Posisi Ban /</span> Buat Konfigurasi</h4>
         <a href="{{ route('tyre-positions.index') }}" class="btn btn-outline-secondary">
            <i class="ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>

      <div class="row">
         <div class="col-lg-8">
            <div class="card mb-4">
               <div class="card-header">
                  <h5 class="card-title mb-0">Informasi Konfigurasi</h5>
               </div>
               <div class="card-body">
                  <form action="{{ route('tyre-positions.store') }}" method="POST" id="configForm">
                     @csrf

                     <div class="row g-3">
                        <div class="col-md-6">
                           <label for="name" class="form-label">Nama Konfigurasi <span
                                 class="text-danger">*</span></label>
                           <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                              name="name" placeholder="e.g. Dump Truck 6x4" value="{{ old('name') }}" required>
                           @error('name')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                           <small class="text-muted">Nama untuk mengidentifikasi konfigurasi ini</small>
                        </div>

                        <div class="col-md-6">
                           <label for="code" class="form-label">Kode Konfigurasi <span
                                 class="text-danger">*</span></label>
                           <input type="text" class="form-control @error('code') is-invalid @enderror" id="code"
                              name="code" placeholder="e.g. DT-6X4" value="{{ old('code') }}" required>
                           @error('code')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                           <small class="text-muted">Kode unik untuk konfigurasi</small>
                        </div>

                        <div class="col-12">
                           <label for="description" class="form-label">Deskripsi</label>
                           <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                              rows="3" placeholder="Deskripsi konfigurasi (opsional)">{{ old('description') }}</textarea>
                           @error('description')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                        </div>
                     </div>

                     <hr class="my-4">

                     <h6 class="mb-3">Konfigurasi As Roda</h6>
                     <p class="text-muted small mb-3">Tentukan jumlah as roda untuk setiap tipe. Sistem akan otomatis
                        generate posisi ban.</p>

                     <div class="row g-3">
                        <div class="col-md-3">
                           <label for="front_axles" class="form-label">
                              <i class="ri-steering-line me-1"></i> As Depan (Front)
                           </label>
                           <input type="number" class="form-control @error('front_axles') is-invalid @enderror"
                              id="front_axles" name="front_axles" min="0" max="5"
                              value="{{ old('front_axles', 1) }}" required>
                           @error('front_axles')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                           <small class="text-muted">2 ban per as</small>
                        </div>

                        <div class="col-md-3">
                           <label for="middle_axles" class="form-label">
                              <i class="ri-truck-line me-1"></i> As Tengah (Middle)
                           </label>
                           <input type="number" class="form-control @error('middle_axles') is-invalid @enderror"
                              id="middle_axles" name="middle_axles" min="0" max="5"
                              value="{{ old('middle_axles', 0) }}" required>
                           @error('middle_axles')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                           <small class="text-muted">4 ban per as (dual)</small>
                        </div>

                        <div class="col-md-3">
                           <label for="rear_axles" class="form-label">
                              <i class="ri-truck-line me-1"></i> As Belakang (Rear)
                           </label>
                           <input type="number" class="form-control @error('rear_axles') is-invalid @enderror"
                              id="rear_axles" name="rear_axles" min="0" max="10"
                              value="{{ old('rear_axles', 2) }}" required>
                           @error('rear_axles')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                           <small class="text-muted">4 ban per as (dual)</small>
                        </div>

                        <div class="col-md-3">
                           <label for="spare_tyres" class="form-label">
                              <i class="ri-tools-line me-1"></i> Ban Cadangan
                           </label>
                           <input type="number" class="form-control @error('spare_tyres') is-invalid @enderror"
                              id="spare_tyres" name="spare_tyres" min="0" max="5"
                              value="{{ old('spare_tyres', 1) }}" required>
                           @error('spare_tyres')
                              <div class="invalid-feedback">{{ $message }}</div>
                           @enderror
                           <small class="text-muted">Jumlah ban cadangan</small>
                        </div>
                     </div>

                     <div class="mt-4 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-outline-secondary" onclick="window.history.back()">
                           Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                           <i class="ri-save-line me-1"></i> Simpan Konfigurasi
                        </button>
                     </div>
                  </form>
               </div>
            </div>
         </div>

         <div class="col-lg-4">
            <div class="card bg-primary text-white mb-4">
               <div class="card-body">
                  <h5 class="text-white mb-3">
                     <i class="ri-information-line me-2"></i> Ringkasan
                  </h5>

                  <div class="mb-3">
                     <small class="text-white d-block mb-1" style="opacity: 0.8;">Total Posisi Ban</small>
                     <h3 class="text-white mb-0" id="totalPositions">10</h3>
                  </div>

                  <hr class="my-3" style="border-color: rgba(255,255,255,0.2);">

                  <div class="row g-2 text-center mb-3">
                     <div class="col-3">
                        <div class="p-2 rounded" style="background-color: rgba(255,255,255,0.1);">
                           <strong class="d-block" id="frontCount">2</strong>
                           <small style="opacity: 0.8; font-size: 0.7rem;">Front</small>
                        </div>
                     </div>
                     <div class="col-3">
                        <div class="p-2 rounded" style="background-color: rgba(255,255,255,0.1);">
                           <strong class="d-block" id="middleCount">0</strong>
                           <small style="opacity: 0.8; font-size: 0.7rem;">Middle</small>
                        </div>
                     </div>
                     <div class="col-3">
                        <div class="p-2 rounded" style="background-color: rgba(255,255,255,0.1);">
                           <strong class="d-block" id="rearCount">8</strong>
                           <small style="opacity: 0.8; font-size: 0.7rem;">Rear</small>
                        </div>
                     </div>
                     <div class="col-3">
                        <div class="p-2 rounded" style="background-color: rgba(255,255,255,0.1);">
                           <strong class="d-block" id="spareCount">1</strong>
                           <small style="opacity: 0.8; font-size: 0.7rem;">Spare</small>
                        </div>
                     </div>
                  </div>
               </div>
            </div>

            <div class="card">
               <div class="card-body">
                  <h6 class="card-title">Live Visual Preview</h6>
                  <p class="text-muted small">Representasi visual sementara berdasarkan input Anda.</p>

                  <div class="mini-chassis-preview" id="chassisPreview">
                     <div class="mini-cabin">FRONT</div>
                     <div id="axleContainer" style="width: 100%"></div>
                     <div id="spareContainer" class="mini-spare-area"></div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection

@section('page-script')
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const frontInput = document.getElementById('front_axles');
         const middleInput = document.getElementById('middle_axles');
         const rearInput = document.getElementById('rear_axles');
         const spareInput = document.getElementById('spare_tyres');

         const axleContainer = document.getElementById('axleContainer');
         const spareContainer = document.getElementById('spareContainer');

         function updatePreview() {
            const front = parseInt(frontInput.value) || 0;
            const middle = parseInt(middleInput.value) || 0;
            const rear = parseInt(rearInput.value) || 0;
            const spare = parseInt(spareInput.value) || 0;

            const frontTotal = front * 2;
            const middleTotal = middle * 4;
            const rearTotal = rear * 4;
            const total = frontTotal + middleTotal + rearTotal + spare;

            document.getElementById('frontCount').textContent = frontTotal;
            document.getElementById('middleCount').textContent = middleTotal;
            document.getElementById('rearCount').textContent = rearTotal;
            document.getElementById('spareCount').textContent = spare;
            document.getElementById('totalPositions').textContent = total;

            // Update Visual Schematic
            axleContainer.innerHTML = '';

            // Add Front Axles
            for (let i = 0; i < front; i++) {
               const axle = document.createElement('div');
               axle.className = 'mini-axle';
               axle.innerHTML = '<div class="mini-tyre"></div><div class="mini-tyre"></div>';
               axleContainer.appendChild(axle);
            }

            // Add Middle Axles (Dual)
            for (let i = 0; i < middle; i++) {
               const axle = document.createElement('div');
               axle.className = 'mini-axle';
               axle.innerHTML = `
                  <div class="mini-tyre-group"><div class="mini-tyre"></div><div class="mini-tyre"></div></div>
                  <div class="mini-tyre-group"><div class="mini-tyre"></div><div class="mini-tyre"></div></div>
               `;
               axleContainer.appendChild(axle);
            }

            // Add Rear Axles
            for (let i = 0; i < rear; i++) {
               const axle = document.createElement('div');
               axle.className = 'mini-axle';
               axle.innerHTML = `
                  <div class="mini-tyre-group"><div class="mini-tyre"></div><div class="mini-tyre"></div></div>
                  <div class="mini-tyre-group"><div class="mini-tyre"></div><div class="mini-tyre"></div></div>
               `;
               axleContainer.appendChild(axle);
            }

            // Add Spares
            spareContainer.innerHTML = '';
            if (spare > 0) {
               for (let i = 0; i < spare; i++) {
                  const sTyre = document.createElement('div');
                  sTyre.className = 'mini-tyre spare';
                  spareContainer.appendChild(sTyre);
               }
            } else {
               spareContainer.style.display = 'none';
            }
            if (spare > 0) spareContainer.style.display = 'flex';
         }

         frontInput.addEventListener('input', updatePreview);
         middleInput.addEventListener('input', updatePreview);
         rearInput.addEventListener('input', updatePreview);
         spareInput.addEventListener('input', updatePreview);

         updatePreview();
      });
   </script>
@endsection
