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
            $texts[] = $field['data']['text'] ?? '';

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
                $sheet = $event->sheet->getDelegate();
                foreach ($this->fieldsList as $key => $val) {
                    $columnLetter = Coordinate::stringFromColumnIndex($key + 1);
                    // $chNumRow = chr(64 + ($key + 1)); //типа по алфавиту
                    $type = match ($val['data']['type']) {
                        'number' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE,
                        'decimal' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL,
                        'text' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH,
                        'list' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST,
                        'multi_select' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST,
                        default => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH,
                    };

                    if ($val['data']['type'] == 'number' || $val['data']['type'] == 'decimal') {
                        $formula1 = 1;
                        $formula2 = 1000000;
                        $errorMes = 'введите число';
                    } else if ($val['data']['type'] == 'text') {
                        $formula1 = 0;
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
                            $field['field_list'] = $items;
                        }
                        $options = $items ?? ['Опция 1', 'Опция 2', 'Опция 3'];
                        // $options = [];
                        // for ($i = 1; $i <= 5; $i++) {
                        //     $options[] = 'Опция' . $i;
                        // }
                        $optionsString = '"' . implode(',', $options) . '"';

                        $formula1 = $optionsString;
                        $formula2 = null;
                    } else {
                        $isList = false;
                        $formula1 = 1;
                        $formula2 = 255;
                        $errorMes = 'не корректный формат';
                    }

                    $require = $val['data']['require_field'] ?? false;
                    // for ($row = 2; $row <= 1000; $row++) {
                    // $cell = "{$columnLetter}{$row}";
                    // $validation = $sheet->getCell($cell)->getDataValidation();

                    $range = "{$columnLetter}2:{$columnLetter}1000";
                    $validation = $sheet->getDataValidation($range);
                    $validation->setType($type);
                    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Ошибка ввода');
                    $validation->setError($errorMes);

                    $validation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
                    $validation->setFormula1("$formula1");

                    if (filled($formula2)) //если список то будет null
                    {
                        $validation->setFormula2("$formula2");
                    }

                    //для списка
                    $validation->setShowDropDown($isList ?? false);

                    //проверка на обязательное поле 
                    if($require)
                    {
                        $val['data']['text'] = $val['data']['text'].' *';
                        $validation->setAllowBlank(!$require);//запретить пустые ячейки если поле обязательное
                    }
                    // $validation->setAllowBlank(true);
                    // }
                }
            }
        ];
    }
    // public function map($category): array
    // {
    //     $row = [
    //         // $category->id,
    //         // $category->title,
    //         // $category->description,
    //         // $category->fields,
    //     ];

    //     foreach($this->fieldsList as $field)
    //         $row[] = $field;


    //     return $row;
    // }

    // public function headings(): array
    // {
    //     $headings = [
    //         'Заголовок',
    //         'Описание',
    //     ];

    //     // foreach($this->fieldsList as $field)
    //     //     $headings[] = "доп поле".$field;

    //     return $headings;
    // }

    // public function columnFormats(): array
    // {
    //     return [
    //         'A' => NumberFormat::FORMAT_NUMBER,
    //     ];
    // }

    // public function rules(): array
    // {
    //     return [
    //         '1' => ['required', 'integer'],
    //     ];
    // }
}
