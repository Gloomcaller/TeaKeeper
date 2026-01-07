<?php
require_once 'includes/database.php';
require_once 'includes/header.php';

echo "<h2>Tea Calendar</h2>";

try {
    $pdo_stmt = $pdo->prepare("SELECT drink_date, COUNT(*) as tea_count FROM teas GROUP BY drink_date");
    $pdo_stmt->execute();
    $tea_days = $pdo_stmt->fetchAll(PDO::FETCH_ASSOC);

    $tea_counts = [];
    foreach ($tea_days as $day) {
        $tea_counts[$day['drink_date']] = $day['tea_count'];
    }

    for ($month = 1; $month <= 12; $month++) {
        echo "<div class='cal-month'>";
        echo "<h4>" . date('F', mktime(0, 0, 0, $month, 1, 2025)) . "</h4>";

        echo "<div class='cal-grid'>";
        $days = ['M', 'T', 'W', 'T', 'F', 'S', 'S'];
        foreach ($days as $day) {
            echo "<div><small><b>$day</b></small></div>";
        }

        $first_day = date('N', strtotime("2025-$month-01"));
        for ($i = 1; $i < $first_day; $i++) {
            echo "<div class='cal-day'></div>";
        }

        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, 2025);
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf("2025-%02d-%02d", $month, $day);
            $tea_count = $tea_counts[$date] ?? 0;

            $class = 'cal-day';
            if ($tea_count >= 3)
                $class .= ' tea-3';
            elseif ($tea_count == 2)
                $class .= ' tea-2';
            elseif ($tea_count == 1)
                $class .= ' tea-1';

            echo "<div class='$class' title='$date: $tea_count teas'>";
            echo $day;
            if ($tea_count > 0) {
                echo "<span class='cal-count'>$tea_count</span>";
            }
            echo "</div>";
        }
        echo "</div></div>";
    }
    echo "<div style='clear:both;'></div>";

    echo "<b>Legend:</b> ";
    echo "<span><span class='legend-color' style='background:#f0f0f0;'></span>0</span> ";
    echo "<span><span class='legend-color' style='background:#CDDC39;'></span>1</span> ";
    echo "<span><span class='legend-color' style='background:#8BC34A;'></span>2</span> ";
    echo "<span><span class='legend-color' style='background:#4CAF50;'></span>3+</span>";
    echo "</div>";

    echo "<h3>Stats</h3>";

    $stats = $mysqli->query("
        SELECT COUNT(DISTINCT drink_date) as tea_days, COUNT(*) as total_teas
        FROM teas
    ");

    if ($row = $stats->fetch_assoc()) {
        echo "<p>Tea days: " . $row['tea_days'] . "</p>";
        echo "<p>Total teas: " . $row['total_teas'] . "</p>";
    }

    echo "</div>";

} catch (PDOException $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}

require_once 'includes/footer.php';
?>