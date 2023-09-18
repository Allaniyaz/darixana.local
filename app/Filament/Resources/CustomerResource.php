<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Клиенты';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('last_name')
                    ->label('Фамилия')
                    ->required(),
                TextInput::make('first_name')
                    ->label('Имя')
                    ->required(),
                TextInput::make('middle_name')
                    ->label('Отчество')
                    ->required(),
                Select::make('user_id')
                    ->label('Пользователь')
                    ->options(User::getCustomers()->pluck('name', 'id')->toArray())
                    ->unique()
                    // ->hiddenOn('edit')
                    ->required(),
                TextInput::make('phone')
                    ->label('Телефон')
                    ->minLength(9)
                    ->maxLength(12)
                    ->required(),
                TextInput::make('email')
                    ->label('Почта'),
                Select::make('gender')
                    ->label('Пол')
                    ->options([
                        '1' => 'Мужской',
                        '2' => 'Женский'
                    ])->required(),
                TextInput::make('citizenship')
                    ->label('Гражданство'),
                TextInput::make('pinfl')
                    ->label('ПИНФЛ')
                    ->integer()
                    ->minLength(14)
                    ->maxLength(14)
                    ->required(),
                TextInput::make('birth_country')
                    ->label('Страна рождения'),
                TextInput::make('birth_place')
                    ->label('Место рождения'),
                DatePicker::make('birth_date')
                    ->label('Дата рождения'),
                TextInput::make('permanent_address')
                    ->label('Постоянный адрес проживания')
                    ->required(),
                TextInput::make('temporary_address')
                    ->label('Временное место проживания'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('last_name')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
