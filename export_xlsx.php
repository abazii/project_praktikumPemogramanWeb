<?php
session_start();
require 'koneksi.php';

// Proteksi agar hanya Owner yang bisa export laporan
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'owner') {
    header("Location: index.php?error=Akses ditolak!");
    exit();
}

// Ambil data booking dari database untuk diolah
$result = $koneksi->query("SELECT * FROM bookings ORDER BY id_booking ASC");

$rows_data         = [];
$total_booking     = 0;
$total_omzet       = 0;
$total_dp_masuk    = 0;
$total_sisa_bayar  = 0;
$count_menunggu    = 0;
$count_berikutnya  = 0;
$count_diproses    = 0;
$count_selesai     = 0;

while ($row = $result->fetch_assoc()) {
    $harga = (int) $row['harga_service'];
    $dp    = (int) $row['jumlah_dp'];
    $sisa  = max(0, $harga - $dp);

    $total_booking++;
    $total_omzet      += $harga;
    $total_dp_masuk   += $dp;
    $total_sisa_bayar += $sisa;

    if ($row['status_antrian'] === 'Menunggu') $count_menunggu++;
    elseif ($row['status_antrian'] === 'Antrian Berikutnya') $count_berikutnya++;
    elseif ($row['status_antrian'] === 'Sedang Diproses') $count_diproses++;
    elseif ($row['status_antrian'] === 'Selesai') $count_selesai++;

    $row['sisa_bayar'] = $sisa;
    $rows_data[] = $row;
}

// Helper konversi indeks kolom ke huruf Excel (0 -> A, 1 -> B, dst)
function excelCol($index)
{
    $letter = '';
    while ($index >= 0) {
        $letter = chr($index % 26 + 65) . $letter;
        $index = intval($index / 26) - 1;
    }
    return $letter;
}

// Helper membuat sel string XML
function xmlStrCell($colIndex, $rowNum, $text, $style = 0)
{
    $ref = excelCol($colIndex) . $rowNum;
    $escaped = htmlspecialchars((string)$text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    return '<c r="' . $ref . '" s="' . $style . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
}

// Helper membuat sel angka XML
function xmlNumCell($colIndex, $rowNum, $num, $style = 0)
{
    $ref = excelCol($colIndex) . $rowNum;
    return '<c r="' . $ref . '" s="' . $style . '"><v>' . (float)$num . '</v></c>';
}

// Susun baris-baris worksheet XML
$sheetRows = [];

// Judul Laporan
$sheetRows[] = '<row r="1">' . xmlStrCell(0, 1, 'REKAPAN DATA BOOKING ONLINE - BARBERBOR RETRO BARBERSHOP', 1) . '</row>';
$sheetRows[] = '<row r="2">' . xmlStrCell(0, 2, 'Tanggal Export: ' . date('d-m-Y H:i:s'), 0) . '</row>';
$sheetRows[] = '<row r="3"></row>';

// Blok Ringkasan Olahan Data
$sheetRows[] = '<row r="4">' . xmlStrCell(0, 4, 'RINGKASAN REKAPAN TRANSAKSI', 1) . xmlStrCell(3, 4, 'REKAP STATUS ANTRIAN', 1) . '</row>';
$sheetRows[] = '<row r="5">' . xmlStrCell(0, 5, 'Total Transaksi Booking', 0) . xmlNumCell(1, 5, $total_booking, 0) . xmlStrCell(3, 5, 'Menunggu', 0) . xmlNumCell(4, 5, $count_menunggu, 0) . '</row>';
$sheetRows[] = '<row r="6">' . xmlStrCell(0, 6, 'Total Nilai Harga Service (Rp)', 0) . xmlNumCell(1, 6, $total_omzet, 2) . xmlStrCell(3, 6, 'Antrian Berikutnya', 0) . xmlNumCell(4, 6, $count_berikutnya, 0) . '</row>';
$sheetRows[] = '<row r="7">' . xmlStrCell(0, 7, 'Total DP QRIS Masuk (Rp)', 0) . xmlNumCell(1, 7, $total_dp_masuk, 2) . xmlStrCell(3, 7, 'Sedang Diproses', 0) . xmlNumCell(4, 7, $count_diproses, 0) . '</row>';
$sheetRows[] = '<row r="8">' . xmlStrCell(0, 8, 'Total Sisa Bayar di Tempat (Rp)', 0) . xmlNumCell(1, 8, $total_sisa_bayar, 2) . xmlStrCell(3, 8, 'Selesai', 0) . xmlNumCell(4, 8, $count_selesai, 0) . '</row>';
$sheetRows[] = '<row r="9"></row>';

// Header Tabel Detail Booking (Baris 10)
$headers = [
    'No. Antrian',
    'Waktu Booking',
    'Nama Customer',
    'No. Handphone',
    'Email Customer',
    'Layanan (Service)',
    'Harga Service (Rp)',
    'DP QRIS Masuk (Rp)',
    'Sisa Pelunasan (Rp)',
    'Metode Bayar',
    'Status Antrian'
];

$headerCells = '';
foreach ($headers as $idx => $hText) {
    $headerCells .= xmlStrCell($idx, 10, $hText, 1);
}
$sheetRows[] = '<row r="10">' . $headerCells . '</row>';

// Isi Tabel Detail Booking
$rNum = 11;
foreach ($rows_data as $item) {
    $c  = xmlStrCell(0, $rNum, '#' . $item['id_booking'], 0);
    $c .= xmlStrCell(1, $rNum, $item['waktu_booking'], 0);
    $c .= xmlStrCell(2, $rNum, $item['nama_customer'], 0);
    $c .= xmlStrCell(3, $rNum, $item['no_hp'], 0);
    $c .= xmlStrCell(4, $rNum, $item['email_customer'], 0);
    $c .= xmlStrCell(5, $rNum, $item['nama_service'], 0);
    $c .= xmlNumCell(6, $rNum, $item['harga_service'], 2);
    $c .= xmlNumCell(7, $rNum, $item['jumlah_dp'], 2);
    $c .= xmlNumCell(8, $rNum, $item['sisa_bayar'], 2);
    $c .= xmlStrCell(9, $rNum, $item['metode_pembayaran'], 0);
    $c .= xmlStrCell(10, $rNum, $item['status_antrian'], 0);

    $sheetRows[] = '<row r="' . $rNum . '">' . $c . '</row>';
    $rNum++;
}

// Baris Total Keseluruhan di Bawah Tabel
$totalCells  = xmlStrCell(5, $rNum, 'TOTAL KESELURUHAN', 1);
$totalCells .= xmlNumCell(6, $rNum, $total_omzet, 3);
$totalCells .= xmlNumCell(7, $rNum, $total_dp_masuk, 3);
$totalCells .= xmlNumCell(8, $rNum, $total_sisa_bayar, 3);
$sheetRows[] = '<row r="' . $rNum . '">' . $totalCells . '</row>';

// Komponen XML pembentuk file .xlsx
$contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

$relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

$workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Rekapan Booking Barberbor" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

$workbookRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="#,##0"/>
  </numFmts>
  <fonts count="2">
    <font><sz val="11"/><name val="Calibri"/></font>
    <font><b/><sz val="11"/><color rgb="FF18120D"/><name val="Calibri"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFE09F3E"/></patternFill></fill>
  </fills>
  <borders count="1">
    <border><left/><right/><top/><bottom/><diagonal/></border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="4">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
    <xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>
    <xf numFmtId="164" fontId="1" fillId="2" borderId="0" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1"/>
  </cellXfs>
</styleSheet>';

$sheet1Xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <cols>
    <col min="1" max="1" width="16" customWidth="1"/>
    <col min="2" max="2" width="22" customWidth="1"/>
    <col min="3" max="3" width="26" customWidth="1"/>
    <col min="4" max="4" width="18" customWidth="1"/>
    <col min="5" max="5" width="28" customWidth="1"/>
    <col min="6" max="6" width="30" customWidth="1"/>
    <col min="7" max="9" width="20" customWidth="1"/>
    <col min="10" max="11" width="20" customWidth="1"/>
  </cols>
  <sheetData>' . implode('', $sheetRows) . '</sheetData>
</worksheet>';

// Buat arsip ZIP menjadi file .xlsx di temporary file
$tempFile = tempnam(sys_get_temp_dir(), 'xlsx');
$zip = new ZipArchive();

if ($zip->open($tempFile, ZipArchive::OVERWRITE) === true) {
    $zip->addFromString('[Content_Types].xml', $contentTypesXml);
    $zip->addFromString('_rels/.rels', $relsXml);
    $zip->addFromString('xl/workbook.xml', $workbookXml);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXml);
    $zip->addFromString('xl/styles.xml', $stylesXml);
    $zip->addFromString('xl/worksheets/sheet1.xml', $sheet1Xml);
    $zip->close();

    $filename = 'Rekapan_Booking_Barberbor_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: max-age=0');

    readfile($tempFile);
    unlink($tempFile);
    exit();
} else {
    die("Gagal membuat file XLSX.");
}
