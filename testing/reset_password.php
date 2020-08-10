<div class="container">
	<div class="row">
		<div class="col-md-4 col-md-offset-4 well">
			<form role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="signupform">
				<fieldset>
					<legend>Reset Password</legend>
					<div class="form-group">
						<label for="name">Password</label>
						<input type="password" name="password" placeholder="Password" required class="form-control" />
						<span class="text-danger"><?php if (isset($password_error)) echo $password_error; ?></span>
					</div>
					<div class="form-group">
						<label for="name">Confirm Password</label>
						<input type="password" name="cpassword" placeholder="Confirm Password" required class="form-control" />
						<span class="text-danger"><?php if (isset($cpassword_error)) echo $cpassword_error; ?></span>
					</div>
					<div class="form-group">
						<input type="submit" name="resetpw" value="Reset Password" class="btn btn-primary" />
					</div>
				</fieldset>
			</form>
			<span class="text-success"><?php if (isset($success_message)) { echo $success_message; } ?></span>
			<span class="text-danger"><?php if (isset($error_message)) { echo $error_message; } ?></span>
		</div>
	</div>
</div>

<?php
session_start();
require(__DIR__ . '/db_connect.php');

if(isset($_SESSION['user_id'])) {
	$password = mysqli_real_escape_string($conn, $_POST['password']);
	$cpassword = mysqli_real_escape_string($conn, $_POST['cpassword']);	
	$error = false;
	if(strlen($password) < 6) {
		$error = true;
		$password_error = "Password must be minimum of 6 characters";
		echo "\n" . $password_error;
	}
	if($password != $cpassword) {
		$error = true;
		$cpassword_error = "Password and Confirm Password doesn't match";
		echo "\n" . $cpassword_error;
	}
	if (!$error) {
		if(mysqli_query($conn, "UPDATE users SET pass = '" . hash('sha256', $password . SALT) .  "' WHERE uid = " . $_SESSION['user_id'])) {
			$success_message = "\nSuccessfully changed password! <a href='index.php'>Click here to go back to the main page</a>";
			echo '<br>' . $success_message;
		} else {
			$error_message = "Error in registering...Please try again later!";
			echo '<br>' . $error_message;
		}
	}
} else {
	printf("Sorry you are not logged in.");
}
?>