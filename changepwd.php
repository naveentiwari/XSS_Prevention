<?php
require_once('login_config.php');
require_once('utils.php');

if( !$user_info->is_user_logged_in() ){ header('Location: login.php'); } 
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Change Password</title>
</head>
<body>

<div id="chpwd">

	<?php
	if(isset($_POST['submit'])){

        extract($_POST);

        list($ret, $info) = $user_info->changePasswd($oldpassword,$newpassword,$cnewpassword);

        if ($ret) {
			header('Location: index.php');
			exit;
		} else
			echo Util::getErrorPara($info);
	}
    ?>

	<form action="" method="post">
	<p><label>Old Password</label><BR/><input type="password" name="oldpassword" value=""  /></p>
	<p><label>New Password</label><BR/><input type="password" name="newpassword" value=""  /></p>
	<p><label>Confirm New Password</label><BR/><input type="password" name="cnewpassword" value=""  /></p>
	<p><label></label><input type="submit" name="submit" value="Update Password"  /></p>
	</form>

</div>
</body>
</html>
