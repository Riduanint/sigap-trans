<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Change requests: " . \App\Models\UptChangeRequest::count() . PHP_EOL;
echo "Audit logs: " . \App\Models\AuditLog::count() . PHP_EOL;
echo "Documents: " . \App\Models\UptDocument::count() . PHP_EOL;
echo "Users: " . \App\Models\User::count() . PHP_EOL;
