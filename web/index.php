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
	<script src="https://use.fontawesome.com/554daba4f6.js"></script>
	<script src="jquery-3.2.1.slim.js"></script>
	<script src="functions.js"></script>
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
<div class="css-top-panel">
<h2 class="css-top-panel-header">Deadbeef Tournament Web Tool</h2>
</div>	
<div class="css-c-panel">
  
  <form id="css-form-get-tournament" action="tournamentPage.php" method="post">
    <h3>Get a tournament by ID</h3>
	<input type="text" name="id" autofocus required> &nbsp<i class="fa fa-search" aria-hidden="true"></i> </br>
    <input class="css-button" type="submit" value="Search ID">
  </form>
  
  <form id="css-form-new-tournament" action="index.php" method="post">
    <h3>Create a new tournament</h3>
	<label for="Tournament">
	<span>Tournament name *</span> <input type="text" name="newTournament" maxlength="24" required>
    </label>
	<label for="Name">
	<span>Your name *</span> <input type="text" name="newOwner" maxlength="24" required>
    </label>
	<label for="Email">
	<span>Your email *</span> <input type="email" name="newOwnerEmail" required>
    </label>
	<label for="TournamentID">
	<span>Your personal tournament ID</span> <input type="text" name="ownId" maxlength="24"> <i class="css-toggle-hover fa fa-info-circle" aria-hidden="true"></i>
	<sub class="css-subtext">You will find your tournament with an ID. If you leave the tournament<br/>ID empty, you will get a generated, unique 10 digit ID.</sub>
	</label>
	<label for="Password">
	<span>Your tournament password *</span> <input type="password" name="passwd" required> <i class="css-toggle-hover fa fa-info-circle" aria-hidden="true"></i>
	<sub class="css-subtext">You will use this password to manage your tournament.<br/>Share it only with your moderators!</sub>
	</label>
	<label for ="PasswordAgain">
	<span>Your tournament password again *</span> <input type="password" name="passwdCheck" required>
	</label>
	<p>Fields marked with * are required</p>
    <div class="g-recaptcha" data-sitekey="6LeHxi8UAAAAAL7017VTiam5iT8TJ47Tl9leOEnn" data-theme="dark"></div>
    <input class="css-button" type="submit">
  </form>
</div>

<div id="content-wrapper">
<div id="site-introduction">
	<h1>What is DeadBeef?</h1>
	<p>This is Deadbeef.</p>
	<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit.
	Etiam gravida nibh eu nibh hendrerit, quis bibendum massa tempor.
	Integer maximus maximus tempor. Nullam faucibus mollis arcu, vel fringilla arcu tempus a.
	Donec ut placerat urna. Proin sit amet arcu magna. Nullam blandit libero non ante condimentum, nec placerat lorem suscipit.
	Phasellus id hendrerit mi. Curabitur eleifend eros non erat tempor, in porttitor lacus sagittis.</p>
	
	<p>In non maximus augue. Quisque consectetur elit et feugiat mollis.
	Maecenas tincidunt non enim ut finibus. Pellentesque elit elit, consequat at tempor eu,
	porta at leo. Aliquam aliquam eu leo nec consequat. Suspendisse potenti.
	Nam pharetra ex at est placerat tempor. In hac habitasse platea dictumst.
	Etiam pulvinar, sapien at egestas dapibus, ante nulla rhoncus purus,
	ac sodales lectus tellus et odio. Interdum et malesuada fames ac ante ipsum primis in faucibus.
	Pellentesque tristique, diam sed pellentesque consectetur, magna massa tristique ex, et rhoncus
	risus sapien eget erat. Etiam commodo mauris ac ultricies imperdiet.
	Pellentesque quis dictum metus, non tempor mauris.
	Nulla eu risus euismod diam posuere tempus.</p>
</div>
</div>

</body>
</html>
