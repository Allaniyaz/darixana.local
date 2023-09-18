<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarAttribute extends Model
{
    use HasFactory;

    protected $table = 'attributes';

    protected $fillable = [
        'name_ru',
        'name_uz',
        'name_en',
    ];
}
