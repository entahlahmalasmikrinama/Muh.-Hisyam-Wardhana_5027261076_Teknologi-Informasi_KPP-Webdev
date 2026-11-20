<?php
// File: C:/xampp/htdocs/techjobs/update_recommendation.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id) || empty($data->status)) {
    die(json_encode(["status" => "error", "message" => "Data tidak lengkap"]));
}

$id = $conn->real_escape_string($data->id);      // ID Rekomendasi
$status = $conn->real_escape_string($data->status); // 'accepted' atau 'rejected'
$note = $conn->real_escape_string($data->note ?? '');

// 1. UPDATE TABEL REKOMENDASI (Milik Perusahaan)
$sqlRec = "UPDATE recommendations SET status = '$status', company_decision = '$note' WHERE id = '$id'";
$updateRec = $conn->query($sqlRec);

if ($updateRec) {
    // --- FITUR SINKRONISASI (BARU) ---
    // Kita cari tahu dulu: Rekomendasi ini milik Pelamar siapa (user_id) dan Posisi apa?
    $cekData = $conn->query("SELECT user_id, position FROM recommendations WHERE id = '$id'");
    
    if ($row = $cekData->fetch_assoc()) {
        $user_id = $row['user_id'];
        $posisi  = $row['position'];
        
        // Terjemahkan Status (Inggris -> Indonesia sesuai ENUM database applications)
        $statusApp = 'Pending';
        if ($status == 'accepted') {
            $statusApp = 'Diterima';
        } else if ($status == 'rejected') {
            $statusApp = 'Ditolak';
        }
        
        // 2. UPDATE TABEL LAMARAN (Milik Admin & User)
        // Kita update baris yang user_id DAN posisinya cocok
        $sqlApp = "UPDATE applications SET status = '$statusApp' 
                   WHERE user_id = '$user_id' AND posisi = '$posisi'";
        $conn->query($sqlApp);
    }
    // ---------------------------------

    echo json_encode(["status" => "success", "message" => "Status diperbarui & tersinkronisasi ke Admin!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}
?>