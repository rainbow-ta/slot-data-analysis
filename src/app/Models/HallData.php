<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HallData extends Model
{
    use HasFactory;

    protected $table = 'hall_data';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];

    public function slotMachine()
    {
        return $this->belongsTo(SlotMachine::class);
    }
}
