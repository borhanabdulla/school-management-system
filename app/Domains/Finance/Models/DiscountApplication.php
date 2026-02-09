<?php

declare(strict_types=1);

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domains\Shared\Models\User;

class DiscountApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_item_id',
        'discount_id',
        'applied_amount',
        'applied_by',
        'applied_at',
        'reason',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'applied_amount' => 'decimal:2',
    ];

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'applied_by');
    }
}
