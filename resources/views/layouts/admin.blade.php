<!doctype html>
<html lang="en" class="layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr" data-skin="default"
   data-bs-theme="light" data-assets-path="{{ asset('template/full-version/assets') }}/"
   data-template="vertical-menu-template">

<head>
   <meta charset="utf-8" />
   <meta name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
   <title>@yield('title') | CPH Dashboard</title>

   <!-- Favicon -->
   <link rel="icon" type="image/x-icon"
      href="{{ asset('template/full-version/assets/img/favicon/favicon.ico') }}" />
   <meta name="csrf-token" content="{{ csrf_token() }}">

   <!-- Fonts -->
   <link rel="preconnect" href="https://fonts.googleapis.com" />
   <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
   <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&ampdisplay=swap"
      rel="stylesheet" />

   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/fonts/iconify-icons.css') }}" />

   <!-- Core CSS -->
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/node-waves/node-waves.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/pickr/pickr-themes.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/css/core.css') }}" />
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/css/demo.css') }}" />

   <!-- Vendors CSS -->
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
   @yield('vendor-style')

   <!-- Page CSS -->
   @yield('page-style')

   <!-- Helpers -->
   <script src="{{ asset('template/full-version/assets/vendor/js/helpers.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/pickr/pickr.js') }}"></script>
   {{--
    <script src="{{ asset('template/full-version/assets/vendor/js/template-customizer.js') }}"></script> --}}
   <script src="{{ asset('template/full-version/assets/js/config.js') }}"></script>
</head>

<body>
   <!-- Layout wrapper -->
   <div class="layout-wrapper layout-content-navbar">
      <div class="layout-container">
         <!-- Menu -->
         @include('layouts.sections.menu')
         <!-- / Menu -->

         <!-- Layout container -->
         <div class="layout-page">
            <!-- Navbar -->
            @include('layouts.sections.navbar')
            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
               <!-- Content -->
               <div class="container-xxl flex-grow-1 container-p-y">
                  @yield('content')
               </div>
               <!-- / Content -->

               <!-- Footer -->
               @include('layouts.sections.footer')
               <!-- / Footer -->

               <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
         </div>
         <!-- / Layout page -->
      </div>

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
      <!-- Drag Target Area To SlideIn Menu On Small Screens -->
      <div class="drag-target"></div>
   </div>
   <!-- / Layout wrapper -->

   <!-- Core JS -->
   <script src="{{ asset('template/full-version/assets/vendor/libs/jquery/jquery.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/popper/popper.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/js/bootstrap.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/hammer/hammer.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/libs/i18n/i18n.js') }}"></script>
   <script src="{{ asset('template/full-version/assets/vendor/js/menu.js') }}"></script>

   <!-- Main JS -->
   <script src="{{ asset('template/full-version/assets/js/main.js') }}"></script>

   @yield('vendor-script')
   @yield('page-script')
</body>

</html>
