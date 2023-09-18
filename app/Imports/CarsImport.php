<?php

namespace App\Imports;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarBrand;
use App\Models\Color;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Validation\Rule;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;


class CarsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
//ToModel, WithValidation, WithHeadingRow, SkipsEmptyRows
{
    use Importable;

    public function collection(Collection $row)
    {

    }

    // /**
    // * @param array $row
    // *
    // * @return \Illuminate\Database\Eloquent\Model|null
    // */
    // public function model(array $row)
    // {
    //     return new Car([
    //         'brand_id'        => $row['Brand'],
    //         'model_id'        => $row['Model'],
    //         'equipment'       => $row['Equipment'],
    //         'color_id'        => $row['Color'],
    //         'vin_number'      => $row['VIN'],
    //         'engine_number'   => $row['Engine'],
    //         'price_customers' => $row['Price'],
    //     ]);
    // }

    public function rules(): array
    {
        return [
            'brand' => 'string',
            'model' => ['required'],
            'equipment' => 'string',
            'color' => 'string',
            'vin_number' => 'string',
            'engine_number' => 'string',
            'price' => 'integer'
        ];
    }

    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        // Handle the failures how you'd like.
    }


    // /**
    //  * @return array
    //  */
    // public function customValidationAttributes()
    // {
    //     return ['1' => 'email'];
    // }
}
