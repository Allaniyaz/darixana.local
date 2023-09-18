<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarRequest;
use App\Http\Requests\UpdateCarRequest;
use Illuminate\Support\Facades\Validator;

use App\Models\Car;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Color;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CarsImport;
use App\Exports\CarErrorsExport;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
class CarController extends Controller
{
    public function import()
    {
        return view('import');
    }

    public function save_import(Request $request)
    {
        //$import = Excel::toArray(new CarsImport, $request->file('file')->store('temp'));
        $request->validate([
            'file' => 'required|mimes:xls,xlsx,xlt,xltx'
        ]);

        $rows = Excel::toArray(new CarsImport(), $request->file);

        $errors = [];
        $success = 0;

        foreach($rows[0] as $row) {

            $data = [];

            // TODO: check $row validation
            $validator = Validator::make($row, [
                'brand' => 'required|max:255',
                'model'  => 'required|max:100',
                'equipment' => 'required|max:255',
                'color' => 'required|max:100',
                'vin_number' => 'required|max:255,unique:cars',
                'engine_number' => 'required|max:255|unique:cars',
                'price' => 'required|integer|min:0',
            ]);


            if ($validator->fails()) {
                $row['errors'] = $validator->errors();
                $errors[] = $row;
                continue;
            }

            $brand = CarBrand::where('name', $row['brand'])->first();
            if ($brand) {
                $data['brand_id'] = $brand->id;
            } else {
                $data['brand_id'] = CarBrand::create(['name' => $row['brand']])->id;
            }

            $model = CarModel::where('name', $row['model'])->first();
            if ($model) {
                $data['model_id'] = $model->id;
            } else {
                $data['model_id'] = CarModel::create([
                    'name' => $row['model'],
                    'brand_id' => $data['brand_id'],
                ])->id;
            }

            $color = Color::where('name', $row['color'])->first();
            if ($color) {
                $data['color_id'] = $color->id;
            } else {
                $data['color_id'] = Color::create(['name' => $row['color']])->id;
            }

            $data['equipment'] = $row['equipment'];
            $data['vin_number'] = $row['vin_number'];
            $data['engine_number'] = $row['engine_number'];
            $data['price_customers'] = $row['price'];


            if (Car::create($data)) {
                $success++;
            }

        }

        Notification::make()
            ->title('При импорте файла возникли некоторые **ошибки**.')
            ->body('Можете посмотреть их в файле ошибок.')
            ->persistent()
            ->actions([
                Action::make('download')->label("Скачать файл")->button()
                    ->url(route('cars.import-errors'), shouldOpenInNewTab: true)
                    ->close(),
            ])
            ->danger()->send();

        $request->session()->put('import_errors', 0);
        if (!empty($errors)) {
            $file = 'import_errors/Errors_' . date('d.m.Y_H_i_s') . '.xlsx';
            $request->session()->put('import_errors', $file);
            Excel::store(new CarErrorsExport($errors), $file);

            return redirect()->route('filament.resources.cars.index', ['import_errors' => $errors])
                ->withErrors('Import finished with ' . count($errors) . '.');
        }

        return redirect()->route('filament.resources.cars.index')->withSuccess("Imported {$success} rows.");
    }

    public function downloadImportErrors(Request $request)
    {
        if ($request->session()->has('import_errors')) {
            $import_errors = $request->session()->pull('import_errors');
            $request->session()->forget('import_errors');
            return Storage::download($import_errors);
        }
        return redirect()->back();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCarRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCarRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function show(Car $car)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function edit(Car $car)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCarRequest  $request
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCarRequest $request, Car $car)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Car  $car
     * @return \Illuminate\Http\Response
     */
    public function destroy(Car $car)
    {
        //
    }
}
