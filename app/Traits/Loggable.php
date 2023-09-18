<?php

namespace App\Traits;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;

trait Loggable {

    protected static function booted()
    {
        static::created(function ($model) {
            $columns = \Schema::getColumnListing($model->getTable());
            $changes = [];

            foreach ($columns as $col) {
                if (in_array($col, ['created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }
                $changes[$col] = $model->{$col};
            }

            Event::create([
                'action' => 'Created ' . $model->getTable(),
                'eventable_type' => get_class($model) ?? '',
                'eventable_id' => $model->id,
                'description' => $changes,
                'user_id' => Auth::id()
            ]);
        });

        static::updated(function ($model) {
            $columns = \Schema::getColumnListing($model->getTable());
            $changes = [];

            foreach ($columns as $col) {
                if (in_array($col, ['created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }
                if ($model->isDirty($col)) {
                    $changes[$col] = [
                        'old' => $model->getOriginal($col),
                        'new' => $model->{$col}
                    ];
                }
            }

            Event::create([
                'action' => 'Changes in ' . $model->getTable(),
                'eventable_type' => get_class($model) ?? '',
                'eventable_id' => $model->id,
                'description' => $changes,
                'user_id' => Auth::id()
            ]);
        });
    }

    public function events()
    {
        return $this->morphMany(\App\Models\Event::class, 'eventable')->latest();;
    }
}
