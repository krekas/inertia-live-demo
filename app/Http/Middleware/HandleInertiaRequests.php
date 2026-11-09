<?php

namespace App\Http\Middleware;

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\LiveProp;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'ordersTodayCount' => $this->ordersTodayCount($request),
        ];
    }

    /**
     * Count today's orders (UTC calendar day) for users who can access the dashboard.
     */
    protected function ordersTodayCount(Request $request): ?LiveProp
    {
        if (! $request->user()?->hasVerifiedEmail()) {
            return null;
        }

        return Inertia::live(function (): int {
            $startOfToday = now('UTC')->startOfDay();

            return Order::query()
                ->where('created_at', '>=', $startOfToday)
                ->where('created_at', '<', $startOfToday->addDay())
                ->count();
        }, OrderCreated::class);
    }
}
