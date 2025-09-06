<?php
// Database connection details
$host = "localhost";
$port = "5432";
$dbname = "noko";
$user = "postgres";
$password = "admin";

// Connection string
$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password";

// Connect to PostgreSQL
$conn = pg_connect($conn_string);

// Check connection
if ($conn) {
    echo "✅ Connected to PostgreSQL database successfully!";
} else {
    echo "❌ Failed to connect to PostgreSQL.";
}
?>
