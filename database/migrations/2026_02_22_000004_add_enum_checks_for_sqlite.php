<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $this->normalizePayrollItemTypes();
        $this->applyChecks($this->checks(), add: true);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        $this->applyChecks($this->checks(), add: false);
    }

    private function checks(): array
    {
        return [
            'students' => [
                'gender' => ['male', 'female'],
                'status' => ['active', 'inactive', 'graduated', 'transferred', 'suspended'],
            ],
            'staff' => [
                'status' => ['active', 'terminated', 'resigned', 'on_leave'],
            ],
            'invoices' => [
                'status' => ['unpaid', 'partially_paid', 'paid', 'cancelled', 'overdue'],
            ],
            'class_sections' => [
                'gender_type' => ['boys', 'girls', 'mixed'],
            ],
            'attendances' => [
                'status' => ['present', 'absent', 'late', 'excused', 'pending', 'escaped'],
            ],
            'ledger_entries' => [
                'direction' => ['in', 'out'],
                'category' => ['student_payment', 'payroll_payout', 'expense', 'adjustment'],
                'status' => ['posted', 'cancelled'],
            ],
            'payroll_batches' => [
                'status' => ['draft', 'frozen', 'approved', 'paid'],
            ],
            'payments' => [
                'method' => ['cash', 'manual_transfer'],
            ],
            'academic_years' => [
                'financial_status' => ['open', 'closed'],
                'status' => ['pending', 'active', 'closed', 'archived'],
            ],
            'grading_templates' => [
                'rounding_rule' => ['none', 'nearest_integer', 'up', 'down', 'half_up'],
            ],
            'payroll_items' => [
                'type' => ['earning', 'deduction'],
            ],
            'homework_submissions' => [
                'status' => ['pending', 'submitted', 'late', 'graded'],
            ],
            'homeworks' => [
                'submission_type' => ['online', 'offline'],
                'status' => ['draft', 'published', 'archived'],
            ],
            'hr_amendments' => [
                'kind' => ['attendance', 'leave'],
                'status' => ['pending'],
            ],
            'loans' => [
                'status' => ['pending', 'approved', 'rejected', 'paid', 'cancelled'],
            ],
            'staff_attendance' => [
                'status' => ['present', 'absent', 'late', 'excused'],
            ],
            'terms' => [
                'status' => ['pending', 'active', 'completed'],
            ],
        ];
    }

    private function normalizePayrollItemTypes(): void
    {
        DB::table('payroll_items')
            ->where('type', 'allowance')
            ->update(['type' => 'earning']);
    }

    private function applyChecks(array $checks, bool $add): void
    {
        $fkState = DB::selectOne('PRAGMA foreign_keys')?->foreign_keys;
        DB::statement('PRAGMA foreign_keys=OFF');

        try {
            foreach ($checks as $table => $columns) {
                $this->rebuildTableWithChecks($table, $columns, $add);
            }
        } finally {
            if ($fkState !== null) {
                DB::statement('PRAGMA foreign_keys=' . (int) $fkState);
            } else {
                DB::statement('PRAGMA foreign_keys=ON');
            }
        }
    }

    private function rebuildTableWithChecks(string $table, array $columns, bool $add): void
    {
        $ddlRow = DB::selectOne(
            'SELECT sql FROM sqlite_master WHERE type = "table" AND name = ?',
            [$table]
        );
        $ddl = $ddlRow?->sql;
        if (!$ddl) {
            return;
        }

        if ($add) {
            foreach ($columns as $column => $values) {
                $this->assertAllowedValues($table, $column, $values);
            }
        }

        foreach ($columns as $column => $values) {
            $ddl = $add
                ? $this->addCheck($ddl, $table, $column, $values)
                : $this->removeCheck($ddl, $table, $column);
        }

        $tempTable = $table . '__tmp_checks';
        $createSql = preg_replace(
            '/^CREATE TABLE \"' . preg_quote($table, '/') . '\"/i',
            'CREATE TABLE "' . $tempTable . '"',
            $ddl,
            1
        );

        $indexRows = DB::select(
            'SELECT sql FROM sqlite_master WHERE type = "index" AND tbl_name = ? AND sql IS NOT NULL',
            [$table]
        );
        $indexSql = array_map(fn($row) => $row->sql, $indexRows);

        DB::statement($createSql);
        DB::statement('INSERT INTO "' . $tempTable . '" SELECT * FROM "' . $table . '"');
        DB::statement('DROP TABLE "' . $table . '"');
        DB::statement('ALTER TABLE "' . $tempTable . '" RENAME TO "' . $table . '"');

        foreach ($indexSql as $sql) {
            DB::statement($sql);
        }
    }

    private function assertAllowedValues(string $table, string $column, array $values): void
    {
        if (!$values) {
            return;
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $sql = 'SELECT DISTINCT "' . $column . '" AS value FROM "' . $table . '"'
            . ' WHERE "' . $column . '" IS NOT NULL'
            . ' AND "' . $column . '" NOT IN (' . $placeholders . ')';

        $rows = DB::select($sql, $values);
        if ($rows) {
            $invalid = implode(', ', array_map(fn($row) => (string) $row->value, $rows));
            throw new RuntimeException("Invalid values for {$table}.{$column}: {$invalid}");
        }
    }

    private function addCheck(string $ddl, string $table, string $column, array $values): string
    {
        $existingPattern = '/"' . preg_quote($column, '/') . '"\\s+varchar\\s+check\\s*\\(/i';
        if (preg_match($existingPattern, $ddl)) {
            return $ddl;
        }

        $allowed = implode(', ', array_map(function ($value) {
            $escaped = str_replace("'", "''", $value);
            return "'" . $escaped . "'";
        }, $values));

        $replacePattern = '/"' . preg_quote($column, '/') . '"\\s+varchar(?!\\s+check)/i';
        $replacement = '"' . $column . '" varchar check ("' . $column . '" in (' . $allowed . '))';
        $newDdl = preg_replace($replacePattern, $replacement, $ddl, 1, $count);

        if ($count !== 1) {
            throw new RuntimeException("Column {$table}.{$column} not found in DDL");
        }

        return $newDdl;
    }

    private function removeCheck(string $ddl, string $table, string $column): string
    {
        $pattern = '/"' . preg_quote($column, '/') . '"\\s+varchar\\s+check\\s*\\([^\\)]*\\)/i';
        $replacement = '"' . $column . '" varchar';
        $newDdl = preg_replace($pattern, $replacement, $ddl, 1, $count);

        if ($count !== 1) {
            throw new RuntimeException("Column {$table}.{$column} check not found in DDL");
        }

        return $newDdl;
    }
};
