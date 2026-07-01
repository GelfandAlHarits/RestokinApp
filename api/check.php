<?php
header('Content-Type: text/plain');
echo extension_loaded('pdo_pgsql') ? "pdo_pgsql: AKTIF\n" : "pdo_pgsql: TIDAK ADA\n";
echo "Driver PDO tersedia:\n";
print_r(PDO::getAvailableDrivers());