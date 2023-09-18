<?php

namespace App\Filament\Pages;

use App\Models\Booking;
use InvadersXX\FilamentKanbanBoard\Pages\FilamentKanbanBoard;
use Illuminate\Support\Collection;
use Filament\Forms\Components\TextInput;

class KanbanBookings extends FilamentKanbanBoard
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // protected static string $view = 'filament.pages.kanban-bookings';
    public bool $recordClickEnabled = true;
    public bool $sortable = true;
    public bool $sortableBetweenStatuses = true;
    public bool $modalRecordClickEnabled = true;


    public function onRecordClick($recordId, $data): void
    {
        $this->editModalRecord->fill($data);
        $this->dispatchBrowserEvent('open-modal', ['id' => 'kanban--edit-modal-record']);
    }

    protected function statuses() : Collection
    {
        return collect([
            [
                'id' => 'new',
                'title' => 'New',
            ],
            [
                'id' => 'accepted',
                'title' => 'Accepted',
            ],
            [
                'id' => 'canceled',
                'title' => 'Canceled',
            ],
            [
                'id' => 'processing',
                'title' => 'Processing',
            ],
            [
                'id' => 'finished',
                'title' => 'Finished',
            ],
        ]);
    }

    protected function records() : Collection
    {
        return Booking::all()
            ->map(function (Booking $item) {
                return [
                    'id' => $item->id,
                    'title' => $item->garage->name_number,
                    'status' => $item->status,
                ];
            });
    }

    protected static function getEditModalRecordSchema(): array
    {
        return [
            TextInput::make('title'),
            TextInput::make('status'),
        ];
    }

}
