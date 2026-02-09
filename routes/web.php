<?php

use App\Http\Controllers\AcademicYear\AcademicYearController;
use App\Http\Controllers\AcademicYear\AcademicDirectoryController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Student\StudentProfile;
use App\Livewire\Student\StudentRegistration;
use App\Livewire\Student\StudentDirectory;
// use App\Http\Controllers\Student;
use App\Http\Controllers\Student\StudentController;
// use App\Http\Controllers\Teacher;
use App\Http\Controllers\Teacher\TeacherController;

use App\Livewire\Payroll;



Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Dashboard\MainDashboard::class)->name('dashboard');

    // Route::get('/academic-years', function () { return view('academic-years.index');})->name('academic-years.index');
    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::get('/academic-years/terms/{year_id?}', [AcademicYearController::class, 'terms'])->name('terms.index');

    // Year Closing Wizard
    Route::get('/academic-years/{year}/close', \App\Livewire\Academic\YearClosingWizard::class)
        ->name('academic-years.close')
        ->middleware('can:close.year');

    Route::get('/structure', [AcademicYearController::class, 'structure'])->name('structure.index'); // المراحل والصفوف
    Route::get('/class-sections', [AcademicYearController::class, 'classSections'])->name('class-sections.index'); // الشعب

    // الدليل الأكاديمي التفاعلي
    Route::get('/academic-directory', [AcademicDirectoryController::class, 'index'])->name('academic-directory.index');
    Route::get('/subject-manager', [AcademicYearController::class, 'subjectManager'])->name('subject-manager.index');

    // Students
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/register', StudentRegistration::class)->name('students.register');
    Route::get('/students/{id}', [StudentController::class, 'show'])->name('students.show');
    Route::get('/student/homeworks', \App\Livewire\Student\Homework\StudentHomeworkList::class)->name('student.homeworks.index');

    // Teachers
    Route::get('/teacher/dashboard', \App\Livewire\Teacher\TeacherDashboard::class)->name('teacher.dashboard');
    Route::get('/teacher/attendance-report', \App\Livewire\Attendance\AttendanceReport::class)->name('teacher.attendance.report');
    Route::get('/teacher/homework', \App\Livewire\Teacher\Homework\TeacherHomeworkDashboard::class)->name('teacher.homework.index');


    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create', \App\Livewire\Teacher\TeacherCreate::class)->name('teachers.create');
    Route::get('/teachers/{id}', [TeacherController::class, 'show'])->name('teachers.show');

    // Guardians
    Route::get('/guardians', \App\Livewire\Guardian\GuardianManager::class)->name('guardians.index');
    Route::get('/guardians/create', \App\Livewire\Guardian\GuardianCreate::class)->name('guardians.create');
    Route::get('/guardians/{id}', \App\Livewire\Guardian\GuardianShow::class)->name('guardians.show');

    // Course Assignment (Teacher Assignment to Sections)
    Route::get('/course-assignment', \App\Livewire\Academic\CourseAssignmentIndex::class)->name('course-offerings.index');
    Route::get('/course-assignment/{sectionId}', \App\Livewire\Academic\CourseAssignment::class)->name('course-assignment.show');

    // Academic Calendar
    Route::get('/academic/calendar', \App\Livewire\Admin\Events\EventManager::class)->name('academic.calendar');

    // Timetable Templates (قوالب الدوام)
    Route::get('/timetable-templates', \App\Livewire\Timetable\TimetableTemplateManager::class)->name('timetable-templates.index');

    // Timetable Builder
    Route::get('/timetable', \App\Livewire\Academic\TimetableBuilder::class)->name('timetable.builder');

    // Attendance Settings
    Route::get('/admin/settings/attendance', \App\Livewire\Attendance\AttendanceSettingsManager::class)->name('attendance.settings');

    // Attendance Taking
    Route::get('/teacher/attendance/{timetableId}', \App\Livewire\Teacher\AttendanceTaker::class)->name('attendance.take');
    Route::get('/teacher/timetable', \App\Livewire\Teacher\WeeklyTimetable::class)->name('teacher.timetable');

    // ==========================================
    // HR Module - إدارة شؤون الموظفين
    // ==========================================
    // ==========================================
    // HR Module - إدارة شؤون الموظفين
    // ==========================================
    Route::prefix('hr')->name('hr.')->group(function () {
        // إدارة فترات الدوام
        Route::get('/shifts', \App\Livewire\HR\WorkShiftManager::class)->name('shifts.index')->middleware('can:attendance.manage');

        // حضور الموظفين
        Route::get('/attendance', \App\Livewire\HR\StaffAttendanceDashboard::class)->name('attendance.index')->middleware('can:attendance.manage');

        // إدارة الموظفين
        Route::get('/staff', \App\Livewire\HR\StaffDirectory::class)->name('staff.index')->middleware('can:staff.view');
        Route::get('/staff/create', \App\Livewire\HR\StaffCreate::class)->name('staff.create')->middleware('can:staff.create');
        Route::get('/staff/{staff}/edit', \App\Livewire\HR\StaffEdit::class)->name('staff.edit')->middleware('can:staff.edit');
        Route::get('/staff/{staff}', \App\Livewire\HR\StaffShow::class)->name('staff.show')->middleware('can:staff.view');

        // إدارة الإجازات
        Route::get('/dashboard', \App\Livewire\HR\HRDashboard::class)->name('dashboard')->middleware('can:attendance.manage');
        Route::get('/leave/dashboard', \App\Livewire\HR\Leave\EmployeeLeaveDashboard::class)->name('leave.dashboard')->middleware('can:leave.request');
        Route::get('/leave/types', \App\Livewire\HR\Leave\LeaveTypeManager::class)->name('leave.types')->middleware('can:settings.edit');
        Route::get('/leave/request', \App\Livewire\HR\Leave\LeaveRequestForm::class)->name('leave.request')->middleware('can:leave.request');
        Route::get('/leave/approvals', \App\Livewire\HR\Leave\LeaveApprovalManager::class)->name('leave.approvals')->middleware('can:leaves.approve');
    });

    // ==========================================
    // Payroll Module - إدارة الرواتب
    // ==========================================
    Route::prefix('payroll')->name('payroll.')->middleware('can:payroll.manage')->group(function () {
        Route::get('/dashboard', \App\Livewire\Payroll\PayrollDashboard::class)->name('dashboard');
        Route::get('/loans', \App\Livewire\Payroll\LoanManager::class)->name('loans.index');
        Route::get('/contracts', \App\Livewire\Payroll\ContractManager::class)->name('contracts.index');
        Route::get('/batches', \App\Livewire\Payroll\PayrollBatchManager::class)->name('batches.index');
        Route::get('/payslip/{id}', \App\Livewire\Payroll\PayslipViewer::class)->name('payslip.show');
        Route::get('/salary-components', \App\Livewire\Payroll\SalaryComponentManager::class)->name('salary-components.index');
        Route::get('/settings', \App\Livewire\Payroll\PayrollPolicyEditor::class)->name('settings');
        Route::get('/process', \App\Livewire\Payroll\PayrollProcessor::class)->name('process');
        Route::get('/batches/{batch}/receipt', [\App\Http\Controllers\HR\Payroll\PayrollReceiptController::class, 'show'])->name('batches.receipt');
    });

    // ==========================================
    // Grading Module - نظام الدرجات
    // ==========================================
    Route::prefix('grading')->name('grading.')->group(function () {
        // Admin: Grading Settings (Unified)
        Route::get('/settings', \App\Livewire\Admin\Grading\GradingSettings::class)->name('settings')->middleware('can:curriculum.manage');
        Route::get('/subjects', \App\Livewire\Admin\Grading\GradingSettings::class)->name('subjects')->middleware('can:curriculum.manage');



        // Teacher: Gradebooks List
        Route::get('/gradebooks', \App\Livewire\Teacher\Grading\TeacherGradebooks::class)->name('gradebooks')->middleware('can:marks.view');

        // Teacher: Smart GradeBook
        Route::get('/gradebook/{courseOfferingId}', \App\Livewire\Teacher\Grading\SmartGradeBook::class)->name('gradebook.show')->middleware('can:marks.view');

        // Teacher: Homework Manager
        Route::get('/course/{courseOfferingId}/homework', \App\Livewire\Teacher\Homework\HomeworkManager::class)->name('homework.index')->middleware('can:marks.edit');
        Route::get('/homework/{homeworkId}/grade', \App\Livewire\Teacher\Homework\HomeworkGrader::class)->name('homework.grade')->middleware('can:marks.edit');
    });

    // ==========================================
    // Finance Module - المالية
    // ==========================================
    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/owner-dashboard', \App\Livewire\Finance\OwnerDashboard::class)->name('owner-dashboard')->middleware('can:finance.apply_discount');
        Route::get('/invoices', \App\Livewire\Finance\InvoiceList::class)->name('invoices.index')->middleware('can:finance.apply_discount');
        Route::get('/invoices/{invoice}', \App\Livewire\Finance\InvoiceShow::class)->name('invoices.show')->middleware('can:finance.apply_discount');
        Route::get('/payments/{payment}/receipt', [\App\Http\Controllers\Finance\PaymentReceiptController::class, 'show'])->name('payments.receipt');
    });

    // ==========================================
    // Control System - نظام الكنترول
    // ==========================================
    Route::prefix('control')->name('control.')->middleware('can:marks.override')->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Control\ControlDashboard::class)->name('dashboard');
        Route::get('/grading/{sessionId?}', \App\Livewire\Admin\Control\BlindGrading::class)->name('grading');
        Route::get('/results/{sessionId}', \App\Livewire\Admin\Control\ResultsViewer::class)->name('results');
        Route::get('/{sessionId}/holds', \App\Livewire\Admin\Control\ResultHoldsManager::class)->name('results.holds');

        // Printing
        Route::get('/print/{sessionId}/report-cards', \App\Livewire\Admin\Control\ReportCardPrint::class)->name('print.report-cards');
        Route::get('/print/{sessionId}/{type?}', \App\Livewire\Admin\Control\SeatingPrint::class)->name('print');
    });

    // Promotion System - نظام الترحيل
    // ==========================================
    Route::prefix('promotion')->name('promotion.')->middleware('can:students.promote')->group(function () {
        Route::get('/annual-results', \App\Livewire\Admin\Promotion\AnnualResultsDashboard::class)->name('annual-results');
        Route::get('/annual-results/{resultId}/report-card', \App\Livewire\Admin\Promotion\AnnualReportCard::class)->name('annual-report-card');
        Route::get('/manage', \App\Livewire\Admin\Promotion\PromotionManager::class)->name('manage');
        Route::get('/settings', \App\Livewire\Admin\Promotion\PromotionSettings::class)->name('settings');
    });

    // ==========================================
    // System Access Control (ACL)
    // ==========================================
    Route::prefix('admin/access')->name('admin.access.')->middleware('can:roles.manage')->group(function () {
        Route::get('/roles', \App\Livewire\Admin\Access\RoleManager::class)->name('roles.index');
        Route::get('/roles/create', \App\Livewire\Admin\Access\RoleEditor::class)->name('roles.create');
        Route::get('/roles/{role}/edit', \App\Livewire\Admin\Access\RoleEditor::class)->name('roles.edit');
    });

});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
?>
