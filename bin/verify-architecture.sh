#!/bin/bash

echo "Running Architectural Guardrails..."
FAILURES=0

# 1. Check for Student:: usage in Promotion Domain
echo "Checking for Student:: usage in Promotion Domain..."
if grep -R "Student::" app/Domains/Academic/Promotion | grep -v "Models/Promotion.php"; then
    echo "❌ VIOLATION: Direct Student:: usage found in Promotion Domain!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ Promotion Domain is clean."
fi

# 2. Check for hardcoded 'active' status in Promotion Domain
echo "Checking for hardcoded 'active' status in Promotion Domain..."
if grep -R "where('status', 'active')" app/Domains/Academic/Promotion; then
    echo "❌ VIOLATION: Hardcoded 'active' status found in Promotion Domain!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ No hardcoded status found."
fi

# 3. Check for whereHas('enrollments') usage outside StudentLookupService
# We exclude StudentLookupService.php and Student.php (if needed)
echo "Checking for whereHas('enrollments') usage..."
# Note: We use a broader grep to catch variations, but exclude the valid service
if grep -R "whereHas('enrollments'" app/Domains | grep -v "StudentLookupService.php"; then
    echo "❌ VIOLATION: whereHas('enrollments') found outside StudentLookupService!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ Enrollment queries are centralized."
fi

if [ $FAILURES -gt 0 ]; then
    echo "⚠️  $FAILURES Architectural violations found!"
    exit 1
else
    echo "🎉 All checks passed!"
    exit 0
fi
