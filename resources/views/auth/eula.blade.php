@extends('layouts.guest')
@section('title', 'EULA - Syarat & Ketentuan')
@section('page-style')
   <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/css/pages/page-auth.css') }}" />
   <style>
      .eula-container {
         max-height: 500px;
         overflow-y: auto;
         border: 1px solid #d9dee3;
         border-radius: 0.5rem;
         padding: 1.5rem;
         background: #f8f9fa;
         font-size: 0.9rem;
         line-height: 1.6;
      }
      .eula-container::-webkit-scrollbar {
         width: 8px;
      }
      .eula-container::-webkit-scrollbar-track {
         background: #f1f1f1; 
         border-radius: 4px;
      }
      .eula-container::-webkit-scrollbar-thumb {
         background: #c1c1c1; 
         border-radius: 4px;
      }
      .eula-container::-webkit-scrollbar-thumb:hover {
         background: #a8a8a8; 
      }
   </style>
@endsection

@section('content')
<div class="container-xxl">
   <div class="authentication-wrapper authentication-basic container-p-y">
      <div class="authentication-inner py-4" style="max-width: 800px;">
         <div class="card p-2 p-md-4">
            <div class="card-body mt-2">
               <h4 class="mb-2 text-center fw-bold">Perjanjian Penggunaan Sistem</h4>
               <p class="mb-4 text-center">END USER LICENSE AGREEMENT (EULA) - CPH TYRE PERFORMANCE DASHBOARD</p>
               
               <div class="eula-container text-dark" id="eulaBody">
                  <p>
                     Perjanjian ini merupakan dokumen hukum yang mengikat antara para Pihak (Pengguna/Pelanggan dan PT Catur Putra Harmonis selaku pemilik dan pengelola Sistem). Dengan mengakses atau menggunakan Sistem, Pelanggan dianggap telah membaca, memahami, dan menyetujui seluruh ketentuan dalam Perjanjian ini.
                  </p>
                  <hr>
                  
                  <h6 class="fw-bold">1. DEFINISI</h6>
                  <ol>
                     <li><strong>“Sistem”</strong> adalah CPH Tyre Performance Dashboard, yaitu platform berbasis web untuk manajemen, monitoring, dan analisis penggunaan ban kendaraan.</li>
                     <li><strong>“Pelanggan”</strong> adalah badan usaha atau individu yang menggunakan produk atau layanan CPH dan diberikan akses ke Sistem.</li>
                     <li><strong>“Data Pelanggan”</strong> adalah seluruh data operasional yang dimasukkan oleh Pelanggan ke dalam Sistem.</li>
                  </ol>
                  <hr>

                  <h6 class="fw-bold">2. SIFAT PENGGUNAAN SISTEM</h6>
                  <ol>
                     <li>Sistem disediakan oleh CPH secara gratis (<em>free of charge</em>) sebagai nilai tambah (<em>value-added service</em>) bagi Pelanggan yang aktif menggunakan produk atau layanan CPH.</li>
                     <li>Hak penggunaan Sistem bersifat: 
                        <ul>
                           <li>Non-eksklusif</li>
                           <li>Tidak dapat dialihkan</li>
                           <li>Dapat dicabut sewaktu-waktu oleh CPH</li>
                        </ul>
                     </li>
                     <li>Akses terhadap Sistem dapat dihentikan apabila:
                        <ul>
                           <li>Pelanggan tidak lagi aktif menggunakan produk CPH</li>
                           <li>Terjadi pelanggaran terhadap ketentuan dalam Perjanjian ini</li>
                        </ul>
                     </li>
                  </ol>
                  <hr>

                  <h6 class="fw-bold">3. HAK DAN KEWAJIBAN PELANGGAN</h6>
                  <ol>
                     <li>Pelanggan wajib:
                        <ul>
                           <li>Menggunakan Sistem hanya untuk kepentingan operasional internal</li>
                           <li>Memastikan data yang dimasukkan akurat dan valid</li>
                           <li>Menjaga kerahasiaan akun dan akses</li>
                        </ul>
                     </li>
                     <li>Pelanggan dilarang:
                        <ul>
                           <li>Menyalahgunakan Sistem untuk tujuan di luar operasional</li>
                           <li>Melakukan reverse engineering, hacking, atau eksploitasi</li>
                           <li>Menggunakan Sistem sebagai dasar klaim tanpa verifikasi independen</li>
                        </ul>
                     </li>
                  </ol>
                  <hr>

                  <h6 class="fw-bold">4. DATA DAN KERAHASIAAN</h6>
                  <ol>
                     <li>Data Pelanggan tetap menjadi milik Pelanggan.</li>
                     <li>CPH berhak menggunakan data dalam bentuk agregat dan anonim untuk:
                        <ul>
                           <li>Analisis performa produk</li>
                           <li>Pengembangan layanan</li>
                           <li>Benchmarking industri</li>
                        </ul>
                     </li>
                     <li>CPH tidak akan mengungkapkan data spesifik Pelanggan kepada pihak ketiga tanpa persetujuan.</li>
                     <li>CPH akan menjaga kerahasiaan Pelanggan yang tidak terbatas pada data-data spesifik Pelanggan yang diperoleh dan Tidak mengungkapkan kepada pihak ketiga tanpa persetujuan Pelanggan.</li>
                  </ol>
                  <hr>

                  <h6 class="fw-bold">5. KETERSEDIAAN DAN LAYANAN</h6>
                  <ol>
                     <li>Sistem disediakan “as is” tanpa jaminan ketersediaan penuh atau bebas gangguan.</li>
                     <li>CPH tidak memberikan Service Level Agreement (SLA) atas Sistem.</li>
                     <li>CPH berhak melakukan pemeliharaan, perubahan, atau penghentian fitur kapan saja.</li>
                  </ol>
                  <hr>

                  <h6 class="fw-bold">6. BATASAN TANGGUNG JAWAB</h6>
                  <ol>
                     <li>Sistem merupakan alat bantu operasional dan tidak dimaksudkan sebagai satu-satunya dasar pengambilan keputusan.</li>
                     <li>CPH tidak bertanggung jawab atas:
                        <ul>
                           <li>Kerugian operasional</li>
                           <li>Kehilangan data</li>
                           <li>Keputusan bisnis yang diambil berdasarkan Sistem</li>
                        </ul>
                     </li>
                     <li>Tanggung jawab maksimal CPH, jika ada, dibatasi hanya sebesar nilai transaksi pembelian produk CPH dalam 3 bulan terakhir.</li>
                  </ol>
                  <hr>

                  <h6 class="fw-bold">7. HAK KEKAYAAN INTELEKTUAL</h6>
                  <p>Seluruh Sistem, termasuk kode, desain, dan fitur merupakan milik eksklusif CPH dan tidak dapat digunakan di luar ketentuan ini.</p>
                  <hr>

                  <h6 class="fw-bold">8. PENGHENTIAN AKSES</h6>
                  <p>CPH berhak menghentikan akses Pelanggan kapan saja tanpa kewajiban kompensasi, termasuk jika:</p>
                  <ul>
                     <li>Pelanggan tidak aktif</li>
                     <li>Terjadi pelanggaran</li>
                     <li>Atas pertimbangan bisnis CPH</li>
                  </ul>
                  <hr>

                  <h6 class="fw-bold">9. HUKUM YANG BERLAKU</h6>
                  <p>Perjanjian ini tunduk pada hukum Republik Indonesia.</p>
                  <hr>

                  <h6 class="fw-bold">10. PERUBAHAN KETENTUAN</h6>
                  <p>CPH berhak mengubah ketentuan ini sewaktu-waktu. Penggunaan berkelanjutan dianggap sebagai persetujuan.</p>
                  <hr>

                  <div class="mt-4 text-center text-muted">
                     <strong>PT Catur Putra Harmonis</strong><br>
                     Perjanjian ini berlaku sejak pertama kali Sistem digunakan.<br>
                     Terakhir diperbarui: {{ date('d F Y') }}
                  </div>
               </div>

               <form action="{{ route('eula.accept') }}" method="POST" class="mt-4">
                  @csrf
                  <div class="d-flex flex-column align-items-center">
                     <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="agreeCheckbox" required>
                        <label class="form-check-label fw-semibold" for="agreeCheckbox">
                           Saya telah membaca, memahami, dan menyetujui seluruh ketentuan di atas.
                        </label>
                     </div>
                     
                     <div class="d-flex gap-3">
                        <button type="submit" class="btn btn-primary px-5" id="btnAccept" disabled>
                           <i class="icon-base ri ri-check-double-line me-1"></i> Lanjutkan & Setuju
                        </button>
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-outline-danger">
                           Tolak & Keluar
                        </a>
                     </div>
                  </div>
               </form>
               
               <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                  @csrf
               </form>

            </div>
         </div>
      </div>
   </div>
</div>
@endsection

@section('page-script')
<script>
   document.addEventListener('DOMContentLoaded', function() {
      const agreeCheckbox = document.getElementById('agreeCheckbox');
      const btnAccept = document.getElementById('btnAccept');

      agreeCheckbox.addEventListener('change', function() {
         btnAccept.disabled = !this.checked;
      });
   });
</script>
@endsection
