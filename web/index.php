<!doctype php>
<?php include "../Tournament.php";?>
<html lang="en">
<head>
  <meta charset="utf-8">

  <title>King of the Hill</title>
  <meta name="description" content="KotH manager">
  <meta name="author" content="Panu 'Pasketi' Siitonen">
  <script src='https://www.google.com/recaptcha/api.js'></script>
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
    Tournament name *: <input type="text" name="newTournament" maxlength="20" required><br />
    Your name *: <input type="text" name="newOwner" maxlength="20" required><br />
    Your email *: <input type="email" name="newOwnerEmail" required><br /><br />
	You will find your tournament with an ID. If you leave the tournament ID empty, you will get a generated, unique 10 digit ID.<br />
    Your personal tournament ID: <input type="text" name="ownId" maxlength="20"><br /><br />
	You will use this password to manage your tournament. Share it only with your moderators!<br />
    Your tournament password *: <input type="password" name="passwd" required><br />
	Your tournament password again *: <input type="password" name="passwdCheck" required><br />
	<p>Fields marked with a * are required</p>
    <div class="g-recaptcha" data-sitekey="6LeHxi8UAAAAAL7017VTiam5iT8TJ47Tl9leOEnn"></div>
    <input type="submit">
  </form>
</body>
</html>
