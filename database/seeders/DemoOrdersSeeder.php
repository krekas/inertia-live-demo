<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
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
     * Earlier calendar days (UTC) that get order history, counted back from today.
     * Day 2 is the most recent day that lies fully before the 24-hour chart window.
     */
    private const HISTORY_FIRST_DAY = 2;

    private const HISTORY_LAST_DAY = 7;

    private const MINIMUM_ORDERS_TODAY = 5;

    /**
     * Seed roughly 100 synthetic orders across the last 24 hours (UTC), plus
     * history on earlier days and a few orders spread across today until now.
     */
    public function run(): void
    {
        if (! User::query()->where('email', 'test@example.com')->exists()) {
            User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        }

        $now = now('UTC');

        $this->seedEarlierDays($now);
        $ordersToday = $this->seedLastTwentyFourHours($now);
        $this->seedToday($now, max(0, self::MINIMUM_ORDERS_TODAY - $ordersToday));
    }

    /**
     * Seed a varying number of orders at random times on each earlier day.
     */
    private function seedEarlierDays(CarbonImmutable $now): void
    {
        for ($daysAgo = self::HISTORY_LAST_DAY; $daysAgo >= self::HISTORY_FIRST_DAY; $daysAgo--) {
            $dayStart = $now->subDays($daysAgo)->startOfDay();

            for ($i = 0, $count = fake()->numberBetween(4, 12); $i < $count; $i++) {
                $this->createOrderAt($dayStart->addSeconds(fake()->numberBetween(0, 86_399)));
            }
        }
    }

    /**
     * Seed orders unevenly across the 24 hourly buckets ending in the current hour.
     *
     * @return int The number of orders placed today.
     */
    private function seedLastTwentyFourHours(CarbonImmutable $now): int
    {
        $currentHour = $now->startOfHour();
        $startOfToday = $now->startOfDay();
        $totalWeight = array_sum(self::HOURLY_WEIGHTS);
        $ordersToday = 0;

        foreach (self::HOURLY_WEIGHTS as $index => $weight) {
            $hourStart = $currentHour->subHours(23 - $index);
            $orderCount = (int) round(self::TOTAL_ORDERS * $weight / $totalWeight);
            $latestSecond = $hourStart->equalTo($currentHour)
                ? (int) $hourStart->diffInSeconds($now)
                : 3599;

            for ($i = 0; $i < $orderCount; $i++) {
                $placedAt = $this->createOrderAt($hourStart->addSeconds(fake()->numberBetween(0, $latestSecond)));

                if ($placedAt->greaterThanOrEqualTo($startOfToday)) {
                    $ordersToday++;
                }
            }
        }

        return $ordersToday;
    }

    /**
     * Seed extra orders at random times between midnight (UTC) and now.
     */
    private function seedToday(CarbonImmutable $now, int $count): void
    {
        $startOfToday = $now->startOfDay();
        $secondsSoFar = (int) $startOfToday->diffInSeconds($now);

        for ($i = 0; $i < $count; $i++) {
            $this->createOrderAt($startOfToday->addSeconds(fake()->numberBetween(0, $secondsSoFar)));
        }
    }

    private function createOrderAt(CarbonImmutable $placedAt): CarbonImmutable
    {
        Order::factory()->placedAt($placedAt)->create();

        return $placedAt;
    }
}
