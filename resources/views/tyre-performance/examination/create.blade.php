@extends('layouts.admin')

@section('title', 'Form Pemeriksaan Ban (Examination)')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet" href="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endsection

@section('page-style')
    <style>
        .form-header-card {
            background: #fdfae0;
            /* Soft yellow theme from Excel */
            border-left: 5px solid #ffd700;
        }

        .table-examination thead {
            background: #ffd700;
            color: #000;
        }

        .table-examination input {
            border: 1px solid #ced4da;
            padding: 4px 8px;
            width: 100%;
        }

        .pos-code {
            background: #f8f9fa;
            font-weight: bold;
            width: 60px;
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Examination /</span> Input Baru</h4>
            <a href="{{ route('examination.index') }}" class="btn btn-label-secondary"><i
                    class="ri-arrow-left-line me-1"></i> Kembali</a>
        </div>

        <form id="examination_form">
            @csrf
            <!-- HEADER SECTION -->
            <div class="card mb-4 shadow-sm form-header-card">
                <div class="card-body pt-3">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold small">DATE</label>
                            <input type="date" name="examination_date" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold small">KM (ODO/RETASE)</label>
                            <input type="number" name="odometer" class="form-control" placeholder="22076" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold small">No. Pol & Unit</label>
                            <select name="vehicle_id" id="vehicle_id" class="form-select select2" required>
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach ($kendaraans as $v)
                                    <option value="{{ $v->id }}">{{ $v->no_polisi }} / {{ $v->kode_kendaraan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Mulai</label>
                                    <input type="time" name="start_time" class="form-control" value="{{ date('H:i') }}">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">Selesai</label>
                                    <input type="time" name="end_time" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold small">LOCATION</label>
                            <select name="location_id" id="location_id" class="form-select select2" required>
                                <option value="">-- Pilih Lokasi --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->location_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold small">SEGMENT</label>
                            <select name="operational_segment_id" id="operational_segment_id" class="form-select select2"
                                required>
                                <option value="">-- Pilih Segmen --</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label fw-bold small">HM (Hour Meter)</label>
                            <input type="number" name="hour_meter" class="form-control" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label fw-bold small">DRIVER #1</label>
                                    <input type="text" name="driver_1" class="form-control">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-bold small">DRIVER #2</label>
                                    <input type="text" name="driver_2" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE SECTION -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-transparent d-flex justify-content-between">
                    <h5 class="mb-0"><i class="ri-list-check me-2"></i>Tyre Check List</h5>
                    <small class="text-muted">Pilih unit untuk memuat daftar ban</small>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-examination mb-0" id="tyre_list_table">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">Pos</th>
                                <th>Brand</th>
                                <th>Pattern</th>
                                <th>Size/PR</th>
                                <th>Serial Number</th>
                                <th width="100">PSI</th>
                                <th width="80">RTD #1</th>
                                <th width="80">RTD #2</th>
                                <th width="80">RTD #3</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody id="tyre_list_body">
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    Silakan pilih unit kendaraan terlebih dahulu.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FOOTER / APPROVAL -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold">Tyre Man (Pemeriksa)</label>
                            <input type="text" name="tyre_man" class="form-control" placeholder="Nama Pemeriksa">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="1"></textarea>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="ri-save-line me-1"></i> SIMPAN PEMERIKSAAN
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('template/full-version/assets/vendor/libs/select2/select2.js') }}"></script>
    <script src="{{ asset('template/full-version/assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(function () {
            $('.select2').select2();

            $('#location_id').on('change', function () {
                const locationId = $(this).val();
                const $segmentSelect = $('#operational_segment_id');

                $segmentSelect.html('<option value="">-- Pilih Segmen --</option>');

                if (locationId) {
                    $.ajax({
                        url: "{{ route('tyre-movement.get-segments', '') }}/" + locationId,
                        success: function (res) {
                            res.forEach(function (segment) {
                                $segmentSelect.append(`<option value="${segment.id}">${segment.segment_name}</option>`);
                            });
                        }
                    });
                }
            });

            $('#vehicle_id').on('change', function () {
                const vehicleId = $(this).val();
                if (!vehicleId) {
                    $('#tyre_list_body').html('<tr><td colspan="10" class="text-center py-5 text-muted">Silakan pilih unit kendaraan.</td></tr>');
                    return;
                }

                Swal.fire({
                    title: 'Memuat data ban...',
                    didOpen: () => Swal.showLoading(),
                    allowOutsideClick: false
                });

                $.ajax({
                    url: "{{ route('examination.get-vehicle-tyres', '') }}/" + vehicleId,
                    success: function (res) {
                        Swal.close();
                        if (res.success) {
                            $('#tyre_list_body').html(res.html);
                        }
                    },
                    error: function () {
                        Swal.fire('Error', 'Gagal memuat layout ban unit', 'error');
                    }
                });
            });

            $('#examination_form').on('submit', function (e) {
                e.preventDefault();

                Swal.fire({
                    title: 'Simpan Data?',
                    text: "Pastikan semua data RTD dan PSI sudah benar.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan!',
                    customClass: {
                        confirmButton: 'btn btn-primary me-3',
                        cancelButton: 'btn btn-label-secondary'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Menyimpan...',
                            didOpen: () => Swal.showLoading(),
                            allowOutsideClick: false
                        });

                        $.ajax({
                            url: "{{ route('examination.store') }}",
                            method: 'POST',
                            data: $(this).serialize(),
                            success: function (res) {
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: res.message,
                                        type: 'success'
                                    }).then(() => {
                                        window.location.href = res.redirect;
                                    });
                                }
                            },
                            error: function (res) {
                                Swal.fire('Oops!', res.responseJSON?.message || 'Terjadi kesalahan sistem', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection