<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار توزيع المواد</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">اختبار صفحة توزيع المواد الدراسية</h1>
        
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">الشعب المتاحة للاختبار:</h2>
            
            <?php
            // Connect to database
            $db = new PDO('sqlite:' . __DIR__ . '/../database/database.sqlite');
            
            // Get sections with grades
            $sections = $db->query("
                SELECT cs.id, cs.name as section_name, g.name as grade_name
                FROM class_sections cs
                JOIN grades g ON cs.grade_id = g.id
                ORDER BY g.level_order, cs.name
                LIMIT 10
            ")->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($sections as $section): ?>
                    <a href="/course-assignment/<?= $section['id'] ?>" 
                       class="block p-4 border border-gray-200 rounded-lg hover:bg-indigo-50 hover:border-indigo-500 transition">
                        <div class="font-semibold text-gray-800">
                            <?= htmlspecialchars($section['section_name']) ?>
                        </div>
                        <div class="text-sm text-gray-600">
                            <?= htmlspecialchars($section['grade_name']) ?>
                        </div>
                        <div class="text-xs text-indigo-600 mt-2">
                            اضغط لتوزيع المواد →
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-800 mb-2">ملاحظات:</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>✓ تم تحديث قاعدة البيانات بنجاح</li>
                <li>✓ تم إنشاء جميع المكونات المطلوبة</li>
                <li>✓ الصفحة متاحة على: <code class="bg-white px-2 py-1 rounded">/course-assignment/{sectionId}</code></li>
            </ul>
        </div>
    </div>
</body>
</html>
