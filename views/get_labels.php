<?php
// get_labels.php
header('Content-Type: application/json');

// Directory containing face folders
$uploadDir = __DIR__ . "/labels";

// If uploads folder doesn't exist
if (!is_dir($uploadDir)) {
    echo json_encode([]);
    exit;
}

// Get folder names (each employee folder = label)
$folders = array_filter(glob($uploadDir . '/*'), 'is_dir');

// Extract folder names only (without full path)
$labels = array_map('basename', $folders);

// Send as JSON
echo json_encode($labels);
