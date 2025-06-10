
<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=Your_database_name", "Your_user_name", "Your_Password", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  // Enables error reporting
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>