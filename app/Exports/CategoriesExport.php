<?php

namespace App\Exports;

use App\Models\Categories;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

use function PHPSTORM_META\map;

class CategoriesExport implements FromCollection, WithEvents //,  WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    protected $fieldsList = [];
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
        foreach ($category->fields as $field) {
            $this->fieldsList[] = $field['data'];
            $texts[] = $field['data']['text'] ?? '';
        }

        return collect([$texts]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                foreach ($this->fieldsList as $key => $val) {
                    $chNumRow = chr(64 + ($key + 1)); //типа по алфавиту
                    $type = match ($val['type']) {
                        'number' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE,
                        'decimal' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL,
                        'text' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH,
                        'list' => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST,
                        default => \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_TEXTLENGTH,
                    };

                    if ($val['type'] == 'number' || $val['type'] == 'decimal') {
                        $minSize = 1;
                        $maxSize = 1000000;
                        $errorMes = 'введите число';
                    } else if ($val['type'] == 'text') {
                        $minSize = 0;
                        $maxSize = 255;
                        $errorMes = 'введите текст';
                    } else {
                        $minSize = 0;
                        $maxSize = 255;
                        $errorMes = 'не корректный формат';
                    }
                    // $type = \PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_DECIMAL;

                    for ($row = 2; $row <= 1000; $row++) {
                        $cell = "$chNumRow{$row}";

                        $validation = $sheet->getCell($cell)->getDataValidation();
                        $validation->setType($type);
                        $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setErrorTitle('Ошибка ввода');
                        $validation->setError($errorMes);
                        // $validation->setError('ID должен быть целым числом от 1 до 1000000');
                        // $validation->setPromptTitle('Ввод ID');
                        // $validation->setPrompt('Введите целое число для ID');

                        $validation->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN);
                        $validation->setFormula1("$minSize");
                        $validation->setFormula2("$maxSize");
                    }
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
