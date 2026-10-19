<?php

$errorCode = 403;
$errorTitle = 'Akses dibatasi';
$errorMessage = $errorMessage ?? 'Halaman ini hanya tersedia untuk admin.';
require __DIR__ . '/layout.php';
