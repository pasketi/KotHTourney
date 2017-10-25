<?php
//	=================================
//	== King of the Hill by Pasketi ==
//	=================================

//	===== DESIGN =====
// 	Make a file for the tournament, give it a random name of, say 10 digits.
//	That's roughly 3,6e15 different games
//	Next, fill up a base JSON array to the file
//	Json must have
//		tournament name
//		owner details
//		current champion
//		opponents beaten by the champion
//		top X longest streaks
//
//	Tournament page will use all this data
//	Whenever a king is dethroned, his streak count will be saved if it's better or tied to the worst
//	The current streak will be wiped and the name of the king defeated will be the first name in the list
//		Last king should always be first in line of defeated people
//	Current king will be changed

// cd *Root*
// php -S localhost:8000

//	FOR ADMINCHECK
//	$isAdmin = ($object->getHash() == $_GET["adminHash"]);

class Tournament {
	public $id = "0000000000";
	public $name = "";
	public $ownerName = "";
	public $ownerEmail = "";
	public $description = "";
	public $currentChampion = "";
	public $destroyedOpponents = array();
	public $topStreaks = array();
	public $created = "";
	public $hash;

	//function Tournament() {
	//	$this->id = "DEADBEEF00";
	//	$this->name = "ERROR";
	//	$this->ownerName = "ERROR";
	//	$this->ownerEmail = "ERROR@ERROR.RU";
	//	$this->description = "ERROR";
	//	$this->currentChampion = "ERROR";
	//	$this->destroyedOpponents = array("ERROR");
	//	$this->created = "";
	//	for ($x = 0; $x < 20; $x++) {
	//		$this->topStreaks[$x] = new StreakObject();
	//		$this->topStreaks[$x]->NewStreakObject("ERROR", 0);
	//	}
	//	$this->hash = "";
	//}

	function NewTournament ($newName, $newOwner, $newOwnerEmail, $id, $passwd){

		$captcha = new ReCaptcha ();
		if (!$captcha->CheckValidity()) {
			echo "You didn't pass ReCaptcha. Go back and try again.";
			return null;
		}
		
		if (!filter_var($newOwnerEmail, FILTER_VALIDATE_EMAIL)) {
			$emailErr = "Invalid email format";
			return $emailErr."</ br>";
		}
		
		$ownId = false;
		if ($id != null) {
			$ownId = true;
			$this->id = $id;
		} else {
			$this->id = $this->GenerateRandomString();
		}
		
		$i = 0;
		while($i < 50) {
			if($this->IdExists($this->id)){
				if ($ownId == true){
						echo "ERROR - This ID has been already taken!<br />";
						return null;
				}
			} else {
				break;
			}
			$this->id = $this->GenerateRandomString();
			$i++;
		}
		
		if (!$this->StringIsValid($newName) || !$this->StringIsValid($newOwner)){
			echo "<br />ERROR - CREATING THE TOURNAMENT FAILED: Keep names under 20 digits. Using other than alphanumeric digits is forbidden for security reasons.<br />";
			return null;
		}
		
		$this->name = $newName;
		$this->ownerName = $newOwner;
		$this->ownerEmail = $newOwnerEmail;
		$this->description = "";
		$this->currentChampion = "";
		$this->destroyedOpponents = array();
		for ($x = 0; $x < 20; $x++) {
			$this->topStreaks[$x] = new StreakObject();
			$this->topStreaks[$x]->NewStreakObject("", 0);
		}
		
		$this->created = time();
		$this->hash = $this->getHashFor($passwd);
		$this->SaveTournament();
		
		return $this;
	}

	function getHashFor($passwd) {
		return hash("sha256", $this->created."&".$this->id."&".$passwd."&".file_get_contents("../hashkey"));
	}

	function checkAccessFor($id, $passwd) {
		$temp = new Tournament();
		$temp = $temp->LoadTournament($id);
		if ($temp->hash == $temp->getHashFor($passwd)){
			return true;
		}
		else
		{
			return false;
		}
	}

	function SetDescription ($str)
	{
		$this->description = $str;
		$this->SaveTournament();
		return "<h4 class='css-confirm-text'>Description saved!</h4>";
	}

	function ChangePassword ($currentPasswd, $newPasswd)
	{
		if ($this->getHashFor($currentPasswd) == $this->hash) {
			$this->hash = $this->getHashFor($newPasswd);
		}
		$this->SaveTournament();
	}

	function SetNewChampion ($newChamp) {
		if ($this->currentChampion == "" && $this->StringIsValid($newChamp)){
			$this->currentChampion = $newChamp;
			$this->SaveTournament();
			return "<h4 class='css-confirm-text'>New champion set as " .$this->currentChampion. "!</h4>";
		}
	}

	function EnterScores ($challengerName, $didChallengerWin) {
		if ($didChallengerWin) {
			$currentStreak = count($this->destroyedOpponents);
			for ($x = 0; $x < 20; $x++) {
				if ($currentStreak > $this->topStreaks[$x]->streak) {
					for ($y = 18; $y >= $x; $y--) {
						$this->topStreaks[$y + 1] = $this->topStreaks[$y];
					}
					$newStreak = new StreakObject();
					$newStreak->NewStreakObject($this->currentChampion, $currentStreak);
					$this->topStreaks[$x] = $newStreak;
					break;
				}
			}
			$this->destroyedOpponents = array($this->currentChampion);
			$this->currentChampion = $challengerName;
			$this->SaveTournament();
			return "<h4 class='css-confirm-text'>New Champion Set!</h4>";
		}
		else {
			array_push($this->destroyedOpponents, $challengerName);
			$this->SaveTournament();
			return "<h4 class='css-confirm-text'>New streak count: ".count($this->destroyedOpponents). "</h4>";
		}
	}

	function GenerateRandomString () {
		$characters = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";
		$max = strlen($characters) - 1;
		$id = "";
		for ($i = 0; $i < 10; $i++) {
			$id .= $characters[mt_rand(0, $max)];
		}
		return $id;
	}

	function IsValid() {
		return !$this->IdExists($this->id);
	}

	function IdExists($newId) {
		if (ctype_alnum($newId) && (strlen($newId) <= 24 && strlen($newId) >= 6)) {
			return file_exists("../tournaments/".$newId.".trnmt");
		} else if (strlen($newId) > 24 || strlen($newId) < 6) {
			echo "YOUR ID SHOULD BE BETWEEN 6 AND 20 DIGITS<br />";
		}
		return false;
	}

	function StringIsValid ($str) {
		if ((strlen($str) <= 24 && strlen($str) > 0)) {
			return true;
		} else if (strlen($str) > 24 || (strlen($str) <= 0)) {
			echo "String ".$str." is not valid?";
			return false;
		}
	}

	function SaveTournament () {
		$json = json_encode($this);
		$file = fopen("../tournaments/".$this->id.".trnmt", "w");
		fwrite($file, $json);
		fclose($file);
		chmod("../tournaments/".$this->id.".trnmt", 0777);
	}

	function RefreshTournament () {
		if ($this->IsValid()) {
			$this->LoadTournament($this->id);
		}
	}

	function LoadTournament ($newId) {
		$new = json_decode(file_get_contents("../tournaments/".$newId.".trnmt"));
		$this->id = $new->id;
		$this->name = $new->name;
		$this->ownerName = $new->ownerName;
		$this->ownerEmail = $new->ownerEmail;
		$this->description = $new->description;
		$this->currentChampion = $new->currentChampion;
		$this->destroyedOpponents = $new->destroyedOpponents;
		$this->topStreaks = $new->topStreaks;
		$this->created = $new->created;
		$this->hash = $new->hash;

		return $this;
	}

	function LoadDummyTournament () {
		$this->LoadTournament ("DEADBEEF00");
		return $this;
	}

	function GetTournamentHTML() {
		$string = "<div class='css-top-panel'>";
		$string .= "<div id='top-panel-toolbar'>";
		$string .= '<form id="top-panel-form" action="admin.php" method="get">
				<input type="hidden" name="id" value="'.$this->id.'"\">
				<button class="fa fa-pencil fa-2x" id="login-button" type="submit" value="Manage tournament"></button>
				</form>';
		$string .= "</div>"; //#top-panel-toolbar
		$string .= "<p class='css-link'>http://deadbeef.dy.fi/tournamentPage.php?id=".$this->id."</p>";
		$string .= "</div>"; // css-top-panel
		$string .= "<div class='css-c-panel'>";
					
		$string .= "<div class='css-div-champion'><h2>Reigning champion:</h2>";
		if ($this->currentChampion == "") {
			$string .= "<p class='css-notification-text'>There is no champion, contact Admin if you wanna be the guy</p>";
		} else {
			$string .= "<h1 class='css-champion-title'>".$this->currentChampion."</h1>"; 
		}
			$string .= "</div>"; // css-div-champion
			$string .= "<div id='css-div-leaderboards'>";
			$string .= "<div id='div-leaderboards-buttons'>";
			$string .= "<button class='css-button-leaderboards-toggle current' data-tab='tab-1'><i class='fa fa-square'></i></button>";
			$string .= "<button class='css-button-leaderboards-toggle' data-tab='tab-2'><i class='fa fa-square'></i></button>";
			$string .= "</div>";
		$string .= "<div id='tab-1' class='tab-content current'><h3 id='header-contenders'>List of fallen foes</h3> <ul class='css-list-contenders'>";
		
		if (count($this->destroyedOpponents) > 0) {
			foreach ($this->destroyedOpponents as &$player) {
				if ($player == $this->destroyedOpponents[0]) {
					$string .= "<li><i class='fa fa-star'></i> &nbsp<p class='css-player-name'><b>".$player."</b></p></li>";
				} else {
					$string .= "<li><i class='fa fa-user-o'></i> &nbsp<p class='css-player-name'>".$player."</p></li>";
				}
			}
		} else {
			$string .= "<p class='css-notification-text'>The champion has not defeated anyone</p>";
		}
		$string .= "</ul></div>"; // div #Contenders
		$string .= "<div id='tab-2' class='tab-content'><h3 id='header-streaks'>Top streaks</h3><ul class='css-list-streaks'>";
		$none = true;
		foreach ($this->topStreaks as &$streak) {
			if ($streak->streak == 0) {
				continue;
			}
			else {
				$string .= "<li>".$streak->streak." -- <p class='css-player-name'>".$streak->name."</p></li>";
				if ($none) {
					$none = false;
				}
			}
		}
		if ($none) {
			$string .= "<p class='css-notification-text'>There are no streaks made by players</p>";
		}
		$string .= "</ul></div>"; // div #Streaks
		$string .= "</div>"; // css-div-leaderboards
		$string .= "</div>"; // css-c-panel
		$string .= "<div id='content-wrapper'>";
		
		$string .= "<div class='css-info-panel'>";
		$string .= "<h1>".$this->name."</h1>";
		$string .= "<h3>Admin: ".$this->ownerName."</h3>";
		//$string .= "<h3>Admin contact: ".$this->ownerEmail."</h3>";
		$string .= "<p>".$this->description."</p>";
		$string .= "</div>"; // css-div-info
		$string .= "</div>"; // #content-wrapper
		return $string;
	}
	
	function GetAdminTournamentHTML($descriptionHTML, $resultHTML, $passwordHTML, $logout) {
		$string = "<div class='css-top-panel'>";
		$string .= "<h2 class='css-top-panel-header'>".$this->name." admin panel</h2>";
		$string .= "<div id='top-panel-toolbar'>";
		$string .= $logout;
		$string .= "</div>"; //#top-panel-toolbar
		$string .= "<p class='css-link'>http://deadbeef.dy.fi/tournamentPage.php?id=".$this->id."</p>";
		$string .= "</div>"; // css-top-panel
		$string .= "<div class='css-c-panel'>";
		
		
		$string .= "<div class='css-div-champion'><h2>Reigning champion:</h2>";
		if ($this->currentChampion == "") {
			$string .= "<p class='css-notification-text'>There is no current champion set.</p>";
		} else {
			$string .= "<h1 class='css-champion-title'>".$this->currentChampion."</h1>"; 
		}
			$string .= "</div>"; // css-div-champion
			$string .= "<div id='css-div-leaderboards'>";
			$string .= "<div id='div-leaderboards-buttons'>";
			$string .= "<button class='css-button-leaderboards-toggle current' data-tab='tab-1'><i class='fa fa-square'></i></button>";
			$string .= "<button class='css-button-leaderboards-toggle' data-tab='tab-2'><i class='fa fa-square'></i></button>";
			$string .= "</div>";
		$string .= "<div id='tab-1' class='tab-content current'><h3 id='header-contenders'>List of fallen foes</h3> <ul class='css-list-contenders'>";
		
		if (count($this->destroyedOpponents) > 0) {
			foreach ($this->destroyedOpponents as &$player) {
				if ($player == $this->destroyedOpponents[0]) {
					$string .= "<li><i class='fa fa-star'></i> &nbsp<p class='css-player-name'><b>".$player."</b></p></li>";
				} else {
					$string .= "<li><i class='fa fa-user-o'></i> &nbsp<p class='css-player-name'>".$player."</p></li>";
				}
			}
		} else {
			$string .= "<p class='css-notification-text'>The champion has not defeated anyone</p>";
		}
		$string .= "</ul></div>"; // div #Contenders
		$string .= "<div id='tab-2' class='tab-content'><h3 id='header-streaks'>Top streaks</h3><ul class='css-list-streaks'>";
		$none = true;
		foreach ($this->topStreaks as &$streak) {
			if ($streak->streak == 0) {
				continue;
			}
			else {
				$string .= "<li>".$streak->streak." -- <p class='css-player-name'>".$streak->name."</p></li>";
				if ($none) {
					$none = false;
				}
			}
		}
		if ($none) {
			$string .= "<p class='css-notification-text'>There are no streaks made by players</p>";
		}
		$string .= "</ul></div>"; // div #Streaks
		$string .= "</div>"; // css-div-leaderboards
		$string .= "</div>"; // css-c-panel
		$string .= "<div id='content-wrapper'>";
		$string .= "<div class='css-description-panel-admin'>";
		$string .= "<div class='css-info-panel-admin'><h1>".$this->name."</h1>";
		$string .= "<h3>Admin: ".$this->ownerName."</h3>";
		$string .= "<h3>Admin contact: ".$this->ownerEmail."</h3>";
		$string .= "</div>"; // css-info-panel-admin
		$string .= "<div class='css-description-admin'>";
		$string .= "".$descriptionHTML."";
		$string .= "</div>"; // css-description-admin
		$string .= "<div id='css-challenger-admin'>".$resultHTML."</div>";
		$string .= "<div id='css-password-admin'>".$passwordHTML."</div>";
		$string .= "</div>"; // css-admin-description-panel
		$string .= "</div>"; // #content-wrapper;
		return $string;
	}

}

class StreakObject {
	public $name = "";
	public $streak = 0;

	function NewStreakObject ($newName, $newStreak) {
		$this->name = $newName;
		$this->streak = $newStreak;
	}
}

class ReCaptcha {
	function CheckValidity() {
		$sender_name = stripslashes($_POST["sender_name"]);
		$sender_email = stripslashes($_POST["sender_email"]);
		$sender_message = stripslashes($_POST["sender_message"]);
		$response = $_POST["g-recaptcha-response"];

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => '6LeHxi8UAAAAAAbIq52A5HqEvp64JA6-nkp_tvBq',
			'response' => $_POST["g-recaptcha-response"]
		);
		$options = array(
				'http' => array (
				'method' => 'POST',
				'content' => http_build_query($data)
			)
		);

		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captcha_success=json_decode($verify);

		//if ($captcha_success->success==false) {
		//	echo "<p>You are a bot! Go away!</p>";
		//} else if ($captcha_success->success==true) {
		//	echo "<p>You are not not a bot!</p>";
		//}

		return $captcha_success->success;
	}
}
?>
