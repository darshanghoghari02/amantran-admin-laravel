<?php

// Secure check
if (($_GET['secret'] ?? '') !== 'debug123') {
    die('Unauthorized');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Amantran Live Server Diagnostic Tool</h2>";

// 1. Check PHP and modules
echo "<h3>1. Environment</h3>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "PDO MySQL Driver: " . (extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled') . "<br>";

// 2. Load Laravel Bootstrap to read config
try {
    define('LARAVEL_START', microtime(true));
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $kernel->bootstrap();
    
    echo "Laravel Bootstrapped Successfully.<br>";
    echo "APP_ENV: " . config('app.env') . "<br>";
    echo "APP_DEBUG: " . (config('app.debug') ? 'True' : 'False') . "<br>";
    echo "DB Connection: " . config('database.default') . "<br>";
    echo "DB Host: " . config('database.connections.mysql.host') . "<br>";
    echo "DB Database: " . config('database.connections.mysql.database') . "<br>";
    echo "DB Username: " . config('database.connections.mysql.username') . "<br>";
} catch (\Throwable $e) {
    echo "<font color='red'>Laravel Bootstrap Failed: " . $e->getMessage() . "</font><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// 3. Test Direct Database connection using Laravel DB facade
echo "<h3>2. Database Test</h3>";
try {
    $results = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    echo "<font color='green'>Database connection successful! Tables count: " . count($results) . "</font><br>";
    echo "<h4>Tables list:</h4><ul>";
    foreach ($results as $row) {
        $rowArray = (array)$row;
        echo "<li>" . reset($rowArray) . "</li>";
    }
    echo "</ul>";
} catch (\Throwable $e) {
    echo "<font color='red'>Database connection failed via Laravel: " . $e->getMessage() . "</font><br>";
    
    // Test native PDO connection to see if it works outside Laravel
    try {
        $host = config('database.connections.mysql.host') ?: env('DB_HOST', '127.0.0.1');
        $db = config('database.connections.mysql.database') ?: env('DB_DATABASE', 'amantran_db');
        $user = config('database.connections.mysql.username') ?: env('DB_USERNAME', 'root');
        $pass = config('database.connections.mysql.password') ?: env('DB_PASSWORD', '');
        $port = config('database.connections.mysql.port') ?: env('DB_PORT', '3306');
        
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        echo "<font color='green'>Direct PDO connection succeeded!</font><br>";
    } catch (\Throwable $pdoError) {
        echo "<font color='red'>Direct PDO connection failed: " . $pdoError->getMessage() . "</font><br>";
    }
}
