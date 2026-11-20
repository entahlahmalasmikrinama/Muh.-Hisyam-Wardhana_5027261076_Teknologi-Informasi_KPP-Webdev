<?php
include 'koneksi.php';

// Menerima data JSON dari Frontend
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit();
}

$email = $conn->real_escape_string($data->email);
$password = $data->password; // Di produksi, gunakan password_verify()

// 1. Cek Admin
$sql = "SELECT * FROM admins WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "user" => [
            "id" => $row['id'],
            "nama" => $row['nama'],
            "email" => $row['email'],
            "role" => "admin"
        ]
    ]);
    exit();
}

// 2. Cek Company
$sql = "SELECT * FROM companies WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "user" => [
            "id" => $row['id'],
            "companyId" => $row['id'], // Menyesuaikan logic JS
            "nama" => $row['name'],
            "email" => $row['email'],
            "role" => "company"
        ]
    ]);
    exit();
}

// 3. Cek User (Pelamar)
$sql = "SELECT * FROM users WHERE email='$email' AND password='$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        "status" => "success",
        "user" => [
            "id" => $row['id'],
            "nama" => $row['nama'],
            "email" => $row['email'],
            "skills" => $row['skills'],
            "experience" => $row['experience'],
            "role" => "user"
        ]
    ]);
    exit();
}

// Jika tidak ditemukan di mana-mana
echo json_encode(["status" => "error", "message" => "Email atau password salah!"]);
?>