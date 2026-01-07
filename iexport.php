<?php
require_once 'includes/database.php';
require_once 'includes/header.php';

echo "<h2>Import/Export</h2>";

echo "<h3>Import CSV</h3>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='csvfile' required><br><br>";
echo "<button type='submit' name='import'>Import CSV</button>";
echo "</form>";

echo "<h3>Export CSV</h3>";
echo "<form method='GET'>";
echo "<button type='submit' name='export'>Download CSV</button>";
echo "</form>";

echo "<h3>Reset Database</h3>";
echo "<form method='POST'>";
echo "<button type='submit' name='reset'>Reset to Default</button>";
echo "</form>";

if (isset($_POST['import']) && isset($_FILES['csvfile'])) {
    $mysqli->begin_transaction();

    try {
        $mysqli->query("DELETE FROM teas");

        $file = fopen($_FILES['csvfile']['tmp_name'], 'r');
        $count = 0;

        while (($row = fgetcsv($file)) !== FALSE) {
            if (count($row) >= 3) {
                $date = $row[0];
                $brand = $row[1];
                $name = $row[2];
                $flavor = isset($row[3]) ? $row[3] : '';

                $stmt = $mysqli->prepare("INSERT INTO teas (drink_date, brand, tea_name, flavor) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $date, $brand, $name, $flavor);
                $stmt->execute();
                $count++;
            }
        }

        fclose($file);
        $mysqli->commit();
        echo "<p>Imported $count teas!</p>";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

if (isset($_GET['export'])) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="TeaKeeperExport.csv"');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['drink_date', 'brand', 'tea_name', 'flavor']);

    $result = $mysqli->query("SELECT drink_date, brand, tea_name, flavor FROM teas ORDER BY drink_date");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [$row['drink_date'], $row['brand'], $row['tea_name'], $row['flavor']]);
    }

    fclose($output);
    exit;
}

if (isset($_POST['reset'])) {
    $mysqli->begin_transaction();

    try {
        $mysqli->query("DELETE FROM teas");

        $file = fopen('data/default.csv', 'r');
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== FALSE) {
            $date = $row[0];
            $brand = $row[1];
            $name = $row[2];
            $flavor = isset($row[3]) ? $row[3] : '';

            $stmt = $mysqli->prepare("INSERT INTO teas (drink_date, brand, tea_name, flavor) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $date, $brand, $name, $flavor);
            $stmt->execute();
        }

        fclose($file);
        $mysqli->commit();
        echo "<p>Database reset from default.csv!</p>";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Current Teas in Database:</h3>";
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
    echo "<p>No teas yet. Import some!</p>";
}

require_once 'includes/footer.php';
?>