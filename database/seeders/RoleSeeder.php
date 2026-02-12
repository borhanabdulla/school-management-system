<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * RoleSeeder - بذور الأدوار والصلاحيات الشاملة
 *
 * ينشئ جميع الصلاحيات والأدوار المطلوبة للنظام ويربطها ببعضها.
 * يمكن تشغيله عدة مرات بأمان (firstOrCreate).
 *
 * الأدوار:
 *  - Super Admin  → كل شيء عبر Gate::before (لا يحتاج صلاحيات صريحة)
 *  - admin        → إدارة شاملة باستثناء الحساس
 *  - teacher      → دفتر الدرجات + الحضور + الواجبات + الجدول
 *  - student      → عرض درجاتي + حضوري + واجباتي
 *  - parent       → عرض درجات وحضور أبنائي
 *  - accountant   → المالية والفواتير
 *  - hr_manager   → الموظفين + الحضور + الإجازات + الرواتب
 *  - academic_coordinator → المناهج + الهيكل الأكاديمي
 */
class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 1. إنشاء جميع الصلاحيات
        // ═══════════════════════════════════════════════════════════════

        $permissions = [
            // === النظام والإعدادات ===
            'close.year',
            'sensitive.manage',
            'roles.manage',
            'settings.edit',
            'logs.view',

            // === الطلاب ===
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            'students.promote',

            // === المناهج والبنية الأكاديمية ===
            'curriculum.manage',
            'classes.manage',
            'timetable.manage',

            // === الدرجات (عام) ===
            'marks.view',
            'marks.edit',
            'marks.override',

            // === الدرجات (تفصيلي) ===
            'grading.amend',
            'grading.recompute',
            'grading.manage_templates',
            'grading.manage_settings',
            'grading.record_marks',
            'grading.view_templates',
            'grading.view_gradebook',
            'grading.view_marks',
            'grading.view_marks_history',

            // === الموظفين ===
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',

            // === الحضور ===
            'attendance.manage',
            'attendance.view',
            'attendance.take',

            // === الإجازات ===
            'leaves.approve',
            'leave.request',

            // === الرواتب ===
            'payroll.manage',

            // === المالية ===
            'finance.view',
            'finance.apply_discount',
            'finance.record_payment',
            'finance.cancel_payment',

            // === أولياء الأمور ===
            'guardians.view',
            'guardians.create',
            'guardians.edit',

            // === صلاحيات المعلم الخاصة ===
            'teacher.dashboard',
            'teacher.timetable',
            'teacher.homework',

            // === صلاحيات الطالب ===
            'student.view_own_grades',
            'student.view_own_attendance',
            'student.homework',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }

        // ═══════════════════════════════════════════════════════════════
        // 2. إنشاء الأدوار
        // ═══════════════════════════════════════════════════════════════

        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $teacher = Role::firstOrCreate(['name' => 'teacher', 'guard_name' => 'web']);
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $parent = Role::firstOrCreate(['name' => 'parent', 'guard_name' => 'web']);
        $accountant = Role::firstOrCreate(['name' => 'accountant', 'guard_name' => 'web']);
        $hrManager = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $academicCoordinator = Role::firstOrCreate(['name' => 'academic_coordinator', 'guard_name' => 'web']);

        // ═══════════════════════════════════════════════════════════════
        // 3. تخصيص الصلاحيات لكل دور
        // ═══════════════════════════════════════════════════════════════

        // Super Admin: لا يحتاج صلاحيات صريحة — يحصل على كل شيء عبر Gate::before
        // لكننا نعطيه كل شيء حتى يظهر في لوحة التحكم
        $superAdmin->syncPermissions(Permission::all());

        // ─── Admin: إدارة شاملة ما عدا الحساس ─────────────────────
        $admin->syncPermissions([
            // الطلاب
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            'students.promote',
            // المناهج
            'curriculum.manage',
            'classes.manage',
            'timetable.manage',
            // الدرجات
            'marks.view',
            'marks.edit',
            'marks.override',
            'grading.amend',
            'grading.recompute',
            'grading.manage_templates',
            'grading.manage_settings',
            'grading.record_marks',
            'grading.view_templates',
            'grading.view_gradebook',
            'grading.view_marks',
            'grading.view_marks_history',
            // الموظفين
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            // الحضور
            'attendance.manage',
            'attendance.view',
            'attendance.take',
            // الإجازات
            'leaves.approve',
            'leave.request',
            // الرواتب
            'payroll.manage',
            // المالية
            'finance.view',
            'finance.apply_discount',
            'finance.record_payment',
            'finance.cancel_payment',
            // أولياء الأمور
            'guardians.view',
            'guardians.create',
            'guardians.edit',
            // صلاحيات معلم (يمكن للأدمن الاطلاع)
            'teacher.dashboard',
            'teacher.timetable',
            'teacher.homework',
            // الإعدادات
            'roles.manage',
            'settings.edit',
            'logs.view',
        ]);

        // ─── Teacher: الأشياء التي تخصه فقط ──────────────────────
        $teacher->syncPermissions([
            'marks.view',
            'marks.edit',
            'attendance.view',
            'attendance.take',
            'leave.request',
            // Grading
            'grading.record_marks',
            'grading.view_templates',
            'grading.view_gradebook',
            'grading.view_marks',
            'grading.view_marks_history',
            // لوحة المعلم
            'teacher.dashboard',
            'teacher.timetable',
            'teacher.homework',
            // عرض طلابه
            'students.view',
        ]);

        // ─── Student: فقط الأشياء التي تخصه ──────────────────────
        $student->syncPermissions([
            'student.view_own_grades',
            'student.view_own_attendance',
            'student.homework',
        ]);

        // ─── Parent: عرض أبنائه فقط ─────────────────────────────
        $parent->syncPermissions([
            'student.view_own_grades',
            'student.view_own_attendance',
        ]);

        // ─── Accountant: المالية فقط + عرض الطلاب ──────────────
        $accountant->syncPermissions([
            'finance.view',
            'finance.apply_discount',
            'finance.record_payment',
            'finance.cancel_payment',
            'students.view',
            'guardians.view',
        ]);

        // ─── HR Manager: الموظفين والحضور والإجازات والرواتب ────
        $hrManager->syncPermissions([
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',
            'attendance.manage',
            'attendance.view',
            'leaves.approve',
            'leave.request',
            'payroll.manage',
        ]);

        // ─── Academic Coordinator: المناهج والبنية ──────────────
        $academicCoordinator->syncPermissions([
            'curriculum.manage',
            'classes.manage',
            'timetable.manage',
            'students.view',
            'marks.view',
            'grading.view_gradebook',
            'grading.view_marks',
            'grading.view_templates',
            'guardians.view',
        ]);

        // ═══════════════════════════════════════════════════════════════
        // 4. ملخص
        // ═══════════════════════════════════════════════════════════════

        $this->command->info('');
        $this->command->info('✅ تم بذر الأدوار والصلاحيات بنجاح!');
        $this->command->info('');
        $this->command->info('   الأدوار المُنشأة:');
        $this->command->info('   ├── Super Admin  (كل الصلاحيات عبر Gate::before)');
        $this->command->info('   ├── admin        (' . $admin->permissions->count() . ' صلاحية)');
        $this->command->info('   ├── teacher      (' . $teacher->permissions->count() . ' صلاحية)');
        $this->command->info('   ├── student      (' . $student->permissions->count() . ' صلاحية)');
        $this->command->info('   ├── parent       (' . $parent->permissions->count() . ' صلاحية)');
        $this->command->info('   ├── accountant   (' . $accountant->permissions->count() . ' صلاحية)');
        $this->command->info('   ├── hr_manager   (' . $hrManager->permissions->count() . ' صلاحية)');
        $this->command->info('   └── academic_coordinator (' . $academicCoordinator->permissions->count() . ' صلاحية)');
        $this->command->info('');
        $this->command->info('   إجمالي الصلاحيات: ' . count($permissions));
    }
}
