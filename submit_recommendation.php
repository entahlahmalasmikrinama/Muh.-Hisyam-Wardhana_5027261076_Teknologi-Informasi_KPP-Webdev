<?php
// File: C:/xampp/htdocs/techjobs/submit_recommendation.php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

// 1. Ambil input mentah
$input_raw = file_get_contents("php://input");
$data = json_decode($input_raw);

// 2. DEBUGGING: Jika kosong, beritahu user apa yang diterima (untuk diagnosa)
if (empty($input_raw)) {
    die(json_encode(["status" => "error", "message" => "Server menerima data KOSONG. Cek Javascript Anda."]));
}

if (is_null($data)) {
    die(json_encode(["status" => "error", "message" => "Format JSON salah. Data yang diterima server: " . $input_raw]));
}

// 3. Cek Kelengkapan Data
if (empty($data->user_id)) {
    die(json_encode(["status" => "error", "message" => "Data tidak lengkap: ID Pelamar (user_id) kosong!"]));
}

if (empty($data->company_id)) {
    die(json_encode(["status" => "error", "message" => "Data tidak lengkap: Anda belum memilih Perusahaan!"]));
}

// 4. Proses Simpan
$user_id = $conn->real_escape_string($data->user_id);
$company_id = $conn->real_escape_string($data->company_id);
$position = $conn->real_escape_string($data->position ?? '');
$message = $conn->real_escape_string($data->message ?? '');

$sql = "INSERT INTO recommendations (user_id, company_id, position, admin_message, status, type) 
        VALUES ('$user_id', '$company_id', '$position', '$message', 'recommended', 'admin_recommendation')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Rekomendasi berhasil dikirim!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
}
?>