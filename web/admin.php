<!doctype php>
<?php
	include "../Tournament.php";

	$pageData = "";

	session_start();

	if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 600)) {
		// last request was more than 30 minutes ago
		session_unset();     // unset $_SESSION variable for the run-time
		session_destroy();   // destroy session data in storage
		error_log ("---- Session Expired ----", 4);
		$pageData .= "<div class='css-alert-panel'><h3>Session expired, log it again</h3></div>";
	}
	$_SESSION['LAST_ACTIVITY'] = time();

	$currentTournament = new Tournament();
	
	// 0 = NONE
	// 1 = CHALLENGER ADDED
	// 2 = DESCRIPTION CHANGED
	// 3 = PASSWORD CHANGED
	$changesDone = 0;
	$championString = "";
	$descriptionString = "";

	$location = "/admin.php";

	$tournamentIDHTML = '
	<h3>Enter tournament ID</h3>
	<form action="admin.php" method="get">
		ID: <input type="text" name="id" autofocus><br />
		<input class="css-button" type="submit">
	</form>
	';

	$passwordHTML = '
	<div class="css-login-panel">
	<h3>Enter password for your tournament</h3>
	<form action="admin.php" method="post">
		<input type="hidden" name="id" value="'.$_GET["id"].'">
		<input type="password" name="passwd" autofocus><br />
		<div class="g-recaptcha" data-sitekey="6LeHxi8UAAAAAL7017VTiam5iT8TJ47Tl9leOEnn" data-theme="dark"></div>
		<input class="css-button" type="submit">
	</form>
	</div>';
	
	$passwordChangeHTML = '
	<h3>Change admin password</h3>
	<form action="admin.php" method="post">
		<input type="hidden" name="id" value="'.$_SESSION["id"].'">
		<input type="hidden" name="passwd" value="'.$_SESSION["passwd"].'">
		<span>Current password</span> <input type="password" name="curPasswd"><br />
		<span>New password</span> <input type="password" name="newPasswd"><br />
		<span>New password again</span> <input type="password" name="newPasswdCheck"><br />
		<input class="css-button" type="submit">
	</form>';

	if ($_GET != null || $_POST != null) {
		if ($_GET["id"] != null) {
			if ($_GET["s"] != null && $_GET["s"] == "true") {
				//$changesDone = 3;
				$pageData .= "Password change successful! Please login again with your new password.";
			}
			if ($currentTournament->IdExists($_GET["id"]) && !isset($_SESSION["id"])) {
				$pageData .= $passwordHTML;
			} else {
				if (!isset($_SESSION["id"])) {
					$pageData .=  "<div class='css-alert-panel'><h1>No such tournament</h1></div>";
				}
			}
		} else if ($_POST["id"] != null && $_POST["passwd"] != null) {
			if ($_SESSION["id"] == null){
				$captcha = new ReCaptcha ();
				if ($captcha->CheckValidity()) {
					$_SESSION["id"] = $_POST["id"];
					$_SESSION["passwd"] = $_POST["passwd"];
				} else {
					$pageData .= "<div class='css-alert-panel'>You didn't pass ReCaptcha. Go back and try again.</div>";
					header('Refresh: 0; url=admin.php?id='.$_POST["id"]);
				}
			}
		}
	}
	else
	{
		$pageData .= $tournamentIDHTML;
	}

	if (isset($_SESSION["id"]) && isset($_SESSION["passwd"])) {
		// Logout
		if ($_POST["logout"] != null) {
			if ($_POST["logout"] == true) {
				header('Refresh: 3; url=tournamentPage.php?id='.$_SESSION["id"]);
				session_unset();
				session_destroy();
				$pageData .= "<div class='css-login-panel'><h2>Logout successful</h2></div>";
			}
		}

		if ($currentTournament->IdExists($_SESSION["id"])) {
			if ($currentTournament->checkAccessFor($_SESSION["id"], $_SESSION["passwd"]) == true) {

				// Access granted. load tournament
				$currentTournament->LoadTournament($_SESSION["id"]);

				// Change Password
				if ($_POST["curPasswd"] != null && $_POST["newPasswd"] != null && $_POST["newPasswdCheck"] != null) {
					if ($_POST["curPasswd"] == $_POST["passwd"]) {
						if ($_POST["newPasswd"] == $_POST["newPasswdCheck"]) {
							$currentTournament->ChangePassword($_POST["curPasswd"], $_POST["newPasswd"]);

							header('Refresh: 0; url=admin.php?id='.$_SESSION["id"]);

							session_unset();
							session_destroy();

						} else {
							$pageData .= "Check your passwords. Something wasn't quite right.<br />";
						}
					} else {
						$pageData .= "Check your passwords. Something wasn't quite right.<br />";
					}
				}

				// Set a new champion
				if($_POST["champion"] != null && $currentTournament->currentChampion == "") {
					$champ = $_POST["champion"];
					if ($currentTournament->StringIsValid($champ)) {
						$championString = $currentTournament->SetNewChampion($champ);
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
						$championString = $currentTournament->EnterScores($chal, $win);
					}
				}

				// Set a new tournament description
				if ($_POST["description"] != null) {
					$descriptionString = $currentTournament->SetDescription($_POST["description"]);
				}

				// Form variables

				$addChallengerForm =
				'<h3>Resolve a match</h3>
				<form action="admin.php" method="post">
					<span>Challenger</span> <input type="text" name="challenger" maxlength="24"><br/>
					<h4>Who won? </h4>
					<input class="css-input-radio" type="radio" name="winner" value="false">'.$currentTournament->currentChampion.'<br />
					<input class="css-input-radio" type="radio" name="winner" value="true">Challenger<br/>
					<input class="css-button" type="submit">'.$championString.'
				</form>';
				$setChampionForm = '<h3>Set a new champion</h3>
				<form action="admin.php" method="post">
					<span>Champion name</span>
					<input type="text" name="champion" maxlength="24" autofocus><br/>
					<input class="css-button" type="submit">
				</form>';
				
				$setDescriptionForm =
				'<form action="admin.php" method="post">
					<textarea id="set-description" name="description">'.$currentTournament->description.'</textarea>
					<input class="css-button" type="submit">'.$descriptionString.'
				</form>';

				$logoutButton =
				'<form id="top-panel-form" action="admin.php" method="post">
					<input type="hidden" name="logout" value="true">
					<button class="fa fa-sign-out fa-2x" aria-hidden="true" id="login-button" type="submit" value="Logout" alt="logout"></button>
				</form>';

				//SITE LOOKS LIKE THIS:
				//$pageData .= "<h3>".$currentTournament->name." administrator panel</h3>";
				if ($currentTournament->currentChampion != "") {
					$pageData .= $currentTournament->GetAdminTournamentHTML($setDescriptionForm, $addChallengerForm, $passwordChangeHTML, $logoutButton, $changesDone);
				} else {
					$pageData .= $currentTournament->GetAdminTournamentHTML($setDescriptionForm, $setChampionForm, $passwordChangeHTML, $logoutButton, $changesDone);
				}
				
			} else {
				$pageData .= "<h1>Authentication failed</h1>";
				$tempID = $_SESSION["id"];
				session_unset();
				session_destroy();
				header('Refresh: 3; url=admin.php?id='.$tempID);
			}
		}
	}
?>
<html lang="en">
<head>

  <meta charset="utf-8">

  <title>King of the Hill</title>
  <meta name="description" content="KotH manager">
  <meta name="author" content="Panu 'Pasketi' Siitonen">
  <script src='https://www.google.com/recaptcha/api.js'></script>
  <style><?php include "style.css";?></style>
  <script src="https://use.fontawesome.com/554daba4f6.js"></script>
  <script src="jquery-3.2.1.slim.js"></script>
  <script src="functions.js"></script>

</head>
<body>
<?php
	echo $pageData;
?>
</body>
</html>
