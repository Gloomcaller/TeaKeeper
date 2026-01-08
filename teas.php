<?php
require_once 'includes/database.php';
require_once 'includes/header.php';

echo "<h2>All Teas</h2>";

$editingId = isset($_GET['edit']) ? $_GET['edit'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    handleEditSave($mysqli);
    $editingId = null;
}

if (isset($_GET['delete'])) {
    $stmt = $mysqli->prepare("DELETE FROM teas WHERE id = ?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    echo "<p>Tea deleted!</p>";
}

$result = $mysqli->query("SELECT * FROM teas ORDER BY drink_date DESC");
$total = $result->num_rows;

echo "<p>Total teas: $total</p>";

if ($total > 0) {
    echo "<table>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Date</th>";
    echo "<th>Brand</th>";
    echo "<th>Name</th>";
    echo "<th>Flavor</th>";
    echo "<th>Actions</th>";
    echo "</tr>";

    while ($tea = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $tea['id'] . "</td>";

        if ($editingId == $tea['id']) {
            echo "<form method='POST'>";
            echo "<input type='hidden' name='id' value='" . $tea['id'] . "'>";

            echo "<td><input type='date' name='date' value='" . $tea['drink_date'] . "'></td>";
            echo "<td><input type='text' name='brand' value='" . htmlspecialchars($tea['brand']) . "'></td>";
            echo "<td><input type='text' name='name' value='" . htmlspecialchars($tea['tea_name']) . "'></td>";
            echo "<td><input type='text' name='flavor' value='" . htmlspecialchars($tea['flavor']) . "'></td>";

            echo "<td>";
            echo "<button class=\"ier-buttons\" type='submit' name='save'>Save</button>";
            echo "<a href='teas.php'><button class=\"ier-buttons\" type='button'>Cancel</button></a>";
            echo "</td>";

            echo "</form>";
        } else {
            echo "<td>" . $tea['drink_date'] . "</td>";
            echo "<td>" . htmlspecialchars($tea['brand']) . "</td>";
            echo "<td>" . htmlspecialchars($tea['tea_name']) . "</td>";
            echo "<td>" . htmlspecialchars($tea['flavor']) . "</td>";

            echo "<td>";
            echo "<form class=\"ier-buttons\" method='GET'>";
            echo "<input type='hidden' name='edit' value='" . $tea['id'] . "'>";
            echo "<button type='submit'>Edit</button>";
            echo "</form> ";

            echo "<form class=\"ier-buttons\" method='GET' onsubmit='return confirm(\"Delete this tea?\")'>";
            echo "<input type='hidden' name='delete' value='" . $tea['id'] . "'>";
            echo "<button type='submit'>Delete</button>";
            echo "</form>";
            echo "</td>";
        }

        echo "</tr>";
    }

    echo "</table>";
}

require_once 'includes/footer.php';
function handleEditSave($mysqli)
{
    $id = $_POST['id'];
    $date = $_POST['date'];
    $brand = $_POST['brand'];
    $name = $_POST['name'];
    $flavor = $_POST['flavor'];

    $mysqli->begin_transaction();

    try {
        $stmt = $mysqli->prepare("UPDATE teas SET drink_date=?, brand=?, tea_name=?, flavor=? WHERE id=?");
        $stmt->bind_param("ssssi", $date, $brand, $name, $flavor, $id);
        $stmt->execute();

        $action = "Updated tea #$id";
        $mysqli->query("INSERT INTO tea_audit (tea_id, action) VALUES ($id, '$action')");

        $mysqli->commit();
        echo "<p>Tea updated!</p>";

    } catch (Exception $e) {
        $mysqli->rollback();
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}