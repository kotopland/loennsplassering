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
        // Set the compatibility mode for PhpSpreadsheet to Excel.
        // This ensures that the generated Excel file is compatible with Excel.
        // Without this, some formulas or features might not work correctly in Excel.
        // This is a global setting that affects all calculations in the spreadsheet.

        \PhpOffice\PhpSpreadsheet\Calculation\Functions::setCompatibilityMode(
            \PhpOffice\PhpSpreadsheet\Calculation\Functions::COMPATIBILITY_EXCEL
        );

        // Load the existing spreadsheet using PhpSpreadsheet
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx;
        $spreadsheet = $reader->load(storage_path('app/public/'.$this->filePath));

        // Get the first sheet
        $sheet = $spreadsheet->getSheet(0);

        // Iterate through the data for the first sheet.
        foreach ($this->dataSheet1 ?? [] as $item) {
            // Define the cell to modify.
            $cell = $item['column'].$item['row'];
            // Define the value to set in the cell.
            $value = $item['value'];

            // If the data type is date, convert it to Excel date format.
            if ($item['datatype'] === 'date') {
                $value = Date::dateTimeToExcel(new \DateTime($value));
            }

            // Set the cell value.
            $sheet->setCellValue($cell, $value);

            // If the data type is date, set the number format to yyyy-mm-dd.
            // This ensures that the date is displayed correctly in Excel.

            if ($item['datatype'] === 'date') {
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            }

            // $sheet->getStyle('A2')->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
        }

        // Get the second sheet
        $sheet = $spreadsheet->getSheet(1);

        // Iterate through the data for the second sheet.
        foreach ($this->dataSheet2 ?? [] as $item) {
            // Define the cell to modify.
            $cell = $item['column'].$item['row'];
            // Define the value to set in the cell.
            $value = $item['value'];

            // If the data type is date, convert it to Excel date format.
            if ($item['datatype'] === 'date') {
                $value = Date::dateTimeToExcel(new \DateTime($value));
            }

            // Set the cell value.
            $sheet->setCellValue($cell, $value);

            // If the data type is date, set the number format to yyyy-mm-dd.
            // This ensures that the date is displayed correctly in Excel.
            if ($item['datatype'] === 'date') {
                $sheet->getStyle($cell)->getNumberFormat()->setFormatCode('yyyy-mm-dd');
            }

            // $sheet->getStyle('A2')->getFont()->getColor()->setARGB(Color::COLOR_BLUE);
        }

        // Save the modified spreadsheet
        $writer = new Xlsx($spreadsheet);
        $writer->save(storage_path('app/public/'.$newFilePath));
    }
}
