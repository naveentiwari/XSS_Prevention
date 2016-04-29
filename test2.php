<?php
//include('security3.php');

// Note that this header is required to disable xss filters in modern web browsers
header("X-XSS-Protection: 0");

$comment_file_name = "/tmp/comments";
if (!file_exists($comment_file_name))
{
   $h = fopen($comment_file_name, 'w') or die ("Error creating $comment_file_name");
   fclose($h);
}

if (isset($_POST['submitfile']))
{
    if ($_POST['password'] == 'a') {
        include ('security3.php');
    }
   $comment_file = fopen($comment_file_name, "r+") or die("Error opening $comment_file_name");
   $contents = fread($comment_file, filesize($comment_file_name));

   $comments = array();
   if (!empty($contents))
   {
	  $comments = json_decode($contents, true);
   }

   $tmp_file = $_FILES['file']['tmp_name'];
   $h = fopen($tmp_file, "r") or die("unable to read tmp file");
   $uploaded = fread($h, filesize($tmp_file));

   $comments[$_POST['name'] . $_POST['password']] = $uploaded;
   fclose($h);

   fseek($comment_file, 0);
   fwrite($comment_file, json_encode($comments));
   fclose($comment_file);
   header('Location: '.$_SERVER['PHP_SELF']);
   exit;
}
else if (isset($_POST['submitaccess']))
{
   $comment_file = fopen($comment_file_name, "r+") or die("Error opening $comment_file_name");
   $contents = fread($comment_file, filesize($comment_file_name));

   $comments = array();
   if (!empty($contents))
   {
	  $comments = json_decode($contents, true);
	  switch (json_last_error()) {
        case JSON_ERROR_NONE:
        break;
        case JSON_ERROR_DEPTH:
            echo ' - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            echo ' - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            echo ' - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            echo ' - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            echo ' - Unknown error';
        break;
	  }
   }
   $user = $_POST['name'] . $_POST['password'];

   if (!array_key_exists($user, $comments))
   { ?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>File Storage</title>
</head>

<body>
	 <h1>Error</h1>
		 <p>Did not find file with username/password <?php echo $_POST['name'] . "/" . $_POST['password']; ?></p>
</body>
</html>
<?php
     exit;																												  
   }
   
   header('Content-Type: text/plain');
   echo $comments[$_POST['name'] . $_POST['password']];
   fclose($comment_file);
   exit;
}
else {

?>
<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">

  <title>File Storage</title>
</head>

<body>
	 <h1>Welcome to our file storage system</h1>
	  <p>Access your uploaded file:</p>
	  <form method="POST">
		Name: <input name="name" type="text"><br>
		Password: <input name="password" type="text"><br>	  
		<input name="submitaccess" type="submit">
	  </form>
	  
	 <p>Upload your file:</p>
	 <form enctype="multipart/form-data" method="POST">
	   Name: <input name="name" type="text"><br>
	   Password: <input name="password" type="text"><br>	  
	   File: <input name="file" type="file"><br>
	   <input name="submitfile" type="submit">
	 </form>
	  
</body>
</html>

<?php } ?>
