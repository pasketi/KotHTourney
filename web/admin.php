<!doctype php>
<?php include "../Tournament.php";?>
<html lang="en">
<head>
  <meta charset="utf-8">

  <title>King of the Hill</title>
  <meta name="description" content="KotH manager">
  <meta name="author" content="Panu 'Pasketi' Siitonen">
  <script src='https://www.google.com/recaptcha/api.js'></script>
</head>
<body>
<?php
	$currentTournament = new Tournament();

	$tournamentIDHTML = '
	<h3>Enter tournament ID</h3>
	<form action="admin.php" method="get">
		ID: <input type="text" name="id" autofocus><br />
		<input type="submit">
	</form>
	';

	$passwordHTML = '
	<h3>Enter password for your tournament</h3>
	<form action="admin.php" method="post">
		<input type="hidden" name="id" value="'.$_GET["id"].'">
		password: <input type="password" name="passwd" autofocus><br />
		<div class="g-recaptcha" data-sitekey="6LeHxi8UAAAAAL7017VTiam5iT8TJ47Tl9leOEnn"></div>
		<input type="submit">
	</form>';
	
	$passwordChangeHTML = '
	<h3>Change your password</h3>
	<form action="admin.php" method="post">
		<input type="hidden" name="id" value="'.$_POST["id"].'">
		<input type="hidden" name="passwd" value="'.$_POST["passwd"].'">
		Current password: <input type="password" name="curPasswd"><br />
		New password: <input type="password" name="newPasswd"><br />
		New password again: <input type="password" name="newPasswdCheck"><br />
		<input type="submit">
	</form>';

	if ($_GET != null || $_POST != null) {
		if ($_GET["id"] != null) {
			if ($_GET["s"] != null && $_GET["s"] == "true") {
				echo "Password change successful! Please login again with your new password.";
			}
			
			if ($currentTournament->IdExists($_GET["id"])) {
				echo $passwordHTML;
			} else {
				echo "<h1>No such tournament</h1>";
			}
		} else if ($_POST["id"] != null && $_POST["passwd"] != null) {
			$captcha = new ReCaptcha ();
			if (!$captcha->CheckValidity()) {
				echo "You didn't pass ReCaptcha. Go back and try again.";
				return null;
			}
			
			if ($currentTournament->IdExists($_POST["id"])) {
				if ($currentTournament->checkAccessFor($_POST["id"], $_POST["passwd"]) == true) {
					// Access granted
					$currentTournament->LoadTournament($_POST["id"]);
					// Change Password
					if ($_POST["curPasswd"] != null && $_POST["newPasswd"] != null && $_POST["newPasswdCheck"] != null) {
						if ($_POST["curPasswd"] == $_POST["passwd"]) {
							if ($_POST["newPasswd"] == $_POST["newPasswdCheck"]) {
								$currentTournament->ChangePassword($_POST["curPasswd"], $_POST["newPasswd"]);
								
								echo '<form id="passwdChanger" action="admin.php" method="get">
								<input type="hidden" name="id" value="'.$_POST["id"].'">
								<input type="hidden" name="s" value="true">
								</form>
								<script type="text/javascript">
									document.getElementById("passwdChanger").submit();
								</script>';
								
							} else {
								echo "Check your passwords. Something wasn't quite right.<br />";
							}
						} else {
							echo "Check your passwords. Something wasn't quite right.<br />";
						}
					}

					// Set a new champion
					if($_POST["champion"] != null && $currentTournament->currentChampion == "") {
						$champ = $_POST["champion"];
						if ($currentTournament->StringIsValid($champ)) {
							$currentTournament->SetNewChampion($champ);
						}
					}
					
					// Report a new result
					if ($_POST["challenger"] != null && $_POST["winner"] != null) {
						$chal = $_POST["challenger"];
						if ($currentTournament->StringIsValid($chal)){
							$win = false;
							if ($_POST["winner"] == "true") {
								$win = true;
							}
							$currentTournament->EnterScores($chal, $win);
						}
					}
					
					// Set a new tournament description
					if ($_POST["description"] != null) {
						$currentTournament->SetDescription($_POST["description"]);
					}
	
					// Form variables
					$addChallengerForm = '<p><h3>Report scores</h3>
					<form action="admin.php" method="post">
						<input type="hidden" name="id" value="'.$_POST["id"].'">
						<input type="hidden" name="passwd" value="'.$_POST["passwd"].'">
						Challenger: <input type="text" name="challenger" maxlength="20"><br />
						Who won? <br />
						'.$currentTournament->currentChampion.' - <input type="radio" name="winner" value="false"><br />
						Challenger - <input type="radio" name="winner" value="true"><br />
						<input type="submit">
					</form></p>';
					$setChampionForm = '<p><h3>Set a new champion</h3>
					<form action="admin.php" method="post">
						<input type="hidden" name="id" value="'.$_POST["id"].'">
						<input type="hidden" name="passwd" value="'.$_POST["passwd"].'">
						Champion name: <input type="text" name="champion" maxlength="20" autofocus><br />
						<input type="submit">
					</form></p>';
					$setDescriptionForm = '<p><h3>Change the description of the tournament</h3>
					<form action="admin.php" method="post">
						<input type="hidden" name="id" value="'.$_POST["id"].'">
						<input type="hidden" name="passwd" value="'.$_POST["passwd"].'">
						Description: </br><textarea name="description">'.$currentTournament->description.'</textarea><br />
						<input type="submit">
					</form></p>';
	
					//SITE LOOKS LIKE THIS:
					echo "<h3>".$currentTournament->name." administrator panel</h3>";
					if ($currentTournament->currentChampion != "") {
						echo $addChallengerForm;
					} else {
						echo $setChampionForm;
					}
					echo $setDescriptionForm;
					echo $passwordChangeHTML;
				} else {
					echo "<h1>Authentication failed</h1>";
				}
			}
		}
	}
	else
	{
		echo $tournamentIDHTML;
	}
?>
</body>
</html>
