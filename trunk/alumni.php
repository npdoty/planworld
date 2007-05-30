<?php
/**
 * $Id: alumni.php,v 1.5.2.5 2003/03/17 15:44:45 seth Exp $
 * ndoty: copy of pref.php, modified to handle switching regular and alumni accounts
 */

/* Initialization */
$_base = dirname(__FILE__) . '/';

/* send them elsewhere if no POST data */
if (!isset($_POST) || !isset($_POST["alumniMove"])) {
  header("Location: " . PW_URL_INDEX);
  exit();
}

// standard response (overridden by errors)
$location = PW_URL_INDEX . "?id=alumni;err=0";

$alumni = $_user->getUsername();
$alumni_id = $_user->getUserID();

if ($_user->hasOldUsername())
{
	$nt = $_user->oldUsername();
	$nt_id = Planworld::nameToID($nt);
}
else {
	$nt = preg_replace("/[0-9]{2}$/","",$alumni);	
	$nt_id = Planworld::nameToID($nt);
	$year = $_user->getYear();

	if (($year == "UNKNOWN" || $year > 6) || ($alumni_id < $nt_id)) 	//invalid user						
	{
		$location = PW_URL_INDEX . "?id=alumni;err=1";
		header("Location: " . $location);
		exit();
	}	
}

if (!Planworld::isUser($nt, true)) {	//not a user at all
	$location = PW_URL_INDEX . "?id=alumni;err=1";
	header("Location: " . $location);
	exit();
}

$errors = "";

//echo "nt: ".$nt."<br>nt_id: ".$nt_id."<br>alumni: ".$alumni."<br>alumni_id: ".$alumni_id;
ob_start();
if ($_POST["alumniMove"] == "full") {
	//rename new account username-tmp
	$query = "UPDATE users SET username='${nt}-tmp' WHERE id=${alumni_id}";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}

	//rename old account alumni username
	$query = "UPDATE users SET username='${alumni}' WHERE id=${nt_id}";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}

	//rename new account old username 
	$query = "UPDATE users SET username='${nt}' WHERE id=${alumni_id}";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}

	//set old account as shared to new account
	$query = "INSERT INTO preferences VALUES (${alumni_id}, 'shared', 'true')";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}
	$query = "INSERT INTO preferences VALUES (${alumni_id}, 'shared_${alumni}', 'true')";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}

	if (!($_user->isNew()))	//if there is an alumni plan already, make it the current plan
	{
		//delete current plan for old account
		$query = "DELETE FROM plans WHERE uid=${nt_id}";
		$result = $_user->dbh->query($query);
		if (DB::isError($result)) {
			$errors .= DB::errorMessage($result);
		}
	
		//make current alumni plan the current plan
		$query = "UPDATE plans SET uid=${nt_id} WHERE uid=${alumni_id}";
		$result = $_user->dbh->query($query);
		if (DB::isError($result)) {
			$errors .= DB::errorMessage($result);
		}
	}	
	
	//move new archive entries to the old id
	$query = "UPDATE archive SET uid=${nt_id} WHERE uid=${alumni_id}";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}
	// TODO correctly update number of archive entries in user_table
	//$dbResult = DB::DB_result($_user->dbh,$result);	//apparently not a function
	//$numEntries = $dbResult->numRows();
	//if ($numEntries > 0) {
	//	$query = "UPDATE user SET archive_size=archive_size+${numEntries} WHERE uid=${nt_id}";
	//	$result = $_user->dbh->query($query);
	//	if (DB::isError($result)) {
	//		$errors .= DB::errorMessage($result);
	//	}
	//}
}
else if ($_POST["alumniMove"] == "archive")
{
	//move old archive entries to the new id
	$query = "UPDATE archive SET uid=${alumni_id} WHERE uid=${nt_id}";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}
	// TODO correctly update number of archive entries in user_table	
	
	//set old account as shared to new account
	$query = "INSERT INTO preferences VALUES (${nt_id}, 'shared', 'true')";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}
	$query = "INSERT INTO preferences VALUES (${nt_id}, 'shared_${alumni}', 'true')";
	$result = $_user->dbh->query($query);
	if (DB::isError($result)) {
		$errors .= DB::errorMessage($result);
	}
}
else {
	$errors = "Unexpected value for alumniMove";
}
$errors .= ob_get_contents();
ob_end_clean();

mail('npdoty@gmail.com', "planworld alumni move log", "type: ".$_POST["alumniMove"].", nt: $nt, nt_id: $nt_id, alumni: $alumni, alumni_id: $alumni_id, errors: $errors", "From: NOTE <note@amherst.edu>");

//comment this out because there's a common unimportant error (DB error: already exists) that I don't want to scare people with
//if ($errors != "") {
//	echo "There were unexpected errors.  This may not indicate any failure to move your account, but please email a NOTE admin (note@amherst.edu) with the text below:<br />".$errors;
//	$location = PW_URL_INDEX . "?id=alumni;err=1";
//}

header("Location: " . $location);
exit();
?>
