#!/usr/bin/env python3
"""
Generate Excel Checklist — Kesiapan Penggunaan Sistem CPH Tyre Dashboard
========================================================================
Dokumen ini berisi checklist yang dibutuhkan untuk onboarding customer/user
ke sistem CPH Tyre Dashboard.

Generated: 28 Feb 2026
"""

import openpyxl
from openpyxl.styles import (
    Font, PatternFill, Alignment, Border, Side, numbers
)
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.datavalidation import DataValidation
import os

# ============================================================
# COLOR PALETTE
# ============================================================
CPH_RED = "C0392B"
CPH_DARK_RED = "922B21"
CPH_ORANGE = "E67E22"
CPH_DARK = "2C3E50"
CPH_LIGHT_GRAY = "ECF0F1"
CPH_WHITE = "FFFFFF"
CPH_GREEN = "27AE60"
CPH_BLUE = "2980B9"
CPH_YELLOW = "F39C12"
CPH_LIGHT_BLUE = "D6EAF8"
CPH_LIGHT_GREEN = "D5F5E3"
CPH_LIGHT_YELLOW = "FEF9E7"
CPH_LIGHT_RED = "FADBD8"

# ============================================================
# STYLE HELPERS
# ============================================================
thin_border = Border(
    left=Side(style='thin'),
    right=Side(style='thin'),
    top=Side(style='thin'),
    bottom=Side(style='thin')
)

medium_border = Border(
    left=Side(style='medium'),
    right=Side(style='medium'),
    top=Side(style='medium'),
    bottom=Side(style='medium')
)

def style_title(ws, row, col, text, merge_end_col=None):
    """Style a title cell"""
    cell = ws.cell(row=row, column=col, value=text)
    cell.font = Font(name='Calibri', size=16, bold=True, color=CPH_WHITE)
    cell.fill = PatternFill(start_color=CPH_RED, end_color=CPH_RED, fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
    if merge_end_col:
        ws.merge_cells(start_row=row, start_column=col, end_row=row, end_column=merge_end_col)
        for c in range(col, merge_end_col + 1):
            ws.cell(row=row, column=c).fill = PatternFill(start_color=CPH_RED, end_color=CPH_RED, fill_type='solid')
            ws.cell(row=row, column=c).border = medium_border

def style_subtitle(ws, row, col, text, merge_end_col=None):
    """Style a subtitle cell"""
    cell = ws.cell(row=row, column=col, value=text)
    cell.font = Font(name='Calibri', size=11, italic=True, color=CPH_DARK)
    cell.fill = PatternFill(start_color=CPH_LIGHT_GRAY, end_color=CPH_LIGHT_GRAY, fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
    if merge_end_col:
        ws.merge_cells(start_row=row, start_column=col, end_row=row, end_column=merge_end_col)
        for c in range(col, merge_end_col + 1):
            ws.cell(row=row, column=c).fill = PatternFill(start_color=CPH_LIGHT_GRAY, end_color=CPH_LIGHT_GRAY, fill_type='solid')

def style_section_header(ws, row, col, text, merge_end_col=None, color=CPH_DARK):
    """Style a section header"""
    cell = ws.cell(row=row, column=col, value=text)
    cell.font = Font(name='Calibri', size=12, bold=True, color=CPH_WHITE)
    cell.fill = PatternFill(start_color=color, end_color=color, fill_type='solid')
    cell.alignment = Alignment(horizontal='left', vertical='center', wrap_text=True)
    if merge_end_col:
        ws.merge_cells(start_row=row, start_column=col, end_row=row, end_column=merge_end_col)
        for c in range(col, merge_end_col + 1):
            ws.cell(row=row, column=c).fill = PatternFill(start_color=color, end_color=color, fill_type='solid')
            ws.cell(row=row, column=c).border = thin_border

def style_header_row(ws, row, headers, start_col=1, color=CPH_DARK_RED):
    """Style a header row"""
    for i, header in enumerate(headers):
        cell = ws.cell(row=row, column=start_col + i, value=header)
        cell.font = Font(name='Calibri', size=10, bold=True, color=CPH_WHITE)
        cell.fill = PatternFill(start_color=color, end_color=color, fill_type='solid')
        cell.alignment = Alignment(horizontal='center', vertical='center', wrap_text=True)
        cell.border = thin_border

def style_data_row(ws, row, data, start_col=1, alt=False):
    """Style a data row"""
    bg = CPH_LIGHT_GRAY if alt else CPH_WHITE
    for i, val in enumerate(data):
        cell = ws.cell(row=row, column=start_col + i, value=val)
        cell.font = Font(name='Calibri', size=10)
        cell.fill = PatternFill(start_color=bg, end_color=bg, fill_type='solid')
        cell.alignment = Alignment(horizontal='left', vertical='center', wrap_text=True)
        cell.border = thin_border

def add_checkbox_column(ws, row, col, alt=False):
    """Add a checkbox placeholder (☐)"""
    bg = CPH_LIGHT_GRAY if alt else CPH_WHITE
    cell = ws.cell(row=row, column=col, value="☐")
    cell.font = Font(name='Calibri', size=12)
    cell.fill = PatternFill(start_color=bg, end_color=bg, fill_type='solid')
    cell.alignment = Alignment(horizontal='center', vertical='center')
    cell.border = thin_border

def set_col_widths(ws, widths):
    """Set column widths"""
    for i, w in enumerate(widths, 1):
        ws.column_dimensions[get_column_letter(i)].width = w


# ============================================================
# SHEET 1: ROLE & AKSES — INTERNAL CPH
# ============================================================
def create_sheet_internal(wb):
    ws = wb.active
    ws.title = "1. Role Internal CPH"
    ws.sheet_properties.tabColor = CPH_RED

    set_col_widths(ws, [5, 25, 35, 40, 20, 15, 25])
    max_col = 7

    # Title
    style_title(ws, 1, 1, "CHECKLIST ROLE & AKSES — INTERNAL CPH", max_col)
    ws.row_dimensions[1].height = 40
    style_subtitle(ws, 2, 1, "Daftar role internal CPH beserta hak akses pada sistem CPH Tyre Dashboard", max_col)
    ws.row_dimensions[2].height = 25

    # Headers
    row = 4
    headers = ["No", "Role", "Deskripsi", "Hak Akses / Menu", "Jumlah User", "Status", "Catatan"]
    style_header_row(ws, row, headers)
    ws.row_dimensions[row].height = 30

    # Data
    roles = [
        ["1", "Super Admin", 
         "Administrator sistem dengan akses penuh ke seluruh fitur dan data.",
         "• Semua menu & fitur\n• Manajemen user & role\n• Import & Approval\n• Activity Log\n• Master Data\n• Dashboard & Laporan\n• Konfigurasi sistem",
         "", "☐ Siap", ""],
        ["2", "Manajerial", 
         "Level manajemen yang memiliki akses baca ke semua data, dashboard, dan log.",
         "• Dashboard (semua filter & chart)\n• View semua data & report\n• Export data\n• Activity Log (view)\n• Examination Report (view/cetak)",
         "", "☐ Siap", ""],
        ["3", "Supervisor", 
         "Level pengawas yang bisa mengedit data dan melakukan export/import.",
         "• Dashboard (semua filter & chart)\n• Master Data (view & edit)\n• Movement (input & edit)\n• Examination (input & edit)\n• Export data\n• Import data (upload & request)",
         "", "☐ Siap", ""],
        ["4", "Admin / Operator", 
         "Level input data yang bisa menginput data dan membuat request import.",
         "• Master Data (view & input)\n• Movement (input — pemasangan & pelepasan)\n• Examination (input inspeksi)\n• Import (upload request saja)\n• Dashboard (view terbatas)",
         "", "☐ Siap", ""],
        ["5", "Viewer / Guest", 
         "Akses terbatas hanya untuk melihat dashboard dan laporan.",
         "• Dashboard (view saja)\n• Report (view saja)",
         "", "☐ Siap", ""],
    ]

    for i, role_data in enumerate(roles):
        r = row + 1 + i
        style_data_row(ws, r, role_data, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 90

    # Summary section
    summary_row = row + 1 + len(roles) + 2
    style_section_header(ws, summary_row, 1, "📋 RINGKASAN KEBUTUHAN AKUN INTERNAL", max_col, CPH_DARK)

    sum_headers = ["No", "Informasi", "Jawaban", "Penanggung Jawab (PIC)", "Status", "", ""]
    style_header_row(ws, summary_row + 1, sum_headers)

    sum_data = [
        ["1", "Total user internal yang dibutuhkan", "", "", "☐ Diisi"],
        ["2", "Siapa yang bertindak sebagai Super Admin?", "", "", "☐ Diisi"],
        ["3", "Siapa yang bertindak sebagai Manajerial?", "", "", "☐ Diisi"],
        ["4", "Berapa Supervisor yang dibutuhkan?", "", "", "☐ Diisi"],
        ["5", "Berapa Admin/Operator yang dibutuhkan?", "", "", "☐ Diisi"],
        ["6", "Apakah perlu akun Viewer/Guest?", "", "", "☐ Diisi"],
        ["7", "Email resmi untuk akun (domain)?", "", "", "☐ Diisi"],
    ]

    for i, d in enumerate(sum_data):
        r = summary_row + 2 + i
        style_data_row(ws, r, d, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 25

    return ws


# ============================================================
# SHEET 2: ROLE & AKSES — EKSTERNAL (CUSTOMER)
# ============================================================
def create_sheet_external(wb):
    ws = wb.create_sheet("2. Role Eksternal (Customer)")
    ws.sheet_properties.tabColor = CPH_ORANGE

    set_col_widths(ws, [5, 25, 35, 40, 20, 15, 25])
    max_col = 7

    # Title
    style_title(ws, 1, 1, "CHECKLIST ROLE & AKSES — EKSTERNAL (CUSTOMER)", max_col)
    ws.row_dimensions[1].height = 40
    style_subtitle(ws, 2, 1, "Daftar role untuk user di sisi customer yang akan menggunakan sistem", max_col)
    ws.row_dimensions[2].height = 25

    # Headers
    row = 4
    headers = ["No", "Role Customer", "Deskripsi", "Hak Akses / Menu", "Kewajiban Customer", "Status", "Catatan"]
    style_header_row(ws, row, headers, color=CPH_ORANGE)
    ws.row_dimensions[row].height = 30

    roles = [
        ["1", "PIC Fleet / Manajer",
         "Penanggung jawab utama dari sisi customer untuk fleet management.",
         "• Dashboard (view data perusahaan sendiri)\n• Report & Export\n• Examination Report (view/cetak)\n• Lihat histori movement\n• Lihat master data unit sendiri",
         "• Menunjuk siapa saja yang diberi akses\n• Memastikan data unit kendaraan lengkap\n• Review & validasi report",
         "☐ Siap", ""],
        ["2", "Supervisor Lapangan",
         "Pengawas di lapangan yang memantau kondisi ban dan unit kendaraan.",
         "• Dashboard (view data area sendiri)\n• Movement (view histori)\n• Examination (view & input inspeksi)\n• Master data (view)",
         "• Input data inspeksi tepat waktu\n• Melaporkan anomali/kerusakan\n• Koordinasi dengan tim CPH",
         "☐ Siap", ""],
        ["3", "Admin / Operator Customer",
         "Petugas input data dari sisi customer.",
         "• Movement (input pemasangan & pelepasan)\n• Examination (input inspeksi)\n• Master data (view)",
         "• Input data sesuai SOP\n• Memastikan data HM/KM akurat\n• Upload foto kondisi ban\n• Melaporkan jika terjadi error input",
         "☐ Siap", ""],
        ["4", "Viewer Customer",
         "Akses view-only untuk memantau dashboard.",
         "• Dashboard (view saja)\n• Report (view saja)",
         "• Tidak ada kewajiban input\n• Memberikan feedback jika diperlukan",
         "☐ Siap", ""],
    ]

    for i, role_data in enumerate(roles):
        r = row + 1 + i
        style_data_row(ws, r, role_data, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 100

    # Summary
    summary_row = row + 1 + len(roles) + 2
    style_section_header(ws, summary_row, 1, "📋 KEWAJIBAN CUSTOMER SEBELUM AKSES DIBERIKAN", max_col, CPH_ORANGE)

    sum_headers = ["No", "Kewajiban / Syarat", "Deskripsi", "Dokumen Pendukung", "Status", "", "PIC Customer"]
    style_header_row(ws, summary_row + 1, sum_headers, color=CPH_ORANGE)

    items = [
        ["1", "Menunjuk PIC utama", "Customer menunjuk 1 orang sebagai PIC utama yang bertanggung jawab atas penggunaan sistem.", "Surat penunjukan / email resmi", "☐ Selesai"],
        ["2", "Daftar user yang diberi akses", "Customer menyediakan daftar nama, jabatan, dan email user yang akan diberi akses.", "Form daftar user (Excel)", "☐ Selesai"],
        ["3", "Data master kendaraan", "Customer menyediakan data lengkap kendaraan: no. polisi, merk, type, axle layout, curb weight, payload.", "File Excel / CSV", "☐ Selesai"],
        ["4", "Data master ban terpasang", "Customer menyediakan data ban yang saat ini terpasang di setiap unit kendaraan.", "File Excel / CSV", "☐ Selesai"],
        ["5", "Persetujuan SOP penggunaan", "Customer menyetujui SOP penggunaan sistem (tata cara input, jadwal inspeksi, dll).", "Tanda tangan SOP", "☐ Selesai"],
        ["6", "Training / Orientasi", "Customer mengikuti sesi training penggunaan sistem.", "Absensi / bukti training", "☐ Selesai"],
        ["7", "Sosialisasi ke operator", "Customer memastikan semua operator lapangan memahami cara penggunaan sistem.", "Notulensi sosialisasi", "☐ Selesai"],
    ]

    for i, d in enumerate(items):
        r = summary_row + 2 + i
        style_data_row(ws, r, d + ["", ""], alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 40

    return ws


# ============================================================
# SHEET 3: DATA REQUIREMENT PER MODULE
# ============================================================
def create_sheet_data_req(wb):
    ws = wb.create_sheet("3. Kebutuhan Data per Modul")
    ws.sheet_properties.tabColor = CPH_BLUE

    set_col_widths(ws, [5, 25, 45, 25, 15, 15, 25])
    max_col = 7

    # Title
    style_title(ws, 1, 1, "CHECKLIST KEBUTUHAN DATA PER MODUL", max_col)
    ws.row_dimensions[1].height = 40
    style_subtitle(ws, 2, 1, "Data apa saja yang perlu disiapkan sebelum sistem bisa digunakan", max_col)

    # ---- Master Data Section ----
    row = 4
    style_section_header(ws, row, 1, "🔧 MASTER DATA", max_col, CPH_BLUE)
    row += 1
    headers = ["No", "Modul", "Data yang Dibutuhkan", "Format", "Wajib?", "Status", "Catatan"]
    style_header_row(ws, row, headers, color=CPH_BLUE)

    master_items = [
        ["1", "Master Kendaraan (Vehicle)", 
         "• No. Polisi\n• Merk Kendaraan\n• Type Kendaraan\n• Area Operasi\n• Segment\n• Axle Layout (konfigurasi roda)\n• Curb Weight & Payload",
         "CSV / Excel", "✅ Ya", "☐ Siap", "Template tersedia di sistem"],
        ["2", "Master Ban (Tyre)", 
         "• Serial Number\n• Brand\n• Size\n• Pattern\n• OTD (Original Tread Depth)\n• Harga\n• Tanggal Pembelian",
         "CSV / Excel", "✅ Ya", "☐ Siap", "Template tersedia di sistem"],
        ["3", "Master Brand Ban", 
         "• Nama Brand\n• Keterangan",
         "CSV / Excel / Input Manual", "✅ Ya", "☐ Siap", "Bisa import atau manual"],
        ["4", "Master Size Ban", 
         "• Ukuran Ban (contoh: 295/80R22.5)\n• Keterangan",
         "CSV / Excel / Input Manual", "✅ Ya", "☐ Siap", ""],
        ["5", "Master Pattern Ban", 
         "• Nama Pattern (contoh: HSR2)\n• Brand\n• Keterangan",
         "CSV / Excel / Input Manual", "✅ Ya", "☐ Siap", ""],
        ["6", "Master Failure Code", 
         "• Kode Kerusakan\n• Deskripsi Kerusakan\n• Kategori (misal: prematur, worn out)",
         "CSV / Excel / Input Manual", "✅ Ya", "☐ Siap", "Bisa disesuaikan per customer"],
        ["7", "Master Lokasi / Area", 
         "• Nama Lokasi\n• Keterangan",
         "Input Manual", "✅ Ya", "☐ Siap", ""],
        ["8", "Master Segment", 
         "• Nama Segment\n• Keterangan",
         "Input Manual", "✅ Ya", "☐ Siap", ""],
    ]

    for i, item in enumerate(master_items):
        r = row + 1 + i
        style_data_row(ws, r, item, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 80

    # ---- Operational Data Section ----
    oper_row = row + 1 + len(master_items) + 2
    style_section_header(ws, oper_row, 1, "📊 DATA OPERASIONAL", max_col, CPH_GREEN)
    oper_row += 1
    style_header_row(ws, oper_row, headers, color=CPH_GREEN)

    oper_items = [
        ["1", "Data Pemasangan (Install)", 
         "• Serial Number Ban\n• No. Polisi Kendaraan\n• Posisi Pemasangan\n• Tanggal Pemasangan\n• HM/KM saat Pasang\n• Kondisi (New/Spare/Repair)\n• OTD/RTD saat Pasang",
         "CSV / Input Manual", "⚠️ Opsional\n(bisa input manual)", "☐ Siap", "Jika ada histori pemasangan sebelumnya"],
        ["2", "Data Pelepasan (Remove)", 
         "• Serial Number Ban\n• No. Polisi Kendaraan\n• Posisi Pelepasan\n• Tanggal Pelepasan\n• HM/KM saat Lepas\n• RTD saat Lepas\n• Failure Code / Alasan\n• Foto Kondisi Ban",
         "CSV / Input Manual", "⚠️ Opsional\n(bisa input manual)", "☐ Siap", ""],
        ["3", "Data Inspeksi (Examination)", 
         "• No. Polisi Kendaraan\n• Tanggal Inspeksi\n• HM/KM saat Inspeksi\n• PSI per Ban\n• RTD per Ban (hingga 4 titik)\n• Foto Kondisi\n• Catatan/Remarks",
         "Input Manual / Form", "⚠️ Opsional\n(berkala)", "☐ Siap", "Dilakukan secara berkala sesuai jadwal inspeksi"],
    ]

    for i, item in enumerate(oper_items):
        r = oper_row + 1 + i
        style_data_row(ws, r, item, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 100

    return ws


# ============================================================
# SHEET 4: CHECKLIST SETUP & ONBOARDING
# ============================================================
def create_sheet_onboarding(wb):
    ws = wb.create_sheet("4. Checklist Setup & Onboarding")
    ws.sheet_properties.tabColor = CPH_GREEN

    set_col_widths(ws, [5, 12, 35, 40, 15, 18, 25])
    max_col = 7

    # Title
    style_title(ws, 1, 1, "CHECKLIST SETUP & ONBOARDING SISTEM", max_col)
    ws.row_dimensions[1].height = 40
    style_subtitle(ws, 2, 1, "Langkah-langkah yang harus dilakukan sebelum dan sesudah go-live sistem", max_col)

    row = 4
    headers = ["No", "Fase", "Aktivitas", "Detail / Kriteria Selesai", "PIC", "Target", "Status"]
    style_header_row(ws, row, headers, color=CPH_GREEN)
    ws.row_dimensions[row].height = 30

    # Phase 1: Persiapan
    phase1_start = row + 1
    style_section_header(ws, phase1_start, 1, "FASE 1: PERSIAPAN (Sebelum Go-Live)", max_col, CPH_DARK)
    
    phase1 = [
        ["1.1", "Persiapan", "Pengumpulan data master kendaraan", 
         "• Semua data kendaraan aktif sudah terdata\n• Format sesuai template\n• Data divalidasi oleh PIC customer", 
         "", "", "☐ Belum"],
        ["1.2", "Persiapan", "Pengumpulan data master ban", 
         "• Semua ban aktif terdata dengan serial number\n• Data brand, size, pattern telah terdaftar\n• OTD terisi", 
         "", "", "☐ Belum"],
        ["1.3", "Persiapan", "Setup failure code", 
         "• Daftar failure code telah disepakati\n• Istilah kerusakan sudah seragam antara CPH & customer", 
         "", "", "☐ Belum"],
        ["1.4", "Persiapan", "Penunjukan PIC & daftar user", 
         "• PIC utama customer sudah ditunjuk\n• Daftar user beserta role sudah dikirim\n• Email untuk akun sudah dikonfirmasi", 
         "", "", "☐ Belum"],
        ["1.5", "Persiapan", "Penyepakatan SOP penggunaan", 
         "• SOP input data disepakati\n• Jadwal inspeksi berkala disepakati\n• Alur eskalasi error sudah jelas", 
         "", "", "☐ Belum"],
    ]

    for i, d in enumerate(phase1):
        r = phase1_start + 1 + i
        style_data_row(ws, r, d, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 60

    # Phase 2: Setup Sistem
    phase2_start = phase1_start + 1 + len(phase1) + 1
    style_section_header(ws, phase2_start, 1, "FASE 2: SETUP SISTEM", max_col, CPH_DARK)
    
    phase2 = [
        ["2.1", "Setup", "Pembuatan akun user (internal CPH)", 
         "• Akun Super Admin dibuat\n• Akun Manajerial, Supervisor, Admin dibuat\n• Password default di-set & dikomunikasikan", 
         "CPH IT", "", "☐ Belum"],
        ["2.2", "Setup", "Pembuatan akun user (eksternal customer)", 
         "• Akun PIC Fleet dibuat\n• Akun Supervisor & Operator dibuat\n• Permission per role sudah di-set", 
         "CPH IT", "", "☐ Belum"],
        ["2.3", "Setup", "Import data master kendaraan", 
         "• Data kendaraan berhasil di-import\n• Data diverifikasi oleh PIC customer\n• Axle layout terkonfigurasi dengan benar", 
         "CPH IT", "", "☐ Belum"],
        ["2.4", "Setup", "Import data master ban", 
         "• Data ban berhasil di-import\n• Serial number tervalidasi (tidak duplikat)\n• Data brand/size/pattern sudah benar", 
         "CPH IT", "", "☐ Belum"],
        ["2.5", "Setup", "Import histori pemasangan (jika ada)", 
         "• Data histori pemasangan existing berhasil di-import\n• Posisi ban saat ini sudah sesuai kondisi aktual", 
         "CPH IT", "", "☐ Belum"],
        ["2.6", "Setup", "Konfigurasi role & permission", 
         "• Semua role sudah terdefinisi\n• Permission per menu sudah di-set\n• Akses data dibatasi per company/toko", 
         "CPH IT", "", "☐ Belum"],
    ]

    for i, d in enumerate(phase2):
        r = phase2_start + 1 + i
        style_data_row(ws, r, d, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 60

    # Phase 3: Training & Go-Live
    phase3_start = phase2_start + 1 + len(phase2) + 1
    style_section_header(ws, phase3_start, 1, "FASE 3: TRAINING & GO-LIVE", max_col, CPH_DARK)
    
    phase3 = [
        ["3.1", "Training", "Training untuk user internal CPH", 
         "• Materi training disiapkan\n• Sesi training dilaksanakan\n• Peserta sudah paham alur kerja sistem", 
         "", "", "☐ Belum"],
        ["3.2", "Training", "Training untuk PIC & Supervisor customer", 
         "• Materi training customer disiapkan\n• Sesi training dilaksanakan\n• User bisa login & navigasi mandiri", 
         "", "", "☐ Belum"],
        ["3.3", "Training", "Training untuk operator customer", 
         "• Demo input data pemasangan/pelepasan\n• Demo input inspeksi\n• User bisa input data secara mandiri", 
         "", "", "☐ Belum"],
        ["3.4", "Go-Live", "Uji coba (Pilot / Simulasi)", 
         "• Uji coba input data real selama 1-2 minggu\n• Evaluasi error rate\n• Feedback dari user dikumpulkan", 
         "", "", "☐ Belum"],
        ["3.5", "Go-Live", "Go-Live", 
         "• Semua data sudah final\n• Semua user sudah terlatih\n• SOP sudah dijalankan\n• Support channel tersedia (WA/Telegram)", 
         "", "", "☐ Belum"],
    ]

    for i, d in enumerate(phase3):
        r = phase3_start + 1 + i
        style_data_row(ws, r, d, alt=(i % 2 == 1))
        ws.row_dimensions[r].height = 60

    return ws


# ============================================================
# SHEET 5: INFORMASI DOKUMEN
# ============================================================
def create_sheet_info(wb):
    ws = wb.create_sheet("Info Dokumen")
    ws.sheet_properties.tabColor = CPH_DARK

    set_col_widths(ws, [5, 25, 50, 25])
    max_col = 4

    style_title(ws, 1, 1, "INFORMASI DOKUMEN", max_col)
    ws.row_dimensions[1].height = 40

    row = 3
    info_data = [
        ["Nama Dokumen", "Checklist Kesiapan Penggunaan Sistem CPH Tyre Dashboard"],
        ["Versi", "1.0"],
        ["Tanggal Dibuat", "28 Februari 2026"],
        ["Dibuat Oleh", "Tim Pengembang CPH Tyre Dashboard"],
        ["Tujuan", "Memastikan semua kebutuhan data, akun, dan SOP sudah terpenuhi sebelum customer menggunakan sistem"],
        ["", ""],
        ["DAFTAR SHEET", ""],
        ["Sheet 1", "Role & Akses Internal CPH — Checklist role dan hak akses untuk user internal CPH"],
        ["Sheet 2", "Role & Akses Eksternal (Customer) — Checklist role, hak akses, dan kewajiban customer"],
        ["Sheet 3", "Kebutuhan Data per Modul — Data apa saja yang harus disiapkan per modul"],
        ["Sheet 4", "Checklist Setup & Onboarding — Langkah-langkah dari persiapan hingga go-live"],
        ["", ""],
        ["PETUNJUK PENGGUNAAN", ""],
        ["1.", "Isi setiap checklist sesuai kondisi aktual"],
        ["2.", "Kolom 'Status' diisi: ☐ Belum / ☑ Selesai / ⚠️ Sebagian"],
        ["3.", "Kolom 'PIC' diisi nama penanggung jawab"],
        ["4.", "Kolom 'Target' diisi tanggal target penyelesaian"],
        ["5.", "Serahkan dokumen ini ke Tim CPH setelah semua item terisi"],
    ]

    for i, d in enumerate(info_data):
        r = row + i
        if d[0] in ["DAFTAR SHEET", "PETUNJUK PENGGUNAAN"]:
            style_section_header(ws, r, 1, d[0], max_col, CPH_DARK)
        elif d[0] == "" and d[1] == "":
            continue
        else:
            cell_a = ws.cell(row=r, column=2, value=d[0])
            cell_a.font = Font(name='Calibri', size=10, bold=True, color=CPH_DARK)
            cell_a.alignment = Alignment(vertical='center')

            cell_b = ws.cell(row=r, column=3, value=d[1])
            cell_b.font = Font(name='Calibri', size=10)
            cell_b.alignment = Alignment(vertical='center', wrap_text=True)
            ws.row_dimensions[r].height = 25

    return ws


# ============================================================
# MAIN
# ============================================================
def main():
    wb = openpyxl.Workbook()
    
    # Create all sheets
    create_sheet_internal(wb)
    create_sheet_external(wb)
    create_sheet_data_req(wb)
    create_sheet_onboarding(wb)
    create_sheet_info(wb)

    # Set print options for all sheets
    for ws in wb.worksheets:
        ws.page_setup.orientation = 'landscape'
        ws.page_setup.paperSize = ws.PAPERSIZE_A4
        ws.page_setup.fitToWidth = 1
        ws.page_setup.fitToHeight = 0
        ws.sheet_view.showGridLines = False

    # Save
    output_dir = os.path.dirname(os.path.abspath(__file__))
    output_path = os.path.join(output_dir, "CPH_Tyre_Dashboard_Checklist_Kesiapan_Sistem.xlsx")
    wb.save(output_path)
    print(f"✅ File berhasil dibuat: {output_path}")
    print(f"   📄 Jumlah sheet: {len(wb.worksheets)}")
    for ws in wb.worksheets:
        print(f"      - {ws.title}")


if __name__ == "__main__":
    main()
