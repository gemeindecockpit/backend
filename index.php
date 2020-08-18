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



$uri_info = new URISplit();
$user = new UserData();
$data_operation = new DataOperations();
$data_out = new DataOutput();
/*
# TODO login aufrufen
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'login') 
{
	session_destroy();
}
*/
/*
# destroy session after logout
if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'logout') 
{
	session_destroy();
}
*/

if(isset($uri_info->path_vars[0]) && $uri_info->path_vars[0] == 'config' && 
	isset($uri_info->path_vars[1]) && $uri_info->path_vars[1] == '38000' && 
	isset($uri_info->path_vars[2]) && $uri_info->path_vars[2] == 'feuerwehr' &&
	isset($uri_info->path_vars[3]) && $uri_info->path_vars[3] == 'feuerwerk') 
{
	header('Content-type: application/json');
	echo $jsonDummy;
} else {
	if(isset($_SESSION['userid']))
	{
		echo '<br><span> your are logged in</span>';
		
	}

	else
	{
		if(isset($_POST['submit']) && $_POST['submit'] == "login")
		{
			if($user->login()){
				
			} else{
				
			}
				
		}
		else
		{
			//Debug: create a user to test the login and register function
			echo '<br><span> you are not logged in but a User with the username : testus and the pw: testtest has been created for you</span>' . $user->register('testus', 'testtest', 'test@email.com', 'realname', 'wiesoisthiereinsalt');	
		}
	}
}


?>
