<?php

namespace App\Filament\Resources\PharmacyMedicineResource\Pages;

use App\Filament\Resources\PharmacyMedicineResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPharmacyMedicines extends ListRecords
{
    protected static string $resource = PharmacyMedicineResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
