<!doctype php>
<?php include "../Tournament.php";?>
<html lang="en">
<head>
  <meta charset="utf-8">

  <title>King of the Hill</title>
  <meta name="description" content="KotH manager">
  <meta name="author" content="Panu 'Pasketi' Siitonen">
	<style><?php include "style.css";?></style>
</head>

<body>
  <?php
	$currentTournament = new Tournament();
	if ($_GET != null) {
		if ($_GET["id"] != null){
			if ($currentTournament->IdExists($_GET["id"])) {
				$currentTournament = $currentTournament->LoadTournament($_GET["id"]);
				if ($currentTournament != null){
				//echo "<h1>Tournament loaded</h1>";
					try {
						echo $currentTournament->GetTournamentHTML();
					} catch (Exception $e) {
						echo "GET HTML Failed";
					}
				}
			} else {
				echo "<h1>No such tournament</h1>";
			}
		}
	}
	else if ($_POST != null) {
		if ($_POST["id"] ?? null != null){
			if ($currentTournament->IdExists($_POST["id"])) {
				$currentTournament = $currentTournament->LoadTournament($_POST["id"]);
				//echo "<h1>Tournament loaded</h1>";
				if ($currentTournament != null){
					try {
						echo $currentTournament->GetTournamentHTML();
					} catch (Exception $e) {
						echo "POST HTML Failed";
					}
				}
			} else {
				echo "<h1>No such tournament</h1>";
			}
		}
	
		if ($_POST["newTournament"] ?? null != null) {
			if ($_POST["newOwner"] != null && $_POST["newOwnerEmail"] != null) {
				if ($_POST["ownId"] != null) {
					$currentTournament = $currentTournament->NewTournament($_POST["newTournament"], $_POST["newOwner"], $_POST["newOwnerEmail"], $_POST["ownId"], $_POST["passwd"]);
				} else {
					$currentTournament = $currentTournament->NewTournament($_POST["newTournament"], $_POST["newOwner"], $_POST["newOwnerEmail"], null, $_POST["passwd"]);
				}
				if ($currentTournament != null) {
					try {
						echo $currentTournament->GetTournamentHTML();
					} catch (Exception $e) {
						echo "POST NEW HTML Failed";
					}
				}
			}
			else 
			{
				echo "No name and mail specified";
			}
		}
	}
	// Make forms for creation and ID
	//REMEMBER https://www.google.com/recaptcha/admin#list
	?>
</body>
</html>
