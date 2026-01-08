<?php
require_once 'includes/database.php';
require_once 'classes/CSVHandler.class.php';
require_once 'includes/functions.php';

if (isset($_GET['export'])) {
    $csvHandler = new CSVHandler();
    $data = $csvHandler->exportFromDB($mysqli);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="teas_export.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['drink_date', 'brand', 'tea_name', 'flavor']);

    foreach ($data as $row) {
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

require_once 'includes/header.php';

$csvHandler = new CSVHandler();

echo showMessage("Use the forms below to manage your tea data", 'info');

if (!tableExists($mysqli, 'teas')) {
    echo showMessage("Teas table not found! Please reset database.", 'warning');
}

if (isset($_POST['import']) && isset($_FILES['csvfile'])) {
    $temp_file = $_FILES['csvfile']['tmp_name'];

    $validation = $csvHandler->validateCSV($temp_file);

    if ($validation === true) {
        $result = $csvHandler->importToDB($mysqli, $temp_file);

        if ($result['success']) {
            echo showMessage($result['message'], 'success');
        } else {
            echo showMessage($result['message'], 'error');
        }
    } else {
        echo showMessage("Validation errors:", 'error');
        echo "<ul>";
        foreach ($validation as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
    }
}

if (isset($_POST['reset'])) {
    $mysqli->begin_transaction();
    try {
        $mysqli->query("DELETE FROM teas");

        $file = fopen('data/default.csv', 'r');
        fgetcsv($file); // Skip header

        while (($row = fgetcsv($file)) !== FALSE) {
            $date = $row[0];
            $brand = $row[1];
            $name = $row[2];
            $flavor = $row[3] ?? '';

            $stmt = $mysqli->prepare("INSERT INTO teas (drink_date, brand, tea_name, flavor) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $date, $brand, $name, $flavor);
            $stmt->execute();
        }

        fclose($file);
        $mysqli->commit();
        echo showMessage("Database reset!", 'success');

    } catch (Exception $e) {
        $mysqli->rollback();
        echo showMessage("Error: " . $e->getMessage(), 'error');
    }
}

echo "<h2>Import/Export</h2>";

echo "<h3>Export CSV</h3>";
echo "<form method='GET'>";
echo "<button type='submit' name='export'>Download CSV</button>";
echo "</form>";

echo "<h3>Import CSV</h3>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='csvfile' required><br><br>";
echo "<button type='submit' name='import'>Import CSV</button>";
echo "</form>";

echo "<h3>Reset Database</h3>";
echo "<form method='POST'>";
echo "<button type='submit' name='reset'>Reset to Default</button>";
echo "</form>";

echo "<h3>Current Teas:</h3>";
$result = $mysqli->query("SELECT * FROM teas ORDER BY drink_date LIMIT 10");
if ($result->num_rows > 0) {
    echo "<table border=1>";
    echo "<tr><th>Date</th><th>Brand</th><th>Name</th></tr>";
    while ($tea = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $tea['drink_date'] . "</td>";
        echo "<td>" . $tea['brand'] . "</td>";
        echo "<td>" . $tea['tea_name'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo showMessage("No teas in database yet.", 'warning');
}

require_once 'includes/footer.php';
?>