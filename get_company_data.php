<?php
// FILE: get_company_data.php (Versi Fix Column Error)

// Matikan error display agar JSON tidak rusak jika ada warning kecil
error_reporting(0);
ini_set('display_errors', 0);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include 'koneksi.php';

$company_id = $_GET['id'] ?? '';

if (empty($company_id)) {
    die(json_encode(["status" => "error", "message" => "Parameter ID Perusahaan (?id=...) tidak ditemukan"]));
}

// Fungsi Helper Query Aman
function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    
    if (!$result) {
        // Log error ke server tapi jangan kirim HTML ke frontend
        error_log("SQL Error: " . $conn->error); 
        return [];
    }
    
    $data = [];
    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// ------------------------------------------
// 1. Ambil Data Rekomendasi (REVISI: Hapus u.cv_path)
// ------------------------------------------
$sqlRec = "SELECT r.id, r.user_id, r.position, r.admin_message, r.status, r.company_decision,
           u.nama, u.skills, u.experience
           FROM recommendations r
           JOIN users u ON r.user_id = u.id
           WHERE r.company_id = '$company_id' 
           ORDER BY r.created_at DESC";

$recommendations = safeQuery($conn, $sqlRec);

// ------------------------------------------
// 2. Ambil Data Lowongan (Jobs)
// ------------------------------------------
// Kita cek dulu apakah tabel jobs ada untuk menghindari error jika tabel belum dibuat
$checkTable = $conn->query("SHOW TABLES LIKE 'jobs'");
if ($checkTable->num_rows == 0) {
    $jobs = [];
} else {
    $sqlJobs = "SELECT * FROM jobs WHERE company_id = '$company_id' ORDER BY created_at DESC";
    $jobs = safeQuery($conn, $sqlJobs);
}

// ------------------------------------------
// OUTPUT JSON
// ------------------------------------------
echo json_encode([
    "status" => "success",
    "recommendations" => $recommendations,
    "jobs" => $jobs
]);
?>