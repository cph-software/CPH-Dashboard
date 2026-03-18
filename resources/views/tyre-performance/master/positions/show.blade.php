@extends('layouts.admin')

@section('title', 'Visualisasi Konfigurasi Konfigurasi Ban')

@section('vendor-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-style')
   <style>
      /* Vehicle Visualization Styles */
      .vehicle-chassis {
         position: relative;
         width: 100%;
         max-width: 500px;
         margin: 0 auto;
         background: #f0f2f5;
         border-radius: 20px;
         padding: 40px 20px;
         border: 2px dashed #cbd5e0;
      }

      .chassis-line {
         position: absolute;
         top: 0;
         left: 50%;
         transform: translateX(-50%);
         width: 40px;
         height: 100%;
         background: #4a5568;
         z-index: 1;
         border-radius: 5px;
         opacity: 0.2;
      }

      .axle-row {
         position: relative;
         display: flex;
         justify-content: space-between;
         align-items: center;
         margin-bottom: 50px;
         z-index: 2;
      }

      .axle-row::after {
         content: '';
         position: absolute;
         top: 50%;
         left: 50%;
         transform: translate(-50%, -50%);
         width: 80%;
         height: 8px;
         background: #718096;
         z-index: -1;
         border-radius: 4px;
      }

      .tyre-group {
         display: flex;
         gap: 8px;
      }

      .tyre-node {
         width: 45px;
         height: 80px;
         background: #2d3748;
         border-radius: 8px;
         display: flex;
         flex-direction: column;
         justify-content: center;
         align-items: center;
         color: white;
         font-size: 0.7rem;
         font-weight: bold;
         box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
         transition: all 0.2s;
         border: 2px solid transparent;
         cursor: help;
      }

      .tyre-node:hover {
         transform: scale(1.1);
         border-color: #4299e1;
         z-index: 10;
      }

      .tyre-node.front {
         background: linear-gradient(180deg, #4a5568 0%, #2d3748 100%);
         border-left: 3px solid #ed8936;
      }

      .tyre-node.rear {
         background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
         border-left: 3px solid #48bb78;
      }

      .tyre-node.spare {
         width: 80px;
         height: 45px;
         margin: 10px;
         background: #4a5568;
         border-radius: 8px;
         border-bottom: 3px solid #4299e1;
      }

      .position-label {
         font-size: 0.6rem;
         opacity: 0.7;
         text-transform: uppercase;
      }

      .cabin-box {
         width: 120px;
         height: 60px;
         background: #cbd5e0;
         margin: 0 auto 30px auto;
         border-radius: 10px 10px 5px 5px;
         display: flex;
         justify-content: center;
         align-items: center;
         font-weight: bold;
         color: #4a5568;
         font-size: 0.8rem;
         text-transform: uppercase;
         letter-spacing: 2px;
         border: 2px solid #a0aec0;
      }

      .spare-container {
         display: flex;
         flex-wrap: wrap;
         justify-content: center;
         gap: 15px;
         margin-top: 40px;
         padding-top: 20px;
         border-top: 2px solid #e2e8f0;
      }

      .legend-card {
         font-size: 0.8rem;
      }

      .legend-item {
         display: flex;
         align-items: center;
         margin-bottom: 5px;
      }

      .legend-color {
         width: 12px;
         height: 12px;
         border-radius: 2px;
         margin-right: 8px;
      }
   </style>
@endsection

@section('content')
   <div class="container-xxl flex-grow-1 container-p-y">
      <div class="d-flex justify-content-between align-items-center mb-4">
         <div>
            <h4 class="fw-bold mb-1">{{ $configuration->name }}</h4>
            <p class="text-muted mb-0">
               <span class="badge bg-label-primary me-2">{{ $configuration->code }}</span>
               <small><i class="ri-truck-line me-1"></i> Visualisasi Tata Letak Ban</small>
            </p>
         </div>
         <div class="d-flex gap-2">
            <a href="{{ route('tyre-positions.edit', $configuration->id) }}" class="btn btn-outline-secondary">
               <i class="ri-pencil-line me-1"></i> Edit
            </a>
            <a href="{{ route('tyre-positions.index') }}" class="btn btn-primary">
               <i class="ri-arrow-left-line me-1"></i> Kembali
            </a>
         </div>
      </div>

      <div class="row">
         <div class="col-lg-8">
            <div class="card h-100">
               <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                     <h5 class="card-title mb-0">Peta Visual Kendaraan</h5>
                  </div>

                  <style>
                     .v-chassis {
                        position: relative;
                        width: 100%;
                        max-width: 350px;
                        margin: 0 auto;
                        background: #fafafa;
                        border-radius: 20px;
                        padding: 40px 20px;
                        border: 2px solid #eee;
                        box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.02);
                     }

                     .v-cabin {
                        width: 100px;
                        height: 45px;
                        background: #333;
                        margin: 0 auto 30px auto;
                        border-radius: 8px 8px 4px 4px;
                        border-bottom: 5px solid #111;
                        text-align: center;
                        line-height: 40px;
                        font-size: 11px;
                        color: #fff;
                        font-weight: bold;
                        letter-spacing: 2px;
                     }

                     .v-axle {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 30px;
                        position: relative;
                     }

                     .v-axle::after {
                        content: '';
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        width: 65%;
                        height: 4px;
                        background: #e0e0e0;
                        z-index: 1;
                        border-radius: 2px;
                     }

                     .v-tyre {
                        width: 32px;
                        height: 55px;
                        background: #fff;
                        border: 2px solid #ddd;
                        border-radius: 6px;
                        z-index: 2;
                        position: relative;
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        cursor: pointer;
                        transition: all 0.3s;
                     }

                     .v-tyre-code {
                        font-size: 9px;
                        font-weight: 800;
                        color: #666;
                     }

                     .v-tyre.front {
                        border-top: 4px solid #ff9f43;
                     }

                     .v-tyre.rear {
                        border-top: 4px solid #28c76f;
                     }

                     .v-tyre.middle {
                        border-top: 4px solid #7367f0;
                     }

                     .v-tyre:hover {
                        transform: scale(1.15) translateY(-2px);
                        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
                        z-index: 10;
                     }

                     .v-group {
                        display: flex;
                        gap: 5px;
                     }

                     .v-spare-list {
                        display: flex;
                        justify-content: center;
                        gap: 15px;
                        margin-top: 20px;
                        padding-top: 20px;
                        border-top: 2px dashed #eee;
                     }

                     .v-tyre.spare {
                        width: 55px;
                        height: 32px;
                        border-top: none;
                        border-right: 4px solid #00cfe8;
                     }
                  </style>

                  <div class="v-chassis">
                     <div class="v-cabin">FRONT</div>

                     @php
                        $frontAxles = $configuration->details->where('axle_type', 'Front')->groupBy('axle_number');
                        $middleAxles = $configuration->details->where('axle_type', 'Middle')->groupBy('axle_number');
                        $rearAxles = $configuration->details->where('axle_type', 'Rear')->groupBy('axle_number');
                        $spareTyres = $configuration->details->where('is_spare', true);
                     @endphp

                     {{-- Front Axles --}}
                     @foreach ($frontAxles as $positions)
                        <div class="v-axle">
                           @php
                              $left = $positions->where('side', 'Left')->first();
                              $right = $positions->where('side', 'Right')->first();
                           @endphp
                           <div class="v-tyre front" title="{{ $left->position_name ?? '' }}">
                              <span class="v-tyre-code">{{ $left->position_code ?? '' }}</span>
                           </div>
                           <div class="v-tyre front" title="{{ $right->position_name ?? '' }}">
                              <span class="v-tyre-code">{{ $right->position_code ?? '' }}</span>
                           </div>
                        </div>
                     @endforeach

                     {{-- Middle Axles --}}
                     @foreach ($middleAxles as $positions)
                        <div class="v-axle">
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
                                 <div class="v-tyre middle" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
                                 <div class="v-tyre middle" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                        </div>
                     @endforeach

                     {{-- Rear Axles --}}
                     @foreach ($rearAxles as $positions)
                        <div class="v-axle">
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Left')->sortBy('display_order') as $p)
                                 <div class="v-tyre rear" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                           <div class="v-group">
                              @foreach ($positions->where('side', 'Right')->sortBy('display_order') as $p)
                                 <div class="v-tyre rear" title="{{ $p->position_name }}">
                                    <span class="v-tyre-code">{{ $p->position_code }}</span>
                                 </div>
                              @endforeach
                           </div>
                        </div>
                     @endforeach

                     {{-- Spares --}}
                     @if ($spareTyres->count() > 0)
                        <div class="v-spare-list">
                           @foreach ($spareTyres as $s)
                              <div class="v-tyre spare" title="{{ $s->position_name }}">
                                 <span class="v-tyre-code">{{ $s->position_code }}</span>
                              </div>
                           @endforeach
                        </div>
                     @endif
                  </div>

               </div>
            </div>
         </div>

         <div class="col-lg-4">
            <div class="card mb-4">
               <div class="card-header d-flex justify-content-between align-items-center">
                  <h6 class="mb-0">Ringkasan Konstruksi</h6>
                  <i class="ri-information-line text-muted"></i>
               </div>
               <div class="card-body">
                  <div class="d-flex align-items-center mb-3 p-3 bg-label-primary rounded">
                     <i class="ri-steering-2-line ri-2x me-3"></i>
                     <div>
                        <small class="d-block text-muted">Total Konfigurasi Ban</small>
                        <h4 class="mb-0 fw-bold">{{ $configuration->total_positions }} Titik</h4>
                     </div>
                  </div>

                  <div class="list-group list-group-flush">
                     <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ri-checkbox-blank-circle-fill text-warning me-2" style="font-size: 8px;"></i> As
                           Depan (Single)</span>
                        <span class="fw-bold">{{ $frontAxles->count() }} As ({{ $frontAxles->count() * 2 }} Ban)</span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ri-checkbox-blank-circle-fill text-success me-2" style="font-size: 8px;"></i> As
                           Tengah (Dual)</span>
                        <span class="fw-bold">{{ $middleAxles->count() }} As ({{ $middleAxles->count() * 4 }} Ban)</span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ri-checkbox-blank-circle-fill text-success me-2" style="font-size: 8px;"></i> As
                           Belakang (Dual)</span>
                        <span class="fw-bold">{{ $rearAxles->count() }} As ({{ $rearAxles->count() * 4 }} Ban)</span>
                     </div>
                     <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><i class="ri-checkbox-blank-circle-fill text-info me-2" style="font-size: 8px;"></i> Ban
                           Cadangan</span>
                        <span class="fw-bold">{{ $spareTyres->count() }} Ban</span>
                     </div>
                  </div>
               </div>
            </div>

            <div class="card">
               <div class="card-body">
                  <h6 class="mb-3">Legenda & Kode</h6>
                  <div class="legend-card p-3 border rounded bg-light">
                     <div class="legend-item">
                        <div class="legend-color bg-warning"></div>
                        <span><strong>F:</strong> Front (Depan)</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color bg-success"></div>
                        <span><strong>R:</strong> Rear (Belakang)</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color bg-info"></div>
                        <span><strong>S:</strong> Spare (Cadangan)</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color" style="background: #2d3748"></div>
                        <span><strong>L/R:</strong> Left/Right (Kiri/Kanan)</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color" style="background: #2d3748"></div>
                        <span><strong>I/O:</strong> Inner/Outer (Dalam/Luar)</span>
                     </div>
                  </div>

                  <div class="alert alert-info mt-3 mb-0 py-2">
                     <small><i class="ri-lightbulb-line me-1"></i> Arahkan kursor ke icon ban untuk melihat deskripsi
                        lengkap posisi.</small>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
@endsection
