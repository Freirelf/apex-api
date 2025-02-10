<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'neighborhood', 'zip_code', 'complement',
        'point_reference', 'number', 'country_id'
    ];

    public function addressable()
    {
        return $this->morphTo();
    }
}
