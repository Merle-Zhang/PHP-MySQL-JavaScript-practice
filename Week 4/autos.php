<?php
require_once "pdo.php";

if ( ! isset($_GET['name']) || strlen($_GET['name']) < 1  ) {
    die('Name parameter missing');
}

if ( isset($_POST['logout']) ) {
    header('Location: index.php');
    return;
}

$failure = false;
$notice = false;

if ( isset($_POST['make']) && isset($_POST['year']) && isset($_POST['mileage']) ) {
    if ( strlen($_POST['make']) < 1 ) {
        $failure = 'Make is required';
    } elseif ( !is_numeric($_POST['year']) || !is_numeric($_POST['mileage']) ) {
        $failure = "Mileage and year must be numeric";
    } else {
        $stmt = $pdo->prepare('INSERT INTO autos (make, year, mileage) VALUES ( :mk, :yr, :mi)');
        $stmt->execute(array(
            ':mk' => $_POST['make'],
            ':yr' => $_POST['year'],
            ':mi' => $_POST['mileage']));
        $notice = 'Record inserted';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Merle c0d447b7 's Automobile Tracker</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Tracking Autos for <?php echo(htmlentities($_REQUEST['name']));?></h1>
<?php
if ( $failure !== false ) {
    echo('<p style="color: red;">'.htmlentities($failure)."</p>\n");
}
if ( $notice !== false ) {
    echo('<p style="color: green;">'.htmlentities($notice)."</p>\n");
}
?>
<form method="post">
<p>Make:
<input type="text" name="make" size="60"/></p>
<p>Year:
<input type="text" name="year"/></p>
<p>Mileage:
<input type="text" name="mileage"/></p>
<input type="submit" value="Add">
<input type="submit" name="logout" value="Logout">
</form>

<h2>Automobiles</h2>
<ul>
<p>
<?php
$stmt = $pdo->query("SELECT * FROM autos");
while ( $row = $stmt->fetch(PDO::FETCH_ASSOC) ) {
    echo '<li><!-- '.($row['auto_id']-1).' -->';
    echo htmlentities($row['make'].' '.$row['year'].' / '.$row['mileage']);
    echo "\n";
}
?>
</ul>
</div>
</body>
</html>
