<?php //include config
require_once('login_config.php');
require_once('utils.php');

//if not logged in redirect to login page
if(!$user_info->is_user_logged_in()){ header('Location: login.php'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Add Post</title>
</head>
<body>

<div id="wrapper">

	<h2>Add Post</h2>

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

		if(!isset($error)){

			try {

				//insert into database

				$stmt = $db->prepare('INSERT INTO blog_entries (userID,title,intro,content,postDate) VALUES (:userID, :title, :intro, :content, :postDate)') ;
				$stmt->execute(array(
                    ':userID'    => $_SESSION['userid'],
					':title'     => $_POST['title'],
					':intro'     => $_POST['intro'],
					':content'   => $_POST['content'],
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

		<p><label>Title</label><br />
		<textarea name='title' cols='60' rows='2'>My First Blog</textarea></p>

		<p><label>Description</label><br />
		<textarea name='intro' cols='60' rows='10'>Yes this is my first blog</textarea></p>

		<p><label>Content</label><br />
		<textarea name='content' cols='60' rows='10'>Hell Yes, This is my first blog.</textarea></p>

		<p><input type='submit' name='submit' value='Submit'></p>

	</form>

</div>

