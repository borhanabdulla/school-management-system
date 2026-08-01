<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->index('financial_closed_by');
        });

        Schema::table('admission_applications', function (Blueprint $table) {
            $table->index('target_grade_id');
            $table->index('academic_year_id');
        });

        Schema::table('annual_results', function (Blueprint $table) {
            $table->index('processed_by');
            $table->index('grade_id');
            $table->index('academic_year_id');
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->index('template_category_id');
            $table->index('course_offering_id');
        });

        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->index('academic_year_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('term_id');
            $table->index('recorded_by');
            $table->index('time_slot_id');
            $table->index('academic_year_id');
        });

        Schema::table('class_attendances', function (Blueprint $table) {
            $table->index('recorded_by_teacher_id');
            $table->index('student_id');
            $table->index('timetable_id');
        });

        Schema::table('class_sections', function (Blueprint $table) {
            $table->index('homeroom_teacher_id');
            $table->index('grade_id');
            $table->index('academic_year_id');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->index('academic_year_id');
            $table->index('locked_by');
        });

        Schema::table('control_marks', function (Blueprint $table) {
            $table->index('audited_by');
            $table->index('entered_by');
            $table->index('course_offering_id');
        });

        Schema::table('course_offerings', function (Blueprint $table) {
            $table->index('subject_id');
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->index('term_id');
        });

        Schema::table('discipline_incidents', function (Blueprint $table) {
            $table->index('violation_type_id');
            $table->index('term_id');
            $table->index('student_id');
        });

        Schema::table('discount_applications', function (Blueprint $table) {
            $table->index('applied_by');
            $table->index('discount_id');
        });

        Schema::table('exam_committees', function (Blueprint $table) {
            $table->index('supervisor_id');
            $table->index('exam_session_id');
        });

        Schema::table('exam_seatings', function (Blueprint $table) {
            $table->index('committee_id');
            $table->index('student_id');
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->index('term_id');
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->index('grade_id');
            $table->index('fee_type_id');
            $table->index('academic_year_id');
        });

        Schema::table('final_results', function (Blueprint $table) {
            $table->index('course_offering_id');
            $table->index('student_id');
        });

        Schema::table('financial_clearance_overrides', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('academic_year_id');
        });

        Schema::table('financial_closing_logs', function (Blueprint $table) {
            $table->index('closed_by');
            $table->index('academic_year_id');
        });

        Schema::table('grade_distributions', function (Blueprint $table) {
            $table->index('assessment_type_id');
            $table->index('term_id');
            $table->index('subject_id');
        });

        Schema::table('grade_subjects', function (Blueprint $table) {
            $table->index('subject_id');
        });

        Schema::table('gradebook_months', function (Blueprint $table) {
            $table->index('academic_year_id');
            $table->index('term_id');
        });

        Schema::table('gradebook_settings', function (Blueprint $table) {
            $table->index('academic_year_id');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->index('next_grade_id');
        });

        Schema::table('grading_templates', function (Blueprint $table) {
            $table->index('term_id');
            $table->index('academic_year_id');
            $table->index('grade_id');
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->index('nationality_id');
            $table->index('user_id');
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->index('student_id');
        });

        Schema::table('homeworks', function (Blueprint $table) {
            $table->index('assessment_id');
            $table->index('course_offering_id');
        });

        Schema::table('hr_amendments', function (Blueprint $table) {
            $table->index('approved_by');
            $table->index('requested_by');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index('discount_id');
            $table->index('fee_type_id');
            $table->index('invoice_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->index('payer_set_by');
            $table->index('student_id');
            $table->index('academic_year_id');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('approved_by');
            $table->index('leave_type_id');
            $table->index('staff_id');
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('cancelled_by');
        });

        Schema::table('loan_installments', function (Blueprint $table) {
            $table->index('payroll_batch_id');
            $table->index('loan_id');
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->index('approved_by');
            $table->index('staff_id');
        });

        Schema::table('monthly_category_mappings', function (Blueprint $table) {
            $table->index('subject_id');
            $table->index('grade_id');
            $table->index('term_id');
        });

        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->index('amended_by');
            $table->index('gradebook_month_id');
            $table->index('graded_by');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index('cancelled_by');
            $table->index('guardian_id');
            $table->index('created_by');
        });

        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->index('academic_year_id');
            $table->index('approved_by');
            $table->index('frozen_by');
            $table->index('generated_by');
            $table->index('paid_by');
        });

        Schema::table('payroll_records', function (Blueprint $table) {
            $table->index('adjusted_by');
            $table->index('staff_id');
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->index('staff_id');
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->index('processed_by');
            $table->index('reverted_by');
            $table->index('to_class_section_id');
            $table->index('to_grade_id');
            $table->index('from_grade_id');
            $table->index('annual_result_id');
            $table->index('academic_year_id');
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->index('role_id');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->index('work_shift_id');
            $table->index('user_id');
        });

        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->index('recorded_by');
        });

        Schema::table('staff_leave_balances', function (Blueprint $table) {
            $table->index('leave_type_id');
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->index('class_section_id');
            $table->index('grade_id');
            $table->index('academic_year_id');
        });

        Schema::table('student_grades', function (Blueprint $table) {
            $table->index('graded_by_user_id');
            $table->index('assessment_id');
        });

        Schema::table('student_guardian', function (Blueprint $table) {
            $table->index('guardian_id');
        });

        Schema::table('student_health_conditions', function (Blueprint $table) {
            $table->index('health_condition_type_id');
            $table->index('student_id');
        });

        Schema::table('student_marks', function (Blueprint $table) {
            $table->index('amended_by');
            $table->index('course_offering_id');
            $table->index('assessment_id');
            $table->index('template_category_id');
            $table->index('graded_by_user_id');
            $table->index('academic_year_id');
            $table->index('term_id');
        });

        Schema::table('student_previous_histories', function (Blueprint $table) {
            $table->index('student_id');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('admission_application_id');
            $table->index('nationality_id');
            $table->index('current_grade_id');
            $table->index('current_class_section_id');
        });

        Schema::table('subject_grading_configs', function (Blueprint $table) {
            $table->index('grading_template_id');
            $table->index('term_id');
            $table->index('grade_id');
        });

        Schema::table('substitution_classes', function (Blueprint $table) {
            $table->index('substitute_teacher_id');
        });

        Schema::table('substitutions', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('leave_request_id');
            $table->index('original_teacher_id');
            $table->index('timetable_id');
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->index('staff_id');
            $table->index('user_id');
        });

        Schema::table('template_categories', function (Blueprint $table) {
            $table->index('parent_id');
            $table->index('grading_template_id');
        });

        Schema::table('term_result_failures', function (Blueprint $table) {
            $table->index('template_category_id');
        });

        Schema::table('term_results', function (Blueprint $table) {
            $table->index('term_id');
            $table->index('course_offering_id');
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->index('facility_id');
            $table->index('time_slot_id');
            $table->index('term_id');
        });

        Schema::table('transport_subscriptions', function (Blueprint $table) {
            $table->index('term_id');
            $table->index('transport_route_id');
            $table->index('vehicle_id');
        });

    }

    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropIndex(['financial_closed_by']);
        });

        Schema::table('admission_applications', function (Blueprint $table) {
            $table->dropIndex(['target_grade_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('annual_results', function (Blueprint $table) {
            $table->dropIndex(['processed_by']);
            $table->dropIndex(['grade_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropIndex(['template_category_id']);
            $table->dropIndex(['course_offering_id']);
        });

        Schema::table('attendance_settings', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
            $table->dropIndex(['recorded_by']);
            $table->dropIndex(['time_slot_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('class_attendances', function (Blueprint $table) {
            $table->dropIndex(['recorded_by_teacher_id']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['timetable_id']);
        });

        Schema::table('class_sections', function (Blueprint $table) {
            $table->dropIndex(['homeroom_teacher_id']);
            $table->dropIndex(['grade_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['locked_by']);
        });

        Schema::table('control_marks', function (Blueprint $table) {
            $table->dropIndex(['audited_by']);
            $table->dropIndex(['entered_by']);
            $table->dropIndex(['course_offering_id']);
        });

        Schema::table('course_offerings', function (Blueprint $table) {
            $table->dropIndex(['subject_id']);
        });

        Schema::table('daily_attendances', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
        });

        Schema::table('discipline_incidents', function (Blueprint $table) {
            $table->dropIndex(['violation_type_id']);
            $table->dropIndex(['term_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('discount_applications', function (Blueprint $table) {
            $table->dropIndex(['applied_by']);
            $table->dropIndex(['discount_id']);
        });

        Schema::table('exam_committees', function (Blueprint $table) {
            $table->dropIndex(['supervisor_id']);
            $table->dropIndex(['exam_session_id']);
        });

        Schema::table('exam_seatings', function (Blueprint $table) {
            $table->dropIndex(['committee_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('exam_sessions', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
        });

        Schema::table('fee_structures', function (Blueprint $table) {
            $table->dropIndex(['grade_id']);
            $table->dropIndex(['fee_type_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('final_results', function (Blueprint $table) {
            $table->dropIndex(['course_offering_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('financial_clearance_overrides', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('financial_closing_logs', function (Blueprint $table) {
            $table->dropIndex(['closed_by']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('grade_distributions', function (Blueprint $table) {
            $table->dropIndex(['assessment_type_id']);
            $table->dropIndex(['term_id']);
            $table->dropIndex(['subject_id']);
        });

        Schema::table('grade_subjects', function (Blueprint $table) {
            $table->dropIndex(['subject_id']);
        });

        Schema::table('gradebook_months', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['term_id']);
        });

        Schema::table('gradebook_settings', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->dropIndex(['next_grade_id']);
        });

        Schema::table('grading_templates', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['grade_id']);
        });

        Schema::table('guardians', function (Blueprint $table) {
            $table->dropIndex(['nationality_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('homework_submissions', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
        });

        Schema::table('homeworks', function (Blueprint $table) {
            $table->dropIndex(['assessment_id']);
            $table->dropIndex(['course_offering_id']);
        });

        Schema::table('hr_amendments', function (Blueprint $table) {
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['requested_by']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex(['discount_id']);
            $table->dropIndex(['fee_type_id']);
            $table->dropIndex(['invoice_id']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['payer_set_by']);
            $table->dropIndex(['student_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['leave_type_id']);
            $table->dropIndex(['staff_id']);
        });

        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['cancelled_by']);
        });

        Schema::table('loan_installments', function (Blueprint $table) {
            $table->dropIndex(['payroll_batch_id']);
            $table->dropIndex(['loan_id']);
        });

        Schema::table('loans', function (Blueprint $table) {
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['staff_id']);
        });

        Schema::table('monthly_category_mappings', function (Blueprint $table) {
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['grade_id']);
            $table->dropIndex(['term_id']);
        });

        Schema::table('monthly_grades', function (Blueprint $table) {
            $table->dropIndex(['amended_by']);
            $table->dropIndex(['gradebook_month_id']);
            $table->dropIndex(['graded_by']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['cancelled_by']);
            $table->dropIndex(['guardian_id']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('payroll_batches', function (Blueprint $table) {
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['approved_by']);
            $table->dropIndex(['frozen_by']);
            $table->dropIndex(['generated_by']);
            $table->dropIndex(['paid_by']);
        });

        Schema::table('payroll_records', function (Blueprint $table) {
            $table->dropIndex(['adjusted_by']);
            $table->dropIndex(['staff_id']);
        });

        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropIndex(['staff_id']);
        });

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropIndex(['processed_by']);
            $table->dropIndex(['reverted_by']);
            $table->dropIndex(['to_class_section_id']);
            $table->dropIndex(['to_grade_id']);
            $table->dropIndex(['from_grade_id']);
            $table->dropIndex(['annual_result_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('role_has_permissions', function (Blueprint $table) {
            $table->dropIndex(['role_id']);
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropIndex(['work_shift_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('staff_attendance', function (Blueprint $table) {
            $table->dropIndex(['recorded_by']);
        });

        Schema::table('staff_leave_balances', function (Blueprint $table) {
            $table->dropIndex(['leave_type_id']);
        });

        Schema::table('student_enrollments', function (Blueprint $table) {
            $table->dropIndex(['class_section_id']);
            $table->dropIndex(['grade_id']);
            $table->dropIndex(['academic_year_id']);
        });

        Schema::table('student_grades', function (Blueprint $table) {
            $table->dropIndex(['graded_by_user_id']);
            $table->dropIndex(['assessment_id']);
        });

        Schema::table('student_guardian', function (Blueprint $table) {
            $table->dropIndex(['guardian_id']);
        });

        Schema::table('student_health_conditions', function (Blueprint $table) {
            $table->dropIndex(['health_condition_type_id']);
            $table->dropIndex(['student_id']);
        });

        Schema::table('student_marks', function (Blueprint $table) {
            $table->dropIndex(['amended_by']);
            $table->dropIndex(['course_offering_id']);
            $table->dropIndex(['assessment_id']);
            $table->dropIndex(['template_category_id']);
            $table->dropIndex(['graded_by_user_id']);
            $table->dropIndex(['academic_year_id']);
            $table->dropIndex(['term_id']);
        });

        Schema::table('student_previous_histories', function (Blueprint $table) {
            $table->dropIndex(['student_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['admission_application_id']);
            $table->dropIndex(['nationality_id']);
            $table->dropIndex(['current_grade_id']);
            $table->dropIndex(['current_class_section_id']);
        });

        Schema::table('subject_grading_configs', function (Blueprint $table) {
            $table->dropIndex(['grading_template_id']);
            $table->dropIndex(['term_id']);
            $table->dropIndex(['grade_id']);
        });

        Schema::table('substitution_classes', function (Blueprint $table) {
            $table->dropIndex(['substitute_teacher_id']);
        });

        Schema::table('substitutions', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['leave_request_id']);
            $table->dropIndex(['original_teacher_id']);
            $table->dropIndex(['timetable_id']);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropIndex(['staff_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('template_categories', function (Blueprint $table) {
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['grading_template_id']);
        });

        Schema::table('term_result_failures', function (Blueprint $table) {
            $table->dropIndex(['template_category_id']);
        });

        Schema::table('term_results', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
            $table->dropIndex(['course_offering_id']);
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->dropIndex(['facility_id']);
            $table->dropIndex(['time_slot_id']);
            $table->dropIndex(['term_id']);
        });

        Schema::table('transport_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['term_id']);
            $table->dropIndex(['transport_route_id']);
            $table->dropIndex(['vehicle_id']);
        });

    }
};
