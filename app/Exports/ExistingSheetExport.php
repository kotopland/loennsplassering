<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExistingSheetExport
{
    protected $data;

    protected $filePath;

    public function __construct($data, $filePath)
    {
        $this->data = $data;
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
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($this->data as $item) {
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
