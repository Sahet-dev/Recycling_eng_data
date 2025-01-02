<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Detail extends Model
{
    use HasFactory;

    protected $fillable = ['unit_id', 'title', 'section', 'content', 'example'];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }


}
