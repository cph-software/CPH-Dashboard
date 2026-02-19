@extends('layouts.admin')

@section('title', 'Dalam Pengembangan')

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
         background: linear-gradient(135deg, rgba(105, 108, 255, 0.08), rgba(105, 108, 255, 0.18));
         display: flex;
         align-items: center;
         justify-content: center;
         margin: 0 auto 2rem;
         animation: pulse-ring 2.5s ease-in-out infinite;
      }

      .dev-icon-wrapper i {
         font-size: 3rem;
         color: #696cff;
      }

      @keyframes pulse-ring {
         0% {
            box-shadow: 0 0 0 0 rgba(105, 108, 255, 0.25);
         }

         50% {
            box-shadow: 0 0 0 18px rgba(105, 108, 255, 0);
         }

         100% {
            box-shadow: 0 0 0 0 rgba(105, 108, 255, 0);
         }
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

      .dev-progress {
         height: 6px;
         border-radius: 3px;
         background: #eceef1;
         overflow: hidden;
         margin-bottom: 0.75rem;
         max-width: 280px;
         margin-left: auto;
         margin-right: auto;
      }

      .dev-progress-bar {
         height: 100%;
         border-radius: 3px;
         background: linear-gradient(90deg, #696cff, #8592ff);
         animation: progress-anim 2.5s ease-in-out infinite;
         width: 60%;
      }

      @keyframes progress-anim {
         0% {
            width: 15%;
            margin-left: 0;
         }

         50% {
            width: 60%;
            margin-left: 20%;
         }

         100% {
            width: 15%;
            margin-left: 85%;
         }
      }

      .dev-badge {
         display: inline-flex;
         align-items: center;
         gap: 6px;
         padding: 6px 16px;
         border-radius: 20px;
         background: rgba(113, 221, 55, 0.12);
         color: #71dd37;
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
            <i class="icon-base ri ri-code-s-slash-line"></i>
            In Development
         </div>

         <div class="dev-icon-wrapper">
            <i class="icon-base ri ri-tools-line"></i>
         </div>

         <h2 class="dev-title">Fitur Sedang Dikembangkan</h2>
         <p class="dev-subtitle">
            Tim kami sedang bekerja keras untuk menyelesaikan fitur
            <strong>{{ $featureName ?? 'ini' }}</strong>.
            Fitur akan segera tersedia dalam waktu dekat.
         </p>

         <div class="dev-progress">
            <div class="dev-progress-bar"></div>
         </div>
         <small class="text-muted d-block mb-4">Proses pengembangan sedang berjalan...</small>

         <a href="{{ url()->previous() != url()->current() ? url()->previous() : url('/dashboard') }}"
            class="btn btn-primary px-4 waves-effect waves-light">
            <i class="icon-base ri ri-arrow-left-line me-1"></i> Kembali
         </a>
      </div>
   </div>
@endsection
