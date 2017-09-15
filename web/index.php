<!doctype php>
<?php include "../Tournament.php";?>
<html lang="en">
<head>
  <meta charset="utf-8">

  <title>King of the Hill</title>
  <meta name="description" content="KotH manager">
  <meta name="author" content="Panu 'Pasketi' Siitonen">
  <script src='https://www.google.com/recaptcha/api.js'></script>
  <style><?php include "style.css";?></style>
<?php
	if ($_POST != null) {
		if ($_POST["passwd"] == $_POST["passwdCheck"]) {
			echo '<form id="passwdChecker" action="tournamentPage.php" method="post">';
			foreach ($_POST as $a => $b) {
				echo '<input type="hidden" name="'.htmlentities($a).'" value="'.htmlentities($b).'">';
			}
			echo '</form>
			<script type="text/javascript">
				document.getElementById("passwdChecker").submit();
			</script>';
		}
		else {
			echo "YOUR PASSWORDS DON'T MATCH. TRY AGAIN!";
		}
	}
?>
</head>
<body>
  <h3>Get a tournament by ID</h3>
  <form action="tournamentPage.php" method="post">
    ID: <input type="text" name="id" autofocus required><br />
    <input type="submit">
  </form>
  <br />
  <h3>Create a new tournament</h3>
  <form action="index.php" method="post">
    <label for="Tournament">
	<span>Tournament name *:</span> <input type="text" name="newTournament" maxlength="20" required>
    </label>
	<label for="Name">
	<span>Your name *:</span> <input type="text" name="newOwner" maxlength="20" required>
    </label>
	<label for="Email">
	<span>Your email *:</span> <input type="email" name="newOwnerEmail" required>
    </label>
	<label for="TournamentID">
	<span>Your personal tournament ID:</span> <input type="text" name="ownId" maxlength="20">
	<sub class="subtext">You will find your tournament with an ID. If you leave the tournament<br/>ID empty, you will get a generated, unique 10 digit ID.</sub>
	</label>
	<label for="Password">
	<span>Your tournament password *:</span> <input type="password" name="passwd" required>
	<sub class="subtext">You will use this password to manage your tournament.<br/>Share it only with your moderators!</sub>
	</label>
	<label for ="PasswordAgain">
	<span>Your tournament password again *:</span> <input type="password" name="passwdCheck" required>
	</label>
	<p>Fields marked with a * are required</p>
    <div class="g-recaptcha" data-sitekey="6LeHxi8UAAAAAL7017VTiam5iT8TJ47Tl9leOEnn"></div>
    <input type="submit">
  </form>
</body>
</html>
