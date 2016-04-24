<?php

/*****************************************
 * Configuration of the database and webserver
 *****************************************/
ob_start();
session_start();

//database credentials
define('DBHOST', 'localhost');
define('DBUSER', 'root');
define('DBPASS', 'navii');
define('DBNAME', 'blogdb');
//define('DBPORT', '9001');

$db = new PDO("mysql:host=".DBHOST.";port=8889;dbname=".DBNAME, DBUSER, DBPASS);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

date_default_timezone_set('America/Phoenix');

class Login {
    private $db;
	
	function __construct($db){
		$this->_db = $db;
	}

    /* Password should be at least 8 characters long
     * and it should be made up of 'a' - 'z' (both cap
     * and small) and numbers
     */
    public function validate_raw_passwd($password) {
        if (strlen($password) < 8)
            return 0;
        return Login::validate_username($password);
    }

    public function validate_username($username) {
        $retval = preg_match('/(\w+)/', $username, $matches);

        if ($retval == false && $retval == 0) {
            return 0;
        } else if ($username == $matches[0]) {
            return 1;
        } else {
            return 0;
        }
    }

    private function get_passwd_hash($password) {
        return  password_hash($password, PASSWORD_BCRYPT);
    }

	private function get_user_login_info ($username){	
		try {
			$stmt = $this->_db->prepare('SELECT shpasswd, userID, fname, lname FROM user_info WHERE username = :username');
			$stmt->execute(array('username' => $username));
			
			$row = $stmt->fetch();
			return array(true, $row['shpasswd'], $row['userID'], $row['fname'], $row['lname']);

		} catch(PDOException $e) {
            return array(false, $e->getMessage(), -1, '', '');
		}
	}

	public function is_user_logged_in(){
		if(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == true){
			return true;
		}		
	}

	public function logout(){
		session_destroy();
	}

	public function login($username,$password){	

		$username = trim($username);
		$password = trim($password);

        if ($this->validate_username($username) == 0 || $this->validate_raw_passwd($password) == 0)
            return array(false, 'Invalid Username or password');

		list($retval, $hashed, $userid, $fname, $lname) = $this->get_user_login_info($username);

        if ($retval == false)
            return array(false, $hashed); /*if 1st argument is false then 2nd argument returned is error message*/
		
		if(password_verify($password,$hashed) == 1) {
		    
		    $_SESSION['loggedin'] = true;
            $_SESSION['userid'] = $userid;
            $_SESSION['username'] = $username;
            $_SESSION['firstname'] = $fname;//Util::santizeUserInput($fname);
            $_SESSION['lastname'] =  $lname;//Util::santizeUserInput($lname);
		    return array(true, "Login Success");
		} else {
            return array(false, "Incorrect username or password");
        }

        return array(false, "Unknown Error");/*Unreachable section of code*/
	}

	public function changePasswd($oldpasswd,$newpasswd,$cnewpasswd){	

		$oldpassword    = trim($oldpasswd);
		$newpassword    = trim($newpasswd);
        $cnewpassword   = trim($cnewpasswd);

        if ($newpassword != $cnewpassword) {
            return array(false, 'New passwords do not match');
        }

		list($retval, $hashed, $userid, $fname, $lname) = $this->get_user_login_info($_SESSION['username']);

        if ($retval == false)
            return array(false, $hashed); /*if 1st argument is false then 2nd argument returned is error message*/
		
		if(password_verify($oldpassword,$hashed) == 1) {

            $hashedpassword = password_hash($newpassword, PASSWORD_BCRYPT);

            try {
                $stmt = $this->_db->prepare('UPDATE user_info SET shpasswd=:shpasswd where userid=:userid');
                $stmt->execute(array(
                    ':shpasswd' => $hashedpassword,
                    ':userid'   => $_SESSION['userid']
                    ));
                return array(true, 'Password Changed Successfully');
            } catch (PDOException $e) {
                return array(false, $e->getMessage());
            }

		    return array(true, "Password changed successfully");
		} else {
            return array(false, "Incorrect current password!!");
        }

        return array(false, "Unknown Error");/*Unreachable section of code*/
	}
}

$user_info = new Login($db);
?>
