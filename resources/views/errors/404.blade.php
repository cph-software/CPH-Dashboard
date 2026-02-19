@extends('layouts.admin')

@section('title', 'Page Not Found')

@section('page-style')
   <style>
      .dev-container {
         min-height: 65vh;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .dev-card {
         text-align: center;
         max-width: 520px;
         padding: 3rem 2rem;
      }

      .dev-icon-wrapper {
         width: 120px;
         height: 120px;
         border-radius: 50%;
         background: linear-gradient(135deg, rgba(255, 62, 29, 0.08), rgba(255, 62, 29, 0.18));
         display: flex;
         align-items: center;
         justify-content: center;
         margin: 0 auto 2rem;
      }

      .dev-icon-wrapper i {
         font-size: 3rem;
         color: #ff3e1d;
      }

      .dev-title {
         font-size: 1.6rem;
         font-weight: 700;
         margin-bottom: 0.75rem;
         color: #566a7f;
      }

      .dev-subtitle {
         color: #a1acb8;
         font-size: 0.95rem;
         line-height: 1.7;
         margin-bottom: 2rem;
      }

      .dev-badge {
         display: inline-flex;
         align-items: center;
         gap: 6px;
         padding: 6px 16px;
         border-radius: 20px;
         background: rgba(255, 62, 29, 0.12);
         color: #ff3e1d;
         font-weight: 600;
         font-size: 0.8rem;
         margin-bottom: 1.5rem;
      }
   </style>
@endsection

@section('content')
   <div class="dev-container">
      <div class="dev-card">
         <div class="dev-badge">
            <i class="icon-base ri ri-error-warning-line"></i>
            404 Not Found
         </div>

         <div class="dev-icon-wrapper">
            <i class="icon-base ri ri-compass-3-line"></i>
         </div>

         <h2 class="dev-title">Halaman Tidak Ditemukan</h2>
         <p class="dev-subtitle">
            Maaf, halaman yang Anda cari tidak ditemukan atau mungkin sedang dalam tahap pengembangan.
            Silakan periksa kembali URL atau hubungi administrator.
         </p>

         <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4 waves-effect waves-light">
            <i class="icon-base ri ri-home-4-line me-1"></i> Kembali ke Dashboard
         </a>
      </div>
   </div>
@endsection
