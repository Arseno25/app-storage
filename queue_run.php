<?php

$artisanPath = __DIR__ . '/artisan';

if (!file_exists($artisanPath)) {
    die("File 'artisan' tidak ditemukan di path: $artisanPath\n");
}

exec("php $artisanPath queue:work --stop-when-empty", $output, $status);

if ($status === 0) {
    echo "Command 'php artisan queue:work --stop-when-empty' berhasil dijalankan di background.\n";
} else {
    echo "Command gagal dijalankan dengan status: $status\n";
    print_r($output);
}
