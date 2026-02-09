# Database Schema Reference

Generated on 2026-01-31 18:14:51

## academic_years

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| start_date | date |  | No |  |
| end_date | date |  | No |  |
| status | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## addresses

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| addressable_type | varchar |  | No |  |
| addressable_id | INTEGER |  | No |  |
| city | varchar |  | No |  |
| district | varchar |  | No |  |
| street_name | varchar |  | No |  |
| building_number | varchar |  | Yes |  |
| national_address_code | varchar |  | Yes |  |
| latitude | numeric |  | Yes |  |
| longitude | numeric |  | Yes |  |
| is_primary | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## admission_applications

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| reference_no | varchar |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| target_grade_id | INTEGER |  | No |  |
| student_national_id | varchar |  | No |  |
| first_name | varchar |  | No |  |
| last_name | varchar |  | No |  |
| date_of_birth | date |  | No |  |
| guardian_name | varchar |  | No |  |
| guardian_phone | varchar |  | No |  |
| guardian_email | varchar |  | Yes |  |
| status | varchar |  | No | 'new' |
| interview_notes | TEXT |  | Yes |  |
| entrance_exam_score | numeric |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## annual_results

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| grade_id | INTEGER |  | No |  |
| term1_total | numeric |  | No | '0' |
| term1_max | numeric |  | No | '0' |
| term2_total | numeric |  | No | '0' |
| term2_max | numeric |  | No | '0' |
| annual_total | numeric |  | No | '0' |
| annual_max | numeric |  | No | '0' |
| percentage | numeric |  | No | '0' |
| failed_subjects | TEXT |  | Yes |  |
| failed_count | INTEGER |  | No | '0' |
| decision | varchar |  | No | 'pending' |
| grade_label | varchar |  | Yes |  |
| rank | INTEGER |  | Yes |  |
| processed_by | INTEGER |  | Yes |  |
| processed_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## assessment_types

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## assessments

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| course_offering_id | INTEGER |  | No |  |
| template_category_id | INTEGER |  | No |  |
| title | varchar |  | No |  |
| max_score | numeric |  | No |  |
| weight | numeric |  | Yes |  |
| due_date | date |  | Yes |  |
| is_published | tinyint(1) |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## attachments

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| attachable_type | varchar |  | No |  |
| attachable_id | INTEGER |  | No |  |
| document_type | varchar |  | No |  |
| file_path | varchar |  | No |  |
| mime_type | varchar |  | No |  |
| original_name | varchar |  | No |  |
| expiry_date | date |  | Yes |  |
| is_verified | tinyint(1) |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## attendance_settings

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| mode | varchar |  | No | 'checkpoints' |
| responsible_role | varchar |  | No | 'subject_teacher' |
| late_tolerance | INTEGER |  | No | '15' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## attendances

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| class_section_id | INTEGER |  | No |  |
| date | date |  | No |  |
| time_slot_id | INTEGER |  | Yes |  |
| status | varchar |  | No | 'present' |
| remarks | TEXT |  | Yes |  |
| delay_minutes | INTEGER |  | No | '0' |
| recorded_by | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| academic_year_id | INTEGER |  | Yes |  |

## audit_logs

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| auditable_type | varchar |  | No |  |
| auditable_id | INTEGER |  | No |  |
| action | varchar |  | No |  |
| old_values | TEXT |  | Yes |  |
| new_values | TEXT |  | Yes |  |
| user_id | INTEGER |  | Yes |  |
| reason | varchar |  | Yes |  |
| ip_address | varchar |  | Yes |  |
| user_agent | varchar |  | Yes |  |
| created_at | datetime |  | No |  |

## cache

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| key | varchar | PK | No |  |
| value | TEXT |  | No |  |
| expiration | INTEGER |  | No |  |

## cache_locks

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| key | varchar | PK | No |  |
| owner | varchar |  | No |  |
| expiration | INTEGER |  | No |  |

## class_attendances

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| timetable_id | INTEGER |  | No |  |
| student_id | INTEGER |  | No |  |
| date | date |  | No |  |
| status | varchar |  | No |  |
| recorded_by_teacher_id | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## class_sections

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| grade_id | INTEGER |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| max_capacity | INTEGER |  | No | '30' |
| gender_type | varchar |  | No | 'mixed' |
| is_active | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| homeroom_teacher_id | INTEGER |  | Yes |  |

## contract_items

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| contract_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| amount | numeric |  | No |  |
| type | varchar |  | No | 'allowance' |
| is_one_time | tinyint(1) |  | No | '0' |
| consumed_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## contracts

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| staff_id | INTEGER |  | No |  |
| start_date | date |  | No |  |
| end_date | date |  | No |  |
| basic_salary | numeric |  | No |  |
| status | varchar |  | No | 'draft' |
| is_locked | tinyint(1) |  | No | '0' |
| locked_at | datetime |  | Yes |  |
| locked_by | INTEGER |  | Yes |  |
| academic_year_id | INTEGER |  | Yes |  |
| notes | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| bank_name | varchar |  | Yes |  |
| iban | varchar |  | Yes |  |

## control_marks

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| exam_seating_id | INTEGER |  | No |  |
| course_offering_id | INTEGER |  | No |  |
| score | numeric |  | Yes |  |
| is_absent | tinyint(1) |  | No | '0' |
| entered_by | INTEGER |  | Yes |  |
| audited_by | INTEGER |  | Yes |  |
| audited_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## countries

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name_ar | varchar |  | No |  |
| name_en | varchar |  | No |  |
| code | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## course_offerings

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| term_id | INTEGER |  | Yes |  |
| subject_id | INTEGER |  | No |  |
| class_section_id | INTEGER |  | No |  |
| teacher_id | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## daily_attendances

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| date | date |  | No |  |
| term_id | INTEGER |  | No |  |
| status | varchar |  | No |  |
| arrival_time | time |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## discipline_incidents

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| term_id | INTEGER |  | No |  |
| violation_type_id | INTEGER |  | No |  |
| incident_date | datetime |  | No |  |
| decision | TEXT |  | Yes |  |
| status | varchar |  | No | 'pending' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## discounts

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| type | varchar |  | No |  |
| value | numeric |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## educational_stages

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| rank | INTEGER |  | No |  |
| min_passing_percentage | numeric |  | No | '50' |
| grading_system | varchar |  | No | 'standard' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## exam_committees

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| exam_session_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| room | varchar |  | Yes |  |
| capacity | INTEGER |  | No | '30' |
| supervisor_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## exam_seatings

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| exam_session_id | INTEGER |  | No |  |
| student_id | INTEGER |  | No |  |
| committee_id | INTEGER |  | Yes |  |
| seat_number | varchar |  | No |  |
| secret_number | varchar |  | No |  |
| is_barred | tinyint(1) |  | No | '0' |
| barred_reason | varchar |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| is_withheld | tinyint(1) |  | No | '0' |
| withhold_reason | varchar |  | Yes |  |

## exam_sessions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| term_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| start_date | date |  | Yes |  |
| end_date | date |  | Yes |  |
| status | varchar |  | No | 'setup' |
| is_active | tinyint(1) |  | No | '0' |
| notes | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## facilities

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| type | varchar |  | No |  |
| capacity | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## failed_jobs

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| uuid | varchar |  | No |  |
| connection | TEXT |  | No |  |
| queue | TEXT |  | No |  |
| payload | TEXT |  | No |  |
| exception | TEXT |  | No |  |
| failed_at | datetime |  | No | CURRENT_TIMESTAMP |

## fee_structures

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| fee_type_id | INTEGER |  | No |  |
| grade_id | INTEGER |  | Yes |  |
| amount | numeric |  | No |  |
| due_date | date |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## fee_types

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| is_tuition | tinyint(1) |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## final_results

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| exam_session_id | INTEGER |  | No |  |
| student_id | INTEGER |  | No |  |
| course_offering_id | INTEGER |  | No |  |
| coursework_score | numeric |  | No | '0' |
| final_exam_score | numeric |  | No | '0' |
| total_score | numeric |  | No | '0' |
| grace_marks | numeric |  | No | '0' |
| grade_label | varchar |  | Yes |  |
| status | varchar |  | No | 'pending' |
| is_published | tinyint(1) |  | No | '0' |
| published_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## grade_distributions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| subject_id | INTEGER |  | No |  |
| term_id | INTEGER |  | No |  |
| assessment_type_id | INTEGER |  | No |  |
| max_score | numeric |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## grade_subjects

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| grade_id | INTEGER |  | No |  |
| subject_id | INTEGER |  | No |  |
| credit_hours | INTEGER |  | No | '1' |
| max_grade | INTEGER |  | No | '100' |
| pass_grade | INTEGER |  | No | '50' |
| term_type | varchar |  | No | 'full_year' |
| is_active | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## grade_timetable_template

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| grade_id | INTEGER |  | No |  |
| template_id | INTEGER |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## gradebook_months

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| term_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| start_date | date |  | No |  |
| end_date | date |  | No |  |
| order | INTEGER |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| academic_year_id | INTEGER |  | Yes |  |

## gradebook_settings

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| monthly_categories | TEXT |  | Yes |  |
| attendance_deduct_after | INTEGER |  | No | '3' |
| attendance_deduct_per_absence | numeric |  | No | '0.5' |
| attendance_max_score | numeric |  | No | '5' |
| allow_custom_categories | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## grades

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| educational_stage_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| level_order | INTEGER |  | No |  |
| next_grade_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| min_age | INTEGER |  | Yes |  |
| max_age | INTEGER |  | Yes |  |

## grading_templates

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| total_max_score | numeric |  | No | '100' |
| pass_score | numeric |  | No | '50' |
| rounding_rule | varchar |  | No | 'none' |
| rounding_precision | INTEGER |  | No | '0' |
| academic_year_id | INTEGER |  | Yes |  |
| grade_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## guardians

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| user_id | INTEGER |  | Yes |  |
| nationality_id | INTEGER |  | Yes |  |
| national_id | varchar |  | Yes |  |
| first_name | varchar |  | No |  |
| last_name | varchar |  | No |  |
| phone | varchar |  | No |  |
| employer | varchar |  | Yes |  |
| work_phone | varchar |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| preferred_language | varchar |  | No | 'ar' |

## health_condition_types

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| action_plan | TEXT |  | Yes |  |
| criticality | varchar |  | No | 'low' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## homework_submissions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| homework_id | INTEGER |  | No |  |
| student_id | INTEGER |  | No |  |
| status | varchar |  | No | 'pending' |
| submitted_at | datetime |  | Yes |  |
| file_path | varchar |  | Yes |  |
| score | numeric |  | Yes |  |
| feedback | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## homeworks

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| course_offering_id | INTEGER |  | No |  |
| assessment_id | INTEGER |  | Yes |  |
| title | varchar |  | No |  |
| description | TEXT |  | Yes |  |
| submission_type | varchar |  | No |  |
| status | varchar |  | No | 'draft' |
| due_date | datetime |  | Yes |  |
| allow_late | tinyint(1) |  | No | '0' |
| max_score | numeric |  | No | '10' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| attachment_path | varchar |  | Yes |  |

## invoice_items

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| invoice_id | INTEGER |  | No |  |
| fee_type_id | INTEGER |  | No |  |
| amount | numeric |  | No |  |
| discount_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## invoices

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| invoice_number | varchar |  | No |  |
| student_id | INTEGER |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| issue_date | date |  | No |  |
| due_date | date |  | No |  |
| total_amount | numeric |  | No |  |
| paid_amount | numeric |  | No | '0' |
| status | varchar |  | No | 'unpaid' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## job_batches

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | varchar | PK | No |  |
| name | varchar |  | No |  |
| total_jobs | INTEGER |  | No |  |
| pending_jobs | INTEGER |  | No |  |
| failed_jobs | INTEGER |  | No |  |
| failed_job_ids | TEXT |  | No |  |
| options | TEXT |  | Yes |  |
| cancelled_at | INTEGER |  | Yes |  |
| created_at | INTEGER |  | No |  |
| finished_at | INTEGER |  | Yes |  |

## jobs

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| queue | varchar |  | No |  |
| payload | TEXT |  | No |  |
| attempts | INTEGER |  | No |  |
| reserved_at | INTEGER |  | Yes |  |
| available_at | INTEGER |  | No |  |
| created_at | INTEGER |  | No |  |

## leave_requests

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| staff_id | INTEGER |  | No |  |
| leave_type_id | INTEGER |  | No |  |
| start_date | date |  | No |  |
| end_date | date |  | No |  |
| days_count | INTEGER |  | No |  |
| reason | TEXT |  | No |  |
| attachment | varchar |  | Yes |  |
| status | varchar |  | No | 'pending' |
| approved_by | INTEGER |  | Yes |  |
| rejection_reason | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## leave_types

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| days_per_year | INTEGER |  | No | '0' |
| requires_proof | tinyint(1) |  | No | '0' |
| is_paid | tinyint(1) |  | No | '1' |
| is_active | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| excludes_holidays | tinyint(1) |  | No | '0' |

## loan_installments

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| loan_id | INTEGER |  | No |  |
| amount | numeric |  | No |  |
| due_date | date |  | No |  |
| status | varchar |  | No | 'pending' |
| payroll_batch_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## loans

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| staff_id | INTEGER |  | No |  |
| amount | numeric |  | No |  |
| paid_amount | numeric |  | No | '0' |
| installments_count | INTEGER |  | No |  |
| monthly_installment | numeric |  | No |  |
| reason | varchar |  | Yes |  |
| status | varchar |  | No | 'pending' |
| start_date | date |  | No |  |
| approved_by | INTEGER |  | Yes |  |
| approved_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| deleted_at | datetime |  | Yes |  |

## model_has_permissions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| permission_id | INTEGER | PK | No |  |
| model_type | varchar | PK | No |  |
| model_id | INTEGER | PK | No |  |

## model_has_roles

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| role_id | INTEGER | PK | No |  |
| model_type | varchar | PK | No |  |
| model_id | INTEGER | PK | No |  |

## monthly_grades

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| course_offering_id | INTEGER |  | No |  |
| gradebook_month_id | INTEGER |  | No |  |
| category | varchar |  | No |  |
| score | numeric |  | Yes |  |
| max_score | numeric |  | No | '10' |
| notes | TEXT |  | Yes |  |
| graded_by | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## notifications

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | varchar | PK | No |  |
| type | varchar |  | No |  |
| notifiable_type | varchar |  | No |  |
| notifiable_id | INTEGER |  | No |  |
| data | TEXT |  | No |  |
| read_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## password_reset_tokens

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| email | varchar | PK | No |  |
| token | varchar |  | No |  |
| created_at | datetime |  | Yes |  |

## payroll_batches

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| period_start | date |  | No |  |
| period_end | date |  | No |  |
| year | INTEGER |  | No |  |
| month | INTEGER |  | No |  |
| status | varchar |  | No | 'draft' |
| frozen_at | datetime |  | Yes |  |
| approved_at | datetime |  | Yes |  |
| paid_at | datetime |  | Yes |  |
| generated_by | INTEGER |  | Yes |  |
| frozen_by | INTEGER |  | Yes |  |
| approved_by | INTEGER |  | Yes |  |
| total_gross | numeric |  | No | '0' |
| total_deductions | numeric |  | No | '0' |
| total_net | numeric |  | No | '0' |
| employees_count | INTEGER |  | No | '0' |
| notes | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## payroll_items

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| payroll_record_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| amount | numeric |  | No |  |
| type | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| category | varchar |  | Yes |  |
| description | varchar |  | Yes |  |
| is_manual_override | tinyint(1) |  | No | '0' |
| original_amount | numeric |  | Yes |  |
| source_type | varchar |  | Yes |  |
| source_id | INTEGER |  | Yes |  |

## payroll_policies

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| is_default | tinyint(1) |  | No | '0' |
| month_days_type | varchar |  | No | '30_fixed' |
| substitution_rate | numeric |  | No | '75' |
| lateness_brackets | TEXT |  | Yes |  |
| absence_deduction_factor | numeric |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## payroll_records

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| payroll_batch_id | INTEGER |  | No |  |
| staff_id | INTEGER |  | No |  |
| contract_id | INTEGER |  | No |  |
| basic_salary | numeric |  | No |  |
| working_days | INTEGER |  | No | '0' |
| days_worked | INTEGER |  | No | '0' |
| days_absent | INTEGER |  | No | '0' |
| days_late | INTEGER |  | No | '0' |
| gross_earnings | numeric |  | No | '0' |
| total_deductions | numeric |  | No | '0' |
| net_payable | numeric |  | No | '0' |
| manual_adjustment | numeric |  | No | '0' |
| adjustment_reason | TEXT |  | Yes |  |
| adjusted_by | INTEGER |  | Yes |  |
| notes | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## payrolls

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| staff_id | INTEGER |  | No |  |
| year | INTEGER |  | No |  |
| month | INTEGER |  | No |  |
| net_salary | numeric |  | No |  |
| status | varchar |  | No | 'generated' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## permissions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| guard_name | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## promotions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| annual_result_id | INTEGER |  | Yes |  |
| from_grade_id | INTEGER |  | No |  |
| to_grade_id | INTEGER |  | Yes |  |
| to_class_section_id | INTEGER |  | Yes |  |
| type | varchar |  | No |  |
| has_financial_clearance | tinyint(1) |  | No | '0' |
| certificate_blocked | tinyint(1) |  | No | '0' |
| is_reverted | tinyint(1) |  | No | '0' |
| reverted_by | INTEGER |  | Yes |  |
| reverted_at | datetime |  | Yes |  |
| revert_reason | TEXT |  | Yes |  |
| notes | TEXT |  | Yes |  |
| processed_by | INTEGER |  | No |  |
| processed_at | datetime |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## role_has_permissions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| permission_id | INTEGER | PK | No |  |
| role_id | INTEGER | PK | No |  |

## roles

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| guard_name | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## salary_components

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| type | varchar |  | No |  |
| is_percentage | tinyint(1) |  | No | '0' |
| percentage_value | numeric |  | Yes |  |
| fixed_value | numeric |  | Yes |  |
| is_active | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## school_events

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| title | varchar |  | No |  |
| description | TEXT |  | Yes |  |
| start_date | date |  | No |  |
| end_date | date |  | No |  |
| type | varchar |  | No | 'holiday' |
| is_holiday | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## school_management_system_tables

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## sessions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | varchar | PK | No |  |
| user_id | INTEGER |  | Yes |  |
| ip_address | varchar |  | Yes |  |
| user_agent | TEXT |  | Yes |  |
| payload | TEXT |  | No |  |
| last_activity | INTEGER |  | No |  |

## staff

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| user_id | INTEGER |  | Yes |  |
| employee_number | varchar |  | No |  |
| first_name | varchar |  | No |  |
| last_name | varchar |  | No |  |
| phone | varchar |  | No |  |
| joining_date | date |  | No |  |
| basic_salary | numeric |  | No | '0' |
| status | varchar |  | No | 'active' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| work_shift_id | INTEGER |  | Yes |  |
| employment_type | varchar |  | No | 'full_time' |
| job_title | varchar |  | Yes |  |

## staff_attendance

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| staff_id | INTEGER |  | No |  |
| date | date |  | No |  |
| check_in | time |  | Yes |  |
| check_out | time |  | Yes |  |
| status | varchar |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| delay_minutes | INTEGER |  | No | '0' |
| early_leave_minutes | INTEGER |  | No | '0' |
| source | varchar |  | No | 'manual' |
| recorded_by | INTEGER |  | Yes |  |
| remarks | TEXT |  | Yes |  |

## staff_leave_balances

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| staff_id | INTEGER |  | No |  |
| leave_type_id | INTEGER |  | No |  |
| year | INTEGER |  | No |  |
| remaining_days | numeric |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## student_enrollments

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| academic_year_id | INTEGER |  | No |  |
| grade_id | INTEGER |  | No |  |
| class_section_id | INTEGER |  | Yes |  |
| enrollment_date | date |  | No |  |
| drop_date | date |  | Yes |  |
| enrollment_type | varchar |  | No |  |
| status | varchar |  | No | 'active' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## student_grades

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| assessment_id | INTEGER |  | No |  |
| score | numeric |  | Yes |  |
| graded_by_user_id | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## student_guardian

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| guardian_id | INTEGER |  | No |  |
| relationship | varchar |  | No |  |
| is_emergency_contact | tinyint(1) |  | No | '0' |
| is_financial_sponsor | tinyint(1) |  | No | '0' |
| lives_with | tinyint(1) |  | No | '1' |
| has_portal_access | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## student_health_conditions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| health_condition_type_id | INTEGER |  | No |  |
| notes | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## student_marks

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| assessment_id | INTEGER |  | Yes |  |
| template_category_id | INTEGER |  | Yes |  |
| raw_score | numeric |  | Yes |  |
| scaled_score | numeric |  | Yes |  |
| is_missing | tinyint(1) |  | No | '0' |
| is_excused | tinyint(1) |  | No | '0' |
| feedback | TEXT |  | Yes |  |
| graded_by_user_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| course_offering_id | INTEGER |  | Yes |  |
| academic_year_id | INTEGER |  | Yes |  |
| term_id | INTEGER |  | Yes |  |

## student_previous_histories

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| school_name | varchar |  | No |  |
| previous_curriculum | varchar |  | Yes |  |
| last_grade_completed | varchar |  | No |  |
| completion_year | INTEGER |  | No |  |
| last_gpa | float |  | Yes |  |
| reason_for_transfer | TEXT |  | Yes |  |
| conduct_summary | varchar |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## students

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| user_id | INTEGER |  | Yes |  |
| admission_application_id | INTEGER |  | Yes |  |
| admission_number | varchar |  | No |  |
| first_name_ar | varchar |  | No |  |
| family_name_ar | varchar |  | No |  |
| first_name_en | varchar |  | Yes |  |
| family_name_en | varchar |  | Yes |  |
| date_of_birth | date |  | No |  |
| gender | varchar |  | No |  |
| nationality_id | INTEGER |  | Yes |  |
| national_id | varchar |  | Yes |  |
| passport_number | varchar |  | Yes |  |
| blood_type | varchar |  | Yes |  |
| current_grade_id | INTEGER |  | Yes |  |
| current_class_section_id | INTEGER |  | Yes |  |
| status | varchar |  | No | 'active' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| profile_photo_path | varchar |  | Yes |  |

## subject_grading_configs

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| subject_id | INTEGER |  | No |  |
| grade_id | INTEGER |  | No |  |
| term_id | INTEGER |  | No |  |
| grading_template_id | INTEGER |  | No |  |
| max_score | numeric |  | No | '100' |
| pass_score | numeric |  | No | '50' |
| is_continuous | tinyint(1) |  | No | '1' |
| counts_in_gpa | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## subjects

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| code | varchar |  | Yes |  |
| type | varchar |  | No | 'theory' |
| description | TEXT |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## substitution_classes

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| timetable_id | INTEGER |  | No |  |
| date | date |  | No |  |
| substitute_teacher_id | INTEGER |  | No |  |
| status | varchar |  | No | 'assigned' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## substitutions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| date | date |  | No |  |
| timetable_id | INTEGER |  | No |  |
| original_teacher_id | INTEGER |  | No |  |
| substitute_teacher_id | INTEGER |  | No |  |
| leave_request_id | INTEGER |  | Yes |  |
| status | varchar |  | No | 'pending' |
| is_paid | tinyint(1) |  | No | '0' |
| notification_sent_at | datetime |  | Yes |  |
| acceptance_status | varchar |  | No | 'pending' |
| rejection_reason | TEXT |  | Yes |  |
| notes | TEXT |  | Yes |  |
| created_by | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## system_settings

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| key | varchar |  | No |  |
| value | TEXT |  | Yes |  |
| group | varchar |  | No | 'general' |
| type | varchar |  | No | 'string' |
| description | varchar |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## teachers

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| user_id | INTEGER |  | Yes |  |
| staff_id | INTEGER |  | No |  |
| specialization | varchar |  | Yes |  |
| max_weekly_classes | INTEGER |  | No | '24' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## template_categories

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| grading_template_id | INTEGER |  | No |  |
| parent_id | INTEGER |  | Yes |  |
| name | varchar |  | No |  |
| weight | numeric |  | No |  |
| max_raw_score | numeric |  | Yes |  |
| calculation_type | varchar |  | No | 'sum' |
| is_dynamic_weight | tinyint(1) |  | No | '0' |
| is_locked | tinyint(1) |  | No | '0' |
| pass_required | tinyint(1) |  | No | '0' |
| pass_threshold | numeric |  | Yes |  |
| order | INTEGER |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| mapping_type | varchar |  | No | 'manual' |
| is_readonly | tinyint(1) |  | No | '0' |
| is_final_exam | tinyint(1) |  | No | '0' |

## term_result_failures

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| term_result_id | INTEGER |  | No |  |
| template_category_id | INTEGER |  | No |  |
| reason | varchar |  | No |  |
| required_min | numeric |  | No |  |
| actual_percentage | numeric |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## term_results

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| course_offering_id | INTEGER |  | No |  |
| term_id | INTEGER |  | No |  |
| coursework_score | numeric |  | No | '0' |
| exam_score | numeric |  | No | '0' |
| total_score | numeric |  | No | '0' |
| max_score | numeric |  | No | '100' |
| percentage | numeric |  | No | '0' |
| grade_letter | varchar |  | Yes |  |
| is_passed | tinyint(1) |  | No | '0' |
| calculated_at | datetime |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## terms

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| academic_year_id | INTEGER |  | No |  |
| name | varchar |  | No |  |
| start_date | date |  | Yes |  |
| end_date | date |  | Yes |  |
| status | varchar |  | No | 'pending' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| order_index | INTEGER |  | No | '1' |

## time_slots

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| template_id | INTEGER |  | No |  |
| label | varchar |  | No |  |
| order_index | INTEGER |  | No |  |
| start_time | time |  | No |  |
| end_time | time |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| day_of_week | INTEGER |  | No | '0' |
| type | varchar |  | No | 'academic' |
| is_attendance_checkpoint | tinyint(1) |  | No | '0' |

## timetable_templates

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| description | TEXT |  | Yes |  |
| is_default | tinyint(1) |  | No | '0' |
| academic_year_id | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| working_days | TEXT |  | No | '["sun","mon","tue","wed","thu"]' |
| status | varchar |  | No | 'draft' |
| educational_stage_id | INTEGER |  | Yes |  |

## timetables

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| class_section_id | INTEGER |  | No |  |
| time_slot_id | INTEGER |  | No |  |
| course_offering_id | INTEGER |  | Yes |  |
| facility_id | INTEGER |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## transport_routes

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| fees | numeric |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## transport_subscriptions

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| student_id | INTEGER |  | No |  |
| vehicle_id | INTEGER |  | No |  |
| transport_route_id | INTEGER |  | No |  |
| term_id | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## users

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| username | varchar |  | No |  |
| email | varchar |  | Yes |  |
| phone | varchar |  | Yes |  |
| password | varchar |  | No |  |
| is_active | tinyint(1) |  | No | '1' |
| last_login_at | datetime |  | Yes |  |
| remember_token | varchar |  | Yes |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |
| email_verified_at | datetime |  | Yes |  |
| profile_photo_path | varchar |  | Yes |  |

## vehicles

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| plate_number | varchar |  | No |  |
| bus_number | varchar |  | No |  |
| capacity | INTEGER |  | No |  |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## violation_types

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| severity_level | INTEGER |  | No | '1' |
| points_deduction | INTEGER |  | No | '0' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

## work_shifts

| Name | Type | Key | Nullable | Default |
|---|---|---|---|---|
| id | INTEGER | PK | No |  |
| name | varchar |  | No |  |
| season | varchar |  | No | 'all' |
| start_time | time |  | No |  |
| end_time | time |  | No |  |
| grace_period_minutes | INTEGER |  | No | '15' |
| working_days | TEXT |  | No |  |
| works_on_holidays | tinyint(1) |  | No | '0' |
| is_active | tinyint(1) |  | No | '1' |
| created_at | datetime |  | Yes |  |
| updated_at | datetime |  | Yes |  |

