<div class="container">
	<h2>Example: Login and Registration Script with PHP, MySQL</h2>		
	<div class="collapse navbar-collapse" >
		<ul class="nav navbar-nav navbar-left">
			<?php if (isset($_SESSION['user_id'])) { ?>
			<li><p class="navbar-text"><strong>Welcome!</strong> You're signed in as <strong><?php echo $_SESSION['user_name']; ?></strong></p></li>
			<li><a href="logout.php">Log Out</a></li>
			<?php } else { ?>
			<li><a href="login.php">Login</a></li>
			<li><a href="register.php">Sign Up</a></li>
			<?php } ?>
		</ul>
	</div>	
</div>	
