<?php

namespace Database\Seeders;

use App\Models\QuizUnit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class QuizDataSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                "title" => "Unit 1: Present Continuous",
                "details" => [
                    [
                        "title" => "1.1 Fill in the blanks with the correct form of verbs",
                        "instructions" => "come, get, happen, look, make, start, try, work, stay",
                        "questions" => [
                            ["text" => "\"You're working hard today.\" \"Yes, I have a lot to do.\"", "answer" => "working"],
                            ["text" => "I _____ for Christine. Do you know where she is?", "answer" => "am looking"],
                            ["text" => "It _____ dark. Shall I turn on the light?", "answer" => "is getting"]
                        ]
                    ],
                    [
                        "title" => "1.2 Complete the sentences with appropriate words",
                        "questions" => [
                            ["text" => "Why _____ at me like that? What's the matter?", "answer" => "are you looking"],
                            ["text" => "Is she? What _____ ?", "answer" => "happened"]
                        ]
                    ]
                ]
            ],

        ];

        foreach ($data as $unit) {
            $quizUnit = QuizUnit::create([
                'title' => $unit['title']
            ]);

            foreach ($unit['details'] as $detail) {
                $quizDetail = $quizUnit->quizDetails()->create([
                    'title' => $detail['title'],
                    'instructions' => $detail['instructions'] ?? null
                ]);

                foreach ($detail['questions'] as $question) {
                    $quizDetail->questions()->create([
                        'text' => $question['text'],
                        'answer' => $question['answer']
                    ]);
                }
            }
        }
    }
}
