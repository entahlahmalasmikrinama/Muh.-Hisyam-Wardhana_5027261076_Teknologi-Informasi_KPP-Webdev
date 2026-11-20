<?php
// ^ Pastikan TIDAK ADA spasi atau enter di atas tanda <?php ini
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$host = "localhost";
$user = "root";
$pass = "";
$db   = "techjobs_portal";

// Hapus atau comment baris error_reporting(0) jika ingin melihat error saat debugging
// error_reporting(0); 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Koneksi Gagal: " . $conn->connect_error]));
}
?>