<?php
require_once('login_config.php');
require_once('utils.php');

$stmt = $db->prepare('SELECT fname, lname, email, title, intro, content, postDate from blog_entries NATURAL JOIN user_info');
$stmt->execute();
?>

<!doctype html>
<html>
<head><title>Index Page</title></head>
<body>
<h2><?php if( $user_info->is_user_logged_in() ){echo 'Hello '.$_SESSION['firstname'].' '.$_SESSION['lastname']; }else {echo 'Hello Guest!';} ?></h2>
<ul id='indexmenu'>
    <?php
    if (!$user_info->is_user_logged_in()) {
        echo '<li><a href=login.php>Login</a></li>';
        echo '<li><a href=register.php>Sign Up</a></li>';
    } else {
        echo '<li><a href=add_post.php>Write a new Blog</a></li>';
        echo '<li><a href=changepwd.php>Change Password</a></li>';
	    echo '<li><a href=logout.php>Logout</a></li>';
    }
    ?>
</ul>
<div class='clear'></div>
<hr />

<?php
$conn = mysql_connect('localhost', 'root', 'navii');
mysql_select_db('blogdb');
$result = mysql_query('SELECT blogID, fname, lname, email, title, intro, postDate from blog_entries NATURAL JOIN user_info', $conn);

if ($result == false) {
    die(mysql_error());
}

echo '<h1>Blogs</h1>';
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    echo '<div>';
        echo '<h2><a href=viewpost.php?id='.$row['blogID'].'>'.$row['title'].'</a></h2>';
        echo '<h4> Posted On - '.date('jS M Y',strtotime($row['postDate'])).' By - '.$row['fname'].' '.$row['lname'].' (email-'.$row['email'].')</h4>';
        echo '<p>'.$row['intro'].'</p>';
        echo '<p><a href=viewpost.php?id='.$row['blogID'].'>Read More</a></h2>';
    echo '</div>';
}
?>

</body>
</html>
