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

#-- config --
require_once("inc/config.php");

#-- classes --
require_once("lib/class-urisplit.php");
require_once("lib/class-user.php");
require_once("lib/class-dataoutput.php");
require_once("lib/class-dataoperations.php");


#Informations about servers and methods
$my_hostname = $_SERVER['HTTP_HOST'];
$my_uri = $_SERVER['REQUEST_URI'];
$my_method = $_SERVER['REQUEST_METHOD'];


session_start();
if (isset($_SESSION['userid'])){
	echo '<br><span> your are logged in</span>';
} else {
	echo '<br><span> your are not logged in</span>';
}


$uri_info = new URISplit();
$user = new UserData();
$data_operation = new DataOperations();
$data_out = new DataOutput();

# destroy session after logout
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'login') 
{
	session_destroy();
}

# destroy session after logout
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'logout') 
{
	session_destroy();
}


else
{
	if(isset($_SESSION['userid']))
	{
		
		
	}

	else
	{
		if($_POST['submit'] == "login")
		{
			if($user->login()){
				
			} else{
				
			}
				
		}
		else
		{
				
		}
	}
}


?>
