<?php
require_once 'includes/database.php';
require_once 'includes/header.php';
require_once 'classes/TeaAnalyzer.class.php';

echo "<h2>Tea Statistics</h2>";

$result = $mysqli->query("SELECT * FROM teas ORDER BY drink_date");
$teas_data = [];
while ($row = $result->fetch_assoc()) {
    $teas_data[] = $row;
}

$analyzer = new TeaAnalyzer($teas_data, 2025);

$brand_stats = $analyzer->getBrandStats();
$flavor_stats = $analyzer->getFlavorStats();
$averages = $analyzer->getDailyAverages();
$most_popular = $analyzer->getMostPopularDay();
$monthly_stats = $analyzer->getMonthlyStats();
$top_brands = $analyzer->getTopBrands(5);
?>

<h3>Overall Statistics</h3>
<p>Total teas: <?php echo $averages['total_teas']; ?></p>
<p>Days with tea: <?php echo $averages['total_days']; ?></p>
<p>Average teas per tea day: <?php echo $averages['avg_per_day']; ?></p>

<?php if ($most_popular): ?>
    <p>Most teas in one day: <?php echo $most_popular['count']; ?> on <?php echo $most_popular['date']; ?></p>
<?php endif; ?>

<h3>Top 5 Brands</h3>
<table>
    <tr>
        <th>Brand</th>
        <th>Teas</th>
    </tr>
    <?php foreach ($top_brands as $brand => $count): ?>
        <tr>
            <td><?php echo htmlspecialchars($brand); ?></td>
            <td><?php echo $count; ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h3>Monthly Distribution</h3>
<table>
    <tr>
        <th>Month</th>
        <th>Teas</th>
    </tr>
    <?php for ($month = 1; $month <= 12; $month++): ?>
        <tr>
            <td><?php echo date('F', mktime(0, 0, 0, $month, 1)); ?></td>
            <td><?php echo $monthly_stats[$month]; ?></td>
        </tr>
    <?php endfor; ?>
</table>

<h3>Flavor Categories</h3>
<table>
    <tr>
        <th>Flavor</th>
        <th>Count</th>
    </tr>
    <?php $counter = 0; ?>
    <?php foreach ($flavor_stats as $flavor => $count): ?>
        <?php if ($counter++ < 10): ?>
            <tr>
                <td><?php echo htmlspecialchars($flavor); ?></td>
                <td><?php echo $count; ?></td>
            </tr>
        <?php endif; ?>
    <?php endforeach; ?>
</table>

<?php
require_once 'includes/footer.php';
?>