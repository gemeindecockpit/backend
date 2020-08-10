<?php
session_start();
require(__DIR__ . '/db_connect.php');
if(isset($_SESSION['user_id'])) {
	foreach ( $conn->query('SELECT * FROM users') as $row ) {
		print_r($row);//echo "{$row['field']}";
	}
	$result = mysqli_query($conn, "SELECT * FROM users");
	$myJson = json_encode($result);
	echo $myJson;
} else {
	printf("Sorry you are not logged in.");
}
?>