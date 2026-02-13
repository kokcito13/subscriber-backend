<?php

/**
 * Simple test script to verify Symfony and MySQL setup
 * Run: php test-setup.php
 */

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;

// Load environment variables
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/.env');

echo "=== Symfony & MySQL Setup Test ===\n\n";

// Test 1: PHP Version
echo "1. PHP Version: " . PHP_VERSION . "\n";
echo "   ✓ PHP is installed\n\n";

// Test 2: Symfony
echo "2. Symfony Framework:\n";
try {
    $kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', (bool) ($_ENV['APP_DEBUG'] ?? true));
    echo "   ✓ Symfony kernel loaded successfully\n";
    echo "   Environment: " . $kernel->getEnvironment() . "\n";
    echo "   Debug mode: " . ($kernel->isDebug() ? 'enabled' : 'disabled') . "\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 3: Database Connection
echo "3. MySQL Database Connection:\n";
$databaseUrl = $_ENV['DATABASE_URL'] ?? '';
echo "   Connection string: " . preg_replace('/:[^:@]+@/', ':****@', $databaseUrl) . "\n";

try {
    // Parse DATABASE_URL
    $url = parse_url($databaseUrl);
    if (!$url) {
        throw new \Exception("Invalid DATABASE_URL format");
    }

    $host = $url['host'] ?? '127.0.0.1';
    $port = $url['port'] ?? 3306;
    $user = $url['user'] ?? 'root';
    $pass = $url['pass'] ?? '';
    $dbname = ltrim($url['path'] ?? '/app', '/');

    // Test connection
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new \PDO($dsn, $user, $pass, [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_TIMEOUT => 5,
    ]);

    echo "   ✓ Successfully connected to MySQL\n";
    echo "   Host: $host:$port\n";
    echo "   Database: $dbname\n";

    // Test query
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch(\PDO::FETCH_ASSOC);
    echo "   MySQL Version: " . $version['version'] . "\n\n";

} catch (\PDOException $e) {
    echo "   ✗ Connection failed: " . $e->getMessage() . "\n";
    echo "   → Make sure MySQL is running and credentials in .env are correct\n\n";
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

// Test 4: Doctrine
echo "4. Doctrine ORM:\n";
try {
    $kernel = new \App\Kernel($_ENV['APP_ENV'] ?? 'dev', (bool) ($_ENV['APP_DEBUG'] ?? true));
    $kernel->boot();
    $container = $kernel->getContainer();

    if ($container->has('doctrine')) {
        echo "   ✓ Doctrine is configured\n";
        $em = $container->get('doctrine.orm.entity_manager');
        echo "   ✓ Entity Manager is available\n\n";
    } else {
        echo "   ✗ Doctrine not found\n\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n\n";
}

echo "=== Test Complete ===\n";
echo "\nNext steps:\n";
echo "1. Start the development server: php -S localhost:8000 -t www\n";
echo "2. Visit http://localhost:8000/test to test the API endpoint\n";
echo "3. Or use Symfony CLI: symfony server:start\n";
