<?php

namespace App\Domains\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Finance\Models\FeeType;
use App\Domains\Finance\Models\DiscountApplication;

class InvoiceItem extends Model
{
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'بند فاتورة';
    protected static string $modelPluralLabel = 'بنود الفواتير';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [];

    protected $fillable = ['invoice_id', 'fee_type_id', 'amount', 'discount_id'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function discountApplications(): HasMany
    {
        return $this->hasMany(DiscountApplication::class);
    }
}
