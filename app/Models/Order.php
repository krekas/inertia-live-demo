<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A standalone synthetic order used by the live dashboard demo. Totals are stored in EUR cents.
 *
 * @property int $id
 * @property int $total_price_cents
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['total_price_cents'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_price_cents' => 'integer',
        ];
    }
}
