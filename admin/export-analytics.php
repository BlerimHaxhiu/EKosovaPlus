<?php
declare(strict_types=1);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$sessionPath = dirname(__DIR__) . '/storage/sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0775, true);
}
session_save_path($sessionPath);
session_start();

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$adminPath = '/admin/export-analytics.php';
if (substr($scriptName, -strlen($adminPath)) === $adminPath) {
    $_SERVER['SCRIPT_NAME'] = substr($scriptName, 0, -strlen($adminPath)) . '/public/index.php';
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/utils/helpers.php';
require_once __DIR__ . '/../src/middleware/auth.php';
require_once __DIR__ . '/../vendor/autoload.php';

require_role(['admin']);

$metrics = [
    ['Vizita fizike', 5, 0, 'vizita'],
    ['Hapa proceduralë', 10, 4, 'hapa'],
    ['Dokumente manuale', 6, 0, 'dokumente'],
    ['Verifikime manuale', 5, 1, 'verifikime'],
    ['Ankesat', 28, 3, 'ditë'],
    ['Koha mesatare', 5760, 5, 'minuta'],
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Analitika');

$sheet->setCellValue('A1', 'Analitika e Optimizimit - EKosova+');
$sheet->mergeCells('A1:E1');
$sheet->fromArray(
    ['Metrika', 'Vlera tradicionale', 'Vlera EKosova+', 'Njësia', 'Përqindja e optimizimit'],
    null,
    'A3'
);

$row = 4;
foreach ($metrics as [$label, $traditional, $ekosova, $unit]) {
    $optimization = $traditional > 0 ? (($traditional - $ekosova) / $traditional) * 100 : 0;

    $sheet->setCellValue('A' . $row, $label);
    $sheet->setCellValue('B' . $row, $traditional);
    $sheet->setCellValue('C' . $row, $ekosova);
    $sheet->setCellValue('D' . $row, $unit);
    $sheet->setCellValue('E' . $row, $optimization / 100);
    $row++;
}

$lastRow = $row - 1;
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FF155FA8');
$sheet->getStyle('A3:E3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle('A3:E3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF155FA8');
$sheet->getStyle('A3:E' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD7DCE3');
$sheet->getStyle('A3:E' . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
$sheet->getStyle('B4:C' . $lastRow)->getNumberFormat()->setFormatCode('0');
$sheet->getStyle('E4:E' . $lastRow)->getNumberFormat()->setFormatCode('0.00%');

foreach (range('A', 'E') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

$sheet->freezePane('A4');

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="analitika-optimizimit-ekosova-plus.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
