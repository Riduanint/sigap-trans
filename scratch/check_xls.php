<?php
require 'vendor/autoload.php';

$fileXls = 'c:/laragon/www/sigap-trans/.Bahan/Data UPT Kalsel.xls';
$fileXlsx = 'c:/laragon/www/sigap-trans/.Bahan/Data UPT Kalsel.xlsx';

$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
$spreadsheet = $reader->load($fileXls);

echo "Sheets: " . implode(', ', $spreadsheet->getSheetNames()) . "\n";
$sheet = $spreadsheet->getActiveSheet();
echo "Active Sheet: " . $sheet->getTitle() . "\n";
echo "Rows: " . $sheet->getHighestRow() . ", Cols: " . $sheet->getHighestColumn() . "\n";

// Print first 5 rows
for ($r = 1; $r <= 7; $r++) {
    $rowVals = [];
    for ($c = 'A'; $c <= 'N'; $c++) {
        $val = trim((string)$sheet->getCell($c . $r)->getValue());
        if ($val !== '') {
            $rowVals[] = "[$c$r]: $val";
        }
    }
    if (!empty($rowVals)) {
        echo "Row $r: " . implode(' | ', $rowVals) . "\n";
    }
}

// Convert to .xlsx format as well so Excel Viewer can render it smoothly
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save($fileXlsx);
echo "Successfully converted to .xlsx: $fileXlsx\n";
