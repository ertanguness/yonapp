<?php
require_once __DIR__ . '/../App/Helper/Date.php';
use App\Helper\Date;

$inputs = [
    '04/12/2025-16:13:53',
    '11/02/2025-12:39:03',
    '07.11.2025 20:42',
];

foreach ($inputs as $in) {
    echo "in=$in\n";
    $p = Date::parseExcelDate($in);
    echo "parseExcelDate=" . ($p ?? 'null') . "\n";
    echo "YmdHIS=" . Date::YmdHIS($in) . "\n";
}

