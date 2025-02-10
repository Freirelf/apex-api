<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motorcyclist extends Model
{
    protected $fillable = ['user_id', 'cpf', 'placa_moto', 'cnh', 'gender'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->morphOne(Address::class, 'addressable');
    }
}
