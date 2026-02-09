<?php

declare(strict_types=1);

namespace App\Infrastructure\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

/**
 * HasFileDeletion - حذف الملفات المرتبطة
 * 
 * هذا الـ Trait يحذف الملفات المرتبطة بالسجل تلقائياً عند حذفه.
 * يستخدم DB::afterCommit لضمان الحذف فقط بعد نجاح حذف السجل.
 * 
 * @example
 * class Student extends Model {
 *     use HasFileDeletion;
 *     
 *     // أعمدة الملفات التي يجب حذفها
 *     protected array $fileColumns = ['profile_photo_path', 'document_path'];
 *     
 *     // القرص المستخدم (اختياري، الافتراضي: public)
 *     protected string $fileDisk = 'public';
 * }
 * 
 * // عند حذف الطالب، ستُحذف صور البروفايل والمستندات تلقائياً
 * 
 * @author School Dashboard Team
 * @version 2.0
 */
trait HasFileDeletion
{
    /**
     * تفعيل حذف الملفات
     */
    public static function bootHasFileDeletion(): void
    {
        static::deleting(function (Model $model) {
            // نجمع الملفات قبل الحذف
            $filesToDelete = $model->getFilesToDelete();

            // نحذف الملفات بعد نجاح الـ Transaction
            if (!empty($filesToDelete)) {
                DB::afterCommit(function () use ($filesToDelete, $model) {
                    $disk = $model->getFileDisk();

                    foreach ($filesToDelete as $path) {
                        if ($path && Storage::disk($disk)->exists($path)) {
                            Storage::disk($disk)->delete($path);
                        }
                    }
                });
            }
        });
    }

    /**
     * الحصول على قائمة الملفات للحذف
     * 
     * @return array<string>
     */
    public function getFilesToDelete(): array
    {
        $files = [];

        foreach ($this->getFileColumns() as $column) {
            $path = $this->{$column};
            if ($path) {
                $files[] = $path;
            }
        }

        return $files;
    }

    /**
     * الحصول على أعمدة الملفات
     * 
     * @return array<string>
     */
    public function getFileColumns(): array
    {
        return $this->fileColumns ?? [];
    }

    /**
     * الحصول على القرص المستخدم
     * 
     * @return string
     */
    public function getFileDisk(): string
    {
        return $this->fileDisk ?? 'public';
    }

    /**
     * حذف ملف معين يدوياً
     * 
     * @param string $column اسم العمود
     * @return bool
     */
    public function deleteFile(string $column): bool
    {
        $path = $this->{$column};

        if ($path && Storage::disk($this->getFileDisk())->exists($path)) {
            $deleted = Storage::disk($this->getFileDisk())->delete($path);

            if ($deleted) {
                $this->update([$column => null]);
            }

            return $deleted;
        }

        return false;
    }

    /**
     * استبدال ملف (حذف القديم ورفع الجديد)
     * 
     * @param string $column اسم العمود
     * @param \Illuminate\Http\UploadedFile $file الملف الجديد
     * @param string $directory المجلد
     * @return string المسار الجديد
     */
    public function replaceFile(string $column, $file, string $directory = ''): string
    {
        // حذف الملف القديم
        $this->deleteFile($column);

        // رفع الملف الجديد
        $path = $file->store($directory ?: class_basename($this), $this->getFileDisk());

        // تحديث المسار
        $this->update([$column => $path]);

        return $path;
    }

    /**
     * الحصول على رابط الملف
     * 
     * @param string $column اسم العمود
     * @return string|null
     */
    public function getFileUrl(string $column): ?string
    {
        $path = $this->{$column};

        if ($path) {
            return Storage::disk($this->getFileDisk())->url($path);
        }

        return null;
    }
}
