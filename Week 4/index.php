<?php
session_start();
require_once "pdo.php";
require_once "util.php";
?>
<!DOCTYPE html>
<html>
<head>
<title>Merle's Resume Registry</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Merle's Resume Registry</h1>
<?php
flashMessages();
if ( !isset($_SESSION['name']) || !isset($_SESSION['user_id']) ) {
    echo '<p><a href="login.php">Please log in</a></p>'."\n";
} else {
    echo '<p><a href="logout.php">Logout</a></p>'."\n";
}
$stmt = $pdo->query("SELECT * FROM profile");
if ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo('<table border="1">'."\n");
    echo '<tr><th>Name</th><th>Headline</th>'.( isset($_SESSION['name']) && isset($_SESSION['user_id']) ? '<th>Action</th>' : '')."<tr>\n";
    do {
        echo '<tr><td>'."\n";
        echo '<a href="view.php?profile_id='.htmlentities($row['profile_id']).'">'.htmlentities($row['first_name']).' '.htmlentities($row['last_name']).'</a></td><td>'."\n";
        echo htmlentities($row['headline']).'</td>';
        echo( isset($_SESSION['name']) && $_SESSION['user_id'] == $row['user_id'] ? ('<td>'."\n".'<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> <a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a></td></tr>'."\n") : "</tr>\n");
    } while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) );
    echo('</table>'."\n");
}
echo( isset($_SESSION['name']) && isset($_SESSION['user_id']) ? '<p><a href="add.php">Add New Entry</a></p>'."\n" : '');
?>
</div>
</body>

