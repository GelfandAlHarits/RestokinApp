<?php
echo extension_loaded('pdo_pgsql') ? 'pdo_pgsql: AKTIF' : 'pdo_pgsql: TIDAK ADA';
echo "\n";
print_r(PDO::getAvailableDrivers());