<?php
session_start();

require_once "pdo.php";

if ( ! isset($_SESSION['name']) || ! isset($_SESSION['user_id'] ) ) {
    die('Not logged in');
}

if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
    $sql = "DELETE FROM profile WHERE profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':profile_id' => $_POST['profile_id']));
    $_SESSION['success'] = 'Profile deleted';
    header( 'Location: index.php' ) ;
    return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :profile_id");
$stmt->execute(array(":profile_id" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Bad value for id';
    header( 'Location: index.php' ) ;
    return;
} elseif ( $row['user_id'] !== $_SESSION['user_id'] ) {
    $_SESSION['error'] = 'No right to edit this profile';
    header( 'Location: index.php' ) ;
    return;
}
$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$profile_id = $row['profile_id'];
?>
<!DOCTYPE html>
<html>
<head>
<title>Merle's Deleting...</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Deleteing Profile</h1>
<form method="post" action="delete.php">
<p>First Name:
<?= $fn ?></p>
<p>Last Name:
<?= $ln ?></p>
<input type="hidden" name="profile_id"
value="<?= $profile_id ?>"
/>
<input type="submit" name="delete" value="Delete">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
</div>
</body>
</html>
