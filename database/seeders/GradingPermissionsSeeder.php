<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * GradingPermissionsSeeder - بذور صلاحيات نظام الدرجات
 * 
 * ينشئ الصلاحيات المطلوبة لنظام الدرجات ويربطها بالأدوار المناسبة.
 * 
 * @see /home/a/.gemini/antigravity/brain/bc1f1c2f-4dd3-47d4-80da-989445301eb9/permissions_guide.md
 */
class GradingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // إنشاء صلاحيات Grading
        // ═══════════════════════════════════════════════════════════════

        $permissions = [
            // Admin Permissions
            'grading.amend' => 'تعديل الدرجات بعد الإغلاق (Amendments)',
            'grading.recompute' => 'إعادة احتساب coursework',
            'grading.manage_templates' => 'إدارة قوالب التقييم',
            'grading.manage_settings' => 'إدارة الإعدادات العامة للدرجات',

            // Teacher Permissions
            'grading.record_marks' => 'تسجيل درجات الطلاب',
            'grading.view_templates' => 'عرض قوالب التقييم',
            'grading.view_gradebook' => 'عرض دفتر الدرجات',
            'grading.view_marks' => 'عرض درجات الطلاب',
            'grading.view_marks_history' => 'عرض تاريخ تعديلات الدرجات',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['guard_name' => 'web'] // default guard
            );
        }

        // ═══════════════════════════════════════════════════════════════
        // تخصيص الصلاحيات للأدوار
        // ═══════════════════════════════════════════════════════════════

        // Admin Role: يحصل على جميع صلاحيات Grading
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'grading.amend',
            'grading.recompute',
            'grading.manage_templates',
            'grading.manage_settings',
        ]);

        // Teacher Role: صلاحيات محدودة للمعلمين
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);
        $teacherRole->givePermissionTo([
            'grading.record_marks',
            'grading.view_templates',
            'grading.view_gradebook',
            'grading.view_marks',
            'grading.view_marks_history',
        ]);

        // ملاحظة: Super Admin يحصل على كل الصلاحيات تلقائياً عبر Gate::before

        $this->command->info('✅ Grading permissions seeded successfully.');
        $this->command->info('   Admin Permissions:');
        $this->command->info('   - grading.amend');
        $this->command->info('   - grading.recompute');
        $this->command->info('   - grading.manage_templates');
        $this->command->info('   - grading.manage_settings');
        $this->command->info('   Teacher Permissions:');
        $this->command->info('   - grading.record_marks');
        $this->command->info('   - grading.view_templates');
        $this->command->info('   - grading.view_gradebook');
        $this->command->info('   - grading.view_marks');
        $this->command->info('   - grading.view_marks_history');
    }
}
