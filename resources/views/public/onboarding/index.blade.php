@extends('layouts.guest')

@section('title', 'Onboarding — CPH Tyre')

@section('page-style')
   <style>
      .onboarding-bg {
         background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
         min-height: 100vh;
         display: flex;
         align-items: center;
         justify-content: center;
      }

      .onboarding-card {
         background: rgba(255, 255, 255, 0.05);
         backdrop-filter: blur(10px);
         border: 1px solid rgba(255, 255, 255, 0.1);
         border-radius: 20px;
         padding: 40px;
         width: 100%;
         max-width: 500px;
         box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      }

      .brand-logo {
         color: #e63946;
         font-size: 2rem;
         font-weight: 800;
         text-align: center;
         margin-bottom: 30px;
         text-transform: uppercase;
         letter-spacing: 2px;
      }

      .form-control-custom {
         background: rgba(255, 255, 255, 0.1);
         border: 1px solid rgba(255, 255, 255, 0.2);
         color: white;
         padding: 15px;
         border-radius: 10px;
         font-size: 1.1rem;
         text-align: center;
         letter-spacing: 5px;
         text-transform: uppercase;
      }

      .form-control-custom:focus {
         background: rgba(255, 255, 255, 0.15);
         border-color: #e63946;
         color: white;
         box-shadow: none;
      }

      .btn-onboarding {
         background: #e63946;
         border: none;
         padding: 15px;
         border-radius: 10px;
         font-weight: 700;
         width: 100%;
         margin-top: 20px;
         transition: all 0.3s;
      }

      .btn-onboarding:hover {
         background: #d62828;
         transform: translateY(-2px);
      }

      .helper-text {
         color: rgba(255, 255, 255, 0.5);
         text-align: center;
         margin-top: 20px;
         font-size: 0.9rem;
      }
   </style>
@endsection

@section('content')
   <div class="onboarding-bg">
      <div class="onboarding-card">
         <div class="brand-logo">CPH TYRE</div>

         <h4 class="text-white text-center mb-4">Onboarding Portal</h4>
         <p class="text-white-50 text-center mb-5">Selamat datang! Silakan masukkan Kode Project yang telah diberikan untuk
            memulai proses onboarding.</p>

         <form action="{{ route('public.onboarding.verify') }}" method="POST">
            @csrf
            <div class="mb-3">
               <input type="text" name="project_code"
                  class="form-control form-control-custom @error('project_code') is-invalid @enderror"
                  placeholder="XXXX-XXXX-XXXX" required autocomplete="off">
               @error('project_code')
                  <div class="invalid-feedback text-center mt-2">{{ $message }}</div>
               @enderror
            </div>

            <button type="submit" class="btn btn-onboarding text-white">VALIDASI KODE</button>
         </form>

         <div class="helper-text">
            Butuh bantuan? Hubungi <a href="#" class="text-danger">Support CPH</a>
         </div>
      </div>
   </div>
@endsection
