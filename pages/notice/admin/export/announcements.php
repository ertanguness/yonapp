<?php
require_once dirname(__DIR__, levels: 4) . '/configs/bootstrap.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\IOFactory;

$format = strtolower($_GET['format'] ?? 'xlsx');

$db = \getDbConnection();
$stmt = $db->query("SELECT id, title, content, start_date, end_date, target_type, status FROM announcements ORDER BY id DESC");
$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

if ($format === 'print') {
    echo '<html><head><title>Duyurular</title><meta charset="utf-8">';
    echo '<link rel="stylesheet" href="/assets/vendors/css/vendors.min.css">';
    echo '</head><body><h3>Duyurular</h3><table border="1" cellpadding="6" cellspacing="0">';
    echo '<tr><th>ID</th><th>Başlık</th><th>İçerik</th><th>Başlangıç</th><th>Bitiş</th><th>Hedef</th><th>Durum</th></tr>';
    foreach ($rows as $r) {
        echo '<tr>';
        echo '<td>'.htmlspecialchars($r['id']).'</td>';
        echo '<td>'.htmlspecialchars($r['title']).'</td>';
        echo '<td>'.htmlspecialchars($r['content']).'</td>';
        echo '<td>'.htmlspecialchars($r['start_date']).'</td>';
        echo '<td>'.htmlspecialchars($r['end_date']).'</td>';
        echo '<td>'.htmlspecialchars($r['target_type']).'</td>';
        echo '<td>'.htmlspecialchars($r['status']).'</td>';
        echo '</tr>';
    }
    echo '</table></body></html>';
    exit;
}

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->fromArray(['ID','Başlık','İçerik','Başlangıç','Bitiş','Hedef','Durum'], null, 'A1');
if (!empty($rows)) {
    $sheet->fromArray(array_map(function($r){
        return [$r['id'],$r['title'],$r['content'],$r['start_date'],$r['end_date'],$r['target_type'],$r['status']];
    }, $rows), null, 'A2');
}

switch ($format) {
    case 'csv':
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="duyurular.csv"');
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(';');
        $writer->setEnclosure('"');
        $writer->save('php://output');
        break;
    case 'pdf':
        IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf::class);
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment;filename="duyurular.pdf"');
        $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
        $writer->save('php://output');
        break;
    case 'html':
        header('Content-Type: text/html; charset=utf-8');
        $writer = new Html($spreadsheet);
        $writer->save('php://output');
        break;
    default:
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="duyurular.xlsx"');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
}
exit;