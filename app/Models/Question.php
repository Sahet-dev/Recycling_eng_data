<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['detail_id', 'text', 'answer'];
    protected $casts = [
        'answer' => 'array',
    ];

}
