<?php
require_once('login_config.php');
require_once('utils.php');

// if the user is logged in take him to index page
if( $user_info->is_user_logged_in() ){ header('Location: index.php'); } 
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login</title>
</head>
<body>

<div id="login">

	<?php
	if(isset($_POST['submit'])){

        extract($_POST);

        list($ret, $info) = $user_info->login($username,$password);

        if ($ret) {
			header('Location: index.php');
			exit;
		} else
			echo Util::getErrorPara($info);
	}
    ?>

	<form action="" method="post">
	<p><label>Username</label><input type="text" name="username" value=""  /></p>
	<p><label>Password</label><input type="password" name="password" value=""  /></p>
	<p><label></label><input type="submit" name="submit" value="Login"  /></p>
	</form>

</div>
</body>
</html>
