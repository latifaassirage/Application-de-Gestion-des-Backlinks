<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SourceSummary extends Model
{
    protected $fillable = [
        'website',
        'cost',
        'link_type',
        'contact_email',
        'spam'
    ];
}
