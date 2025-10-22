<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelService
{
    public $excelSpread;
    public function __construct(Spreadsheet $excelSpread)
    {
        $this->excelSpread = $excelSpread;
    }

    public function export($fieldsList, $fileName)
    {
        //основная страница
        $sheet = $this->excelSpread->getActiveSheet();
        $sheet->setTitle('Template');
        $spreadsheet = $sheet->getParent();

        //страница списков
        $listItemsSheet =  $spreadsheet->createSheet();
        $listItemsSheet->setTitle('List');

        $columnListLetter = Coordinate::stringFromColumnIndex(1);
        foreach ($fieldsList as $key => $val) {
            
            $columnLetter = Coordinate::stringFromColumnIndex($key + 1); //индекс колонки

            $sheet->setCellValue($columnLetter.'1',$val['data']['text']);

            //определение типа колонки
            $type = match ($val['data']['type']) {
                'number' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE,
                'decimal' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL,
                'text' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH,
                'list' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST,
                'multi_select' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST,
                default => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH,
            };

            //валидация типа колоноки
            if ($val['data']['type'] == 'number' || $val['data']['type'] == 'decimal') {
                $formula1 = 1;
                $formula2 = 1000000;
                $errorMes = 'введите число';
            } else if ($val['data']['type'] == 'text') {
                $formula1 = 1;
                $formula2 = 255;
                $errorMes = 'введите текст';
            } else if ($val['data']['type'] == 'list') {

                $errorMes = 'не корректнй формат';
                $isList = true;

                //формирование списка 
                $items = [];
                if (isset($val['field_list'])) {
                    foreach ($val['field_list'] as $listItem) {
                        $items[] =  $listItem['data']['list-item'];
                    }
                }

                // //тестовый список
                // $options = [];
                // for ($i = 0; $i < 10; $i++) {
                //     $options[$i] = 'Опция ' . $i;
                //     //
                // }
                $options = $items ?? ['Опция 1', 'Опция 2', 'Опция 3'];

                //заполнение второй страницы для списка
                foreach ($options as $listKey => $listVal) {
                    $listItemsSheet->getColumnDimension($columnListLetter)->setAutoSize(true);
                    $listItemsSheet->setCellValue($columnListLetter . ($listKey + 1), $listVal);
                }

                $title = $listItemsSheet->getTitle();
                $optionsString = "'{$title}'!\${$columnListLetter}\$1:\${$columnListLetter}\$" . count($options);
                $formula1 = $optionsString;
                $formula2 = null;

                $columnListLetter++;
            } else {
                $isList = false;
                $formula1 = 1;
                $formula2 = 255;
                $errorMes = 'не корректный формат';
            }

            $require = $val['data']['require_field'] ?? false;

            $range = "{$columnLetter}2:{$columnLetter}1000";
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true); //->setWidth(120,'pt');
            $validation = $sheet->getDataValidation($range);
            $validation->setType($type);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setErrorTitle('Ошибка ввода');
            $validation->setError($errorMes);

            $validation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
            $validation->setFormula1($formula1);

            if (!is_null($formula2)) //если список то будет null
            {
                $validation->setFormula2($formula2);
            }

            //для списка
            $validation->setShowDropDown($isList ?? false);

            //проверка на обязательное поле 
            $validation->setAllowBlank(!$require); //запретить пустые ячейки если поле обязательное
        }
        //сохранение файла :
        header("Expires: Mon, 1 Apr 1974 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");

        header("Content-type: application/vnd.ms-excel");

        header("Content-Disposition: attachment; filename=$fileName");
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }
}
