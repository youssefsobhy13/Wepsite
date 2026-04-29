<?php
// save_data.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$dataFile = "results.json";

// التأكد من وجود المجلد أو إنشاؤه (نفس المجلد)
$dir = dirname($dataFile);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = file_get_contents('php://input');
    $newData = json_decode($input, true);
    
    if (!$newData || !isset($newData['name'], $newData['id'], $newData['score'], $newData['total'])) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid data"]);
        exit;
    }
    
    // إضافة حقل النسبة المئوية
    $newData['percentage'] = round(($newData['score'] / $newData['total']) * 100, 2);
    if (!isset($newData['timestamp'])) {
        $newData['timestamp'] = date('Y-m-d H:i:s');
    }
    
    // قراءة الملف الحالي إذا وجد
    $existing = [];
    if (file_exists($dataFile)) {
        $content = file_get_contents($dataFile);
        if (!empty($content)) {
            $existing = json_decode($content, true);
            if (!is_array($existing)) $existing = [];
        }
    }
    
    // إضافة السجل الجديد
    $existing[] = $newData;
    
    // حفظ الملف (إنشاءه إذا لم يكن موجوداً)
    $jsonString = json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (file_put_contents($dataFile, $jsonString, LOCK_EX)) {
        echo json_encode(["status" => "success", "message" => "Data saved"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Could not write file"]);
    }
} else {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
}
?>