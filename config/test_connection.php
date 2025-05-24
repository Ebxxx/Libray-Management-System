<?php
require_once 'Database.php';

// Enable error reporting for testing
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Testing Supabase Connection</h2>";

try {
    // Create database instance
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        echo "<p style='color: green;'>✓ Database connection established successfully!</p>";
        
        // Test 1: Simple count query
        echo "<h3>Test 1: Count Query</h3>";
        $response = $database->query('users', 'GET', []);
        
        if ($response !== null) {
            echo "<p style='color: green;'>✓ Count query successful!</p>";
            echo "<p>Number of users: " . count($response) . "</p>";
            echo "<p>Response: " . print_r($response, true) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Count query failed</p>";
        }

        // Test 2: Select specific columns
        echo "<h3>Test 2: Select Columns</h3>";
        $response = $database->query('users', 'GET', [
            'username' => 'admin'  // Example filter
        ]);
        
        if ($response !== null) {
            echo "<p style='color: green;'>✓ Column select successful!</p>";
            echo "<p>Response: " . print_r($response, true) . "</p>";
        } else {
            echo "<p style='color: red;'>✗ Column select failed</p>";
        }

        // Test 3: Check table existence
        echo "<h3>Test 3: Table Check</h3>";
        try {
            $response = $database->query('users', 'GET', []);
            if ($response !== null) {
                echo "<p style='color: green;'>✓ Table exists and is accessible!</p>";
                echo "<p>Response: " . print_r($response, true) . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Table check failed</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='color: red;'>✗ Table check failed: " . $e->getMessage() . "</p>";
        }
        
        // Display connection details
        echo "<h3>Connection Details:</h3>";
        echo "<pre>";
        echo "Host: " . $database->getHost() . "\n";
        echo "Database: " . $database->getDatabase() . "\n";
        echo "Username: " . $database->getUsername() . "\n";
        echo "</pre>";

        // Display PDO connection info
        echo "<h3>PDO Connection Information:</h3>";
        echo "<pre>";
        echo "PDO Driver: " . $conn->getAttribute(PDO::ATTR_DRIVER_NAME) . "\n";
        echo "PDO Server Version: " . $conn->getAttribute(PDO::ATTR_SERVER_VERSION) . "\n";
        echo "PDO Client Version: " . $conn->getAttribute(PDO::ATTR_CLIENT_VERSION) . "\n";
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'>✗ Failed to establish database connection</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<pre>Stack trace:\n" . $e->getTraceAsString() . "</pre>";
} 