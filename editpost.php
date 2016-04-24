
<?php //include config
require_once('login_config.php');
require_once('utils.php');

//if not logged in redirect to login page
if(!$user_info->is_user_logged_in()){ header('Location: login.php'); }

$stmt = $db->prepare('SELECT blogID, userID, title, intro, content, postDate FROM blog_entries NATURAL JOIN user_info WHERE blogID=:blogID');
$stmt->execute(array(':blogID' => $_GET['id']));
$row = $stmt->fetch();
$content = explode (PHP_EOL, $row['content']);

//if post does not exists redirect user.
if($row['blogID'] == '' || $row['userID'] != $_SESSION['userid']){
	header('Location: index.php');
	exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Post</title>
</head>
<body>

<div id="wrapper">

	<h2>Edit Post</h2>

	<?php

	//if form has been submitted process it
	if(isset($_POST['submit'])){

		//very basic validation
		if(!isset($_POST['title']) || $_POST['title'] ==''){
			$error[] = 'Please enter the title.';
		}

		if(!isset($_POST['intro']) || $_POST['intro'] ==''){
			$error[] = 'Please enter the description.';
		}

		if(!isset($_POST['content']) || $_POST['content'] ==''){
			$error[] = 'Please enter the content.';
		}

        if ($row['userID']  != $_SESSION['userid']) {
            $error[] = 'You cannot edit this post, you can edit only things you posted';
        }

		if(!isset($error)){

			try {

				//insert into database

				$stmt = $db->prepare('UPDATE blog_entries SET title=:title, intro=:intro, content=:content, postDate=:postDate where blogID=:blogID') ;
				$stmt->execute(array(
					':title'     => $_POST['title'],
					':intro'     => $_POST['intro'],
					':content'   => htmlspecialchars($_POST['content'], ENT_QUOTES),
                    ':blogID'    => $row['blogID'],
					':postDate'  => date('Y-m-d H:i:s')
				));

				//redirect to index page
				header('Location: index.php?action=added');
				exit;

			} catch(PDOException $e) {
			    echo $e->getMessage();
			}
		}
	}

	//check for any errors
	if(isset($error)){
		foreach($error as $error){
			echo '<p class="error">'.$error.'</p>';
		}
	}
	?>

	<form action='' method='post'>
    <hr/>

		<p><label>Title</label><br />
		<textarea name='title' cols='100' rows='2'><?php echo $row['title']; ?></textarea></p>

		<p><label>Description</label><br />
		<textarea name='intro' cols='100' rows='10'><?php echo $row['intro']; ?></textarea></p>

		<p><label>Content</label><br />
		<textarea name='content' cols='100' rows='10'><?php foreach ($content as $line){echo $line; if($line == '')echo '<BR/>';} ?></textarea></p>

		<p><input type='submit' name='submit' value='Submit'></p>

	</form>

</div>

