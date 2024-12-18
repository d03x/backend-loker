<?php
include "./core/db.php";
include "./functions.php"; // This is where we will move the functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rfid = $_POST['rfid'] ?? null;
    $loker_id = $_POST['loker'] ?? null;

    if (!empty($rfid) && !empty($loker_id)) {
        $response = processLockerAccess($rfid, $loker_id, $konek);
        echo json_encode($response);
    } else {
        echo json_encode(["error" => "RFID atau Loker ID tidak boleh kosong."]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['loker_id'])) {
        $loker_id = $_GET['loker_id'];
        $lockerData = getLockerData($loker_id, $konek);
        echo json_encode($lockerData);
    } else {
        echo json_encode(["error" => "Loker ID tidak boleh kosong."]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    if (isset($_GET['loker_id'])) {
        $loker_id = $_GET['loker_id'];
        clearLocker($loker_id, $konek);
        echo json_encode(["message" => "Locker cleared successfully."]);
    } else {
        echo json_encode(["error" => "Loker ID tidak boleh kosong."]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    parse_str(file_get_contents("php://input"), $_PUT);
    $rfid = $_PUT['rfid'] ?? null;
    $loker_id = $_PUT['loker'] ?? null;

    if (!empty($rfid) && !empty($loker_id)) {
        updateLockerStatus($rfid, $loker_id, $konek);
        echo json_encode(["message" => "Locker status updated successfully."]);
    } else {
        echo json_encode(["error" => "RFID atau Loker ID tidak boleh kosong."]);
    }
} else {
    echo json_encode(["error" => "Invalid request."]);
}
