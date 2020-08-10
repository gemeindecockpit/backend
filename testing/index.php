<?php session_start(); 
//creates a Data URI scheme that can be used to display files in html.
function data_uri($file, $mime) 
{  
  $contents = file_get_contents($file);
  $base64   = base64_encode($contents); 
  return ('data:' . $mime . ';base64,' . $base64);
}
?>
?>
<div class="container">
	<h2>Testing the login and register script</h2>		
	<div class="collapse navbar-collapse" >
		<ul class="nav navbar-nav navbar-left">
			<?php if (isset($_SESSION['user_id'])) { ?>
			<li><p class="navbar-text"><strong>Welcome!</strong> You're signed in as <strong><?php echo $_SESSION['user_name']; ?></strong></p></li>
			<li> you can only see this picture if you are logged in.</li>
			<img src="<?php echo data_uri('/home/alx/testfiles/shrek.png','image/png'); ?>" </img>
			<li><a href="logout.php">Log Out</a></li>
			<?php } else { ?>
			<li>not logged in</li>
			<li><a href="login.php">Login</a></li>
			<li><a href="register.php">Sign Up</a></li>
			<?php } ?>
		</ul>
	</div>	
</div>	

