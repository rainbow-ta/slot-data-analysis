<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlotsData extends Model
{
    use HasFactory;

    protected $table = 'slots_data';

    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];
}
