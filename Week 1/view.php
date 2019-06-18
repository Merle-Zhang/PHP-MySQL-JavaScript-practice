<?php
session_start();

require_once "pdo.php";

?>
<!DOCTYPE html>
<html>
<head>
<title>Merle's Profile View</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Profile information</h1>
<?php
$stmt = $pdo->prepare("SELECT * FROM profile WHERE profile_id = :profile_id");
$stmt->execute(array( ':profile_id' => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row !== false ) {
    echo '<p>First Name:'."\n";
    echo $row['first_name'].'</p>'."\n";
    echo '<p>Last Name:'."\n";
    echo $row['last_name'].'</p>'."\n";
    echo '<p>Email:'."\n";
    echo $row['email'].'</p>'."\n";
    echo '<p>Headline:<br/>'."\n";
    echo $row['headline'].'</p>'."\n";
    echo '<p>Summary:<br/>'."\n";
    echo $row['summary'].'<p>'."\n";
    echo '</p>'."\n";
}
?>
<a href="index.php">Done</a>
</div>
</body>
</html>
