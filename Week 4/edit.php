<?php
session_start();

require_once "pdo.php";
require_once "util.php";

if ( ! isset($_SESSION['name']) || ! isset($_SESSION['user_id'] ) ) {
    die('ACCESS DENIED');
    return;
}

if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

if ( ! isset($_REQUEST['profile_id']) ) {
    $_SESSION['error'] = 'Missing profile_id';
    header('Location: index.php');
    return;
}

if ( isset($_POST['first_name']) && isset($_POST['last_name']) && isset($_POST['email']) && isset($_POST['headline']) && isset($_POST['summary']) && isset($_POST['profile_id']) ) {
    $msg = validateProfile();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_POST["profile_id"]);
        return;
    }

    $msg = validatePos();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_POST["profile_id"]);
        return;
    }

    $msg = validateEdu();
    if ( is_string($msg) ) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=" . $_POST["profile_id"]);
        return;
    }

    $sql = "UPDATE profile SET first_name = :fn, last_name = :ln, email = :em, headline = :he, summary = :su WHERE profile_id = :profile_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':fn' => $_POST['first_name'],
        ':ln' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':he' => $_POST['headline'],
        ':su' => $_POST['summary'],
        ':profile_id' => $_REQUEST['profile_id'],
        ':user_id' => $_SESSION['user_id']
    ));

    // Clear out the old position entries
    $stmt = $pdo->prepare('DELETE FROM Position
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    // Insert the position entries
    insertPositions($pdo, $_REQUEST['profile_id']);

    // Clear out the old education entries
    $stmt = $pdo->prepare('DELETE FROM education
        WHERE profile_id=:pid');
    $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

    insertEducations($pdo, $_REQUEST['profile_id']);

    $_SESSION['success'] = 'Profile updated';
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :profile_id AND user_id = :user_id");
$stmt->execute(array(":profile_id" => $_GET['profile_id'], ':user_id' => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}

$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$em = htmlentities($row['email']);
$he = htmlentities($row['headline']);
$su = htmlentities($row['summary']);
$profile_id = $row['profile_id'];

$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);
?>
<!DOCTYPE html>
<html>
<head>
<title>Merle's Profile Edit</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Editing Profile for <?php echo(htmlentities($_SESSION['name']));?></h1>
<?php flashMessages(); ?>
<form method="post">
<p>First Name:
<input type="text" name="first_name" size="60"
value="<?= $fn ?>"
/></p>
<p>Last Name:
<input type="text" name="last_name" size="60"
value="<?= $ln ?>"
/></p>
<p>Email:
<input type="text" name="email" size="30"
value="<?= $em ?>"
/></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80"
value="<?= $he ?>"
/></p>
<p>Summary:<br/>
<textarea name="summary" rows="8" cols="80">
<?= $su ?></textarea>
<?php
$countEdu = 0;

echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");
if ( count($schools) >0 ) {
    foreach( $schools as $school ) {
        $countEdu++;
        echo('<div id="edu'.$countEdu.'">');
        echo '<p>Year: <input type="text" name="edu_year'.$countEdu.'" value="'.htmlentities($school['year']).'" />
        <input type="button" value="-" onclick="$(\'#edu'.$countEdu.'\').remove();return false;"></p>
        <p>School: <input type="text" size="80" name="edu_school'.$countEdu.'" class="school"
        value="'.htmlentities($school['name']).'" />';
        echo "\n</div>\n";
    }
}
echo("</div></p>\n");
?>
<p>
Position: <input type="submit" id="addPos" value="+">
<div id="position_fields">
<?php
$countPos = 0;
if ( count($positions) > 0 ) {
    foreach( $positions as $position ) {
        $countPos++;
        echo('<div class="position" id="position'.$countPos.'">'."\n");
        echo('<p>Year: <input type="text" name="year'.$countPos.'"');
        echo(' value="'.htmlentities($position['year']).'" />'."\n");
        echo('<input type="button" value="-" ');
        echo('onclick="$(\'#position'.$countPos.'\').remove();return false;"><br>'."\n");
        // echo("</p>\n");
        echo('<textarea name="desc'.$countPos.'" rows="8" cols="80">'."\n");
        echo(htmlentities($position['description'])."\n");
        echo("\n</textarea>\n</div>\n");
    }
}
?>
</div>
</p>
<p>
<input type="hidden" name="profile_id"
value="<?= $profile_id ?>"
/>
<input type="submit" value="Save">
<input type="submit" name="cancel" value="Cancel">
</p>
</form>
<script>
countPos = <?= $countPos ?>;
countEdu = <?= $countEdu ?>;

// http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
$(document).ready(function(){
    window.console && console.log('Document ready called');

    $('#addPos').click(function(event){
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if ( countPos >= 9 ) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position "+countPos);

        $('#position_fields').append(
            '<div id="position'+countPos+'"> \
            <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
            <input type="button" value="-" \
                onclick="$(\'#position'+countPos+'\').remove();return false;"></p> \
            <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea>\
            </div>');
    });

    $('#addEdu').click(function(event){
        event.preventDefault();
        if ( countEdu >= 9 ) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education "+countEdu);

        // Grab some HTML with hot spots and insert into the DOM
        var source  = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        // Add the even handler to the new ones
        $('.school').autocomplete({
            source: "school.php"
        });

    });

    $('.school').autocomplete({
        source: "school.php"
    });

});

</script>
<!-- HTML with Substitution hot spots -->
<script id="edu-template" type="text">
  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="edu_year@COUNT@" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="edu_school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</div>
</body>
</html>
