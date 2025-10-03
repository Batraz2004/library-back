<?php

namespace App\Exports;

use App\Models\Categories;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

use function PHPSTORM_META\map;

class CategoriesExport implements FromCollection //,  WithHeadings
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

    public function collection()
    {
        $category = Categories::query()->where('id', $this->id)->first();


        // $result = collect($category->fields);

        $texts = [];
        foreach ($category->fields as $field) {
            $texts[] = $field['data']['text'] ?? '';
        }

        // Создаем одну строку
        return collect([$texts]);

        return $result;
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
    //         'D' => '',
    //     ];
    // }
}
