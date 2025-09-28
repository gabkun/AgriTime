<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image = $_POST['image'];
    $folder = $_POST['folder'];
    $index = $_POST['index'];

    $uploadDir = "uploads/" . $folder;

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $image = str_replace('data:image/jpeg;base64,', '', $image);
    $image = str_replace(' ', '+', $image);
    $data = base64_decode($image);
    $file = $uploadDir . "/" . ($index + 1) . ".jpg";

    if (file_put_contents($file, $data)) {
        echo "OK";
    } else {
        echo "ERROR";
    }
}
?>
