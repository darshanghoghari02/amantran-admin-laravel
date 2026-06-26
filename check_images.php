<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$templates = DB::table('templates')->limit(5)->get();
foreach ($templates as $t) {
    $data = json_decode($t->data, true);
    echo "ID: " . $t->id . "\n";
    echo "  thumbnailUrl: " . ($data['thumbnailUrl'] ?? 'NOT SET') . "\n";
    echo "  imageUrl: " . ($data['imageUrl'] ?? 'NOT SET') . "\n";
    echo "  pages[0][thumbnail]: " . ($data['pages'][0]['thumbnail'] ?? 'NOT SET') . "\n";
    echo "---\n";
}
