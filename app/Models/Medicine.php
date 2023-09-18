<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medicine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'images',
        'instructions',
        'info'
    ];

    /**
     * The pharmacies that belong to the Medicine
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function pharmacies(): BelongsToMany
    {
        return $this->belongsToMany(PharmacyMedicine::class);
    }
}
