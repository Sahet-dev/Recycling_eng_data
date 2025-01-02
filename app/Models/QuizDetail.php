<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizDetail extends Model
{
    protected $fillable = ['unit_id', 'title', 'instructions'];

    public function questions()
    {
        return $this->hasMany(Question::class, 'detail_id');
    }
}
