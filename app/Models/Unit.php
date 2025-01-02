<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['unit'];

    public function details(): HasMany
    {
        return $this->hasMany(Detail::class);
    }

    public function images()
    {
        return $this->hasMany(Image::class);
    }
}
