<?php

exec('php artisan queue:work --stop-when-empty', $output, $status);

if ($status === 0) {
    echo "Command berhasil dijalankan:\n";
} else {
    echo "Command gagal dijalankan dengan status: $status\n";
    print_r($output);
}