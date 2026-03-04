@extends('layouts.guest')

@section('title', 'Terima Kasih - CPH Tyre Onboarding')

@section('page-style')
   <style>
      .success-bg {
         background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .success-card {
         background: white;
         border-radius: 20px;
         padding: 50px;
         width: 100%;
         max-width: 600px;
         box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
         text-align: center;
      }

      .success-icon {
         font-size: 5rem;
         color: #27ae60;
         margin-bottom: 20px;
      }

      .project-badge {
         background: #f1f2f6;
         color: #2f3542;
         padding: 10px 20px;
         border-radius: 50px;
         font-weight: 700;
         display: inline-block;
         margin-bottom: 30px;
      }

      .btn-action {
         background: #1a1a2e;
         color: white;
         padding: 12px 30px;
         border-radius: 10px;
         text-decoration: none;
         font-weight: 600;
         transition: all 0.3s;
      }

      .btn-action:hover {
         background: #e63946;
         color: white;
         transform: translateY(-2px);
      }
   </style>
@endsection

@section('content')
   <div class="success-bg">
      <div class="success-card">
         <div class="success-icon">
            <i class="icon-base ri ri-checkbox-circle-fill"></i>
         </div>
         <h2 class="mb-2">Data Berhasil Disimpan!</h2>
         <div class="project-badge">Project: {{ $project->customer_name }} ({{ $project->project_code }})</div>

         <p class="text-muted mb-5">
            Terima kasih telah mengisi kuesioner onboarding. Tim CPH Tyre akan segera memverifikasi data Anda.
            Progres pengisian Anda saat ini adalah <strong>{{ $project->progress_percent }}%</strong>.
         </p>

         <div class="alert alert-info border-0 mb-5 text-start">
            <h6 class="alert-heading fw-bold"><i class="icon-base ri ri-information-line me-1"></i> Apa langkah selanjutnya?
            </h6>
            <ul class="mb-0 small">
               <li>Tim kami akan menghubungi Anda via WhatsApp untuk validasi master data.</li>
               <li>Setelah validasi selesai, akun resmi Anda akan segera diterbitkan.</li>
               <li>Anda masih bisa memperbarui data kuesioner menggunakan kode yang sama sebelum project dinyatakan
                  "Go-Live".</li>
            </ul>
         </div>

         <div class="d-grid gap-2">
            <a href="{{ route('public.onboarding.show', $project->project_code) }}" class="btn btn-outline-secondary">
               <i class="icon-base ri ri-edit-line"></i> Edit / Lihat Kembali Data
            </a>
            <a href="{{ url('/') }}" class="btn-action">KEMBALI KE BERANDA</a>
         </div>
      </div>
   </div>
@endsection
