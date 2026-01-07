</body>
<footer>
    <hr>
    <p>TeaKeeper - Simple Tea Tracker</p>
    <?php
    $result = $mysqli->query("SELECT COUNT(*) as total FROM teas");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "<p>Teas in database: " . $row['total'] . "</p>";
    }
    ?>
</footer>

</html>