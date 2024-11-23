<?php

$artisanPath = __DIR__ . '/artisan';

if (!file_exists($artisanPath)) {
    die("File 'artisan' tidak ditemukan di path: $artisanPath\n");
}

exec("php $artisanPath queue:work --stop-when-empty", $queueOutput, $queueStatus);

if ($queueStatus === 0) {
    echo "Command 'php artisan queue:work --stop-when-empty' berhasil dijalankan di background.\n";
} else {
    echo "Command gagal dijalankan dengan status: $queueStatus\n";
    print_r($queueOutput);
}

exec("php $artisanPath schedule:run", $scheduleOutput, $scheduleStatus);

if ($scheduleStatus === 0) {
    echo "Command 'php artisan schedule:run' berhasil dijalankan di background.\n";
} else {
    echo "Command gagal dijalankan dengan status: $scheduleStatus\n";
    print_r($scheduleOutput);
}
