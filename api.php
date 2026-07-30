<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$dataFile = __DIR__ . '/data/submissions.json';

// Ensure data directory exists
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0755, true);
    // Write .htaccess to block direct browser access to the files inside
    file_put_contents(__DIR__ . '/data/.htaccess', "Deny from all");
}

// Ensure submissions file exists
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([]));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'OPTIONS') {
    exit(0);
}

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    if (!$input) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
        exit;
    }

    $action = isset($input['action']) ? $input['action'] : 'save';

    if ($action === 'save') {
        $submission = isset($input['submission']) ? $input['submission'] : null;
        if (!$submission || !isset($submission['id'])) {
            echo json_encode(["status" => "error", "message" => "Missing submission data"]);
            exit;
        }

        $currentData = json_decode(file_get_contents($dataFile), true);
        if (!is_array($currentData)) {
            $currentData = [];
        }

        // Add to beginning of array
        array_unshift($currentData, $submission);
        file_put_contents($dataFile, json_encode($currentData, JSON_PRETTY_PRINT));

        echo json_encode(["status" => "success", "message" => "Submission saved"]);
        exit;
    } 
    
    if ($action === 'delete') {
        $id = isset($input['id']) ? $input['id'] : null;
        $password = isset($input['password']) ? $input['password'] : '';

        // Auth check for deletion
        if ($password !== 'gosyen123') {
            echo json_encode(["status" => "error", "message" => "Unauthorized"]);
            exit;
        }

        if (!$id) {
            echo json_encode(["status" => "error", "message" => "Missing ID"]);
            exit;
        }

        $currentData = json_decode(file_get_contents($dataFile), true);
        if (!is_array($currentData)) {
            $currentData = [];
        }

        $filteredData = array_filter($currentData, function($item) use ($id) {
            return $item['id'] !== $id;
        });

        file_put_contents($dataFile, json_encode(array_values($filteredData), JSON_PRETTY_PRINT));

        echo json_encode(["status" => "success", "message" => "Submission deleted"]);
        exit;
    }
}

if ($method === 'GET') {
    $password = isset($_GET['password']) ? $_GET['password'] : '';
    if ($password !== 'gosyen123') {
        echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
        exit;
    }

    $currentData = json_decode(file_get_contents($dataFile), true);
    if (!is_array($currentData)) {
        $currentData = [];
    }

    echo json_encode($currentData);
    exit;
}
