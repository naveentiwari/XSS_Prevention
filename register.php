<?php
require_once('login_config.php');
require_once('utils.php');
include ('security.php');
//check if already logged in
if( $user_info->is_user_logged_in() ){ header('Location: index.php'); } 
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>New user registration</title>
</head>
<body>

<div id="wrapper">

	<h2>User Info</h2>

	<?php

	//if form has been submitted process it
	if(isset($_POST['submit'])){
        ValidateInput('adduser', $_POST);   // to be implemented
        list($ret, $error) = Util::addNewUser($_POST, $db);

            if ($ret) {
                extract($_POST);
                 $user_info->login($username, $password);

                header('Location: index.php?action=added');
                exit;
            } else {
                foreach($error as $error){
                    echo Util::getErrorPara($error);
                }
                exit;
            }
	}

	if(isset($error) && !$ret){
		foreach($error as $error){
			echo '<p class="error">'.$error.'</p>';
		}
	}
	?>

	<form name='adduser' action='' method='post'>

		<p><label>Username</label><br />
		<input type='text' name='username' value=''></p>
		<p><label>First Name</label><br />
		<input type='text' name='fname' value=''></p>
		<p><label>Last Name</label><br />
		<input type='text' name='lname' value=''></p>

		<p><label>Password</label><br />
		<input type='password' name='password' value=''></p>

		<p><label>Confirm Password</label><br />
		<input type='password' name='passwordConfirm' value=''></p>

		<p><label>Email</label><br />
		<input type='text' name='email' value=''></p>

		<p><input type='submit' name='submit' value='Add User'></p>

	</form>

</div>
</body>
</html>
