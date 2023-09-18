<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PharmacyMedicineResource\Pages;
use App\Filament\Resources\PharmacyMedicineResource\RelationManagers;
use App\Models\PharmacyMedicine;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PharmacyMedicineResource extends Resource
{
    protected static ?string $model = PharmacyMedicine::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('pharmacy_id')
                    ->required(),
                Forms\Components\TextInput::make('medicine_id')
                    ->required(),
                Forms\Components\TextInput::make('count'),
                Forms\Components\TextInput::make('price'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pharmacy_id'),
                Tables\Columns\TextColumn::make('medicine_id'),
                Tables\Columns\TextColumn::make('count'),
                Tables\Columns\TextColumn::make('price'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    
    public static function getRelations(): array
    {
        return [
            //
        ];
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPharmacyMedicines::route('/'),
            'create' => Pages\CreatePharmacyMedicine::route('/create'),
            'edit' => Pages\EditPharmacyMedicine::route('/{record}/edit'),
        ];
    }    
}
