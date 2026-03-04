@extends('layouts.admin')

@section('title', 'Project Detail: ' . $project->customer_name)

@section('content')
   <div class="row">
      <!-- Header Stats -->
      <div class="col-12 mb-4">
         <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-0">
               <div class="row g-0">
                  <div class="col-md-8 p-4 border-end">
                     <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-lg me-3">
                           <span
                              class="avatar-initial rounded bg-label-danger fs-4">{{ substr($project->customer_name, 0, 1) }}</span>
                        </div>
                        <div>
                           <h4 class="mb-1 fw-bold">{{ $project->customer_name }}</h4>
                           <div class="d-flex align-items-center gap-2">
                              <span class="badge bg-label-dark">{{ $project->project_code }}</span>
                              <span class="text-muted small"><i class="icon-base ri ri-history-line"></i> Terakhir aktif:
                                 {{ $project->last_interaction_at ? $project->last_interaction_at->diffForHumans() : 'Belum ada interaksi' }}</span>
                           </div>
                        </div>
                     </div>
                     <div class="row mt-4">
                        <div class="col-4">
                           <small class="text-muted d-block">Current Status</small>
                           <span class="badge bg-label-info mt-1">{{ $project->status }}</span>
                        </div>
                        <div class="col-8">
                           <small class="text-muted d-block">Onboarding Readiness
                              ({{ $project->progress_percent }}%)</small>
                           <div class="progress mt-2" style="height: 8px;">
                              <div class="progress-bar bg-success" role="progressbar"
                                 style="width: {{ $project->progress_percent }}%"></div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="col-md-4 p-4 bg-light d-flex flex-column justify-content-center">
                     <small class="text-muted mb-2">Internal Action</small>
                     <form id="finalize-form" action="{{ route('onboarding-projects.generate-accounts', $project->id) }}"
                        method="POST">
                        @csrf
                        <button type="button" class="btn btn-success w-100 py-2 mb-2"
                           {{ $project->status === 'Go-Live' ? 'disabled' : '' }} onclick="confirmFinalize()">
                           <i class="icon-base ri ri-user-add-line me-1"></i>
                           {{ $project->status === 'Go-Live' ? 'Akun Berhasil Dibuat' : 'Finalize & Generate Accounts' }}
                        </button>
                     </form>
                     <p class="small text-muted text-center mb-0">Hanya lakukan ini jika data kuesioner & PIC sudah valid.
                     </p>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Questionnaire Sections -->
      <div class="col-md-8">
         <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom bg-white py-3">
               <h5 class="card-title mb-0 fw-bold">I. Questionnaire Results (Sheet 5)</h5>
            </div>
            <div class="card-body py-4">
               @if ($project->questionnaire_answers)
                  <div class="row g-4">
                     @foreach ([
            'company_name' => 'Company Full Name',
            'site_name' => 'Site Name',
           'op_hours' => 'Operational Hours',
           'site_address' => 'Site Address',
           'vehicle_count' => 'Vehicle Population',
           'input_method' => 'Preferred Input Method',
           'internet' => 'Internet Availability',
           'marking_method' => 'Tire Marking Method',
           'current_system' => 'Existing System',
           'target_date' => 'Target Go-Live',
           'major_brand' => 'Dominant Brand',
       ] as $key => $label)
                        <div class="col-md-6 border-bottom pb-2">
                           <small class="text-muted d-block">{{ $label }}</small>
                           <span class="fw-bold text-dark">{{ $project->questionnaire_answers[$key] ?? 'N/A' }}</span>
                        </div>
                     @endforeach
                  </div>
               @else
                  <div class="text-center py-5">
                     <i class="icon-base ri ri-file-warning-line ri-3x text-light mb-2"></i>
                     <p class="text-muted">Data kuesioner belum diisi oleh kustomer.</p>
                  </div>
               @endif
            </div>
         </div>
      </div>

      <!-- PIC Section -->
      <div class="col-md-4">
         <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-bottom bg-white py-3">
               <h5 class="card-title mb-0 fw-bold">II. Registered PICs</h5>
            </div>
            <div class="card-body p-0">
               <ul class="list-group list-group-flush">
                  @forelse($project->pics_data ?? [] as $pic)
                     @if (!empty($pic['name']))
                        <li class="list-group-item p-3">
                           <div class="d-flex align-items-center">
                              <div class="avatar avatar-sm me-3">
                                 <span
                                    class="avatar-initial rounded-circle bg-label-primary">{{ substr($pic['name'], 0, 1) }}</span>
                              </div>
                              <div>
                                 <h6 class="mb-0 fw-bold">{{ $pic['name'] }}</h6>
                                 <small class="text-muted d-block">{{ $pic['email'] }}</small>
                                 <a href="https://wa.me/{{ $pic['whatsapp'] }}" class="small text-success fw-bold"
                                    target="_blank"><i class="icon-base ri ri-whatsapp-line"></i>
                                    {{ $pic['whatsapp'] }}</a>
                              </div>
                           </div>
                        </li>
                     @endif
                  @empty
                     <li class="list-group-item py-5 text-center">
                        <p class="text-muted mb-0">Belum ada PIC terdaftar.</p>
                     </li>
                  @endforelse
               </ul>
            </div>
         </div>

         <div class="card bg-label-secondary border-0 text-center p-4">
            <small class="d-block mb-3">Internal Notes</small>
            <form action="{{ route('onboarding-projects.update', $project->id) }}" method="POST">
               @csrf
               @method('PUT')
               <textarea name="internal_notes" class="form-control mb-3" rows="3" placeholder="Tambahkan catatan internal...">{{ $project->internal_notes }}</textarea>
               <button type="submit" class="btn btn-secondary btn-sm w-100">Update Note</button>
            </form>
         </div>
      </div>
   </div>

   <div class="mt-4">
      <a href="{{ route('onboarding-projects.index') }}" class="btn btn-label-dark">
         <i class="icon-base ri ri-arrow-left-line"></i> Kembali ke List
      </a>
   </div>
   @section('page-script')
      <script>
         function confirmFinalize() {
            Swal.fire({
               title: 'Generate Akun Sekarang?',
               text: "Sistem akan otomatis membuat Company dan User Fleet. Lanjutkan?",
               icon: 'question',
               showCancelButton: true,
               confirmButtonColor: '#27ae60',
               cancelButtonColor: '#30336b',
               confirmButtonText: 'Ya, Generate!',
               cancelButtonText: 'Batal'
            }).then((result) => {
               if (result.isConfirmed) {
                  document.getElementById('finalize-form').submit();
               }
            });
         }
      </script>
   @endsection
@endsection
