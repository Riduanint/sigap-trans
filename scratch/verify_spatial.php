<?php
$pdo = new PDO('pgsql:host=127.0.0.1;dbname=sigap_trans', 'postgres', 'postgres');

echo "=== EXTENSIONS INSTALLED ===\n";
foreach ($pdo->query("SELECT extname, extversion FROM pg_extension") as $ext) {
    echo "- {$ext['extname']} (v{$ext['extversion']})\n";
}

echo "\n=== SPATIAL GEOMETRY COLUMNS ===\n";
$stmt = $pdo->query("SELECT f_table_name, f_geometry_column, coord_dimension, srid, type FROM geometry_columns");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Table: {$r['f_table_name']} | Column: {$r['f_geometry_column']} | Type: {$r['type']} | SRID: {$r['srid']}\n";
}

echo "\n=== SPATIAL GIST INDEXES ===\n";
$stmt = $pdo->query("SELECT indexname, indexdef FROM pg_indexes WHERE indexname LIKE '%gist%'");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Index: {$r['indexname']} -> {$r['indexdef']}\n";
}
