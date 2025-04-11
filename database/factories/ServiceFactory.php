<?php

namespace Database\Factories;

use App\Models\Process;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $actions = ['Submit', 'Request', 'Schedule', 'Access', 'Review', 'Approve', 'Track', 'Manage'];
        $objects = ['Expense Report', 'Support Ticket', 'Meeting Room', 'Legal Advice', 'IT Help', 'Budget Plan', 'Job Application', 'Training Session'];

        return [
            'name' => $this->faker->randomElement($actions) . ' ' . $this->faker->randomElement($objects),
            'icon' => $this->faker->md5(),
        ];
    }

    public function withProcess(): static
    {
        return $this->state(fn(array $attributes) => [
            'process_id' => Process::query()->inRandomOrder()->value('id'),
        ]);
    }
}
