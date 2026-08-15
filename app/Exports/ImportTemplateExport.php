<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\ImportTemplateSheets\TemplateDataSheet;
use App\Exports\ImportTemplateSheets\TemplateGuideSheet;
use App\Exports\ImportTemplateSheets\TemplateReferenceSheet;
use App\Models\TyreBrand;
use App\Models\TyreSize;
use App\Models\TyrePattern;
use App\Models\TyreSegment;
use App\Models\TyreLocation;
use App\Models\TyrePositionConfiguration;
use App\Models\MasterImportKendaraan;
use App\Models\TyreFailureCode;

class ImportTemplateExport implements WithMultipleSheets
{
    protected string $module;

    public function __construct(string $module)
    {
        $this->module = $module;
    }

    public function sheets(): array
    {
        switch ($this->module) {
            case 'Tyre Master':
            case 'Master Tyre':
                return $this->tyreMasterSheets();

            case 'Vehicle Master':
            case 'Master Vehicle':
                return $this->vehicleMasterSheets();

            case 'Movement History':
            case 'Tyre Movement':
                return $this->movementHistorySheets();

            case 'Tyre Brand':
            case 'Brands':
                return $this->tyreBrandSheets();

            case 'Tyre Size':
            case 'Sizes':
                return $this->tyreSizeSheets();

            case 'Tyre Pattern':
            case 'Patterns':
                return $this->tyrePatternSheets();

            case 'Failure Codes':
                return $this->failureCodesSheets();

            case 'Locations':
                return $this->locationsSheets();

            case 'Segments':
                return $this->segmentsSheets();

            default:
                return $this->tyreMasterSheets();
        }
    }

    private function tyreMasterSheets(): array
    {
        $headers = [
            'serial_number', 'brand', 'size', 'pattern', 'segment_name',
            'ply_rating', 'initial_rtd', 'price', 'status', 'in_warehouse', 'location_name'
        ];

        $samples = [
            ['SN-BS-001', 'BRIDGESTONE', '11.00-20', 'G580', 'Coal Hauling', '16', '16.50', '5500000', 'New', 'Yes', 'GUDANG PUSAT'],
            ['SN-GT-002', 'GITI', '10.00-20', 'GTL971', 'Overburden', '14', '15.00', '4200000', 'New', 'Yes', 'GUDANG SITE A'],
            ['SN-MC-003', 'MICHELIN', 'R25 29.5', 'XDM2', 'OB Haul', '32', '42.00', '18000000', 'Repaired', 'Yes', 'GUDANG PUSAT'],
        ];

        $guidelines = [
            ['column' => 'serial_number', 'label' => 'Nomor Seri Ban (SN)', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'SN-BS-001', 'notes' => 'Nomor seri fisik ban. Jika dikosongkan, sistem otomatis membuatkan nomor seri stok (STK-...).'],
            ['column' => 'brand', 'label' => 'Merek / Brand Ban', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'BRIDGESTONE', 'notes' => 'Nama brand ban. Otomatis didaftarkan jika belum ada di database.'],
            ['column' => 'size', 'label' => 'Ukuran Ban (Size)', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => '11.00-20', 'notes' => 'Ukuran standar ban (misal: 11.00-20, R25 29.5). Otomatis didaftarkan jika belum ada.'],
            ['column' => 'pattern', 'label' => 'Kembangan Ban (Pattern)', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'G580', 'notes' => 'Nama tipe kembangan ban. Otomatis didaftarkan jika belum ada.'],
            ['column' => 'segment_name', 'label' => 'Segmen Operasional', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'Coal Hauling', 'notes' => 'Nama segmen/divisi operasi ban.'],
            ['column' => 'ply_rating', 'label' => 'Ply Rating (PR)', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '16', 'notes' => 'Kekuatan lapisan ban (misal: 14, 16, 18, 32). Isikan angka saja.'],
            ['column' => 'initial_rtd', 'label' => 'Original Tread Depth (OTD)', 'type' => 'Desimal (mm)', 'required' => 'WAJIB', 'sample' => '16.50', 'notes' => 'Ketebalan tapak awal saat ban baru dalam milimeter (mm). Gunakan titik untuk desimal.'],
            ['column' => 'price', 'label' => 'Harga Beli (Rp)', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '5500000', 'notes' => 'Harga pembelian ban. Masukkan angka bulat tanpa titik atau koma.'],
            ['column' => 'status', 'label' => 'Status Ban', 'type' => 'Pilihan', 'required' => 'WAJIB', 'sample' => 'New', 'notes' => 'Pilihan: New (Baru) / Repaired (Siap Pakai) / Scrap (Afkir) / Installed (Terpasang).'],
            ['column' => 'in_warehouse', 'label' => 'Ada di Gudang?', 'type' => 'Pilihan', 'required' => 'WAJIB', 'sample' => 'Yes', 'notes' => 'Isikan "Yes" jika ban ada di stok gudang, atau "No" jika tidak.'],
            ['column' => 'location_name', 'label' => 'Nama Lokasi Gudang', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'GUDANG PUSAT', 'notes' => 'Nama lokasi/gudang penyimpanan. Otomatis didaftarkan jika belum ada.'],
        ];

        $refSections = [
            'Daftar Brand' => TyreBrand::orderBy('brand_name')->pluck('brand_name')->toArray(),
            'Daftar Size' => TyreSize::orderBy('size')->pluck('size')->toArray(),
            'Daftar Pattern' => TyrePattern::orderBy('name')->pluck('name')->toArray(),
            'Daftar Segmen' => TyreSegment::where('status', 'Active')->pluck('segment_name')->toArray(),
            'Daftar Lokasi' => TyreLocation::orderBy('location_name')->pluck('location_name')->toArray(),
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '1E40AF'),
            new TemplateGuideSheet('Tyre Master (Aset Ban)', 'Template ini digunakan untuk mengunggah master data aset ban ke sistem.', $guidelines, '1E40AF'),
            new TemplateReferenceSheet('Referensi Master Data', $refSections),
        ];
    }

    private function vehicleMasterSheets(): array
    {
        $headers = [
            'kode_kendaraan', 'no_polisi', 'model_kendaraan', 'brand_kendaraan', 'site_location',
            'curb_weight', 'payload_capacity', 'segment', 'total_positions', 'layout', 'status'
        ];

        $samples = [
            ['DT-101', 'B 1234 ABC', 'DUMP TRUCK', 'HINO', 'SITE A', '15000', '20', 'Coal Hauling', '10', '6 Roda (2+4)', 'Active'],
            ['DT-102', 'B 5678 DEF', 'DUMP TRUCK', 'SCANIA', 'SITE B', '18000', '30', 'Overburden', '10', '6 Roda (2+4)', 'Active'],
            ['HX-301', 'DC 9999 ZZ', 'HAUL TRUCK', 'KOMATSU', 'SITE A', '135000', '220', 'OB Haul', '4', '4 Roda (2+2)', 'Active'],
        ];

        $guidelines = [
            ['column' => 'kode_kendaraan', 'label' => 'Kode / No. Lambung Unit', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'DT-101', 'notes' => 'Nomor lambung / ID unik kendaraan unit (misal: DT-101, HD-785).'],
            ['column' => 'no_polisi', 'label' => 'Nomor Polisi (Plat)', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'B 1234 ABC', 'notes' => 'Nomor plat registrasi kendaraan.'],
            ['column' => 'model_kendaraan', 'label' => 'Jenis / Model Unit', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'DUMP TRUCK', 'notes' => 'Tipe / model kendaraan (misal: DUMP TRUCK, HAUL TRUCK, TRAILER, BUS).'],
            ['column' => 'brand_kendaraan', 'label' => 'Merek Kendaraan', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'HINO', 'notes' => 'Merek pabrikan unit (misal: HINO, SCANIA, VOLVO, KOMATSU, CATERPILLAR).'],
            ['column' => 'site_location', 'label' => 'Lokasi / Site Kerja', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'SITE A', 'notes' => 'Area / site penempatan operasional kendaraan.'],
            ['column' => 'curb_weight', 'label' => 'Berat Kosong (kg)', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '15000', 'notes' => 'Berat kosong kendaraan dalam kilogram (kg). Isikan angka saja.'],
            ['column' => 'payload_capacity', 'label' => 'Kapasitas Muat (Ton)', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '20', 'notes' => 'Kapasitas angkut maksimal dalam satuan Ton. Isikan angka saja.'],
            ['column' => 'segment', 'label' => 'Segmen Operasional', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'Coal Hauling', 'notes' => 'Nama segmen operasi unit (misal: Coal Hauling, Overburden, Hauling).'],
            ['column' => 'total_positions', 'label' => 'Total Posisi Ban', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '10', 'notes' => 'Jumlah roda/posisi ban pada unit kendaraan.'],
            ['column' => 'layout', 'label' => 'Konfigurasi Axle (Layout)', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => '6 Roda (2+4)', 'notes' => 'Nama konfigurasi axle (lihat sheet Referensi Master Data).'],
            ['column' => 'status', 'label' => 'Status Kendaraan', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Active', 'notes' => 'Pilihan: Active (Beroperasi) atau Inactive (Breakdown / Standby).'],
        ];

        $refSections = [
            'Konfigurasi Axle (Layout)' => TyrePositionConfiguration::orderBy('name')->pluck('name')->toArray(),
            'Daftar Segmen' => TyreSegment::where('status', 'Active')->pluck('segment_name')->toArray(),
            'Daftar Lokasi Site' => TyreLocation::orderBy('location_name')->pluck('location_name')->toArray(),
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '059669'),
            new TemplateGuideSheet('Vehicle Master (Unit Kendaraan)', 'Template ini digunakan untuk mengunggah master data unit kendaraan ke sistem.', $guidelines, '059669'),
            new TemplateReferenceSheet('Referensi Master Data', $refSections),
        ];
    }

    private function movementHistorySheets(): array
    {
        $headers = [
            'no_seri', 'unit', 'posisi_ban', 'pemasangan_tanggal', 'pemasangan_km', 'pemasangan_hm',
            'pelepasan_tanggal', 'pelepasan_km', 'pelepasan_hm', 'keterangan', 'tebal_telapak', 'penyebab'
        ];

        $samples = [
            ['SN-BS-001', 'DT-101', '1', '2024-01-10', '32000', '1500', '2024-06-15', '48500', '2300', 'BUANG', '8.5', 'TELAPAK RUSAK'],
            ['SN-GT-002', 'DT-101', '2', '2024-01-10', '32000', '1500', '', '', '', '', '', ''],
            ['SN-MC-003', 'DT-102', '1', '2024-02-01', '25000', '1100', '2024-05-20', '39000', '1850', '', '18.0', 'Dilepas untuk vulkanisir'],
        ];

        $guidelines = [
            ['column' => 'no_seri', 'label' => 'Nomor Seri Ban (SN)', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'SN-BS-001', 'notes' => 'Nomor seri ban yang dipasang/dilepas. Otomatis didaftarkan jika belum ada.'],
            ['column' => 'unit', 'label' => 'Kode Unit Kendaraan', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'DT-101', 'notes' => 'Nomor lambung unit tempat ban dipasang. Otomatis didaftarkan jika belum ada.'],
            ['column' => 'posisi_ban', 'label' => 'Posisi Ban (Nomor / Kode)', 'type' => 'Teks / Angka', 'required' => 'OPSIONAL', 'sample' => '1', 'notes' => 'Nomor urut posisi atau kode posisi ban (misal: 1, 2, FL, FR, LMI, RMO).'],
            ['column' => 'pemasangan_tanggal', 'label' => 'Tanggal Pemasangan', 'type' => 'Tanggal (YYYY-MM-DD)', 'required' => 'WAJIB', 'sample' => '2024-01-10', 'notes' => 'Tanggal ban dipasang ke unit (format: YYYY-MM-DD atau DD.MM.YYYY).'],
            ['column' => 'pemasangan_km', 'label' => 'KM Odometer Pasang', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '32000', 'notes' => 'Angka KM speedometer kendaraan saat ban dipasang.'],
            ['column' => 'pemasangan_hm', 'label' => 'Hour Meter Pasang', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '1500', 'notes' => 'Angka HM (Hour Meter) mesin saat ban dipasang.'],
            ['column' => 'pelepasan_tanggal', 'label' => 'Tanggal Pelepasan', 'type' => 'Tanggal (YYYY-MM-DD)', 'required' => 'OPSIONAL', 'sample' => '2024-06-15', 'notes' => 'Tanggal ban dilepas. KOSONGKAN jika ban saat ini masih terpasang di unit.'],
            ['column' => 'pelepasan_km', 'label' => 'KM Odometer Lepas', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '48500', 'notes' => 'Angka KM speedometer kendaraan saat ban dilepas.'],
            ['column' => 'pelepasan_hm', 'label' => 'Hour Meter Lepas', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '2300', 'notes' => 'Angka HM saat ban dilepas.'],
            ['column' => 'keterangan', 'label' => 'Status Akhir Pelepasan', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'BUANG', 'notes' => 'Isi "BUANG" jika ban diafkir (Scrap), atau kosongkan jika ban masuk gudang/reparasi.'],
            ['column' => 'tebal_telapak', 'label' => 'Sisa Tread Depth (RTD mm)', 'type' => 'Desimal (mm)', 'required' => 'OPSIONAL', 'sample' => '8.5', 'notes' => 'Ketebalan sisa alur ban saat dilepas dalam satuan mm.'],
            ['column' => 'penyebab', 'label' => 'Penyebab / Catatan Lepas', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'TELAPAK RUSAK', 'notes' => 'Alasan pelepasan atau deskripsi kerusakan ban.'],
        ];

        $refSections = [
            'Daftar Unit Aktif' => MasterImportKendaraan::where('tyre_unit_status', 'Active')->orderBy('kode_kendaraan')->pluck('kode_kendaraan')->toArray(),
            'Kamus Kerusakan (Failure Code)' => TyreFailureCode::where('status', 'Active')->orderBy('failure_code')->pluck('failure_code')->toArray(),
            'Daftar Lokasi Gudang' => TyreLocation::orderBy('location_name')->pluck('location_name')->toArray(),
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '2563EB'),
            new TemplateGuideSheet('Movement History (Riwayat Pasang & Lepas Ban)', 'Template ini digunakan untuk mengunggah riwayat lengkap siklus pemasangan dan pelepasan ban.', $guidelines, '2563EB'),
            new TemplateReferenceSheet('Referensi Master Data', $refSections),
        ];
    }

    private function tyreBrandSheets(): array
    {
        $headers = ['brand_name', 'status'];
        $samples = [
            ['BRIDGESTONE', 'Active'],
            ['GITI', 'Active'],
            ['MICHELIN', 'Active'],
            ['GOODYEAR', 'Active'],
        ];

        $guidelines = [
            ['column' => 'brand_name', 'label' => 'Nama Merek Ban (Brand)', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'BRIDGESTONE', 'notes' => 'Nama merek ban (disarankan menggunakan huruf kapital).'],
            ['column' => 'status', 'label' => 'Status Brand', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Active', 'notes' => 'Pilihan: Active / Inactive.'],
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '7C3AED'),
            new TemplateGuideSheet('Tyre Brand (Master Merek Ban)', 'Template ini digunakan untuk mengunggah daftar master merek ban.', $guidelines, '7C3AED'),
        ];
    }

    private function tyreSizeSheets(): array
    {
        $headers = ['size', 'brand_name', 'type', 'std_otd', 'ply_rating'];
        $samples = [
            ['11.00-20', 'BRIDGESTONE', 'Bias', '16.5', '16'],
            ['10.00-20', 'GITI', 'Bias', '15.0', '14'],
            ['R25 29.5', 'MICHELIN', 'Radial', '42.0', '32'],
        ];

        $guidelines = [
            ['column' => 'size', 'label' => 'Ukuran Ban (Size)', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => '11.00-20', 'notes' => 'Ukuran spesifikasi ban sesuai standar.'],
            ['column' => 'brand_name', 'label' => 'Nama Brand', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'BRIDGESTONE', 'notes' => 'Nama brand terkait ukuran ini (otomatis didaftarkan jika belum ada).'],
            ['column' => 'type', 'label' => 'Tipe Konstruksi', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Bias', 'notes' => 'Pilihan: Bias / Radial.'],
            ['column' => 'std_otd', 'label' => 'Standar OTD (mm)', 'type' => 'Desimal (mm)', 'required' => 'OPSIONAL', 'sample' => '16.5', 'notes' => 'Kedalaman tapak awal standar untuk ukuran ini.'],
            ['column' => 'ply_rating', 'label' => 'Ply Rating (PR)', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '16', 'notes' => 'Kekuatan ply rating (angka).'],
        ];

        $refSections = [
            'Daftar Brand Terdaftar' => TyreBrand::orderBy('brand_name')->pluck('brand_name')->toArray(),
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '0891B2'),
            new TemplateGuideSheet('Tyre Size (Master Ukuran Ban)', 'Template ini digunakan untuk mengunggah master data ukuran ban.', $guidelines, '0891B2'),
            new TemplateReferenceSheet('Referensi Master Data', $refSections),
        ];
    }

    private function tyrePatternSheets(): array
    {
        $headers = ['pattern_name', 'brand', 'status'];
        $samples = [
            ['G580', 'BRIDGESTONE', 'Active'],
            ['GTL971', 'GITI', 'Active'],
            ['XDM2', 'MICHELIN', 'Active'],
        ];

        $guidelines = [
            ['column' => 'pattern_name', 'label' => 'Nama Kembangan (Pattern)', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'G580', 'notes' => 'Nama tipe kembangan atau pattern ban.'],
            ['column' => 'brand', 'label' => 'Nama Brand', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'BRIDGESTONE', 'notes' => 'Nama merek ban produsen pattern ini.'],
            ['column' => 'status', 'label' => 'Status Pattern', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Active', 'notes' => 'Pilihan: Active / Inactive.'],
        ];

        $refSections = [
            'Daftar Brand Terdaftar' => TyreBrand::orderBy('brand_name')->pluck('brand_name')->toArray(),
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, 'BE185D'),
            new TemplateGuideSheet('Tyre Pattern (Master Kembangan Ban)', 'Template ini digunakan untuk mengunggah master data kembangan ban.', $guidelines, 'BE185D'),
            new TemplateReferenceSheet('Referensi Master Data', $refSections),
        ];
    }

    private function failureCodesSheets(): array
    {
        $headers = ['failure_code', 'failure_name', 'default_category'];
        $samples = [
            ['CUT', 'Cut Separation', 'Repair'],
            ['EXTN', 'External Damage', 'Repair'],
            ['WEAR', 'Irregular Wear', 'Claim'],
            ['BURS', 'Burst/Blowout', 'Scrap'],
            ['BEAD', 'Bead Damage', 'Repair'],
        ];

        $guidelines = [
            ['column' => 'failure_code', 'label' => 'Kode Kerusakan (Code)', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'CUT', 'notes' => 'Kode singkat kerusakan (disarankan huruf kapital unik).'],
            ['column' => 'failure_name', 'label' => 'Nama / Deskripsi Kerusakan', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'Cut Separation', 'notes' => 'Penjelasan lengkap jenis kerusakan ban.'],
            ['column' => 'default_category', 'label' => 'Kategori Tindakan', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Repair', 'notes' => 'Pilihan: Repair (Dapat Diperbaiki) / Scrap (Afkir) / Claim (Klaim Garansi).'],
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, 'DC2626'),
            new TemplateGuideSheet('Failure Codes (Kamus Kerusakan Ban)', 'Template ini digunakan untuk mengunggah master kamus kode kerusakan ban.', $guidelines, 'DC2626'),
        ];
    }

    private function locationsSheets(): array
    {
        $headers = ['location_name', 'location_type', 'capacity'];
        $samples = [
            ['GUDANG PUSAT', 'Warehouse', '300'],
            ['WORKSHOP SITE A', 'Service', '50'],
            ['AREA SCRAP UTAMA', 'Disposal', '500'],
        ];

        $guidelines = [
            ['column' => 'location_name', 'label' => 'Nama Lokasi / Gudang', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'GUDANG PUSAT', 'notes' => 'Nama lokasi kerja atau gudang penyimpanan (disarankan huruf kapital).'],
            ['column' => 'location_type', 'label' => 'Tipe Lokasi', 'type' => 'Pilihan', 'required' => 'WAJIB', 'sample' => 'Warehouse', 'notes' => 'Pilihan: Warehouse (Gudang) / Service (Bengkel/Workshop) / Disposal (Tempat Afkir).'],
            ['column' => 'capacity', 'label' => 'Kapasitas Maksimal (Unit Ban)', 'type' => 'Angka', 'required' => 'OPSIONAL', 'sample' => '300', 'notes' => 'Kapasitas daya tampung ban di lokasi ini (angka).'],
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '059669'),
            new TemplateGuideSheet('Locations (Master Lokasi Gudang/Kerja)', 'Template ini digunakan untuk mengunggah master data lokasi penyimpanan dan bengkel ban.', $guidelines, '059669'),
        ];
    }

    private function segmentsSheets(): array
    {
        $headers = ['segment_id', 'segment_name', 'location_name', 'terrain_type', 'status'];
        $samples = [
            ['SEG_HAUL_01', 'Coal Hauling', 'GUDANG SITE A', 'Muddy', 'Active'],
            ['SEG_OB_01', 'Overburden', 'GUDANG SITE A', 'Rocky', 'Active'],
            ['SEG_INFRA_01', 'Infrastructure', 'GUDANG PUSAT', 'Asphalt', 'Active'],
        ];

        $guidelines = [
            ['column' => 'segment_id', 'label' => 'ID / Kode Segmen', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'SEG_HAUL_01', 'notes' => 'Kode unik pengenal segmen operasional.'],
            ['column' => 'segment_name', 'label' => 'Nama Segmen Operasi', 'type' => 'Teks', 'required' => 'WAJIB', 'sample' => 'Coal Hauling', 'notes' => 'Nama lengkap divisi/segmen operasional (misal: Coal Hauling, Overburden).'],
            ['column' => 'location_name', 'label' => 'Nama Lokasi Terkait', 'type' => 'Teks', 'required' => 'OPSIONAL', 'sample' => 'GUDANG SITE A', 'notes' => 'Lokasi basis operasional segmen ini.'],
            ['column' => 'terrain_type', 'label' => 'Tipe Medan Jalan', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Muddy', 'notes' => 'Pilihan: Muddy (Berlumpur) / Rocky (Berbatu) / Asphalt (Aspal) / Unknown.'],
            ['column' => 'status', 'label' => 'Status Segmen', 'type' => 'Pilihan', 'required' => 'OPSIONAL', 'sample' => 'Active', 'notes' => 'Pilihan: Active / Inactive.'],
        ];

        $refSections = [
            'Daftar Lokasi Terdaftar' => TyreLocation::orderBy('location_name')->pluck('location_name')->toArray(),
        ];

        return [
            new TemplateDataSheet('Import Data', $headers, $samples, '0F766E'),
            new TemplateGuideSheet('Segments (Master Segmen Operasional)', 'Template ini digunakan untuk mengunggah master data segmen operasional.', $guidelines, '0F766E'),
            new TemplateReferenceSheet('Referensi Master Data', $refSections),
        ];
    }
}
