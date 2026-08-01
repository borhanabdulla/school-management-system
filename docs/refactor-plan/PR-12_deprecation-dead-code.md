# PR-12 — Deprecation & Dead Code Hygiene

Status: Planned
Priority: Low
Depends On: PR--0, PR--1, PR-10

Scope (Exact Files)
- docs/enums-violations/README.md
- docs/enums-violations/finance.md
- docs/enums-violations/hr.md
- docs/enums-violations/academic.md
- docs/deprecation/index.md

Evidence
- docs/enums-violations/README.md:5
- docs/enums-violations/README.md:8

Impact
- تراكم ملفات/مراجع غير محدثة يخلق ضوضاء ويصعّب المراجعة الطويلة.

Tasks
- [ ] Evidence Revalidation: تحديث الأدلة والملفات المرتبطة (docs/refactor-plan/EVIDENCE_REVALIDATION.md).
- [ ] Audit: حصر الملفات/Enums/Services/Docs غير المستخدمة أو غير المشار لها.
- [ ] تصنيف: Keep / Deprecate / Remove.
- [ ] وضع بروتوكول Deprecation (توثيق + مدة قبل الإزالة).
- [ ] إزالة آمنة بعد التحقق من عدم وجود مراجع.

Tests
- [ ] لا اختبارات جديدة مطلوبة إلا إذا أزيل منطق فعلي.

Risks
- إزالة شيء مستخدم فعلياً إذا كان البحث ناقصاً.

Rollback
- إعادة الملفات المحذوفة من git عند الحاجة.

Definition of Done
- قائمة Deprecation موثقة، ولا توجد إشارات إلى ملفات غير موجودة.
