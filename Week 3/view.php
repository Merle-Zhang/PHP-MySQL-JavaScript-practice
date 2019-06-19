<?php
session_start();

require_once "pdo.php";
require_once "util.php";    

$positions = loadPos($pdo, $_REQUEST['profile_id']);

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
    echo htmlentities($row['first_name']).'</p>'."\n";
    echo '<p>Last Name:'."\n";
    echo htmlentities($row['last_name']).'</p>'."\n";
    echo '<p>Email:'."\n";
    echo htmlentities($row['email']).'</p>'."\n";
    echo '<p>Headline:<br/>'."\n";
    echo htmlentities($row['headline']).'</p>'."\n";
    echo '<p>Summary:<br/>'."\n";
    echo htmlentities($row['summary']).'<p>'."\n";
    echo '<p>Position</p><ul>'."\n";
    foreach( $positions as $position ) {
        echo '<li>'.htmlentities($position['year']).': '.htmlentities($position['description']).'</li>'."\n";
    }
    echo '</ul>';
    echo '</p>'."\n";
}
?>
<a href="index.php">Done</a>
</div>
</body>
</html>
