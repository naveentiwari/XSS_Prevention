<?php
require_once('login_config.php');
include('security.php');
class Util {
    public function getErrorPara ($message) {
        $para = sprintf('<p class=\"error\">%s</p>', $message);
        return $para;
    }

    public function writeContentInLines () {
        foreach ($content as $line) {
            echo $line;
            echo '<BR>';
        }
    }

    private function validateNewUserInfo($POST) {
        $ret = true;

		//very basic validation
		if(!isset($POST['username']) || $POST['username'] == '') {
			$error[] = 'Please enter the username.';
            $ret     = false;
        } elseif (Login::validate_username($POST['username']) == 0) {
            $error[] = 'Invalid Username. Username can contain only characters a-z and numbers';
            $ret     = false;
        }

		if(!isset($POST['password']) || $POST['password'] == '') {
			$error[] = 'Please enter the password.';
            $ret     = false;
        } else if (Login::validate_raw_passwd($POST['password']) == 0) {
            $error[] = 'Invalid password. Password should be atleast 8 characters long and can contains a-z and numbers';
            $ret     = false;
        }

		if(!isset($POST['passwordConfirm']) || $POST['passwordConfirm'] == '') {
			$error[] = 'Please confirm the password.';
            $ret     = false;
        }

		if ((isset($POST['password']) && isset($POST['passwordConfirm'])) && ($POST['password'] != $POST['passwordConfirm'])) {
			$error[] = 'Passwords do not match.';
            $ret     = false;
        }

		if(!isset($POST['email']) || $POST['email'] == '') {
			$error[] = 'Please enter the email address.';
            $ret     = false;
        }

        return array($ret, $error);
    }

    public function addNewUser ($POST, $tgtdb) {
        list($ret, $error) = Util::validateNewUserInfo($POST);

        echo 'Pos1 - In addNewUser';
        if (!$ret)
            return array($ret, $error);

        $hashedpassword = password_hash($POST['password'], PASSWORD_BCRYPT);

        try {

            //insert into database
            // $stmt = $tgtdb->prepare('INSERT INTO user_info (username,shpasswd,fname,lname,email) VALUES (:username, :shpasswd, :fname, :lname, :email)') ;
            // $stmt->execute(array(
            //     ':username' => $POST['username'],
            //     ':shpasswd' => $hashedpassword,
            //     ':fname'    => $POST['fname'],
            //     ':lname'    => $POST['lname'],
            //     ':email'    => $POST['email']
            // ));

            $dbhost = 'localhost:8889';
            $dbuser = 'root';
            $dbpass = 'navii';
            $conn = mysql_connect($dbhost, $dbuser, $dbpass);
            if(! $conn ) { die('Could not connect: ' . mysql_error()); }

            $username = $POST['username'];
            $fname    = $POST['fname'];
            $lname    = $POST['lname'];
            $email    = $POST['email'];

            $sql = "INSERT INTO user_info  (username,shpasswd,fname,lname,email) VALUES ".
                   "('$username','$hashedpassword', '$fname', '$lname','$email')";

            mysql_select_db('blogdb');
            $retval = mysql_query( $sql, $conn );
            if(! $retval ) { die('Could not enter data: ' . mysql_error()); }
            mysql_close($conn);

            $smsg[] = '';
            return array(true, $smsg);

        } catch(PDOException $e) {
            $dberr[] = $e->getMessage();
            return array(false, $dberr);
        }
        return array($ret, $error);
    }
}
?>
