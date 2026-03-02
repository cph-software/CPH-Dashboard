@extends('layouts.admin')

@section('title', 'Onboarding Manager')

@section('content')
   <div class="card shadow-sm border-0">
      <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
         <h5 class="mb-0 fw-bold"><i class="icon-base ri ri-rocket-line me-2 text-danger"></i>Onboarding Projects</h5>
         <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="icon-base ri ri-add-line me-1"></i> Mulai Onboarding Baru
         </button>
      </div>
      <div class="table-responsive text-nowrap">
         <table class="table table-hover align-middle">
            <thead class="bg-light">
               <tr>
                  <th>Customer / Site</th>
                  <th>Project Code</th>
                  <th class="text-center">Status</th>
                  <th width="150px">Completion %</th>
                  <th>Pic Internal</th>
                  <th class="text-end">Actions</th>
               </tr>
            </thead>
            <tbody class="table-border-bottom-0">
               @forelse($projects as $project)
                  <tr>
                     <td>
                        <div class="d-flex flex-column">
                           <span class="fw-bold text-dark">{{ $project->customer_name }}</span>
                           <small class="text-muted">{{ $project->site_name ?? 'Site Belum Diisi' }}</small>
                        </div>
                     </td>
                     <td>
                        <div class="input-group input-group-sm" style="max-width: 180px;">
                           <input type="text" class="form-control bg-light border-0"
                              value="{{ $project->project_code }}" id="code-{{ $project->id }}" readonly>
                           <button class="btn btn-outline-secondary border-0" type="button"
                              onclick="copyToClipboard('{{ $project->project_code }}', 'code-{{ $project->id }}')">
                              <i class="icon-base ri ri-file-copy-line"></i>
                           </button>
                        </div>
                     </td>
                     <td class="text-center">
                        @php
                           $badgeClass =
                               [
                                   'Prospect' => 'bg-label-secondary',
                                   'Data Collection' => 'bg-label-info',
                                   'Validation' => 'bg-label-warning',
                                   'Training' => 'bg-label-primary',
                                   'Go-Live' => 'bg-label-success',
                               ][$project->status] ?? 'bg-label-secondary';
                        @endphp
                        <span class="badge rounded-pill {{ $badgeClass }}">{{ $project->status }}</span>
                     </td>
                     <td>
                        <div class="d-flex align-items-center gap-2">
                           <div class="progress w-100" style="height: 6px;">
                              <div class="progress-bar rounded" role="progressbar"
                                 style="width: {{ $project->progress_percent }}%;"
                                 aria-valuenow="{{ $project->progress_percent }}" aria-valuemin="0" aria-valuemax="100">
                              </div>
                           </div>
                           <small class="fw-bold">{{ $project->progress_percent }}%</small>
                        </div>
                     </td>
                     <td>
                        <div class="d-flex align-items-center">
                           <div class="avatar avatar-xs me-2">
                              <span
                                 class="avatar-initial rounded-circle bg-label-dark text-uppercase small">{{ substr($project->internalPic->name ?? '?', 0, 1) }}</span>
                           </div>
                           <span class="small">{{ $project->internalPic->name ?? '-' }}</span>
                        </div>
                     </td>
                     <td class="text-end">
                        <div class="d-flex justify-content-end gap-1">
                           <a href="{{ route('onboarding-projects.show', $project->id) }}"
                              class="btn btn-icon btn-label-primary btn-sm" title="View Detail">
                              <i class="icon-base ri ri-eye-line"></i>
                           </a>
                           <button type="button" class="btn btn-icon btn-label-success btn-sm"
                              title="Copy Onboarding Link"
                              onclick="copyToClipboard('{{ route('public.onboarding.show', $project->project_code) }}', null, 'Link berhasil disalin!')">
                              <i class="icon-base ri ri-link"></i>
                           </button>
                           <div class="dropdown">
                              <button type="button"
                                 class="btn btn-icon btn-label-secondary btn-sm p-0 dropdown-toggle hide-arrow"
                                 data-bs-toggle="dropdown">
                                 <i class="icon-base ri ri-more-2-fill"></i>
                              </button>
                              <div class="dropdown-menu dropdown-menu-end">
                                 <a class="dropdown-item"
                                    href="https://wa.me/{{ $project->pics_data[0]['whatsapp'] ?? '' }}?text=Halo%20{{ $project->customer_name }},%20silakan%20lengkapi%20data%20onboarding%20Anda%20di:%20{{ route('public.onboarding.show', $project->project_code) }}"
                                    target="_blank">
                                    <i class="icon-base ri ri-whatsapp-line me-1 text-success"></i> Share via WhatsApp
                                 </a>
                                 <div class="dropdown-divider"></div>
                                 <form action="{{ route('onboarding-projects.destroy', $project->id) }}" method="POST"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                       <i class="icon-base ri ri-delete-bin-line me-1"></i> Hapus Project
                                    </button>
                                 </form>
                              </div>
                           </div>
                        </div>
                     </td>
                  </tr>
               @empty
                  <tr>
                     <td colspan="6" class="text-center py-5">
                        <div class="text-muted">
                           <i class="icon-base ri ri-folder-open-line ri-3x mb-2 d-block"></i>
                           Belum ada project onboarding yang sedang berjalan.
                        </div>
                     </td>
                  </tr>
               @endforelse
            </tbody>
         </table>
      </div>
   </div>

   <!-- Modal Add Project -->
   <div class="modal fade" id="addProjectModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header border-bottom">
               <h5 class="modal-title fw-bold">🚀 Onboarding Customer Baru</h5>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('onboarding-projects.store') }}" method="POST">
               @csrf
               <div class="modal-body py-4">
                  <div class="mb-3">
                     <label class="form-label fw-bold">Nama / Alias Perusahaan Customer</label>
                     <input type="text" name="customer_name" class="form-control"
                        placeholder="Contoh: PT. UNITED TRACTORS" required autocomplete="off">
                     <div class="form-text mt-2 small">Sistem akan secara otomatis men-generate <strong>Kode
                           Project</strong> setelah Anda menyimpan.</div>
                  </div>
               </div>
               <div class="modal-footer bg-light border-0">
                  <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                  <button type="submit" class="btn btn-danger px-4">Generate & Mulai</button>
               </div>
            </form>
         </div>
      </div>
   </div>

   <script>
      function copyToClipboard(text, inputId, successMsg = 'Kode disalin!') {
         const el = document.createElement('textarea');
         el.value = text;
         document.body.appendChild(el);
         el.select();
         document.execCommand('copy');
         document.body.removeChild(el);

         // UI feedback would be nice here using Toastr or Alert
         alert(successMsg);
      }
   </script>
@endsection
