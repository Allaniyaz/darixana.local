<?php

namespace App\Filament\Resources\PharmacyMedicineResource\Pages;

use App\Filament\Resources\PharmacyMedicineResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPharmacyMedicine extends EditRecord
{
    protected static string $resource = PharmacyMedicineResource::class;

    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
