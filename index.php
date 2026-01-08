<?php
require_once 'includes/database.php';
require_once 'includes/header.php';

echo "<h1>TeaKeeper - Test Page</h1>";
echo "<p>Testing database connection...</p>";

if ($mysqli->connect_error) {
    echo "<p>Database connection failed!</p>";
} else {
    echo "<p>✓ Database connected successfully</p>";
}

$tables = ['teas', 'import_history', 'tea_audit'];
foreach ($tables as $table) {
    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows > 0) {
        echo "<p>✓ Table '$table' exists</p>";
    } else {
        echo "<p'>Table '$table' not found</p>";
    }
}

$result = $mysqli->query("SELECT COUNT(*) as count FROM teas");
$row = $result->fetch_assoc();
echo "<p>Teas in database: " . $row['count'] . "</p>";

if ($row['count'] > 0) {
    echo "<h3>First 5 Teas:</h3>";
    $teas = $mysqli->query("SELECT * FROM teas ORDER BY drink_date LIMIT 5");

    echo "<table>";
    echo "<tr><th>ID</th><th>Date</th><th>Brand</th><th>Name</th><th>Flavor</th></tr>";

    while ($tea = $teas->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $tea['id'] . "</td>";
        echo "<td>" . $tea['drink_date'] . "</td>";
        echo "<td>" . $tea['brand'] . "</td>";
        echo "<td>" . $tea['tea_name'] . "</td>";
        echo "<td>" . $tea['flavor'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<p>No teas in database yet.</p>";
}

echo "<hr>";
echo "<h3>Quick Navigation:</h3>";
echo "<a href='iexport.php'>Go to Import/Export</a> | ";
echo "<a href='teas.php'>View All Teas</a>";

require_once 'includes/footer.php';
?>