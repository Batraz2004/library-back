<?php

namespace App\Exports;

use App\Models\Categories;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use function PHPSTORM_META\map;

class CategoriesExport implements FromCollection, WithEvents //,  WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $fieldsList = [];
    // protected $listItems = []; // для ячейки типа select
    protected $id;

    public function __construct($categoryId)
    {
        $this->id = $categoryId;
    }

    public function getCategory()
    {
        return Categories::query()?->where('id', $this->id)?->first();
    }

    public function collection()
    {
        $category = $this->getCategory();
        $texts = [];
        foreach ($category->fields as $key => $field) {
            $this->fieldsList[] = $field;

            $text = ($field['data']['require_field']
                ? $field['data']['text'] . ' *'
                : $field['data']['text']) ?? '';

            $texts[] = $text;

            //если тип список:
            // if(isset($field['field_list']) && filled($field['field_list']))
            // {   
            //     $items = [];
            //     foreach($field['field_list'] as $listItem)
            //     {    
            //         $items []=  $listItem['data']['list-item'];
            //     }
            //     $field['field_list'] = $items;
            //     echo '<pre>'.htmlentities(print_r(1, true)).'</pre>';exit();
            // }
        }

        return collect([$texts]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                //страницы:
                
                //основная страница
                $sheet = $event->sheet->getDelegate();
                $sheet->setTitle('Template');
                
                //страница списков
                $spreadsheet = $sheet->getParent();
                $listItemsSheet =  $spreadsheet->createSheet();
                $listItemsSheet->setTitle('List');

                $columnListLetter = Coordinate::stringFromColumnIndex(1);
                foreach ($this->fieldsList as $key => $val) {
                    $columnLetter = Coordinate::stringFromColumnIndex($key + 1);//индекс колонки

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
                        foreach($options as $listKey => $listVal){
                            $listItemsSheet->getColumnDimension($columnListLetter)->setAutoSize(true);
                            $listItemsSheet->setCellValue($columnListLetter.($listKey+1),$listVal);
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
            }
        ];
    }
}
