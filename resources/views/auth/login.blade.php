@extends('layouts.guest')

@section('title', 'Login')

@section('vendor-style')
   <link rel="stylesheet"
      href="{{ asset('template/full-version/assets/vendor/libs/@form-validation/form-validation.css') }}" />
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
                  <path
                     d="M30.0944 2.22569C29.0511 0.444187 26.7508 -0.172113 24.9566 0.849138C23.1623 1.87039 22.5536 4.14247 23.5969 5.92397L30.5368 17.7743C31.5801 19.5558 33.8804 20.1721 35.6746 19.1509C37.4689 18.1296 38.0776 15.8575 37.0343 14.076L30.0944 2.22569Z"
                     fill="currentColor" />
                  <path
                     d="M30.171 2.22569C29.1277 0.444187 26.8274 -0.172113 25.0332 0.849138C23.2389 1.87039 22.6302 4.14247 23.6735 5.92397L30.6134 17.7743C31.6567 19.5558 33.957 20.1721 35.7512 19.1509C37.5455 18.1296 38.1542 15.8575 37.1109 14.076L30.171 2.22569Z"
                     fill="url(#paint0_linear_2989_100980)" fill-opacity="0.4" />
                  <path
                     d="M22.9676 2.22569C24.0109 0.444187 26.3112 -0.172113 28.1054 0.849138C29.8996 1.87039 30.5084 4.14247 29.4651 5.92397L22.5251 17.7743C21.4818 19.5558 19.1816 20.1721 17.3873 19.1509C15.5931 18.1296 14.9843 15.8575 16.0276 14.076L22.9676 2.22569Z"
                     fill="currentColor" />
                  <path
                     d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                     fill="currentColor" />
                  <path
                     d="M14.9558 2.22569C13.9125 0.444187 11.6122 -0.172113 9.818 0.849138C8.02377 1.87039 7.41502 4.14247 8.45833 5.92397L15.3983 17.7743C16.4416 19.5558 18.7418 20.1721 20.5361 19.1509C22.3303 18.1296 22.9391 15.8575 21.8958 14.076L14.9558 2.22569Z"
                     fill="url(#paint1_linear_2989_100980)" fill-opacity="0.4" />
                  <path
                     d="M7.82901 2.22569C8.87231 0.444187 11.1726 -0.172113 12.9668 0.849138C14.7611 1.87039 15.3698 4.14247 14.3265 5.92397L7.38656 17.7743C6.34325 19.5558 4.04298 20.1721 2.24875 19.1509C0.454514 18.1296 -0.154233 15.8575 0.88907 14.076L7.82901 2.22569Z"
                     fill="currentColor" />
                  <defs>
                     <linearGradient id="paint0_linear_2989_100980" x1="5.36642" y1="0.849138" x2="10.532"
                        y2="24.104" gradientUnits="userSpaceOnUse">
                        <stop offset="0" stop-opacity="1" />
                        <stop offset="1" stop-opacity="0" />
                     </linearGradient>
                     <linearGradient id="paint1_linear_2989_100980" x1="5.19475" y1="0.849139" x2="10.3357"
                        y2="24.1155" gradientUnits="userSpaceOnUse">
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
         <div
            class="d-flex col-12 col-lg-5 col-xl-4 align-items-center authentication-bg position-relative py-sm-12 px-12 py-6">
            <div class="w-px-400 mx-auto pt-12 pt-lg-0">
               <h4 class="mb-1">Welcome to CPH Tyre! 👋</h4>
               <p class="mb-5">Please sign-in to your account and start the adventure</p>

               @if (session('fail'))
                  <div class="alert alert-danger">
                     {{ session('fail') }}
                  </div>
               @endif

               @if ($errors->any())
                  <div class="alert alert-danger">
                     <ul>
                        @foreach ($errors->all() as $error)
                           <li>{{ $error }}</li>
                        @endforeach
                     </ul>
                  </div>
               @endif

               <form id="formAuthentication" class="mb-5" action="{{ route('login') }}" method="POST">
                  @csrf
                  <input type="hidden" name="login_type" value="cph">
                  <div class="form-floating form-floating-outline mb-5 form-control-validation">
                     <input type="text" class="form-control" id="employee_id" name="employee_id"
                        placeholder="Email / ID Pengguna" autofocus value="{{ old('employee_id') }}" />
                     <label for="employee_id">Email / ID Pengguna</label>
                  </div>
                  <div class="mb-5">
                     <div class="form-password-toggle form-control-validation">
                        <div class="input-group input-group-merge">
                           <div class="form-floating form-floating-outline">
                              <input type="password" id="password" class="form-control" name="password"
                                 placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                                 aria-describedby="password" />
                              <label for="password">Password</label>
                           </div>
                           <span class="input-group-text cursor-pointer"><i
                                 class="icon-base ri ri-eye-off-line icon-20px"></i></span>
                        </div>
                     </div>
                  </div>
                  <div class="mb-5 d-flex justify-content-between mt-5">
                     <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="remember-me" name="remember" />
                        <label class="form-check-label" for="remember-me"> Remember Me </label>
                     </div>
                  </div>
                  <button class="btn btn-primary d-grid w-100" type="submit" id="btnSignIn">Sign in</button>

                  {{-- EULA Notice --}}
                  <div class="text-center mt-4">
                     <p class="mb-0" style="font-size: 0.75rem; color: #a1acb8; line-height: 1.5;">
                        Dengan masuk, Anda menyetujui
                        <a href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#eulaModal"
                           class="text-primary fw-semibold" style="text-decoration: none;">
                           Syarat & Ketentuan Penggunaan
                        </a>
                     </p>
                  </div>
               </form>
            </div>
         </div>
         <!-- /Login -->

         <!-- Illustration (Right) -->
         <div class="d-none d-lg-flex col-lg-7 col-xl-8 align-items-center justify-content-center p-12 pb-2">
            <img src="{{ asset('template/full-version/assets/img/illustrations/auth-login-illustration-light.png') }}"
               class="auth-cover-illustration w-100" alt="auth-illustration"
               data-app-light-img="illustrations/auth-login-illustration-light.png"
               data-app-dark-img="illustrations/auth-login-illustration-dark.png" />
            <img alt="mask"
               src="{{ asset('template/full-version/assets/img/illustrations/auth-basic-login-mask-light.png') }}"
               class="authentication-image d-none d-lg-block"
               data-app-light-img="illustrations/auth-basic-login-mask-light.png"
               data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
         </div>
         <!-- /Illustration -->
      </div>
   </div>

   {{-- EULA MODAL --}}
   <div class="modal fade" id="eulaModal" tabindex="-1" aria-labelledby="eulaModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
         <div class="modal-content" style="border: none; border-radius: 1rem; overflow: hidden;">
            {{-- Header --}}
            <div class="modal-header px-4 py-3" style="background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%); border: none;">
               <div class="d-flex align-items-center gap-3">
                  <div class="rounded-circle d-flex align-items-center justify-content-center"
                     style="width: 40px; height: 40px; background: rgba(255,255,255,0.2);">
                     <i class="icon-base ri ri-shield-check-line text-white" style="font-size: 1.25rem;"></i>
                  </div>
                  <div>
                     <h5 class="modal-title text-white mb-0 fw-bold" id="eulaModalLabel">Syarat & Ketentuan Penggunaan</h5>
                     <small class="text-white" style="opacity: 0.8;">End User License Agreement (EULA)</small>
                  </div>
               </div>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Body --}}
            <div class="modal-body px-4 py-4" id="eulaModalBody" style="max-height: 65vh; font-size: 0.85rem; line-height: 1.75; color: #5d596c;">

               {{-- Intro --}}
               <div class="alert border-0 mb-4 p-3" style="background: #f4f3fe; border-left: 4px solid #7367f0 !important; border-radius: 0.5rem;">
                  <p class="mb-0" style="font-size: 0.8rem;">
                     <i class="icon-base ri ri-information-line me-1 text-primary"></i>
                     Perjanjian ini merupakan dokumen hukum yang mengikat antara <strong>Pengguna</strong> dan
                     <strong>PT Catur Putra Harmonis</strong> selaku pemilik dan pengelola sistem
                     <strong>CPH Tyre Performance Dashboard</strong>. Dengan mengakses sistem ini, Pengguna
                     dianggap telah membaca, memahami, dan menyetujui seluruh ketentuan yang tercantum di bawah ini.
                  </p>
               </div>

               {{-- Section 1 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">1</span>
                     Definisi & Ruang Lingkup
                  </h6>
                  <p>
                     <strong>CPH Tyre Performance Dashboard</strong> (selanjutnya disebut "Sistem") adalah platform
                     manajemen performa ban kendaraan berbasis web yang dikembangkan oleh PT Catur Putra Harmonis.
                     Sistem ini mencakup, namun tidak terbatas pada, fitur pencatatan pergerakan ban, pemeriksaan
                     berkala, pemantauan kondisi ban di lapangan, analisis umur pakai, serta pelaporan dan
                     visualisasi data analitik operasional armada kendaraan.
                  </p>
                  <p>
                     Perjanjian ini berlaku bagi seluruh Pengguna yang mengakses Sistem, baik selaku mitra,
                     pelanggan, maupun pihak lain yang telah diberikan otorisasi resmi oleh PT Catur Putra Harmonis,
                     melalui antarmuka web maupun kanal resmi lainnya yang disediakan oleh Perusahaan.
                  </p>
               </div>

               {{-- Section 2 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">2</span>
                     Hak Akses & Otorisasi Pengguna
                  </h6>
                  <ul class="ps-3">
                     <li class="mb-2">
                        Akses terhadap Sistem hanya diberikan kepada pihak yang telah ditunjuk dan diotorisasi
                        secara resmi oleh PT Catur Putra Harmonis, termasuk namun tidak terbatas pada mitra bisnis,
                        pelanggan, dan pihak ketiga yang terikat perjanjian kerja sama dengan Perusahaan.
                     </li>
                     <li class="mb-2">
                        Setiap Pengguna wajib menjaga kerahasiaan kredensial akun (ID Pengguna dan kata sandi) dan
                        bertanggung jawab penuh atas seluruh aktivitas yang dilakukan melalui akun tersebut.
                     </li>
                     <li class="mb-2">
                        Pengguna <strong>dilarang keras</strong> membagikan, meminjamkan, atau mengalihkan akses
                        akun kepada pihak mana pun tanpa persetujuan tertulis dari administrator Sistem atau
                        pihak yang berwenang dari PT Catur Putra Harmonis.
                     </li>
                     <li class="mb-2">
                        Perusahaan berhak menangguhkan atau mencabut hak akses Pengguna kapan saja apabila terdapat
                        indikasi penyalahgunaan, pelanggaran keamanan, atau berakhirnya perjanjian kerja sama
                        yang mendasari pemberian akses.
                     </li>
                  </ul>
               </div>

               {{-- Section 3 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">3</span>
                     Kerahasiaan & Perlindungan Data
                  </h6>
                  <ul class="ps-3">
                     <li class="mb-2">
                        Seluruh data yang dikelola dalam Sistem — mencakup data operasional, data armada kendaraan,
                        data ban, data lokasi, maupun data yang berkaitan dengan Pengguna — bersifat
                        <strong>rahasia</strong> dan merupakan aset yang dilindungi oleh PT Catur Putra Harmonis.
                     </li>
                     <li class="mb-2">
                        Pengguna dilarang menyalin, mengunduh secara massal, mendistribusikan, mempublikasikan, atau
                        mengungkapkan data Sistem dalam bentuk apa pun kepada pihak lain, baik secara langsung
                        maupun melalui media elektronik, tanpa otorisasi tertulis dari Perusahaan.
                     </li>
                     <li class="mb-2">
                        Perusahaan menerapkan mekanisme pencatatan log aktivitas (<em>audit trail</em>) secara
                        menyeluruh. Seluruh aktivitas Pengguna di dalam Sistem dapat dipantau, direkam, dan digunakan
                        sebagai bahan evaluasi maupun investigasi apabila diperlukan.
                     </li>
                     <li class="mb-2">
                        Pengelolaan data pribadi dalam Sistem dilaksanakan sesuai dengan ketentuan
                        <strong>Undang-Undang Nomor 27 Tahun 2022 tentang Perlindungan Data Pribadi</strong> dan
                        regulasi terkait yang berlaku di Republik Indonesia.
                     </li>
                  </ul>
               </div>

               {{-- Section 4 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">4</span>
                     Kewajiban Pengguna
                  </h6>
                  <ul class="ps-3">
                     <li class="mb-2">
                        Menggunakan Sistem semata-mata untuk keperluan yang telah disepakati dalam perjanjian
                        kerja sama atau otorisasi akses yang diberikan oleh PT Catur Putra Harmonis.
                     </li>
                     <li class="mb-2">
                        Memasukkan dan memperbarui data secara <strong>akurat, jujur, lengkap, dan tepat waktu</strong>
                        berdasarkan kondisi aktual, demi menjaga integritas data dalam Sistem.
                     </li>
                     <li class="mb-2">
                        Segera melaporkan kepada administrator Sistem apabila mengetahui atau menduga adanya
                        pelanggaran keamanan, akses tidak sah, kehilangan kredensial, atau anomali data
                        yang mencurigakan.
                     </li>
                     <li class="mb-2">
                        Tidak melakukan segala bentuk upaya untuk memanipulasi data, merekayasa balik
                        (<em>reverse engineering</em>) Sistem, mengeksploitasi celah keamanan, atau mengganggu
                        ketersediaan dan integritas Sistem dengan cara apa pun.
                     </li>
                     <li class="mb-2">
                        Mengakhiri sesi akses dengan benar (<em>log out</em>) setiap kali selesai menggunakan
                        Sistem, terutama pada perangkat yang digunakan bersama.
                     </li>
                  </ul>
               </div>

               {{-- Section 5 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">5</span>
                     Batasan Tanggung Jawab Perusahaan
                  </h6>
                  <ul class="ps-3">
                     <li class="mb-2">
                        Perusahaan senantiasa berupaya menjaga ketersediaan, keandalan, dan keamanan Sistem, namun
                        <strong>tidak memberikan jaminan mutlak</strong> atas operasional tanpa gangguan, termasuk
                        namun tidak terbatas pada kondisi pemeliharaan sistem, gangguan infrastruktur, atau kejadian
                        di luar kendali (<em>force majeure</em>).
                     </li>
                     <li class="mb-2">
                        Perusahaan tidak bertanggung jawab atas kerugian — baik langsung maupun tidak langsung —
                        yang timbul akibat kelalaian Pengguna dalam menjaga kerahasiaan akun, memasukkan data yang
                        tidak akurat, atau menyalahgunakan Sistem di luar ketentuan yang berlaku.
                     </li>
                     <li class="mb-2">
                        Seluruh keputusan yang diambil berdasarkan data atau laporan yang dihasilkan Sistem
                        sepenuhnya menjadi tanggung jawab Pengguna yang bersangkutan.
                     </li>
                  </ul>
               </div>

               {{-- Section 6 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">6</span>
                     Hak Kekayaan Intelektual
                  </h6>
                  <p>
                     Seluruh komponen Sistem — mencakup namun tidak terbatas pada kode sumber (<em>source code</em>),
                     antarmuka pengguna, desain visual, logo, merek, algoritma, struktur basis data, alur proses
                     bisnis, serta dokumentasi teknis — merupakan hak kekayaan intelektual milik eksklusif
                     PT Catur Putra Harmonis yang dilindungi oleh <strong>Undang-Undang Nomor 28 Tahun 2014
                     tentang Hak Cipta</strong> dan peraturan perundang-undangan lainnya yang berlaku di
                     Republik Indonesia. Tidak ada bagian mana pun dari Sistem yang boleh digandakan,
                     dimodifikasi, atau didistribusikan tanpa izin tertulis dari Perusahaan.
                  </p>
               </div>

               {{-- Section 7 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">7</span>
                     Kepatuhan Regulasi & Hukum yang Berlaku
                  </h6>
                  <p>
                     Penggunaan Sistem wajib mematuhi seluruh peraturan perundang-undangan yang berlaku di
                     Republik Indonesia, termasuk namun tidak terbatas pada:
                  </p>
                  <ul class="ps-3">
                     <li class="mb-2"><strong>UU No. 11 Tahun 2008 jo. UU No. 19 Tahun 2016</strong> tentang Informasi dan Transaksi Elektronik (UU ITE).</li>
                     <li class="mb-2"><strong>UU No. 27 Tahun 2022</strong> tentang Perlindungan Data Pribadi.</li>
                     <li class="mb-2"><strong>PP No. 71 Tahun 2019</strong> tentang Penyelenggaraan Sistem dan Transaksi Elektronik.</li>
                     <li class="mb-2">Ketentuan dan kebijakan yang ditetapkan oleh PT Catur Putra Harmonis yang berlaku.</li>
                  </ul>
                  <p>
                     Perjanjian ini tunduk pada dan ditafsirkan berdasarkan hukum yang berlaku di
                     Republik Indonesia. Segala sengketa yang timbul dari perjanjian ini akan diselesaikan
                     secara musyawarah, atau apabila tidak tercapai kesepakatan, melalui jalur hukum di
                     pengadilan yang berwenang.
                  </p>
               </div>

               {{-- Section 8 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">8</span>
                     Pelanggaran & Sanksi
                  </h6>
                  <p>Pelanggaran terhadap ketentuan dalam perjanjian ini dapat mengakibatkan tindakan sebagai berikut:</p>
                  <ul class="ps-3">
                     <li class="mb-2">
                        Penangguhan atau pencabutan hak akses Sistem secara <strong>permanen dan segera</strong>
                        tanpa kewajiban pemberitahuan terlebih dahulu.
                     </li>
                     <li class="mb-2">
                        Pengakhiran perjanjian kerja sama atau otorisasi akses yang mendasari penggunaan Sistem,
                        sesuai dengan ketentuan yang disepakati antara Pengguna dan PT Catur Putra Harmonis.
                     </li>
                     <li class="mb-2">
                        Tuntutan ganti rugi secara perdata atas kerugian yang ditimbulkan kepada Perusahaan
                        sebagai akibat langsung maupun tidak langsung dari pelanggaran tersebut.
                     </li>
                     <li class="mb-2">
                        Proses hukum pidana sesuai perundang-undangan yang berlaku di Republik Indonesia,
                        apabila pelanggaran yang dilakukan memenuhi unsur tindak pidana.
                     </li>
                  </ul>
               </div>

               {{-- Section 9 --}}
               <div class="mb-4">
                  <h6 class="fw-bold text-dark mb-2">
                     <span class="badge bg-primary rounded-pill me-2" style="font-size: 0.65rem;">9</span>
                     Perubahan Ketentuan
                  </h6>
                  <p>
                     PT Catur Putra Harmonis berhak mengubah, memperbarui, atau merevisi ketentuan dalam
                     perjanjian ini kapan saja sesuai kebutuhan operasional dan regulasi yang berlaku.
                     Perubahan akan diinformasikan melalui Sistem atau kanal komunikasi resmi Perusahaan.
                     Pengguna yang tetap menggunakan Sistem setelah perubahan diberlakukan dianggap telah
                     membaca dan menyetujui ketentuan yang telah diperbarui.
                  </p>
               </div>

               {{-- Closing --}}
               <div class="border-top pt-3 mt-4">
                  <div class="d-flex align-items-center gap-2 mb-2">
                     <i class="icon-base ri ri-building-2-line text-primary"></i>
                     <strong class="text-dark">PT Catur Putra Harmonis</strong>
                  </div>
                  <p class="mb-1 text-muted" style="font-size: 0.75rem;">
                     Perjanjian ini berlaku efektif secara otomatis sejak Pengguna pertama kali mengakses
                     atau masuk ke dalam Sistem, dan tetap berlaku selama Pengguna memiliki hak akses aktif.
                  </p>
                  <p class="mb-0 text-muted" style="font-size: 0.75rem;">
                     Terakhir diperbarui: <strong>{{ date('d F Y') }}</strong>
                  </p>
               </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer px-4 py-3 border-top d-flex justify-content-between align-items-center" style="background: #fafafa;">
               <div id="eulaScrollNotice" class="d-flex align-items-center text-danger">
                  <i class="icon-base ri ri-arrow-down-circle-line me-2 fs-5 animation-bounce"></i>
                  <small class="fw-bold">Scroll ke bawah untuk menyetujui</small>
               </div>
               <button type="button" class="btn btn-primary px-4 fw-bold" id="btnUnderstandEula" data-bs-dismiss="modal" disabled>
                  <i class="icon-base ri ri-check-double-line me-1"></i> Saya Memahami
               </button>
            </div>
         </div>
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
   <script>
      document.addEventListener('DOMContentLoaded', function() {
         const modalBody = document.getElementById('eulaModalBody');
         const btnUnderstand = document.getElementById('btnUnderstandEula');
         const scrollNotice = document.getElementById('eulaScrollNotice');
         const btnSignIn = document.getElementById('btnSignIn');
         const formAuth = document.getElementById('formAuthentication');
         let eulaAccepted = false; // State EULA

         // Tambahkan CSS animasi bounce ringan untuk panah bawah
         const style = document.createElement('style');
         style.innerHTML = `
            @keyframes bounceSmall {
               0%, 100% { transform: translateY(0); }
               50% { transform: translateY(3px); }
            }
            .animation-bounce { animation: bounceSmall 1.5s infinite; }
         `;
         document.head.appendChild(style);

         // Cek apakah content EULA kurang panjang (jadi tidak bisa di-scroll)
         function checkScrollable() {
            if (modalBody.scrollHeight <= modalBody.clientHeight) {
               enableEulaAccept();
            }
         }

         function enableEulaAccept() {
            btnUnderstand.removeAttribute('disabled');
            scrollNotice.classList.remove('text-danger');
            scrollNotice.classList.add('text-success');
            scrollNotice.innerHTML = '<i class="icon-base ri ri-checkbox-circle-fill me-2 fs-5"></i><small class="fw-bold">Terima kasih telah membaca</small>';
         }

         // Periksa saat user scroll
         modalBody.addEventListener('scroll', function() {
            // Toleransi 10px untuk memastikan event selalu tertangkap meski zoom level berbeda
            if (modalBody.scrollTop + modalBody.clientHeight >= modalBody.scrollHeight - 10) {
               enableEulaAccept();
            }
         });

         // Bind event ke modal opened (karena DOM awal belum tahu tinggi asli)
         const eulaModalEl = document.getElementById('eulaModal');
         let bsModal = null;
         eulaModalEl.addEventListener('shown.bs.modal', function () {
            checkScrollable();
            // Cache instance modal
            bsModal = bootstrap.Modal.getInstance(eulaModalEl) || new bootstrap.Modal(eulaModalEl);
         });

         // Saat tombol paham diklik
         btnUnderstand.addEventListener('click', function() {
            eulaAccepted = true;
         });

         // Keamanan mutlak pada tombol Sign In (Mencegat klik sebelum diproses oleh FormValidation)
         btnSignIn.addEventListener('click', function(e) {
            if (!eulaAccepted) {
               e.preventDefault();
               e.stopPropagation();
               e.stopImmediatePropagation(); // Blokir script template Vuexy agar form TIDAK submit

               // Buka modal EULA secara otomatis
               if(bsModal) {
                   bsModal.show();
               } else {
                   bsModal = new bootstrap.Modal(eulaModalEl);
                   bsModal.show();
               }
            }
         }, true); // { capture: true } sangat penting agar dijalankan pertama kali
      });
   </script>
@endsection