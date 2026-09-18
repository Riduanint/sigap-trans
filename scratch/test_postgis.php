<?php
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=sigap_trans', 'postgres', 'postgres');
$pdo->exec('CREATE EXTENSION IF NOT EXISTS postgis');
$version = $pdo->query('SELECT PostGIS_Full_Version()')->fetchColumn();
echo "SUCCESS:\n" . $version . "\n";
