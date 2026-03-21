<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BacklinkType extends Model
{
    use HasFactory;

    protected $table = 'backlink_types';

    protected $fillable = [
        'name',
    ];
}