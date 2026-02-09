<?php

declare(strict_types=1);

namespace App\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * InvalidatesCache - إبطال الكاش التلقائي
 * 
 * هذا الـ Trait يحذف الكاش المرتبط بالموديل تلقائياً عند:
 * - الإنشاء (created)
 * - التعديل (updated)
 * - الحذف (deleted)
 * 
 * يدعم نظام Cache Tags لحذف مجموعات كاملة من الكاش.
 * 
 * @example
 * class Grade extends Model {
 *     use InvalidatesCache;
 *     
 *     // Tags للحذف الجماعي (Redis فقط)
 *     protected array $cacheTags = ['academic', 'grades'];
 *     
 *     // مفاتيح محددة للحذف (يعمل مع جميع الـ Drivers)
 *     protected array $cacheKeys = ['grades.list', 'academic.structure'];
 * }
 * 
 * // عند تعديل أي صف، سيتم:
 * // 1. حذف Cache::tags(['academic', 'grades'])
 * // 2. حذف Cache::forget('grades.list')
 * // 3. حذف Cache::forget('academic.structure')
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
trait InvalidatesCache
{
    /**
     * تفعيل إبطال الكاش
     */
    public static function bootInvalidatesCache(): void
    {
        // عند الإنشاء
        static::created(function (Model $model) {
            $model->invalidateRelatedCache();
        });

        // عند التحديث
        static::updated(function (Model $model) {
            $model->invalidateRelatedCache();
        });

        // عند الحذف
        static::deleted(function (Model $model) {
            $model->invalidateRelatedCache();
        });
    }

    /**
     * إبطال الكاش المرتبط
     * 
     * @return void
     */
    public function invalidateRelatedCache(): void
    {
        // 1. حذف عبر Tags (إذا كان الـ Driver يدعمها)
        $this->invalidateCacheTags();

        // 2. حذف مفاتيح محددة
        $this->invalidateCacheKeys();

        // 3. حذف مفاتيح ديناميكية (مرتبطة بالـ ID)
        $this->invalidateDynamicCacheKeys();
    }

    /**
     * حذف الكاش عبر Tags
     * 
     * يعمل فقط مع Redis و Memcached
     * 
     * @return void
     */
    protected function invalidateCacheTags(): void
    {
        $tags = $this->getCacheTags();

        if (!empty($tags) && $this->cacheDriverSupportsTags()) {
            try {
                Cache::tags($tags)->flush();
            } catch (\Exception $e) {
                // Silently fail if tags not supported
                report($e);
            }
        }
    }

    /**
     * حذف مفاتيح محددة
     * 
     * @return void
     */
    protected function invalidateCacheKeys(): void
    {
        foreach ($this->getCacheKeys() as $key) {
            Cache::forget($key);
        }
    }

    /**
     * حذف مفاتيح ديناميكية (مرتبطة بالـ ID)
     * 
     * مثال: student.1.profile, student.1.grades
     * 
     * @return void
     */
    protected function invalidateDynamicCacheKeys(): void
    {
        $prefix = $this->getCacheKeyPrefix();
        $id = $this->getKey();

        // قائمة اللواحق الشائعة
        $suffixes = ['profile', 'details', 'list', 'stats', 'summary'];

        foreach ($suffixes as $suffix) {
            Cache::forget("{$prefix}.{$id}.{$suffix}");
        }

        // أيضاً نحذف الكاش العام للموديل
        Cache::forget("{$prefix}.all");
        Cache::forget("{$prefix}.list");
        Cache::forget("{$prefix}.count");
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * Getters
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * الحصول على الـ Tags
     * 
     * @return array<string>
     */
    public function getCacheTags(): array
    {
        return $this->cacheTags ?? [];
    }

    /**
     * الحصول على المفاتيح المحددة
     * 
     * @return array<string>
     */
    public function getCacheKeys(): array
    {
        return $this->cacheKeys ?? [];
    }

    /**
     * الحصول على بادئة مفتاح الكاش
     * 
     * @return string
     */
    public function getCacheKeyPrefix(): string
    {
        return $this->cacheKeyPrefix ?? strtolower(class_basename($this));
    }

    /**
     * التحقق من دعم الـ Driver للـ Tags
     * 
     * @return bool
     */
    protected function cacheDriverSupportsTags(): bool
    {
        $driver = config('cache.default');
        return in_array($driver, ['redis', 'memcached', 'dynamodb']);
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * أدوات مساعدة للكاش
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * تخزين قيمة في الكاش مع الـ Tags الخاصة بالموديل
     * 
     * @param string $key
     * @param mixed $value
     * @param int $ttl بالثواني
     * @return mixed
     */
    public function cacheWithTags(string $key, mixed $value, int $ttl = 3600): mixed
    {
        $tags = $this->getCacheTags();

        if (!empty($tags) && $this->cacheDriverSupportsTags()) {
            return Cache::tags($tags)->put($key, $value, $ttl);
        }

        return Cache::put($key, $value, $ttl);
    }

    /**
     * جلب أو تخزين قيمة في الكاش مع Tags
     * 
     * @param string $key
     * @param int $ttl
     * @param \Closure $callback
     * @return mixed
     */
    public function rememberWithTags(string $key, int $ttl, \Closure $callback): mixed
    {
        $tags = $this->getCacheTags();

        if (!empty($tags) && $this->cacheDriverSupportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }
}
