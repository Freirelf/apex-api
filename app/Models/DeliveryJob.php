<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'title',
        'description',
        'distance',
        'date',
        'time',
        'value',
        'is_guaranteed',
        'meal_included',
        'provides_bag',
        'pickup_address',
        'delivery_address',
        'status',
    ];

    // Relation between DeliveryJob and Store
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    // Relation between DeliveryJob and Applications
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
}
