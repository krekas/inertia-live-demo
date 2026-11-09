<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Carbon\CarbonImmutable;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    private const HOURLY_BUCKETS = 24;

    private const LATEST_ORDERS_LIMIT = 15;

    /**
     * Show the live orders dashboard.
     */
    public function __invoke(): Response
    {
        return Inertia::render('dashboard', [
            'ordersOverview' => fn (): array => $this->ordersOverview(),
            'newUsers' => fn (): int => User::query()
                ->where('created_at', '>=', now('UTC')->subDay())
                ->count(),
        ]);
    }

    /**
     * Build the order metrics, hourly chart buckets, and latest rows for the current UTC window.
     *
     * @return array{
     *     timezone: string,
     *     currency: string,
     *     windowStart: string,
     *     orderCount: int,
     *     orderValueCents: int,
     *     hourly: array<int, array{hour: string, count: int}>,
     *     latestOrders: array<int, array{id: int, totalPriceCents: int, createdAt: string}>,
     * }
     */
    private function ordersOverview(): array
    {
        $currentHour = CarbonImmutable::now('UTC')->startOfHour();
        $windowStart = $currentHour->subHours(self::HOURLY_BUCKETS - 1);

        $windowOrders = Order::query()
            ->where('created_at', '>=', $windowStart)
            ->toBase()
            ->get(['created_at', 'total_price_cents']);

        $countsByHour = $windowOrders->countBy(
            fn (object $order): string => CarbonImmutable::parse($order->created_at, 'UTC')->startOfHour()->toIso8601ZuluString(),
        );

        $hourly = collect(range(0, self::HOURLY_BUCKETS - 1))
            ->map(function (int $offset) use ($windowStart, $countsByHour): array {
                $hour = $windowStart->addHours($offset)->toIso8601ZuluString();

                return ['hour' => $hour, 'count' => $countsByHour->get($hour, 0)];
            })
            ->all();

        $latestOrders = Order::query()
            ->latest()
            ->latest('id')
            ->limit(self::LATEST_ORDERS_LIMIT)
            ->get()
            ->map(fn (Order $order): array => [
                'id' => $order->id,
                'totalPriceCents' => $order->total_price_cents,
                'createdAt' => $order->created_at->toIso8601ZuluString(),
            ])
            ->all();

        return [
            'timezone' => 'UTC',
            'currency' => 'EUR',
            'windowStart' => $windowStart->toIso8601ZuluString(),
            'orderCount' => array_sum(array_column($hourly, 'count')),
            'orderValueCents' => (int) $windowOrders->sum('total_price_cents'),
            'hourly' => $hourly,
            'latestOrders' => $latestOrders,
        ];
    }
}
