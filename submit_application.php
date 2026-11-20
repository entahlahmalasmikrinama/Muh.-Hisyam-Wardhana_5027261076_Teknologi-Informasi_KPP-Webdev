<?php
// Aktifkan Error Reporting untuk melihat jika ada masalah PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';

// Cek koneksi database dulu
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database Connection Failed: " . $conn->connect_error]));
}

// Pastikan method adalah POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit();
}

// Ambil data teks
$user_id = $_POST['user_id'] ?? '';
$posisi = $_POST['posisi'] ?? '';
$surat = $_POST['surat_lamaran'] ?? '';

// Validasi input dasar
if (empty($user_id) || empty($posisi)) {
    echo json_encode(["status" => "error", "message" => "User ID atau Posisi kosong. Pastikan Anda sudah Login."]);
    exit();
}

// --- LOGIKA UPLOAD CV ---
$cv_path = NULL; // Default NULL
if (isset($_FILES['cv']) && $_FILES['cv']['error'] == 0) {
    $allowed = ['pdf'];
    $filename = $_FILES['cv']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(["status" => "error", "message" => "Format CV harus PDF"]);
        exit();
    }
    
    // Pastikan folder ada
    if (!is_dir('uploads/cv')) { mkdir('uploads/cv', 0777, true); }

    $new_cv_name = $user_id . "_" . time() . "_cv." . $ext;
    $target = "uploads/cv/" . $new_cv_name;
    
    if (move_uploaded_file($_FILES['cv']['tmp_name'], $target)) {
        $cv_path = $new_cv_name;
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal memindahkan file CV. Cek permission folder uploads/cv"]);
        exit();
    }
} else {
    // Cek error spesifik upload
    if (isset($_FILES['cv']['error']) && $_FILES['cv']['error'] != 4) {
        echo json_encode(["status" => "error", "message" => "Error Upload CV Code: " . $_FILES['cv']['error']]);
        exit();
    }
}

// --- LOGIKA UPLOAD FOTO ---
$foto_path = NULL; // Default NULL
if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $filename = $_FILES['foto']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowed)) {
        echo json_encode(["status" => "error", "message" => "Format Foto harus JPG/PNG"]);
        exit();
    }

    if (!is_dir('uploads/foto')) { mkdir('uploads/foto', 0777, true); }
    
    $new_foto_name = $user_id . "_" . time() . "_foto." . $ext;
    $target = "uploads/foto/" . $new_foto_name;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
        $foto_path = $new_foto_name;
    } else {
        echo json_encode(["status" => "error", "message" => "Gagal memindahkan Foto."]);
        exit();
    }
}

// --- SIMPAN KE DATABASE ---
$stmt = $conn->prepare("INSERT INTO applications (user_id, posisi, surat_lamaran, cv_path, foto_path, status) VALUES (?, ?, ?, ?, ?, 'Pending')");

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Prepare Statement Error: " . $conn->error]);
    exit();
}

$stmt->bind_param("issss", $user_id, $posisi, $surat, $cv_path, $foto_path);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Lamaran berhasil dikirim!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal simpan ke DB: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>