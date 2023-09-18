<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacyMedicine extends Model
{
    use HasFactory;

    protected $table = 'pharmacy_medicine';

    protected $fillable = [
        'pharmacy_id',
        'medicine_id',
        'count',
        'price',
    ];
}
