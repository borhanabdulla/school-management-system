<?php

declare(strict_types=1);

namespace App\Domains\Finance\Events;

use App\Domains\Finance\Models\Invoice;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * InvoiceCreated - حدث إنشاء فاتورة جديدة
 * 
 * يُطلق عند إنشاء فاتورة جديدة لطالب
 * يمكن استخدامه لإرسال إشعارات أو تحديث الإحصائيات
 * 
 * PR-A1: afterCommit لضمان عدم الإطلاق على transaction فاشلة
 */
class InvoiceCreated implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Invoice $invoice
    ) {
    }
}
