<?php

namespace App\Domains\Academic\Student\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domains\Academic\Grade\Models\Grade;
use App\Domains\Academic\ClassSection\Models\ClassSection;
use App\Domains\Academic\Student\Models\Guardian;
use App\Domains\Shared\Models\Address;
use App\Domains\Shared\Models\Attachment;
use App\Domains\Academic\Student\Models\AdmissionApplication;
use App\Domains\Shared\Models\User;
use App\Domains\Finance\Models\Invoice;
use App\Domains\Academic\Attendance\Models\Attendance;
use App\Domains\Academic\Control\Models\ExamSeating;
use App\Domains\Academic\Results\Models\AnnualResult;
use App\Domains\Academic\Promotion\Models\Promotion;
use App\Domains\Academic\Student\Models\StudentHealthCondition;

class Student extends Model
{
    use HasFactory;
    use \App\Domains\Academic\Student\Traits\StudentScopes;
    use \App\Infrastructure\Traits\HandlesSafeDelete;
    use \App\Infrastructure\Traits\HasModelLabels;
    use \App\Infrastructure\Traits\InvalidatesCache;
    use \App\Infrastructure\Traits\HasFileDeletion;

    protected static function newFactory()
    {
        return \Database\Factories\Domains\Academic\Student\Models\StudentFactory::new();
    }

    /**
     * اسم الموديل بالعربي
     */
    protected static string $modelLabel = 'طالب';
    protected static string $modelPluralLabel = 'طلاب';

    /**
     * العلاقات المحمية من الحذف
     */
    protected array $protectedRelations = [
        'invoices' => 'فواتير',
        'attendances' => 'سجلات حضور',
        'annualResults' => 'نتائج سنوية',
        'enrollments' => 'تسجيلات',
        'seatings' => 'مقاعد اختبارات',
        'promotions' => 'قرارات ترحيل',
        'healthConditions' => 'حالات صحية',
        'previousHistories' => 'سجلات سابقة',
    ];

    /**
     * Cache Tags
     */
    protected array $cacheTags = ['students'];

    /**
     * إعدادات حذف الملفات
     */
    protected array $fileColumns = ['profile_photo_path'];
    protected string $fileDisk = 'public';

    protected $fillable = [
        'user_id',
        'admission_application_id',// رقم طلب القبول    
        'admission_number',// رقم القبول
        'first_name_ar',
        'family_name_ar',
        'first_name_en',
        'family_name_en',
        'date_of_birth',
        'gender',
        'nationality_id',
        'national_id',
        'passport_number',// 
        'blood_type',
        'current_grade_id',
        'current_class_section_id',
        'status',
        'profile_photo_path',
    ];

    protected $casts = [
        'status' => \App\Domains\Academic\Student\Enums\StudentStatus::class,
        'gender' => \App\Domains\Shared\Enums\Gender::class,
        'date_of_birth' => 'date',
    ];
    // اربط بين الطلاب والمحافظين   نستطيع من خلالها مثلا معرفه العلاقة بين الطالب وولي الامر ظ 
    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'student_guardian')
            ->withPivot(['relationship', 'is_emergency_contact', 'is_financial_sponsor', 'lives_with', 'has_portal_access'])
            ->withTimestamps();
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function admissionApplication()// علاقة الطلاب بالطلبات  بحيث نستطيع معرفة طالب ما ينتمي للطلب ما            
    {
        return $this->belongsTo(AdmissionApplication::class);
    }

    public function enrollments()
    {
        return $this->hasMany(StudentEnrollment::class);
    }

    //  تعمل على سجلات الطالب السابق   بحيث  من خلالها نستطيع ان نرى سجلات الطالب السابق   
    public function previousHistories()
    {
        return $this->hasMany(StudentPreviousHistory::class);
    }

    public function healthConditions()
    {
        return $this->hasMany(StudentHealthCondition::class);
    }

    // Accessors
    public function getEmailAttribute()
    {
        return $this->user?->email;
    }

    public function getFullNameArAttribute()
    {
        return "{$this->first_name_ar} {$this->family_name_ar}";
    }

    public function getFullNameEnAttribute()
    {
        return trim("{$this->first_name_en} {$this->family_name_en}");
    }

    public function getProfilePhotoUrlAttribute()
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : 'https://ui-avatars.com/api/?name=' . urlencode($this->full_name_en ?: $this->full_name_ar) . '&color=7F9CF5&background=EBF4FF';
    }

    // Scopes


    public function currentGrade()
    {
        return $this->belongsTo(Grade::class, 'current_grade_id');
    }

    public function currentClassSection()
    {
        return $this->belongsTo(ClassSection::class, 'current_class_section_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function seatings()
    {
        return $this->hasMany(ExamSeating::class);
    }

    public function annualResults()
    {
        return $this->hasMany(AnnualResult::class);
    }

    public function promotions()
    {
        return $this->hasMany(Promotion::class);
    }

}
