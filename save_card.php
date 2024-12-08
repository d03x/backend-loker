<?php
include "./core/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rfid'])) {
    $rfid = $_POST['rfid'] ?? null;
    $loker_id = $_POST['loker'] ?? null;

    if (!empty($rfid) && !empty($loker_id)) {
        processLockerAccess($rfid, $loker_id, $konek);
    } else {
        die("RFID atau Loker ID tidak boleh kosong.");
    }
}

function processLockerAccess($rfid, $loker_id, $db)
{
    $lokerData = getLockerData($loker_id, $db);

    if ($lokerData) {
        if (isLockerInUseByAnotherUser($lokerData, $rfid)) {
            die("Loker dipakai oleh: " . $lokerData->rf_uid);
        }

        if (isSameUserClosingLocker($lokerData, $rfid)) {
            updateLockerStatus($rfid, $loker_id, $db);
            addHistory($rfid, $loker_id, $db, 'open');
        } else {
            clearLocker($loker_id, $db);
            assignLocker($rfid, $loker_id, $db);
            addHistory($rfid, $loker_id, $db, 'close');
        }
    } else {
        assignLocker($rfid, $loker_id, $db);
        addHistory($rfid, $loker_id, $db, 'open');
    }
}

function getLockerData($loker_id, $db)
{
    $query = $db->query("SELECT * FROM lokers_access WHERE locker_number='$loker_id'");
    return $query->fetch_object();
}

function isLockerInUseByAnotherUser($lokerData, $rfid)
{
    return $lokerData->rf_uid !== $rfid && $lokerData->tap_ke != 2;
}

function isSameUserClosingLocker($lokerData, $rfid)
{
    return $lokerData->rf_uid === $rfid && $lokerData->tap_ke == 1;
}

function updateLockerStatus($rfid, $loker_id, $db)
{
    $update = $db->query("UPDATE lokers_access SET tap_ke = 2, locker_number = '$loker_id' WHERE rf_uid = '$rfid'");
    if ($update) {
        die($rfid);
    } else {
        die("Gagal memperbarui status loker.");
    }
}

function clearLocker($loker_id, $db)
{
    $db->query("DELETE FROM lokers_access WHERE locker_number='$loker_id'");
}

function assignLocker($rfid, $loker_id, $db)
{
    $insert = $db->query("INSERT INTO lokers_access (rf_uid, tap_ke, locker_number) VALUES ('$rfid', 1, '$loker_id')");
    if ($insert) {
        die($rfid);
    } else {
        die("Gagal menambahkan data loker.");
    }
}

function addHistory($rfid, $loker_id, $db, $action)
{
    $timestamp = date('Y-m-d H:i:s');
    if ($action === 'open') {
        $query = "INSERT INTO history (rf_id, open_locker_at, locker_number) VALUES ('$rfid', '$timestamp', '$loker_id')";
        if (!$db->query($query)) {
            die("Gagal menyimpan riwayat pembukaan: " . $db->error);
        }
    }
    if ($action === 'close') {
        $query = "UPDATE history SET close_locker_at = '$timestamp' WHERE rf_id = '$rfid' AND locker_number = '$loker_id' AND close_locker_at IS NULL";
        if (!$db->query($query)) {
            die("Gagal memperbarui riwayat penutupan: " . $db->error);
        }
    }
}
