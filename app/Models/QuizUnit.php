<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizUnit extends Model
{
    protected $fillable = ['title'];

    public function quizDetails()
    {
        return $this->hasMany(QuizDetail::class, 'unit_id');
    }
}
