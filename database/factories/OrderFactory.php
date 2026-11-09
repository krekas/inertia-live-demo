<?php

namespace Database\Factories;

use App\Models\Order;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'total_price_cents' => fake()->numberBetween(499, 49_999),
        ];
    }

    /**
     * Indicate that the order was placed at the given time.
     */
    public function placedAt(CarbonInterface $placedAt): static
    {
        return $this->state(fn (array $attributes): array => [
            'created_at' => $placedAt,
            'updated_at' => $placedAt,
        ]);
    }
}
