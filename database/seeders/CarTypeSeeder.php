<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('car_types')->insert([
            'name_ru' => 'Бензиновый двигатель',
            'name_uz' => 'Benzin dvigatel',
            'name_en' => 'Gas engine',
        ]);
        DB::table('car_types')->insert([
            'name_ru' => 'Дизельный двигатель',
            'name_uz' => 'Dizel dvigatel',
            'name_en' => 'Diesel engine',
        ]);
        DB::table('car_types')->insert([
            'name_ru' => 'Электромобиль',
            'name_uz' => 'Elektromobil',
            'name_en' => 'Electric car',
        ]);
        DB::table('car_types')->insert([
            'name_ru' => 'Водородный автомобиль',
            'name_uz' => 'Vodorodli avtomobil',
            'name_en' => 'Hydrogen car',
        ]);
        DB::table('car_types')->insert([
            'name_ru' => 'Гибрид',
            'name_uz' => 'Gibrid',
            'name_en' => 'Hybrid',
        ]);
    }
}
