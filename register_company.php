<?php
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

$name = $conn->real_escape_string($data->name);
$email = $conn->real_escape_string($data->email);
$password = $conn->real_escape_string($data->password);
$telepon = $conn->real_escape_string($data->telepon);
$industry = $conn->real_escape_string($data->industry);

// Cek email duplikat
$check = $conn->query("SELECT id FROM companies WHERE email='$email'");
if ($check->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email perusahaan sudah terdaftar!"]);
    exit();
}

$sql = "INSERT INTO companies (name, email, password, telepon, industry) 
        VALUES ('$name', '$email', '$password', '$telepon', '$industry')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Registrasi Perusahaan Berhasil"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}
?>