<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->id)) {
    die(json_encode(["status" => "error", "message" => "ID Lowongan tidak ditemukan"]));
}

$id = $conn->real_escape_string($data->id);

$sql = "DELETE FROM jobs WHERE id = '$id'";

if ($conn->query($sql) === TRUE) {
    echo json_encode(["status" => "success", "message" => "Lowongan dihapus"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error: " . $conn->error]);
}
?>