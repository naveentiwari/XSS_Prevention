<?php
include('security.php');

$dbhost = 'localhost:8889';
$dbuser = 'root';
$dbpass = 'navii';
$conn = mysql_connect($dbhost, $dbuser, $dbpass);
if(! $conn ) { die('Could not connect: ' . mysql_error()); }

$sql = "SELECT * FROM user_info";

mysql_select_db('blogdb');
$result = mysql_query( $sql, $conn );
if(! $result ) { die('Could not enter data: ' . mysql_error()); }

while ($row = mysql_fetch_array($result, MYSQL_BOTH)) {
    print_r($row);
}

mysql_free_result($result);

mysql_close($conn);
?>
