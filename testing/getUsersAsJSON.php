<?php
session_start();
require(__DIR__ . '/db_connect.php');
if(isset($_SESSION['user_id'])) {
	foreach ( $conn->query('SELECT * FROM users') as $row ) {
		print_r($row);//echo "{$row['field']}";
	}
	//mysqli_fetch_assoc — Fetch a result row as an associative array
	$sth = mysqli_query($conn, "SELECT * FROM users");
	$rows = array();
	while($r = mysqli_fetch_assoc($sth)) {
		$rows[] = $r;
	}
	print json_encode($rows);
} else {
	printf("Sorry you are not logged in.");
}
?>