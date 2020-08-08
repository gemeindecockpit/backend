<div class="container">	
	<div class="row">
		<div class="col-md-4 col-md-offset-4 well">
			<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="loginform">
				<fieldset>
					<legend>Login</legend>						
					<div class="form-group">
						<label for="name">Email</label>
						<input type="text" name="email" placeholder="Your Email" required class="form-control" />
					</div>	
					<div class="form-group">
						<label for="name">Password</label>
						<input type="password" name="password" placeholder="Your Password" required class="form-control" />
					</div>	
					<div class="form-group">
						<input type="submit" name="login" value="Login" class="btn btn-primary" />
					</div>
				</fieldset>
			</form>
			<span class="text-danger"><?php if (isset($error_message)) { echo $error_message; } ?></span>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4 col-md-offset-4 text-center">	
		New User? <a href="register.php">Sign Up Here</a>
		</div>
	</div>	
</div>
<?php
session_start();
require(__DIR__ . '/db_connect.php');

$filename = __DIR__ . '/db_connect.php';

if (file_exists($filename)) {
    echo "Die Datei $filename existiert";
} else {
    echo "Die Datei $filename existiert nicht";
}
if(isset($_SESSION['user_id'])!="") {
	header("Location: litwinow.xyz");
	echo "is logged in";
} else {
	echo "is not logged in";
}

if (isset($_POST['login'])) {
	echo "login post received";
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = mysqli_real_escape_string($conn, $_POST['password']);
	echo "\npw is : " . hash('sha256', $password);
	$result = mysqli_query($conn, "SELECT * FROM users WHERE email = '" . $email. "' and pass = '" . hash('sha256', $password). "'");
	if ($row = mysqli_fetch_array($result)) {
		$_SESSION['user_id'] = $row['uid'];
		$_SESSION['user_name'] = $row['user'];
		header("Location: litwinow.xyz");
	} else {
		$error_message = "Incorrect Email or Password!!!";
		echo "\n" . $error_message;
	}
}
?>