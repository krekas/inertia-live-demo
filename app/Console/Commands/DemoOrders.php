<?php

namespace App\Console\Commands;

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;
use Throwable;

#[Signature('demo:orders')]
#[Description('Create synthetic orders on an interval and broadcast each one to the live dashboard')]
class DemoOrders extends Command
{
    private const ORDER_COUNT = 20;

    private const INTERVAL_SECONDS = 2;

    /**
     * @var array<int, string>
     */
    private const ALLOWED_ENVIRONMENTS = ['local', 'testing'];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->environment(self::ALLOWED_ENVIRONMENTS)) {
            $this->components->error('Synthetic orders can only be generated in the '.implode(' or ', self::ALLOWED_ENVIRONMENTS).' environment.');

            return self::FAILURE;
        }

        $this->components->info('Creating '.self::ORDER_COUNT.' orders, one every '.self::INTERVAL_SECONDS.' seconds. Press Ctrl+C to stop early.');

        $totalPriceCents = 0;

        for ($number = 1; $number <= self::ORDER_COUNT; $number++) {
            if ($number > 1) {
                Sleep::for(self::INTERVAL_SECONDS)->seconds();
            }

            $order = Order::factory()->create();
            $totalPriceCents += $order->total_price_cents;

            try {
                OrderCreated::dispatch($order->id);
            } catch (Throwable $exception) {
                $this->components->error("Order #{$order->id} was saved, but broadcasting it failed: {$exception->getMessage()}");

                return self::FAILURE;
            }

            $this->components->info(sprintf(
                '[%d/%d] Order #%d created for %s.',
                $number,
                self::ORDER_COUNT,
                $order->id,
                $this->formatEuros($order->total_price_cents),
            ));
        }

        $this->components->info(sprintf(
            'Created %d orders worth %s in total.',
            self::ORDER_COUNT,
            $this->formatEuros($totalPriceCents),
        ));

        return self::SUCCESS;
    }

    /**
     * Format an amount in cents as euros.
     */
    private function formatEuros(int $cents): string
    {
        return '€'.number_format($cents / 100, 2);
    }
}
