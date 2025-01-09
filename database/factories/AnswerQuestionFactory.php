<?php

namespace Database\Factories;

use App\Models\AnswerQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnswerQuestion>
 */
class AnswerQuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'answer' => $this->faker->randomFloat(2, 0, 10),
        ];
    }
}
