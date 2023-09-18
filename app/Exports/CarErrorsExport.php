<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CarErrorsExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'brand' => 'Марка',
            'model' => 'Модель',
            'equipment' => 'Комплектация',
            'color' => 'Цвет',
            'vin_number' => 'Номер кузова (VIN)',
            'engine_number' => 'Номер двигателя',
            'price' => 'Цена с НДС',
            'errors' => 'Ошибки при импорте',
        ];
    }

    public function export()
    {
        return self::download(self::class, 'invoices.xlsx');
    }

}
