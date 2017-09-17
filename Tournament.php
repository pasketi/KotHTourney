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
			echo "New champion set as ".$this->currentChampion;
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
			echo $this->destroyedOpponents[0]." has fallen with his streak of ".$currentStreak."! Long live new champion ".$this->currentChampion."!";
		}
		else {
			array_push($this->destroyedOpponents, $challengerName);
			echo $this->currentChampion." won ".$challengerName." and now has a streak of ".count($this->destroyedOpponents);
		}
		$this->SaveTournament();
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
		if (ctype_alnum($newId) && (strlen($newId) <= 20 && strlen($newId) >= 6)) {
			return file_exists("../tournaments/".$newId.".trnmt");
		} else if (strlen($newId) > 20 || strlen($newId) < 6) {
			echo "YOUR ID SHOULD BE BETWEEN 6 AND 20 DIGITS<br />";
		}
		return false;
	}

	function StringIsValid ($str) {
		if ((strlen($str) <= 20 && strlen($str) > 0)) {
			return true;
		} else if (strlen($str) > 20 || (strlen($str) <= 0)) {
			echo "String ".$str." is not valid?";
			return false;
		}
	}

	function SaveTournament () {
		$json = json_encode($this);
		$file = fopen("../tournaments/".$this->id.".trnmt", "w");
		fwrite($file, $json);
		fclose($file);
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
		$string = "";
		$string .= "<p class='css-share'>Share-link:</p> <p class='css-link'>http://url.fi/tournamentPage.php?id=".$this->id."</p>";
		$string .= "<h1>".$this->name."</h1>";
		$string .= '<form action="admin.php" method="get">
					<input type="hidden" name="id" value="'.$this->id.'"\">
					<input type="submit" value="Manage tournament">
					</form>';
		$string .= "<h3>Admin: ".$this->ownerName."</h3>";
		//$string .= "<h3>Admin contact: ".$this->ownerEmail."</h3>";
		$string .= "<p>".$this->description."</p>";
		$string .= "<h1>Reigning champion:</h1>";
		if ($this->currentChampion == "") {
			$string .= "<h2>There is no champion, contact Admin if you wanna be the guy</h2>";
		} else {
			$string .= "<h2>".$this->currentChampion."</h2>";
		}
		$string .= "List of fallen foes<br /><ul>";
		if (count($this->destroyedOpponents) > 0) {
			foreach ($this->destroyedOpponents as &$player) {
				if ($player == $this->destroyedOpponents[0]) {
					$string .= "<li><b>".$player."</b></li>";
				} else {
					$string .= "<li>".$player."</li>";
				}
			}
		} else {
			$string .= "<li>The champion has not defeated anyone</li>";
		}
		$string .= "</ul><br />";
		$string .= "Top streaks<br /><ul>";
		$none = true;
		foreach ($this->topStreaks as &$streak) {
			if ($streak->streak == 0) {
				continue;
			}
			else {
				$string .= "<li>".$streak->streak." -- ".$streak->name."</li>";
				if ($none) {
					$none = false;
				}
			}
		}
		if ($none) {
			$string .= "<li>There are no streaks yet</li>";
		}
		$string .= "</ul>";
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
