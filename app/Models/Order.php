<?php

namespace App\Models;

use App\Enums\OrderType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Scopes\OrderScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Order extends Model
{
    use HasUlids;
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'invoice',
        'table_id',
        'user_id',
        'customer',
        'grand_total',
        'type',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected function casts(): array
    {
        return [
            'type' => OrderType::class,
            'status' => OrderStatus::class,
            'grand_total' => 'decimal:2',
        ];
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope(new OrderScope);
    }

    /**
     * Order belongs to table.
     */
    public function table()
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Order belongs to user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Order has many order items.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Order has one payment.
     */
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * Generate a unique invoice number with the given prefix.
     */
    public static function generateInvoice(string $prefix): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $shuffled = str_shuffle($characters);
        $unique = substr($shuffled, 0, 4);
        $timestamp = now()->format('ymdHi');

        return $prefix . '-' . $unique . '-' . $timestamp;
    }

    /**
     * Scope: filter by date range (inclusive).
     */
    public function scopeWhereInRange(Builder $query, string $start, string $end): Builder
    {
        return $query
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end);
    }

    /**
     * Scope: filter by order type.
     */
    public function scopeWhereType(Builder $query, string|OrderType $type): Builder
    {
        if ($type instanceof OrderType) $type = $type->value;
        else $type = OrderType::tryFrom($type)?->value ?? $type;

        return $query->where('type', $type);
    }

    /**
     * Scope: filter by order status.
     */
    public function scopeWhereStatus(Builder $query, string|OrderStatus $status): Builder
    {
        if ($status instanceof OrderStatus) $status = $status->value;
        else $status = OrderStatus::tryFrom($status)?->value ?? $status;

        return $query->where('status', $status);
    }

    /**
     * Scope: search by invoice (LIKE %...%).
     */
    public function scopeWhereInvoice(Builder $query, string $invoice): Builder
    {
        return $query->where('invoice', 'like', '%' . $invoice . '%');
    }

    /**
     * Scope: filter by payment method.
     */
    public function scopeWherePayment(Builder $query, string|PaymentMethod $method): Builder
    {
        if ($method instanceof PaymentMethod) $method = $method->value;
        else $method = PaymentMethod::tryFrom($method)?->value ?? $method;

        return $query->whereHas('payment', fn($q) => $q->where('method', $method));
    }

    /**
     * Scope: orders that have been paid.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->whereHas('payment', function (Builder $q) {
            $q->where('status', PaymentStatus::PAID);
        });
    }

    /**
     * Scope: active orders (pending or being processed).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            OrderStatus::PENDING,
            OrderStatus::DIPROSES,
        ]);
    }

    /**
     * Scope: orders completed today.
     */
    public function scopeCompletedToday(Builder $query): Builder
    {
        $today = today();

        return $query
            ->where('status', OrderStatus::SELESAI)
            ->whereDate('created_at', $today);
    }

    /**
     * Scope a query to only include orders from today.
     */
    public function scopeToday(Builder $query): Builder
    {
        $today = today();
        return $query->whereDate('created_at', $today);
    }
}
