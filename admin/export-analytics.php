<?php
declare(strict_types=1);

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

require_role(['admin']);

$autoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($autoload)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Per eksport ne Excel duhet te instalohet phpoffice/phpspreadsheet me Composer.';
    exit;
}

require_once $autoload;

if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
    header('Content-Type: text/plain; charset=utf-8');
    echo 'Per eksport ne Excel duhet te instalohet phpoffice/phpspreadsheet me Composer.';
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$weights = ['w1' => 0.30, 'w2' => 0.20, 'w3' => 0.20, 'w4' => 0.15, 'w5' => 0.10, 'w6' => 0.05];
$traditional = ['T' => 5760, 'H' => 10, 'D' => 6, 'V' => 5, 'M' => 5, 'G' => 20];
$ekosova = ['T' => 0.05, 'H' => 2.5, 'D' => 0, 'V' => 0, 'M' => 0, 'G' => 0];
$zTraditional = objective_value($traditional, $weights);
$zEkosova = objective_value($ekosova, $weights);
$optimizationScore = $zTraditional > 0 ? (($zTraditional - $zEkosova) / $zTraditional) * 100 : 0;

$metrics = [
    ['Reduktimi i dokumenteve fizike', '6 dokumente', '0 dokumente fizike', 100],
    ['Reduktimi i vizitave fizike', '5 institucione fizike', '0 vizita fizike', 100],
    ['Reduktimi i hapave procedurale', '10 hapa', '2-3 hapa', percent_reduction_export(10, 2.5)],
    ['Reduktimi i kohes se aplikimit', '4 dite', '3 sekonda pas klikimit', percent_reduction_export(345600, 3)],
    ['Eliminimi i refuzimit manual', 'Listim i plote dhe refuzim manual', 'Shfaqen vetem bursat e pershtatshme', 100],
];

$comparison = [
    ['Pikat e kontaktit', '5 institucione', '1 platforme'],
    ['Hapat procedurale', '10', '2 ose 3'],
    ['Dokumentet fizike/PDF', '6', '0'],
    ['Koha', '4 dite', '3 sekonda pas klikimit'],
    ['Verifikimet manuale', '5', '0'],
    ['Gabimet nga dokumentet e paplota', '20%', '0% ose shume afer 0'],
    ['Rreziku i aplikimit te gabuar', 'i larte', 'i ulet'],
    ['Nevoja per printim', 'po', 'jo'],
    ['Nevoja per dorezim fizik', 'po', 'jo'],
];

$documents = [
    ['ID / Leternjoftimi', 'Te dhenat personale', 'Agjencia e Regjistrimit Civil', 'Digjitalizuar'],
    ['Certifikata e Vendbanimit', 'Vendbanimi', 'Komuna perkatese', 'Digjitalizuar'],
    ['Certifikata e Notave', 'Te dhenat akademike', 'Universiteti perkates', 'Digjitalizuar'],
    ['Konfirmimi Bankar', 'Banka', 'Banka perkatese', 'Digjitalizuar'],
    ['Vertetimi nga ATK', 'Punesimi', 'Administrata Tatimore e Kosoves', 'Digjitalizuar'],
    ['Vertetimi per Ndihme Sociale', 'Statusi social', 'Qendra per Pune Sociale', 'Digjitalizuar'],
];

$sources = [
    ['Eliminimi i dokumenteve fizike/PDF'],
    ['Eliminimi i vizitave fizike'],
    ['Matching automatik student-burse'],
    ['Shfaqja vetem e bursave te pershtatshme'],
    ['Eliminimi i refuzimit manual'],
    ['Aplikimi me nje klikim'],
    ['Ruajtja e te dhenave ne profil'],
    ['Verifikimi institucional i simuluar'],
    ['Reduktimi i gabimeve nga dokumentet e paplota'],
    ['Reduktimi i ngarkeses se komisionit'],
];

$stats = [
    ['Bursa aktive', scalar_export('SELECT COUNT(*) FROM scholarships WHERE status=?', ['active'])],
    ['Total aplikime', scalar_export('SELECT COUNT(*) FROM applications')],
    ['Aplikime te fituara', scalar_export('SELECT COUNT(*) FROM applications WHERE status IN ("fituar", "approved")')],
    ['Ankesa', scalar_export('SELECT COUNT(*) FROM complaints')],
    ['Profile te kompletuara', scalar_export('SELECT COUNT(*) FROM student_profiles WHERE personal_number<>"" AND city<>"" AND average_grade IS NOT NULL AND bank_name<>"" AND bank_account_number<>""')],
    ['Profile me mungesa', scalar_export('SELECT COUNT(*) FROM student_profiles WHERE personal_number="" OR city="" OR average_grade IS NULL OR bank_name="" OR bank_account_number=""')],
];

$applications = rows_export('SELECT a.id, st.name student, s.title scholarship, a.status, COALESCE(a.applied_at, a.created_at) applied_at FROM applications a JOIN users st ON st.id=a.student_id JOIN scholarships s ON s.id=a.scholarship_id ORDER BY a.created_at DESC');
$complaints = rows_export('SELECT c.id, u.name student, c.scholarship_category, COALESCE(s.title, c.provider_name) scholarship, c.status, c.created_at FROM complaints c JOIN users u ON u.id=c.student_id LEFT JOIN applications a ON a.id=c.application_id LEFT JOIN scholarships s ON s.id=a.scholarship_id ORDER BY c.created_at DESC');

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Metrikat');
write_table($sheet, 'A1', 'Analitika e Optimizimit - EKosova+', ['Metrika', 'Para', 'Pas', 'Optimizimi %'], $metrics);

$objectiveSheet = $spreadsheet->createSheet();
$objectiveSheet->setTitle('Funksioni Z');
write_table($objectiveSheet, 'A1', 'Funksioni objektiv', ['Parametri', 'Tradicional', 'EKosova+', 'Pesha'], [
    ['T - koha', $traditional['T'], $ekosova['T'], $weights['w1']],
    ['H - hapat', $traditional['H'], $ekosova['H'], $weights['w2']],
    ['D - dokumentet', $traditional['D'], $ekosova['D'], $weights['w3']],
    ['V - vizitat', $traditional['V'], $ekosova['V'], $weights['w4']],
    ['M - verifikimet manuale', $traditional['M'], $ekosova['M'], $weights['w5']],
    ['G - gabimet', $traditional['G'], $ekosova['G'], $weights['w6']],
    ['Z total', $zTraditional, $zEkosova, ''],
    ['Optimization score', $optimizationScore . '%', '', ''],
]);

$comparisonSheet = $spreadsheet->createSheet();
$comparisonSheet->setTitle('Para Pas');
write_table($comparisonSheet, 'A1', 'Tabela Para / Pas', ['Metrika', 'Tradicional', 'EKosova+'], $comparison);

$documentsSheet = $spreadsheet->createSheet();
$documentsSheet->setTitle('Dokumentet');
write_table($documentsSheet, 'A1', 'Dokumente te zevendesuara', ['Dokumenti tradicional', 'Seksioni ne profil', 'Institucioni', 'Statusi'], $documents);

$statsSheet = $spreadsheet->createSheet();
$statsSheet->setTitle('Statistika DB');
write_table($statsSheet, 'A1', 'Statistika nga databaza', ['Metrika', 'Vlera'], $stats);

$applicationsSheet = $spreadsheet->createSheet();
$applicationsSheet->setTitle('Aplikime');
write_table($applicationsSheet, 'A1', 'Aplikimet', ['ID', 'Studenti', 'Bursa', 'Statusi', 'Data'], array_map(fn($row) => array_values($row), $applications));

$complaintsSheet = $spreadsheet->createSheet();
$complaintsSheet->setTitle('Ankesa');
write_table($complaintsSheet, 'A1', 'Ankesat', ['ID', 'Studenti', 'Kategoria', 'Ofruesi/Bursa', 'Statusi', 'Data'], array_map(fn($row) => array_values($row), $complaints));

$sourcesSheet = $spreadsheet->createSheet();
$sourcesSheet->setTitle('Burimet');
write_table($sourcesSheet, 'A1', 'Burimet e optimizimit', ['Burimi'], $sources);

$spreadsheet->setActiveSheetIndex(0);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="analitika-optimizimit-ekosova-plus.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;

function objective_value(array $values, array $weights): float
{
    return ($weights['w1'] * $values['T'])
        + ($weights['w2'] * $values['H'])
        + ($weights['w3'] * $values['D'])
        + ($weights['w4'] * $values['V'])
        + ($weights['w5'] * $values['M'])
        + ($weights['w6'] * $values['G']);
}

function percent_reduction_export(float $before, float $after): float
{
    return $before > 0 ? max(0, min(100, (($before - $after) / $before) * 100)) : 0;
}

function scalar_export(string $sql, array $params = []): int
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

function rows_export(string $sql, array $params = []): array
{
    try {
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

function write_table(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $cell, string $title, array $headers, array $rows): void
{
    $sheet->setCellValue($cell, $title);
    [$startColumn, $startRow] = coordinate_parts($cell);
    $headerRow = $startRow + 2;
    $sheet->fromArray($headers, null, $startColumn . $headerRow);
    $dataRow = $headerRow + 1;
    if ($rows) {
        $sheet->fromArray($rows, null, $startColumn . $dataRow);
    }
    $lastColumn = chr(ord($startColumn) + count($headers) - 1);
    $lastRow = max($dataRow, $dataRow + count($rows) - 1);
    $sheet->mergeCells($startColumn . $startRow . ':' . $lastColumn . $startRow);
    $sheet->getStyle($startColumn . $startRow)->getFont()->setBold(true)->setSize(16)->getColor()->setARGB('FF155FA8');
    $sheet->getStyle($startColumn . $headerRow . ':' . $lastColumn . $headerRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
    $sheet->getStyle($startColumn . $headerRow . ':' . $lastColumn . $headerRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF155FA8');
    $sheet->getStyle($startColumn . $headerRow . ':' . $lastColumn . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setARGB('FFD7DCE3');
    $sheet->getStyle($startColumn . $headerRow . ':' . $lastColumn . $lastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    foreach (range($startColumn, $lastColumn) as $column) {
        $sheet->getColumnDimension($column)->setAutoSize(true);
    }
}

function coordinate_parts(string $cell): array
{
    preg_match('/^([A-Z]+)([0-9]+)$/', $cell, $matches);
    return [$matches[1] ?? 'A', (int) ($matches[2] ?? 1)];
}
