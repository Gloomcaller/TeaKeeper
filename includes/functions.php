<?php
function validateDateFilter($date)
{
    return filter_var($date, FILTER_VALIDATE_REGEXP, [
        'options' => ['regexp' => '/^\d{4}-\d{2}-\d{2}$/']
    ]);
}
function sanitizeInput($text)
{
    $text = trim($text);
    $text = stripslashes($text);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    return $text;
}
function getTeaCount($mysqli)
{
    $result = $mysqli->query("SELECT COUNT(*) as count FROM teas");
    $row = $result->fetch_assoc();
    return $row['count'];
}
function formatDateNice($date)
{
    return date('j M Y', strtotime($date));
}
function tableExists($mysqli, $table_name)
{
    $result = $mysqli->query("SHOW TABLES LIKE '$table_name'");
    return $result->num_rows > 0;
}
function showMessage($text, $type = 'info')
{
    return "<div class='message-box message-$type'>$text</div>";
}
?>