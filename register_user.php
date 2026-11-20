<?php
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit();
}

$nama = $conn->real_escape_string($data->nama);
$email = $conn->real_escape_string($data->email);
$password = $conn->real_escape_string($data->password); // Di produksi gunakan password_hash()
$telepon = $conn->real_escape_string($data->telepon);
$skills = $conn->real_escape_string($data->skills);
$experience = (int)$data->experience;

// Cek email duplikat di tabel users
$check = $conn->query("SELECT id FROM users WHERE email='$email'");
if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email sudah terdaftar sebagai Pelamar!"]);
    exit();
}

$sql = "INSERT INTO users (nama, email, password, telepon, skills, experience) 
        VALUES ('$nama', '$email', '$password', '$telepon', '$skills', $experience)";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Registrasi Berhasil"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}
?>