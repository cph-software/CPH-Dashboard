@extends('layouts.guest')

@section('title', 'Onboarding Form - ' . $project->customer_name)

@section('page-style')
   <style>
      .onboarding-header {
         background: #1a1a2e;
         color: white;
         padding: 40px 0;
         margin-bottom: 40px;
         border-bottom: 3px solid #e63946;
      }

      .form-section {
         background: white;
         padding: 30px;
         border-radius: 15px;
         box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
         margin-bottom: 30px;
         border: 1px solid #f1f2f6;
      }

      .section-title {
         border-left: 5px solid #e63946;
         padding-left: 15px;
         margin-bottom: 25px;
         color: #1a1a2e;
         font-weight: 700;
      }

      .btn-submit {
         background: #e63946;
         border: none;
         padding: 15px 40px;
         border-radius: 10px;
         font-weight: 700;
         transition: all 0.3s;
      }

      .btn-submit:hover {
         background: #d62828;
         transform: translateY(-2px);
      }
   </style>
@endsection

@section('content')
   <div class="onboarding-header">
      <div class="container text-center">
         <h1>CPH TYRE ONBOARDING</h1>
         <p class="lead">Selamat Datang, <strong>{{ $project->customer_name }}</strong></p>
         <span class="badge bg-danger">Project Code: {{ $project->project_code }}</span>
      </div>
   </div>

   <div class="container mb-5">
      <div class="row justify-content-center">
         <div class="col-lg-9">
            <form action="{{ route('public.onboarding.save', $project->project_code) }}" method="POST">
               @csrf

               <!-- I. PROFIL PERUSAHAAN & SITE -->
               <div class="form-section">
                  <h4 class="section-title">I. PROFIL PERUSAHAAN & SITE</h4>
                  <div class="row">
                     <div class="col-md-12 mb-3">
                        <label class="form-label">Nama Lengkap Perusahaan</label>
                        <input type="text" name="answers[company_name]" class="form-control"
                           value="{{ $project->customer_name }}" required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Site / Proyek</label>
                        <input type="text" name="answers[site_name]" class="form-control"
                           placeholder="Contoh: Site Kaltim" required>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Waktu Operasional</label>
                        <input type="text" name="answers[op_hours]" class="form-control"
                           placeholder="Contoh: 24/7 atau 2 Shift">
                     </div>
                     <div class="col-md-12 mb-3">
                        <label class="form-label">Alamat Lokasi Site</label>
                        <textarea name="answers[site_address]" class="form-control" rows="2"></textarea>
                     </div>
                  </div>
               </div>

               <!-- II. KONTAK PERSON (PIC) -->
               <div class="form-section">
                  <h4 class="section-title">II. KONTAK PERSON IMPLEMENTASI (PIC)</h4>
                  <p class="text-muted small mb-4">Email akan digunakan sebagai username login. No. WhatsApp untuk
                     notifikasi sistem.</p>

                  @for ($i = 0; $i < 3; $i++)
                     <div class="pic-entry mb-4 {{ $i > 0 ? 'pt-4 border-top' : '' }}">
                        <label class="form-label fw-bold">PIC {{ $i + 1 }}
                           {{ $i > 0 ? '(Opsional)' : '(Utama/Fleet Manager)' }}</label>
                        <div class="row">
                           <div class="col-md-4 mb-3">
                              <input type="text" name="pics[{{ $i }}][name]" class="form-control"
                                 placeholder="Nama Lengkap" {{ $i == 0 ? 'required' : '' }}>
                           </div>
                           <div class="col-md-4 mb-3">
                              <input type="email" name="pics[{{ $i }}][email]" class="form-control"
                                 placeholder="Email" {{ $i == 0 ? 'required' : '' }}>
                           </div>
                           <div class="col-md-4 mb-3">
                              <input type="text" name="pics[{{ $i }}][whatsapp]" class="form-control"
                                 placeholder="No. WhatsApp" {{ $i == 0 ? 'required' : '' }}>
                           </div>
                        </div>
                     </div>
                  @endfor
               </div>

               <!-- III. TEKNIS & HARDWARE -->
               <div class="form-section">
                  <h4 class="section-title">III. TEKNIS & HARDWARE</h4>
                  <div class="row">
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Metode Input Dominan</label>
                        <select name="answers[input_method]" class="form-select">
                           <option value="PC">PC / Desktop (Office)</option>
                           <option value="Tablet">Tablet (Workshop)</option>
                           <option value="Mobile">Mobile App (Field)</option>
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Koneksi Internet di Site</label>
                        <select name="answers[internet]" class="form-select">
                           <option value="Stabil">Stabil / Fiber</option>
                           <option value="Terbatas">Terbatas / GSM Only</option>
                           <option value="None">Tidak Ada Internet (Offline)</option>
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Metode Penandaan Ban</label>
                        <select name="answers[marking_method]" class="form-select">
                           <option value="Barcode">Barcode / QR Code</option>
                           <option value="Branding">Branding (Bakar)</option>
                           <option value="Painting">Painting (Cat)</option>
                        </select>
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Total Populasi Unit Kendaraan</label>
                        <input type="number" name="answers[vehicle_count]" class="form-control" placeholder="Contoh: 50">
                     </div>
                  </div>
               </div>

               <!-- IV. MANAJEMEN DATA -->
               <div class="form-section">
                  <h4 class="section-title">IV. MANAJEMEN DATA & TARGET</h4>
                  <div class="row">
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Sistem Pencatatan Saat Ini</label>
                        <input type="text" name="answers[current_system]" class="form-control"
                           placeholder="Contoh: Excel / SAP / Manual">
                     </div>
                     <div class="col-md-6 mb-3">
                        <label class="form-label">Target Go-Live</label>
                        <input type="date" name="answers[target_date]" class="form-control">
                     </div>
                     <div class="col-md-12 mb-3">
                        <label class="form-label">Brand Ban Terbanyak Digunakan</label>
                        <input type="text" name="answers[major_brand]" class="form-control"
                           placeholder="Contoh: Bridgestone, Michelin, Gajah Tunggal">
                     </div>
                  </div>
               </div>

               <div class="text-center mt-5">
                  <button type="submit" class="btn btn-submit text-white px-5 shadow">SIMPAN & SUBMIT DATA
                     ONBOARDING</button>
                  <p class="text-muted mt-3"><i class="icon-base ri ri-lock-line"></i> Data Anda aman dan hanya akan
                     digunakan untuk
                     keperluan implementasi sistem.</p>
               </div>
            </form>
         </div>
      </div>
   </div>
@endsection
