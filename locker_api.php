<?php
include "./core/db.php";
include "./functions.php"; // This is where we will move the functions

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfid'])) {
    $rfid = $_POST['rfid'] ?? null;
    $loker_id = $_POST['loker'] ?? null;

    if (!empty($rfid) && !empty($loker_id)) {
        $response = processLockerAccess($rfid, $loker_id, $konek);
        echo json_encode($response);
    } else {
        echo json_encode(["error" => "RFID atau Loker ID tidak boleh kosong."]);
    }
} else {
    echo json_encode(["error" => "Invalid request."]);
}
