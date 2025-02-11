<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'motorcyclist_id',
        'delivery_job_id',
        'status',
    ];

    // Relation between Application and Motorcyclist
    public function motorcyclist()
    {
        return $this->belongsTo(Motorcyclist::class);
    }

    // Relation between Application and DeliveryJob
    public function deliveryJob()
    {
        return $this->belongsTo(DeliveryJob::class);
    }
}
