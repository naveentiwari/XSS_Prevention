<?php require('login_config.php'); 

$stmt = $db->prepare('SELECT blogID, userID, fname, lname, email, title, content, postDate FROM blog_entries NATURAL JOIN user_info WHERE blogID=:blogID');
$stmt->execute(array(':blogID' => $_GET['id']));
$row = $stmt->fetch();
$content = explode (PHP_EOL, $row['content']);

//if post does not exists redirect user.
if($row['blogID'] == ''){
	header('Location: ./');
	exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Blog - <?php echo $row['title'];?></title>
</head>
<body>

	<div id="wrapper">

		<p><a href="./">Home Page</a></p>


		<?php
            if (isset($_SESSION['userid']) && $row['userID'] == $_SESSION['userid'])
                echo '<p><a href=editpost.php?id='.$_GET['id'].'>edit</a></p>';
                echo '<hr />';
			echo '<div>';
				echo '<h1>'.$row['title'].'</h1>';
                echo '<h4> Posted On - '.date('jS M Y',strtotime($row['postDate'])).' By - '.$row['fname'].' '.$row['lname'].' (email-'.$row['email'].')</h4>';
				echo '<p>';
                foreach($content as $line) {
                    echo $line;
                    if ($line != '')
                        echo '<BR/>';
                }
                echo '</p>';				
			echo '</div>';
		?>

	</div>

</body>
</html>
