<?php

exec('php artisan queue:work --stop-when-empty');

echo "Command 'php artisan queue:work --stop-when-empty' berhasil dijalankan di background.\n";
