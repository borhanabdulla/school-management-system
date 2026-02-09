<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Student\Models\Student;
use App\Domains\Shared\Models\Address;
use App\Domains\Shared\Models\User;

/**
 * Guardian Model - ولي الأمر
 * 
 * يمثل ولي أمر الطالب ويحتوي على بياناته الأساسية
 */
class Guardian extends Model
{
    use HasFactory;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;

    // ============================================
    // التسميات بالعربي
    // ============================================
    protected static string $modelLabel = 'ولي أمر';
    protected static string $modelPluralLabel = 'أولياء الأمور';
    protected static string $labelAttribute = 'first_name';

    // ============================================
    // العلاقات المحمية من الحذف
    // ============================================
    protected array $protectedRelations = [
        'students' => 'طلاب',
    ];

    protected $fillable = [
        'user_id',
        'nationality_id',
        'national_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'employer',
        'work_phone',
        'preferred_language',
    ];

    /**
     * العلاقة مع الطلاب
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot(['relationship', 'is_financial_sponsor']);
    }

    /**
     * العلاقة مع User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * العلاقة مع العناوين
     */
    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }
}
