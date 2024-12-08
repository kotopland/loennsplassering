<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExistingSheetExport
{
    protected $dataSheet1;

    protected $dataSheet2;

    protected $filePath;

    public function __construct($data, $filePath)
    {
        $this->dataSheet1 = $data['sheet1'] ?? [];
        $this->dataSheet2 = $data['sheet2'] ?? [];
        $this->filePath = $filePath;
    }

    public function modifyAndSave(string $newFilePath)
    {
        \PhpOffice\PhpSpreadsheet\Calculation\Functions::setCompatibilityMode(
            \PhpOffice\PhpSpreadsheet\Calculation\Functions::COMPATIBILITY_EXCEL
        );

        // Load the existing spreadsheet using PhpSpreadsheet
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
        $spreadsheet = $reader->load(storage_path('app/public/'.$this->filePath));

        // Get the first sheet
        $sheet = $spreadsheet->getSheet(0);

        foreach ($this->dataSheet1 ?? [] as $item) {
            $cell = $item['column'].$item['row'];
            $value = $item['value'];

            if ($item['datatype'] === 'date') {
                $value = Date::dateTimeToExcel(new \DateTime($value));
            }

            $sheet->setCellValue($cell, $value);
            // $sheet->getStyle('A2')->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
        }

        // Get the second sheet
        $sheet = $spreadsheet->getSheet(1);

        foreach ($this->dataSheet2 ?? [] as $item) {
            $cell = $item['column'].$item['row'];
            $value = $item['value'];

            if ($item['datatype'] === 'date') {
                $value = Date::dateTimeToExcel(new \DateTime($value));
            }

            $sheet->setCellValue($cell, $value);
            // $sheet->getStyle('A2')->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
        }

        // Save the modified spreadsheet
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/public/'.$newFilePath));
    }
}
