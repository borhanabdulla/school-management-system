#!/bin/bash

echo "Running Quality Gates..."
FAILURES=0

# 1. Check for Student:: usage in UI layers (Livewire & Controllers)
echo "Checking for Student:: usage in UI layers..."
if grep -R "Student::" app/Livewire app/Http/Controllers; then
    echo "❌ VIOLATION: Direct Student:: usage found in UI layers!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ UI layers are clean of direct Student model usage."
fi

# 2. Check for StudentLookupService::clearCache usage in UI layers
echo "Checking for StudentLookupService::clearCache usage in UI layers..."
if grep -R "StudentLookupService::clearCache" app/Livewire app/Http/Controllers; then
    echo "❌ VIOLATION: StudentLookupService::clearCache called directly in UI layers!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ UI layers are clean of direct cache clearing."
fi

# 3. Check for hardcoded status strings in Academic domains
# We exclude Enums and Scopes from this check
echo "Checking for hardcoded status strings in Academic domains..."

# Check for 'active'
if grep -R "where('status', 'active')" app/Domains/Academic | grep -v "StudentScopes.php"; then
    echo "❌ VIOLATION: Hardcoded 'active' status found!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ No hardcoded 'active' status found."
fi

# Check for 'completed'
if grep -R "where('status', 'completed')" app/Domains/Academic; then
    echo "❌ VIOLATION: Hardcoded 'completed' status found!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ No hardcoded 'completed' status found."
fi

# Check for 'absent'
if grep -R "where('status', 'absent')" app/Domains/Academic; then
    echo "❌ VIOLATION: Hardcoded 'absent' status found!"
    FAILURES=$((FAILURES+1))
else
    echo "✅ No hardcoded 'absent' status found."
fi

if [ $FAILURES -gt 0 ]; then
    echo "⚠️  $FAILURES Quality Gate violations found!"
    exit 1
else
    echo "🎉 All Quality Gates passed!"
    exit 0
fi
