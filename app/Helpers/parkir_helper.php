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
