<?php

function rupiah(int|string|null $nilai): string
{
    return 'Rp' . number_format((int) $nilai, 0, ',', '.');
}

function waktu(?string $nilai): string
{
    return $nilai ? date('d/m/Y H:i', strtotime($nilai)) . ' WIB' : '-';
}

function durasi(int $menit): string
{
    return intdiv($menit, 60) . ' jam ' . $menit % 60 . ' menit';
}

function nav_aktif(string $path): string
{
    $uri = trim(service('request')->getUri()->getRoutePath(), '/');

    return $uri === $path || str_starts_with($uri, $path . '/') ? 'active' : '';
}

function status_pengguna(string $status): string
{
    return [
        'menunggu' => 'Menunggu verifikasi',
        'aktif' => 'Sedang parkir',
        'minta_keluar' => 'Menunggu pembayaran',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan',
        'ditolak' => 'Ditolak petugas',
    ][$status] ?? 'Status belum tersedia';
}

function warna_status_pengguna(string $status): string
{
    return match ($status) {
        'aktif' => 'status-active',
        'selesai' => 'status-complete',
        'ditolak', 'dibatalkan' => 'status-closed',
        default => 'status-waiting',
    };
}
