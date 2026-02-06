@extends('layouts.guest')

@section('title', 'Login')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endsection

@section('page-style')
<link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/css/pages/page-auth.css') }}" />
<style>
  .authentication-wrapper {
    overflow: hidden;
  }
  .authentication-inner {
    overflow: hidden;
  }
  /* Fix mask positioning when illustration is on the right */
  .authentication-wrapper.authentication-cover .authentication-image {
    inset-inline-start: unset;
    inset-inline-end: 0;
  }
</style>
@endsection

@section('content')
<div class="authentication-wrapper authentication-cover">
  <!-- Logo -->
  <a href="/" class="auth-cover-brand d-flex align-items-center gap-2">
    <span class="app-brand-logo demo">
      <span class="text-primary">
        <svg width="32" height="18" viewBox="0 0 38 20" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M30.0944 2.22569C29.0511 0.444187 26.7508 -0.172113 24.9566 0.849138C23.1623 1.87039 22.5536 4.14247 23.5969 5.92397L30.5368 17.7743C31.5801 19.5558 33.8804 20.1721 35.6746 19.1509C37.4689 18.1296 38.0776 15.8575 37.0343 14.076L30.0944 2.22569Z" fill="currentColor" />
          <path d="M30.171 2.22569C29.1277 0.444187 26.8274 -0.172113 25.0332 0.849138C23.2389 1.87039 22.6302 4.14247 23.6735 5.92397L30.6134 17.7743C31.6567 19.5558 33.957 20.1721 35.7512 19.1509C37.5455 18.1296 38.1542 15.8575 37.1109 14.076L30.171 2.22569Z" fill="url(#paint0_linear_2989_100980)" fill-opacity="0.4" />
          <path d="M22.9676 2.22569C24.0109 0.444187 26.3112 -0.172113 28.1054 0.849138C29.8996 1.87039 30.5084 4.14247 29.4651 5.92397L22.5251 17.7743C21.4818 19.5558 19.1816 20.1721 17.3873 19.1509C15.5931 18.1296 14.9843 15.8575 16.0276 14.076L22.9676 2.22569Z" fill="currentColor" />
          <path d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z" fill="currentColor" />
          <path d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z" fill="url(#paint1_linear_2989_100980)" fill-opacity="0.4" />
          <path d="M7.82901 2.22569C8.87231 0.444187 11.1726 -0.172113 12.9668 0.849138C14.7611 1.87039 15.3698 4.14247 14.3265 5.92397L7.38656 17.7743C6.34325 19.5558 4.04298 20.1721 2.24875 19.1509C0.454514 18.1296 -0.154233 15.8575 0.88907 14.076L7.82901 2.22569Z" fill="currentColor" />
          <defs>
            <linearGradient id="paint0_linear_2989_100980" x1="5.36642" y1="0.849138" x2="10.532" y2="24.104" gradientUnits="userSpaceOnUse">
              <stop offset="0" stop-opacity="1" />
              <stop offset="1" stop-opacity="0" />
            </linearGradient>
            <linearGradient id="paint1_linear_2989_100980" x1="5.19475" y1="0.849139" x2="10.3357" y2="24.1155" gradientUnits="userSpaceOnUse">
              <stop offset="0" stop-opacity="1" />
              <stop offset="1" stop-opacity="0" />
            </linearGradient>
          </defs>
        </svg>
      </span>
    </span>
    <span class="app-brand-text demo text-heading fw-semibold">CPH TYRE</span>
  </a>
  <!-- /Logo -->
  <div class="authentication-inner row m-0">
    <!-- Login (Left) -->
    <div class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
      <div class="w-px-400 mx-auto pt-12 pt-lg-0">
        <h4 class="mb-1">Welcome to CPH Tyre! 👋</h4>
        <p class="mb-5">Please sign-in to your account and start the adventure</p>

        @if(session('fail'))
        <div class="alert alert-danger">
            {{ session('fail') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form id="formAuthentication" class="mb-5" action="{{ route('login') }}" method="POST">
          @csrf
          <input type="hidden" name="login_type" value="cph">
          <div class="form-floating form-floating-outline mb-5 form-control-validation">
            <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Enter your ID Karyawan" autofocus value="{{ old('employee_id') }}" />
            <label for="employee_id">ID Karyawan</label>
          </div>
          <div class="mb-5">
            <div class="form-password-toggle form-control-validation">
              <div class="input-group input-group-merge">
                <div class="form-floating form-floating-outline">
                  <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" aria-describedby="password" />
                  <label for="password">Password</label>
                </div>
                <span class="input-group-text cursor-pointer"><i class="icon-base ri ri-eye-off-line icon-20px"></i></span>
              </div>
            </div>
          </div>
          <div class="mb-5 d-flex justify-content-between mt-5">
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
              <label class="form-check-label" for="remember-me"> Remember Me </label>
            </div>
          </div>
          <button class="btn btn-primary d-grid w-100" type="submit">Sign in</button>
        </form>
      </div>
    </div>
    <!-- /Login -->

    <!-- Illustration (Right) -->
    <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
      <img src="{{ asset('template/full-version/assets/img/illustrations/auth-login-illustration-light.png') }}" class="auth-cover-illustration w-100" alt="auth-illustration" data-app-light-img="illustrations/auth-login-illustration-light.png" data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
      <img alt="mask" src="{{ asset('template/full-version/assets/img/illustrations/auth-basic-login-mask-light.png') }}" class="authentication-image d-none d-lg-block" data-app-light-img="illustrations/auth-basic-login-mask-light.png" data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
    </div>
    <!-- /Illustration -->
  </div>
</div>
@endsection

@section('vendor-script')
<script src="{{ asset('template/full-version/assets/vendor/libs/@form-validation/popular.js') }}"></script>
<script src="{{ asset('template/full-version/assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
<script src="{{ asset('template/full-version/assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endsection

@section('page-script')
<script src="{{ asset('template/full-version/assets/js/pages-auth.js') }}"></script>
@endsection