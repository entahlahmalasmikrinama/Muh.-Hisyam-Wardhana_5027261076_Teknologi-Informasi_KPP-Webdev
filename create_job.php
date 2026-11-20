<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->company_id) || empty($data->name) || empty($data->salary)) {
    die(json_encode(["status" => "error", "message" => "Data lowongan tidak lengkap (Nama, Gaji, dll wajib diisi)"]));
}

$company_id = $conn->real_escape_string($data->company_id);
$name = $conn->real_escape_string($data->name);
$type = $conn->real_escape_string($data->type);
$category = $conn->real_escape_string($data->category);
$location = $conn->real_escape_string($data->location);
$salary = $conn->real_escape_string($data->salary);
$experience = $conn->real_escape_string($data->experience);
$desc = $conn->real_escape_string($data->desc);
$reqs = $conn->real_escape_string($data->requirements);
$benefits = $conn->real_escape_string($data->benefits);
$icon = $conn->real_escape_string($data->icon);

$sql = "INSERT INTO jobs (company_id, name, type, category, location, salary, experience, description, requirements, benefits, icon, status) 
        VALUES ('$company_id', '$name', '$type', '$category', '$location', '$salary', '$experience', '$desc', '$reqs', '$benefits', '$icon', 'active')";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Lowongan berhasil diterbitkan!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}
?>