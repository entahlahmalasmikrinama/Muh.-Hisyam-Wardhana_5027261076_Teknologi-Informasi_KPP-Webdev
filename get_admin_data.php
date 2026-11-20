<?php
// File: get_admin_data.php
error_reporting(0);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
include 'koneksi.php';

// Helper Anti-Crash
function safeQuery($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) return [];
    $data = [];
    while($row = $result->fetch_assoc()) { $data[] = $row; }
    return $data;
}

function safeCount($conn, $sql) {
    $result = $conn->query($sql);
    if (!$result) return 0;
    $row = $result->fetch_assoc();
    return $row ? $row['total'] : 0;
}

// 1. AMBIL DATA LAMARAN (LOGIKA PINTAR)
// Kita gunakan sub-query untuk mengintip status dari tabel recommendations
$sqlApps = "SELECT 
                a.id as app_id, 
                a.user_id, 
                a.posisi, 
                a.cv_path, 
                a.foto_path, 
                a.status as status_asli, 
                a.created_at,
                u.nama, 
                u.email, 
                u.skills,
                -- INI BAGIAN PENTINGNYA: Intip status rekomendasi
                (SELECT status FROM recommendations r WHERE r.user_id = a.user_id AND r.position = a.posisi LIMIT 1) as status_rekomendasi
            FROM applications a 
            JOIN users u ON a.user_id = u.id 
            ORDER BY a.created_at DESC";

$rawApps = safeQuery($conn, $sqlApps);

// Proses Data: Tentukan Status Akhir
$applications = [];
foreach($rawApps as $app) {
    // Default status dari tabel applications
    $finalStatus = $app['status_asli']; 
    
    // Tapi kalau ada status rekomendasi, kita pakai itu!
    if (!empty($app['status_rekomendasi'])) {
        if ($app['status_rekomendasi'] == 'accepted') {
            $finalStatus = 'Diterima';
        } else if ($app['status_rekomendasi'] == 'rejected') {
            $finalStatus = 'Ditolak';
        }
    }
    
    // Update array dengan status yang sudah diperbaiki
    $app['status'] = $finalStatus;
    $applications[] = $app;
}

// 2. Ambil Perusahaan
$companies = safeQuery($conn, "SELECT id, name, industry FROM companies");

// 3. Ambil Lowongan
$jobs = safeQuery($conn, "SELECT j.*, c.name as company_name FROM jobs j JOIN companies c ON j.company_id = c.id ORDER BY j.created_at DESC");

// 4. Statistik
$stats = [
    "total_users" => safeCount($conn, "SELECT count(*) as t FROM users WHERE role='user'"),
    "total_companies" => safeCount($conn, "SELECT count(*) as t FROM companies"),
    "total_jobs" => safeCount($conn, "SELECT count(*) as t FROM jobs")
];

echo json_encode([
    "status" => "success",
    "applications" => $applications,
    "companies" => $companies,
    "jobs" => $jobs,
    "stats" => $stats
]);
?>