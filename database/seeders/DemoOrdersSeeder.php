<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoOrdersSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Relative order volume for each of the 24 hourly buckets, oldest first and
     * ending in the current hour. Zeros leave gaps in the dashboard chart.
     *
     * @var array<int, int>
     */
    private const HOURLY_WEIGHTS = [
        1, 0, 0, 0, 1, 2, 4, 7, 9, 6, 3, 0,
        2, 5, 8, 10, 7, 4, 0, 0, 3, 6, 4, 2,
    ];

    private const TOTAL_ORDERS = 100;

    /**
     * Seed roughly 100 synthetic orders spread unevenly across the last 24 hours (UTC).
     */
    public function run(): void
    {
        if (! User::query()->where('email', 'test@example.com')->exists()) {
            User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        }

        $now = now('UTC');
        $currentHour = $now->startOfHour();
        $totalWeight = array_sum(self::HOURLY_WEIGHTS);

        foreach (self::HOURLY_WEIGHTS as $index => $weight) {
            $hourStart = $currentHour->subHours(23 - $index);
            $orderCount = (int) round(self::TOTAL_ORDERS * $weight / $totalWeight);
            $latestSecond = $hourStart->equalTo($currentHour)
                ? max(0, (int) $hourStart->diffInSeconds($now))
                : 3599;

            for ($i = 0; $i < $orderCount; $i++) {
                Order::factory()
                    ->placedAt($hourStart->addSeconds(fake()->numberBetween(0, $latestSecond)))
                    ->create();
            }
        }
    }
}
