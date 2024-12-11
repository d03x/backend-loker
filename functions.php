<?php

function processLockerAccess($rfid, $loker_id, $db)
{
    $lokerData = getLockerData($loker_id, $db);

    if ($lokerData) {
        if (isLockerInUseByAnotherUser($lokerData, $rfid)) {
            return ["error" => "Loker Telah Digunakan"];
        }

        if (isSameUserClosingLocker($lokerData, $rfid)) {
            updateLockerStatus($rfid, $loker_id, $db);
            addHistory($rfid, $loker_id, $db, 'open');
            return ["message" => "Locker opened successfully."];
        } else {
            clearLocker($loker_id, $db);
            assignLocker($rfid, $loker_id, $db);
            addHistory($rfid, $loker_id, $db, 'close');
            return ["message" => "Locker closed successfully."];
        }
    } else {
        assignLocker($rfid, $loker_id, $db);
        addHistory($rfid, $loker_id, $db, 'open');
        return ["message" => "Locker assigned and opened successfully."];
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
    if (!$update) {
        return ["error" => "Gagal memperbarui status loker."];
    }
}

function clearLocker($loker_id, $db)
{
    $db->query("DELETE FROM lokers_access WHERE locker_number='$loker_id'");
}

function assignLocker($rfid, $loker_id, $db)
{
    $insert = $db->query("INSERT INTO lokers_access (rf_uid, tap_ke, locker_number) VALUES ('$rfid', 1, '$loker_id')");
    if (!$insert) {
        return ["error" => "Gagal menambahkan data loker."];
    }
}

function addHistory($rfid, $loker_id, $db, $action)
{
    $timestamp = date('Y-m-d H:i:s');
    if ($action === 'open') {
        $query = "INSERT INTO history (rf_id, open_locker_at, locker_number) VALUES ('$rfid', '$timestamp', '$loker_id')";
        if (!$db->query($query)) {
            return ["error" => "Gagal menyimpan riwayat pembukaan: " . $db->error];
        }
    }
    if ($action === 'close') {
        $query = "UPDATE history SET close_locker_at = '$timestamp' WHERE rf_id = '$rfid' AND locker_number = '$loker_id' AND close_locker_at IS NULL";
        if (!$db->query($query)) {
            return ["error" => "Gagal memperbarui riwayat penutupan: " . $db->error];
        }
    }
}
