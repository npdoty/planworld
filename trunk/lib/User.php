<?php
/* $Id: User.php,v 1.20.2.11 2003/12/07 16:44:23 seth Exp $ */

/* includes */
require_once($_base . 'lib/Archive.php');
require_once($_base . 'lib/Planwatch.php');
require_once($_base . 'lib/Planworld.php');
require_once($_base . 'lib/FingerUser.php');
require_once($_base . 'lib/LJUser.php');
require_once($_base . 'lib/RemoteUser.php');
require_once($_base . 'lib/Snoop.php');

/**
 * User class for planworld.
 * @author Seth Fitzsimmons
 */
class User {
    
    /** general user information */
    var $archive;
    var $lastLogin;
    var $lastUpdate;
    var $last_ip;
    var $plan;
    var $planwatch;
    var $snitchDisplayNum;
    var $snitchEnabled;
    var $skin = PW_DEFAULT_SKIN;
    var $theme;
    var $timezone = PW_TIMEZONE;
    var $type;
    var $userID;
    var $userInfo;
    var $username;
    var $views;
    var $watchOrder;
    var $prefs;
    var $editor;

    /** flags (booleans) */
    var $admin;
    var $remoteUser;
    var $shared = false;
    var $snitch;
    var $snitchTracker;
    var $valid;
    var $world;

    var $changed = false;

    var $dbh;
    
    /**
     * Factory
     * @param uid User to initialize
     * @public
     * @static
     * @returns User
     */
    function &factory ($uid) {
      if (Planworld::isUser($uid) && !Planworld::isRemoteUser($uid)) {
	return new User($uid);
      } else if (!Planworld::isUser($uid) && !strstr($uid, '@')) {
      	return new User($uid);
      } else {
	list(,$host) = split('@', $uid);
	$nodeinfo = Planworld::getNodeInfo($host);
	if (empty($nodeinfo)) {
	  if ($host == 'livejournal.com' || $host == 'livejournal')
	    return new LJUser($uid);
	  else
	    return new FingerUser($uid);
	} else {
	  return new RemoteUser($uid, $nodeinfo);
	}
      }
    }

    /**
     * Constructor.
     * @param uid User to initialize.
     * @public
     * @returns bool
     */
    function User ($uid) {
      /* establish a database connection */
      $this->dbh = &Planworld::_connect();

      $this->type = 'local';

      if (is_string($uid)) {
	$this->username = $uid;
      } else if (is_int($uid)) {
	$this->userID = (int) $uid;
      }
      
      /* check if this user exists */
      if (isset($this->userID) || $this->isUser()) {
	$this->load();
      }
    }
    
    /**
     * Loads user information.
     * @public
     * @returns bool
     */
    function load () {
      /* Build the query */
      $query = 'SELECT * FROM users WHERE ';
      if (isset($this->username)) $query .= "username='" . addslashes($this->username) . "'";
      else if (isset($this->userID)) $query .= "id='{$this->userID}'";
      else return false;
      
      $result = $this->dbh->limitQuery($query,0,1);
      $this->dbh->limit_from = $this->dbh->limit_count = null;
      
      if (isset($result) && !DB::isError($result)) {
	$row = $result->fetchRow();
	if (DB::isError($row)) return false;
	
	$this->userID = (int) $row['id'];
	$this->username = $row['username'];
	$this->remoteUser = ($row['remote'] == 'Y') ? true : false;
	$this->world = ($row['world'] == 'Y') ? true : false;
	$this->snitch = ($row['snitch'] == 'Y') ? true : false;
	$this->archive = $row['archive'];
	$this->snitchDisplayNum = $row['snitch_views'];
	$this->views = $row['views'];
	$this->watchOrder = $row['watch_order'];
	$this->theme = $row['theme_id'];
	$this->snitchEnabled = $row['snitch_activated'];
	$this->lastLogin = $row['last_login'];
	$this->lastUpdate = $row['last_update'];
      } else {
	return false;
      }
      
      /* fetch miscellaneous preferences */
      if ($tz = $this->getPreference('timezone')) {
	$this->timezone = $tz;
      }

      $this->snitchTracker = ($this->getPreference('snitchtracker') == 'true') ? true : false;
      
      // data has now been synchronized
      $this->changed = false;
      return true;
    }
    
    function dump () {
      echo "user id: " . $this->userID . "<br />\n";
      echo "username: " . $this->username . "<br />\n";
      echo "remote user: " . (($this->remoteUser) ? 'true' : 'false') . "<br />\n";
      echo "world-viewable: " . (($this->world) ? 'true' : 'false') . "<br />\n";
      echo "snitch: " . (($this->snitch) ? 'true' : 'false') . "<br />\n";
      echo "snitchtracker: " . (($this->snitchTracker) ? 'true' : 'false') . "<br />\n";
      echo "archive: " . $this->archive . "<br />\n";
      echo "snitch display #: " . $this->snitchDisplayNum . "<br />\n";
      echo "views: " . $this->views . "<br />\n";
      echo "watchOrder: " . $this->watchOrder . "<br />\n";
      echo "theme: " . $this->theme . "<br />\n";
      echo "timezone: " . $this->timezone . "<br />\n";
      echo "snitchEnabled: " . $this->snitchEnabled . "<br />\n";
      echo "lastLogin: " . $this->lastLogin . "<br />\n";
      echo "lastUpdate: " . $this->lastUpdate . "<br />\n";
    }

    /**
     * Saves user information.
     * @public
     * @returns bool Whether any data was actually saved
     */
    function save () {
      if ($this->changed) {
	/* column <-> variable mapping */
	$info = array();
	$info['username'] = "'{$this->username}'";
	$info['remote'] = ($this->remoteUser) ? "'Y'" : "'N'";
	$info['world'] = ($this->world) ? "'Y'" : "'N'";
	$info['snitch'] = ($this->snitch) ? "'Y'" : "'N'";
	$info['snitch_views'] = &$this->snitchDisplayNum;
	$info['archive'] = "'" . $this->archive . "'";
	$info['watch_order'] = "'{$this->watchOrder}'";
	$info['theme_id'] = &$this->theme;
	$info['snitch_activated'] = &$this->snitchEnabled;
	$info['last_login'] = &$this->lastLogin;
	$info['last_update'] = &$this->lastUpdate;
	$info['last_ip'] = "'{$this->last_ip}'";
	
	/* assemble the query */
	$pair = array();
	foreach ($info as $key => $value) {
	  $pair[] = $key . '=' . $value;
	}
	$query = 'UPDATE users SET ';
	$query .= implode(',', $pair);
	if (isset($this->username)) $query .= " WHERE username='{$this->username}'";
	else if (isset($this->userID)) $query .= " WHERE id='{$this->userID}'";
	else return false;

	/* execute the query */
	$result = $this->dbh->query($query);
	if (DB::isError($result)) {
	  return false;
	}
	
	/* data has now been synchronized */
	$this->changed = false;
      }

      /* save any changed planwatch data if need be */
      if (isset($this->planwatch)) {
	$this->planwatch->save();
      }

      return true;
    }
    
    /**
     * Create this user
     */
    function create () {
      /* create the user */
      if (isset($this->username))
	$this->userID = Planworld::addUser($this->username);

      /* set the valid flag */
      $this->valid = true;
    }

    /**
     * Clear this user's snitch list.
     * @private
     * @returns void
     */
    function clearSnitch () {
        $this->snitch = false;
        $this->snitchEnabled = 0;
        $this->changed = true;

        $query = "DELETE FROM snitch WHERE uid={$this->userID}";
        $this->dbh->query($query);
    }
    
    /**
     * Clear this user's plan.
     * @returns void
     */
    function clearPlan () {
      $refs = Snoop::_getReferences($this->getPlan($this));

      /* delete one's plan */
      $this->dbh->query("DELETE FROM plans WHERE uid=" . $this->userID);
      
      /* update the last update time */
      $this->dbh->query("UPDATE users SET last_update=" . mktime() . " WHERE id=" . $this->userID);
      
      /* clear snoop references */
      Snoop::clearReferences($this->userID);
      
      $hosts = array();
      foreach ($refs[1] as $r) {
	if (!strstr($r, '@')) continue;
	list($user, $host) = split('@', $r);
	if (!in_array($host, $hosts)) {
	  $hosts[] = $host;
	  Snoop::clearRemoteReferences(Planworld::getNodeInfo($host), $this->username);
	}
      }
    }

    /**
     * Add a view by $user to this user's snitch list.
     * @param user Viewing user.
     * @returns void
     */
    function addSnitchView (&$user) {
      
      if ($this->snitchTracker) {
	/* add an entry to the snitch tracker */
	$query = "INSERT INTO snitchtracker (uid, s_uid, viewed) VALUES (" . $this->userID . ", " . $user->getUserID() . ", " . mktime() . ")";
	$this->dbh->query($query);
      }

      $query = "UPDATE snitch SET last_view=" . mktime() . ", views=views + 1 WHERE uid=" . $this->userID . " AND s_uid=" . $user->getUserID();

      /* attempt to execute the query */
      $result = $this->dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	if ($this->dbh->affectedRows() < 1) {
	  /* query failed; use this instead */
	  $this->dbh->query("INSERT INTO snitch (uid, s_uid, last_view, views) VALUES (" . $this->userID . ", " . $user->getUserID() . ", " . mktime() . ", 1)");
	} else {
	  return true;
	}
      } else {
	return PLANWORLD_ERROR;
      }
    }

    /**
     * Increment the number of plan views for this user.
     * @public
     * @returns int
     */
    function addView () {
      $this->views++;
      $this->dbh->query("UPDATE users SET views=views+1 WHERE id=" . $this->userID);
      return $this->views;
    }
    
    /**
     * Starts this user's snitch list.
     * @private
     * @returns void
     */
    function startSnitch () {
        $this->snitch = true;
        $this->snitchEnabled = mktime();
        $this->changed = true;
    }
    
    /* flags */
    
    /**
     * Is this user an admin?
     * @public
     * @returns bool
     */
    function isAdmin () {
      if (!isset($this->admin)) {
	$this->admin = $this->getPreference('admin');
      }
      return $this->admin;
    }
    
    /**
     * Is this user's plan archived?
     * @public
     * @returns bool
     */
    function isArchived () {
        return ($this->archive == 'Y' || $this->archive == 'P') ? true : false;
    }
    
    /**
     * Is this user's plan archived publicly?
     * @public
     * @returns bool
     */
    function isArchivedPublicly () {
        return ($this->archive == 'Y') ? true : false;
    }
    
    /**
     * Has this user been changed?
     * @public
     * @returns bool
     */
    function isChanged () {
        return $this->changed;
    }
    
    /**
     * Is this user remote?
     * @public
     * @returns bool
     */
    function isRemoteUser () {
        return $this->remoteUser;
    }

    /**
     * Is this a shared plan?
     * @public
     * @returns bool
     */
    function isShared () {
      return $this->getPreference('shared') && $this->shared;
    }

    /**
     * Mark this as an actively shared plan (not the logged-in user).
     */
    function setShared () {
      $this->shared = true;
    }

    /**
     * Set the user who is editing this shared plan.
     */
    function setEditingUser ($user) {
      $this->editor = $user;
    }

    /**
     * Is this shared for $uid to edit?
     */
    function isSharedFor (&$uid) {
      if (is_object($uid)) {
	$username = $uid->getUsername();
      } else if (is_string($uid)) {
	$username = $uid;
      } else {
	return false;
      }
      return $this->getPreference('shared') && $this->getPreference('shared_' . $username);
    }

    /**
     * Return a list of the users for whom this plan is shared.
     */
    function showSharedUsers () {
      $query = "SELECT name FROM preferences WHERE name LIKE 'shared_%' AND uid=" . $this->userID;
      
      /* execute the query */
      $result = $this->dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	$ret = '';
	while ($row = $result->fetchRow()) {
	  $ret .= substr($row['name'], 7) . "\n";
	}
	return $ret;
      } else {
	return PLANWORLD_ERROR;
      }
    }

    /**
     * Return a list of users whose plans this user is allowed to edit.
     */
    function getPermittedPlans () {
      $query = "SELECT uid, username FROM preferences as p, users as u WHERE p.name='shared_" . $this->username . "' AND u.id=p.uid";

      /* execute the query */
      $result = $this->dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	$ret = array();
	while ($row = $result->fetchRow()) {
	  $ret[] = $row['username'];
	}
        if (empty($ret))
	  return false;
	else
	  return $ret;
      } else {
	return false;
      }
    }

    /**
     * Does this user exist?
     * @public
     * @returns bool
     */
    function isUser () {
      if (!isset($this->valid)) {
	$this->valid = Planworld::isUser($this->username, true);
      }
      return $this->valid;
    }
    
    /**
     * Is this user's plan world-accessible?
     * @public
     * @returns bool
     */
    function isWorld () {
        return $this->getWorld();
    }
    
    /**
     * Is this user new? (NPD: for welcome pages)
     * @public
     * @returns bool
     */
    function isNew() {
    	return !$this->lastUpdate;
    }

    /**
     * Guess the user's class year (NPD: for alumni stuff)
     * @public
     * @returns string
     */
    function getYear() {
    	$name = $this->username;
		ereg("([0-9]{2})$",$name,$regs);
		if ($regs[0] != "") return $regs[0];
		else return "UNKNOWN";
    }

	function hasOldUsername()
	{
		$oldUsernamesArray = array('NewUsername' => 'OldUsername',
		'hmplum04' => 'hmplum03',
		'arc' => 'npdoty',
		'jsschmiedeskamp55' => 'JSSCHMIEDESKAMP5',
		'flancasterraymond51' => 'FLANCASTERRAYMON',
		'cewoolmanwashburn46' => 'CEWOOLMANWASHBU',
		'dwalsh' => 'DCWALSH',
		'jslouis63' => 'JSLOUIS',
		'bhcraigen05' => 'BHCraigen06',
		'psstatt' => 'PSSTATT78',
		'dldix' => 'DDBECK',
		'hwagnervosskochler87' => 'HWAGNERVOSSKOCHL',
		'gthargreavesheald72' => 'GTHARGREAVESHEAL',
		'keleisman05' => 'KELeisman06',
		'phayes' => 'PWHAYES03',
		'jslwebugamukasa70' => 'JSLWEBUGAMUKASA7',
		'pamieczkowski' => 'PAMIECZKOWSK',
		'tgerety' => 'TRGERETY',
		'pmanly56' => 'PMANLY',
		'kli05' => 'KLI06',
		'bbarmak' => 'BEBARMAK98',
		'jmdoyle' => 'JMDOYLE91',
		'ajhart' => 'AJHART82',
		'jdtang05' => 'JDTang06',
		'RABEAUDOIN' => 'RABEAUDOIN98',
		'jrmead' => 'JRMead04',
		'ddhixon' => 'DDHIXON75',
		'jaclark' => 'JCLARK',
		'pjpowers' => 'PJPOWERS90',
		'jaarena' => 'JAARENA83',
		'jwmanly' => 'JWMANLY85',
		'rhromer' => 'RHROMER52',
		'mkomard03' => 'MKOMARD06',
		'hlvonschmidt' => 'HLVONSCHMIDT78',
		'tpsengupta03' => 'TPSENGUPTA',
		'tzhou' => 'TZHOU05',
		'jaolmos' => 'JAOLMOS05',
		'ccobhamsande' => 'CRCOBHAM',
		'pmiller' => 'PJBOYLE',
		'aellis' => 'AEELLIS98',
		'ikdamonarmstrong84' => 'IKDAMONARMSTRONG',
		'sfkaplan' => 'SFKAPLAN95',
		'slplatteviandier86' => 'SLPLATTEVIANDIER',
		'ncmurray45' => 'NCMURRAY',
		'japistel' => 'JAPISTEL69',
		'ecsmith' => 'ECSMITH84',
		'jddrake05' => 'JDDrake06',
		'dahardawar' => 'DAHardawar05',
		'tcsimoneau00' => 'TCBUCKNELLPOGUE0',
		'ebacker' => 'EBAcker05',
		'sggreenkyknecht83' => 'SGGREENKYKNECHT8',
		'brblock80' => 'BRBLOCKGOTTESMAN',
		'hgilpin' => 'HGILPIN84',
		'aspostman90' => 'ALSIEGELPOSTMAN9',
		'egreenbergschneide79' => 'EGREENBERGSCHNEI',
		'kdduke' => 'KDDuke05',
		'klfretwell' => 'KLFRETWELL81',
		'egarespacochaga93' => 'EGARESPACOCHAGA9',
		'jwreyes' => 'JWWOLPAW94',
		'dmtull' => 'dmtull06',
		'tesafronoff97' => 'TENEELAKANTAPPA9',
		'rabinder' => 'RABinder02',
		'mpvanhoogenstyn46' => 'MPVANHOOGENSTYN4',
		'elnasreddinlongo85' => 'ELNASREDDINLONGO',
		'mggamosobenhamed91' => 'MGGAMOSOBENHAMED',
		'koedwards' => 'CMOHARA87',
		'pdfloodradoslovich85' => 'PDFLOODRADOSLOVI',
		'jphealy91' => '1PHEALY91',
		'teriksen' => 'TWERIKSEN88',
		'snstonecrivelli97' => 'SNSTONECRIVELLI9',
		'cslifschultz93' => 'CSLIFSCHULTZ',
		'gculucundis82' => 'GACOULOUCOUNDIS8',
		'avennemawettick83' => 'AVENNEMAWETTICK8',
		'rbrittenloprete90' => 'RBRITTENLOPRETE9',
		'recorfieldmartin90' => 'RECORFIELDMARTIN',
		'aapiccinidevelazqu90' => 'AAPICCINIDEVELAZ',
		'djreaume' => 'DJReaume02',
		'palohrerlefebvre88' => 'PALOHRERLEFEBVRE',
		'msbayliss84' => 'MSBAYLISSPORTER8',
		'hrumansbialowas83' => 'HRUMANSBIALOWAS8',
		'hsfeinsteinthompso78' => 'HSFEINSTEINTHOMP',
		'ewbromberg' => 'EWBromberg02',
		'crnewman' => 'CRNEWMAN90',
		'sjrabinowitz' => 'SJRABINOWIT',
		'aokuasigassaway75' => 'AJKUASIGASSAWAY7',
		'jeforman' => 'JEForman05',
		'jmarburggoodman79' => 'JMARBURGGOODMAN7',
		'rmchung' => 'RMChung05',
		'kemiscallbannon88' => 'KEMISCALLBANNON8',
		'jbbaltaxekolodner94' => 'JTBALTAXEKOLODNE',
		'istavans' => 'ISTAVCHANSKY',
		'racouloucoundis79' => 'RACOULOUCOUNDIS7',
		'whpritchard' => 'WHPRITCHARD53',
		'smstafford' => 'SMStafford05',
		'pebendicksen78' => 'PEBENDICKSENIII7',
		'liblackwoodellis80' => 'LIBLACKWOODELLIS',
		'nmjohnstonbrown88' => 'NMJOHNSTONBROWN8',
		'rcardona' => 'RCardona05',
		'omrichards' => 'omrichards06',
		'jbquigley' => 'JBQuigley04',
		'recorrigan05' => 'RECorrigan06',
		'llitchfieldkimber92' => 'LLITCHFIELDKIMBE',
		'jrhernandezribicof87' => 'JRHERNANDEZRIBIC',
		'hcruzhubbard93' => 'HGMORGANHUBBARD9',
		'wmvickery' => 'WMVICKERY57',
		'kjsanchezepp' => 'KJSANCHEZ',
		'hmsmith' => 'HMSMITH57',
		'kafinnertyclarke83' => 'KAFINNERTYCLARKE',
		'acameron51' => 'ACAMERON',
		'akramanathan' => 'AKRamanthan06',
		'rdelacarrera' => 'RMDELACARRERA',
		'mgandersenhunter88' => 'MGANDERSENHUNTER',
		'ppwintersteiner64' => 'PPWINTERSTEINER6',
		'atlichtenberger51' => 'ATLICHTENBERGER5',
		'mrjacobsoncarroll87' => 'MRJACOBSONCARROL',
		'wskleingoldhersz80' => 'WSKLEINGOLDHERSZ',
		'ptlobdell' => 'PTLOBDELL68',
		'dschatzkinhiggins76' => 'DSCHATZKINHIGGIN',
		'aharmstrongcoben85' => 'AHARMSTRONGCOBEN',
		'scdickman' => 'SCDICKMAN89',
		'eballard' => 'EESWAIN95',
		'crmartinstanley79' => 'CRMARTINSTANLEY7',
		'igaprindashvili98' => 'IGAPRINDASHVILI9',
		'elleggettsweeney89' => 'ELLEGGETTSWEENEY',
		'jlhimmelstei' => 'JLHIMMELSTEIN',
		'wlgundersheimer59' => 'WLGUNDERSHEIMER5',
		'fwesthoff' => 'FHWESTHOFF',
		'mwindfeldhansen78' => 'MWINDFELDHANSEN7',
		'amiddletonbauer85' => 'AMIDDLETONBAUER8',
		'spstockeredwards84' => 'SPSTOCKEREDWARDS',
		'taneale' => 'TANEALE70',
		'taehrgood' => 'TAEHRGOOD73',
		'jbatgosgarfinkle85' => 'JBATGOSGARFINKLE',
		'skpeck' => 'SKPECK84',
		'dharper' => 'DVHARPER90',
		'nghahn' => 'nghahn06',
		'pvcornellduhoux73' => 'PVCORNELLDUHOUX7',
		'jcrasatarainiketam98' => 'JCRASATARAINIKET',
		'pmbrunnschweiler80' => 'PMBRUNNSCHWEILER',
		'cytakahashi85' => 'CYTAKAHASHIKELSE',
		'dshall' => 'DSHALL91',
		'rmschell' => 'RMSCHELL72',
		'eoashamu' => 'EOAshamu04',
		'vmmccauley81' => 'VMMCCAULEY',
		'hemyers' => 'HEMyers05',
		'eechanlettavery96' => 'EECHANLETTAVERY9',
		'clakor' => 'CLAKOR05',
		'mjarp' => 'MJARP01',
		'caciepiela' => 'CACIEPIELA83',
		'bmrodriguezcancio94' => 'BMRODRIGUEZCANCI',
		'tmcarter04' => 'TMCarter',
		'dcwilson' => 'DCWILSON62',
		'lbernerholmberg82' => 'LBERNERHOLMBERG8',
		'pwesthoff' => 'LWESTHOFF',
		'srpiercecoleman86' => 'SRPIERCECOLEMAN8',
		'kcouch' => 'KCCOUCH95',
		'psmayerammirati81' => 'PSMAYERAMMIRATI8',
		'bamuhammad' => 'BAMUHAMMAD99',
		'ttvonrosenvinge63' => 'TTVONROSENVINGE6',
		'elbutlerakinyemi95' => 'ELBUTLERAKINYEMI',
		'bpwhittenberger73' => 'BPWHITTENBERGER7',
		'eivorychambers83' => 'EWIVORYCHAMBERS8',
		'wpsinnottarmstrong77' => 'WPSINNOTTARMSTRO',
		'malanningkinderman91' => 'MALANNINGKINDERM',
		'ralopez' => 'RALOPEZ93',
		'rjsaunderspullman87' => 'RJSAUNDERSPULLMA',
		'rmbakeryeboa' => 'rmbakeryeboa08',
		'speppersullivan82' => 'SPEPPERSULLIVAN8',
		'wcboeschenstein62' => 'WCBOESCHENSTEIN6',
		'lagomez' => 'LAGOMEZ03',
		'jedubinsky98' => 'JEDUBINSKY95',
		'cvhollingsworth88' => 'CVHOLLINGSWORTH8',
		'gtmarshall80' => 'GSABESTIANTECUMA',
		'elevisonwilliams80' => 'ELEVISONWILLIAMS',
		'mlkopaska' => 'mlkopaska05',
		'teconnerbernardez80' => 'TECONNERBERNARDE',
		'jtwalker05' => 'JTWalker06',
		'jjtrauschtvanhorn88' => 'JJTRAUSCHTVANHOR',
		'cckaplan86' => 'CCSCHUSTER86',
		'sesorscher05' => 'SESorscher06',
		'coronquillo92' => 'CRONQUILLO',
		'jaedwards' => 'JAEdwards05',
		'cjkuipers' => 'CJKUIPERS01',
		'ecarr' => 'EECARR',
		'raflibotteluskow90' => 'RAFLIBOTTELUSKOW',
		'dgwong' => 'DGWong04',
		'rlharrisfuentes00' => 'RLHARRISFUENTES0',
		'mpjanisaparicio82' => 'MPJANISAPARICIO8',
		'sksoken04' => 'SKSOKEN06',
		'jslaguilles' => 'JSLAGUILLES97',
		'whtreseder' => 'WHTRESEDER03',
		'mmartinezdelrio80' => 'MMARTINEZDELRIO8',
		'jwvonderschulenbur80' => 'JWVONDERSCHULENB',
		'idecrombrugghemcgi93' => 'IMDECROMBRUGGHE9',
		'amiddletonmerrick87' => 'AMIDDLETONMERRIC',
		'lggonzalezesteves84' => 'LGGONZALEZESTEVE',
		'jrgonzalezesteves82' => 'JRGONZALEZESTEVE',
		'fjmartinezalvarez82' => 'FJMARTINEZALVARE',
		'imberlingerivincen88' => 'IMBERLINGERIVINC',
		'kmchongsiriwatana00' => 'KMCHONGSIRIWATAN',
		'sahenderson' => 'sahenderson06',
		'samasinter' => 'SAMasinter04',
		'rcollar' => 'rcollar06',
		'vjbowman' => 'vjbowman06',
		'nadahlman' => 'NADAHLMAN98',
		'jjhkim00' => 'JJKIM00A',
		'absanipe' => 'ABSanipe05',
		'kwilliams' => 'KHWILLIAMS',
		'dhylee01' => 'DHLEE01A',
		'hleung' => 'HOLEUNG',
		'nko' => 'nko06',
		'mvdabova' => 'mvdabova06',
		'splynch' => 'splynch06',
		'lawojcik' => 'lawojcik06',
		'ksraverta' => 'ksraverta06',
		'sjbirnsswindlehurst' => 'sjbirnsswindle06',
		'athadley' => 'athadley06',
		'smmaurer' => 'smmaurer06',
		'jneley' => 'jneley06',
		'tshooper' => 'tshooper06',
		'metedaldi' => 'metedaldi06',
		'njbrewster' => 'njbrewster06',
		'kchunt' => 'kchunt06',
		'ashurd' => 'ashurd06',
		'cmburnor' => 'cmburnor06',
		'mkim' => 'mkim06',
		'neross' => 'neross06',
		'emscheiderer' => 'emscheiderer06',
		'jcrucker' => 'jcrucker06',
		'chkim' => 'chkim06',
		'jlbuchman' => 'jlbuchman07',
		'jbcollins05' => 'JBCOLLINS06',
		'nhjuul' => 'NHJUUL05',
		'ejangowski' => 'EJANGOWSKI05',
		'slaidlaw' => 'WSLAIDLAW',
		'ckeller' => 'CWKELLER',
		'marx' => 'AWMARX',
		'nnbastien' => 'NMBASTIEN',
		'mbradbury10' => 'mbradbury09',
		'vsochat08' => 'VVSOCHAT',
		'sconway10' => 'sconway09',
		'aoka' => 'aoka06',
		'rabbey10' => 'rabbey09',
		'semiller10' => 'smiller09',
		'kleeroberts' => 'kmleeroberts',
		'ccunningham' => 'CACUNNINGHAM',
		'jdiaz10' => 'jdiaz09',
		'ehowland' => 'bhowland',
		'tlamkin10' => 'tlamkincarughi10',
		'achang10' => 'achanggraham10',
		'eleblanc' => 'EALEBLANC',
		'lbui10' => 'ldbui',
		'jbeyer' => 'JNBEYER',
		'coettel10' => 'coettelflaherty10',
		'acalderon' => 'APCALDERON',
		'dpaula' => 'DCPAULA',
		'gdiaz10' => 'gdiazsilveira10',
		'ccallahan' => 'CLCALLAHAN',
		'mgonzalez10' => 'mgonzalezhernandez10',
		'jchisamore' => 'jmchisamore',
		'lgaylebrissett' => 'ldgaylebr',
		'ctarantino' => 'cntarantino',
		'eboutilier' => 'EGBOUTILIER');
		
		return array_key_exists($this->username, $oldUsernamesArray);
	}
	
	function oldUsername()
	{
		$oldUsernamesArray = array('NewUsername' => 'OldUsername',
		'hmplum04' => 'hmplum03',
		'arc' => 'npdoty',
		'jsschmiedeskamp55' => 'JSSCHMIEDESKAMP5',
		'flancasterraymond51' => 'FLANCASTERRAYMON',
		'cewoolmanwashburn46' => 'CEWOOLMANWASHBU',
		'dwalsh' => 'DCWALSH',
		'jslouis63' => 'JSLOUIS',
		'bhcraigen05' => 'BHCraigen06',
		'psstatt' => 'PSSTATT78',
		'dldix' => 'DDBECK',
		'hwagnervosskochler87' => 'HWAGNERVOSSKOCHL',
		'gthargreavesheald72' => 'GTHARGREAVESHEAL',
		'keleisman05' => 'KELeisman06',
		'phayes' => 'PWHAYES03',
		'jslwebugamukasa70' => 'JSLWEBUGAMUKASA7',
		'pamieczkowski' => 'PAMIECZKOWSK',
		'tgerety' => 'TRGERETY',
		'pmanly56' => 'PMANLY',
		'kli05' => 'KLI06',
		'bbarmak' => 'BEBARMAK98',
		'jmdoyle' => 'JMDOYLE91',
		'ajhart' => 'AJHART82',
		'jdtang05' => 'JDTang06',
		'RABEAUDOIN' => 'RABEAUDOIN98',
		'jrmead' => 'JRMead04',
		'ddhixon' => 'DDHIXON75',
		'jaclark' => 'JCLARK',
		'pjpowers' => 'PJPOWERS90',
		'jaarena' => 'JAARENA83',
		'jwmanly' => 'JWMANLY85',
		'rhromer' => 'RHROMER52',
		'mkomard03' => 'MKOMARD06',
		'hlvonschmidt' => 'HLVONSCHMIDT78',
		'tpsengupta03' => 'TPSENGUPTA',
		'tzhou' => 'TZHOU05',
		'jaolmos' => 'JAOLMOS05',
		'ccobhamsande' => 'CRCOBHAM',
		'pmiller' => 'PJBOYLE',
		'aellis' => 'AEELLIS98',
		'ikdamonarmstrong84' => 'IKDAMONARMSTRONG',
		'sfkaplan' => 'SFKAPLAN95',
		'slplatteviandier86' => 'SLPLATTEVIANDIER',
		'ncmurray45' => 'NCMURRAY',
		'japistel' => 'JAPISTEL69',
		'ecsmith' => 'ECSMITH84',
		'jddrake05' => 'JDDrake06',
		'dahardawar' => 'DAHardawar05',
		'tcsimoneau00' => 'TCBUCKNELLPOGUE0',
		'ebacker' => 'EBAcker05',
		'sggreenkyknecht83' => 'SGGREENKYKNECHT8',
		'brblock80' => 'BRBLOCKGOTTESMAN',
		'hgilpin' => 'HGILPIN84',
		'aspostman90' => 'ALSIEGELPOSTMAN9',
		'egreenbergschneide79' => 'EGREENBERGSCHNEI',
		'kdduke' => 'KDDuke05',
		'klfretwell' => 'KLFRETWELL81',
		'egarespacochaga93' => 'EGARESPACOCHAGA9',
		'jwreyes' => 'JWWOLPAW94',
		'dmtull' => 'dmtull06',
		'tesafronoff97' => 'TENEELAKANTAPPA9',
		'rabinder' => 'RABinder02',
		'mpvanhoogenstyn46' => 'MPVANHOOGENSTYN4',
		'elnasreddinlongo85' => 'ELNASREDDINLONGO',
		'mggamosobenhamed91' => 'MGGAMOSOBENHAMED',
		'koedwards' => 'CMOHARA87',
		'pdfloodradoslovich85' => 'PDFLOODRADOSLOVI',
		'jphealy91' => '1PHEALY91',
		'teriksen' => 'TWERIKSEN88',
		'snstonecrivelli97' => 'SNSTONECRIVELLI9',
		'cslifschultz93' => 'CSLIFSCHULTZ',
		'gculucundis82' => 'GACOULOUCOUNDIS8',
		'avennemawettick83' => 'AVENNEMAWETTICK8',
		'rbrittenloprete90' => 'RBRITTENLOPRETE9',
		'recorfieldmartin90' => 'RECORFIELDMARTIN',
		'aapiccinidevelazqu90' => 'AAPICCINIDEVELAZ',
		'djreaume' => 'DJReaume02',
		'palohrerlefebvre88' => 'PALOHRERLEFEBVRE',
		'msbayliss84' => 'MSBAYLISSPORTER8',
		'hrumansbialowas83' => 'HRUMANSBIALOWAS8',
		'hsfeinsteinthompso78' => 'HSFEINSTEINTHOMP',
		'ewbromberg' => 'EWBromberg02',
		'crnewman' => 'CRNEWMAN90',
		'sjrabinowitz' => 'SJRABINOWIT',
		'aokuasigassaway75' => 'AJKUASIGASSAWAY7',
		'jeforman' => 'JEForman05',
		'jmarburggoodman79' => 'JMARBURGGOODMAN7',
		'rmchung' => 'RMChung05',
		'kemiscallbannon88' => 'KEMISCALLBANNON8',
		'jbbaltaxekolodner94' => 'JTBALTAXEKOLODNE',
		'istavans' => 'ISTAVCHANSKY',
		'racouloucoundis79' => 'RACOULOUCOUNDIS7',
		'whpritchard' => 'WHPRITCHARD53',
		'smstafford' => 'SMStafford05',
		'pebendicksen78' => 'PEBENDICKSENIII7',
		'liblackwoodellis80' => 'LIBLACKWOODELLIS',
		'nmjohnstonbrown88' => 'NMJOHNSTONBROWN8',
		'rcardona' => 'RCardona05',
		'omrichards' => 'omrichards06',
		'jbquigley' => 'JBQuigley04',
		'recorrigan05' => 'RECorrigan06',
		'llitchfieldkimber92' => 'LLITCHFIELDKIMBE',
		'jrhernandezribicof87' => 'JRHERNANDEZRIBIC',
		'hcruzhubbard93' => 'HGMORGANHUBBARD9',
		'wmvickery' => 'WMVICKERY57',
		'kjsanchezepp' => 'KJSANCHEZ',
		'hmsmith' => 'HMSMITH57',
		'kafinnertyclarke83' => 'KAFINNERTYCLARKE',
		'acameron51' => 'ACAMERON',
		'akramanathan' => 'AKRamanthan06',
		'rdelacarrera' => 'RMDELACARRERA',
		'mgandersenhunter88' => 'MGANDERSENHUNTER',
		'ppwintersteiner64' => 'PPWINTERSTEINER6',
		'atlichtenberger51' => 'ATLICHTENBERGER5',
		'mrjacobsoncarroll87' => 'MRJACOBSONCARROL',
		'wskleingoldhersz80' => 'WSKLEINGOLDHERSZ',
		'ptlobdell' => 'PTLOBDELL68',
		'dschatzkinhiggins76' => 'DSCHATZKINHIGGIN',
		'aharmstrongcoben85' => 'AHARMSTRONGCOBEN',
		'scdickman' => 'SCDICKMAN89',
		'eballard' => 'EESWAIN95',
		'crmartinstanley79' => 'CRMARTINSTANLEY7',
		'igaprindashvili98' => 'IGAPRINDASHVILI9',
		'elleggettsweeney89' => 'ELLEGGETTSWEENEY',
		'jlhimmelstei' => 'JLHIMMELSTEIN',
		'wlgundersheimer59' => 'WLGUNDERSHEIMER5',
		'fwesthoff' => 'FHWESTHOFF',
		'mwindfeldhansen78' => 'MWINDFELDHANSEN7',
		'amiddletonbauer85' => 'AMIDDLETONBAUER8',
		'spstockeredwards84' => 'SPSTOCKEREDWARDS',
		'taneale' => 'TANEALE70',
		'taehrgood' => 'TAEHRGOOD73',
		'jbatgosgarfinkle85' => 'JBATGOSGARFINKLE',
		'skpeck' => 'SKPECK84',
		'dharper' => 'DVHARPER90',
		'nghahn' => 'nghahn06',
		'pvcornellduhoux73' => 'PVCORNELLDUHOUX7',
		'jcrasatarainiketam98' => 'JCRASATARAINIKET',
		'pmbrunnschweiler80' => 'PMBRUNNSCHWEILER',
		'cytakahashi85' => 'CYTAKAHASHIKELSE',
		'dshall' => 'DSHALL91',
		'rmschell' => 'RMSCHELL72',
		'eoashamu' => 'EOAshamu04',
		'vmmccauley81' => 'VMMCCAULEY',
		'hemyers' => 'HEMyers05',
		'eechanlettavery96' => 'EECHANLETTAVERY9',
		'clakor' => 'CLAKOR05',
		'mjarp' => 'MJARP01',
		'caciepiela' => 'CACIEPIELA83',
		'bmrodriguezcancio94' => 'BMRODRIGUEZCANCI',
		'tmcarter04' => 'TMCarter',
		'dcwilson' => 'DCWILSON62',
		'lbernerholmberg82' => 'LBERNERHOLMBERG8',
		'pwesthoff' => 'LWESTHOFF',
		'srpiercecoleman86' => 'SRPIERCECOLEMAN8',
		'kcouch' => 'KCCOUCH95',
		'psmayerammirati81' => 'PSMAYERAMMIRATI8',
		'bamuhammad' => 'BAMUHAMMAD99',
		'ttvonrosenvinge63' => 'TTVONROSENVINGE6',
		'elbutlerakinyemi95' => 'ELBUTLERAKINYEMI',
		'bpwhittenberger73' => 'BPWHITTENBERGER7',
		'eivorychambers83' => 'EWIVORYCHAMBERS8',
		'wpsinnottarmstrong77' => 'WPSINNOTTARMSTRO',
		'malanningkinderman91' => 'MALANNINGKINDERM',
		'ralopez' => 'RALOPEZ93',
		'rjsaunderspullman87' => 'RJSAUNDERSPULLMA',
		'rmbakeryeboa' => 'rmbakeryeboa08',
		'speppersullivan82' => 'SPEPPERSULLIVAN8',
		'wcboeschenstein62' => 'WCBOESCHENSTEIN6',
		'lagomez' => 'LAGOMEZ03',
		'jedubinsky98' => 'JEDUBINSKY95',
		'cvhollingsworth88' => 'CVHOLLINGSWORTH8',
		'gtmarshall80' => 'GSABESTIANTECUMA',
		'elevisonwilliams80' => 'ELEVISONWILLIAMS',
		'mlkopaska' => 'mlkopaska05',
		'teconnerbernardez80' => 'TECONNERBERNARDE',
		'jtwalker05' => 'JTWalker06',
		'jjtrauschtvanhorn88' => 'JJTRAUSCHTVANHOR',
		'cckaplan86' => 'CCSCHUSTER86',
		'sesorscher05' => 'SESorscher06',
		'coronquillo92' => 'CRONQUILLO',
		'jaedwards' => 'JAEdwards05',
		'cjkuipers' => 'CJKUIPERS01',
		'ecarr' => 'EECARR',
		'raflibotteluskow90' => 'RAFLIBOTTELUSKOW',
		'dgwong' => 'DGWong04',
		'rlharrisfuentes00' => 'RLHARRISFUENTES0',
		'mpjanisaparicio82' => 'MPJANISAPARICIO8',
		'sksoken04' => 'SKSOKEN06',
		'jslaguilles' => 'JSLAGUILLES97',
		'whtreseder' => 'WHTRESEDER03',
		'mmartinezdelrio80' => 'MMARTINEZDELRIO8',
		'jwvonderschulenbur80' => 'JWVONDERSCHULENB',
		'idecrombrugghemcgi93' => 'IMDECROMBRUGGHE9',
		'amiddletonmerrick87' => 'AMIDDLETONMERRIC',
		'lggonzalezesteves84' => 'LGGONZALEZESTEVE',
		'jrgonzalezesteves82' => 'JRGONZALEZESTEVE',
		'fjmartinezalvarez82' => 'FJMARTINEZALVARE',
		'imberlingerivincen88' => 'IMBERLINGERIVINC',
		'kmchongsiriwatana00' => 'KMCHONGSIRIWATAN',
		'sahenderson' => 'sahenderson06',
		'samasinter' => 'SAMasinter04',
		'rcollar' => 'rcollar06',
		'vjbowman' => 'vjbowman06',
		'nadahlman' => 'NADAHLMAN98',
		'jjhkim00' => 'JJKIM00A',
		'absanipe' => 'ABSanipe05',
		'kwilliams' => 'KHWILLIAMS',
		'dhylee01' => 'DHLEE01A',
		'hleung' => 'HOLEUNG',
		'nko' => 'nko06',
		'mvdabova' => 'mvdabova06',
		'splynch' => 'splynch06',
		'lawojcik' => 'lawojcik06',
		'ksraverta' => 'ksraverta06',
		'sjbirnsswindlehurst' => 'sjbirnsswindle06',
		'athadley' => 'athadley06',
		'smmaurer' => 'smmaurer06',
		'jneley' => 'jneley06',
		'tshooper' => 'tshooper06',
		'metedaldi' => 'metedaldi06',
		'njbrewster' => 'njbrewster06',
		'kchunt' => 'kchunt06',
		'ashurd' => 'ashurd06',
		'cmburnor' => 'cmburnor06',
		'mkim' => 'mkim06',
		'neross' => 'neross06',
		'emscheiderer' => 'emscheiderer06',
		'jcrucker' => 'jcrucker06',
		'chkim' => 'chkim06',
		'jlbuchman' => 'jlbuchman07',
		'jbcollins05' => 'JBCOLLINS06',
		'nhjuul' => 'NHJUUL05',
		'ejangowski' => 'EJANGOWSKI05',
		'slaidlaw' => 'WSLAIDLAW',
		'ckeller' => 'CWKELLER',
		'marx' => 'AWMARX',
		'nnbastien' => 'NMBASTIEN',
		'mbradbury10' => 'mbradbury09',
		'vsochat08' => 'VVSOCHAT',
		'sconway10' => 'sconway09',
		'aoka' => 'aoka06',
		'rabbey10' => 'rabbey09',
		'semiller10' => 'smiller09',
		'kleeroberts' => 'kmleeroberts',
		'ccunningham' => 'CACUNNINGHAM',
		'jdiaz10' => 'jdiaz09',
		'ehowland' => 'bhowland',
		'tlamkin10' => 'tlamkincarughi10',
		'achang10' => 'achanggraham10',
		'eleblanc' => 'EALEBLANC',
		'lbui10' => 'ldbui',
		'jbeyer' => 'JNBEYER',
		'coettel10' => 'coettelflaherty10',
		'acalderon' => 'APCALDERON',
		'dpaula' => 'DCPAULA',
		'gdiaz10' => 'gdiazsilveira10',
		'ccallahan' => 'CLCALLAHAN',
		'mgonzalez10' => 'mgonzalezhernandez10',
		'jchisamore' => 'jmchisamore',
		'lgaylebrissett' => 'ldgaylebr',
		'ctarantino' => 'cntarantino',
		'eboutilier' => 'EGBOUTILIER');
		
		if (array_key_exists($this->username, $oldUsernamesArray))
		{
			return $oldUsernamesArray[$this->username];
		}
		else
		{
			return "ERROR";
		}
	}
    
    /* getX and setX */
 
    /**
     * Sets a preference for this user.
     * @public
     * @returns string
     */   
    function setPreference ($name, $val) {
      $query = "DELETE FROM preferences WHERE uid=" . $this->userID . " AND name='" . $name . "'";
      $this->dbh->query($query);

      /* don't add false preferences (lack is assumed to be false) */
      if (!$val || strtolower($val) != 'false' || $val != '') {
	$query = "INSERT INTO preferences (uid, name, value) VALUES (" . $this->userID . ", '{$name}', '{$val}')";
	$this->dbh->query($query);
      }

      $this->prefs[$name] = $val;
    }

    /**
     * Fetches a preference for this user.
     */
    function getPreference ($name) {
      if (isset($this->prefs[$name]))
	return $this->prefs[$name];

      $query = "SELECT value FROM preferences WHERE uid=" . $this->userID . " AND name='{$name}'";
      
      /* execute the query */
      $result = $this->dbh->limitQuery($query,0,1);
      $this->dbh->limit_from = $this->dbh->limit_count = null;
      if (isset($result) && !DB::isError($result)) {
	$row = $result->fetchRow();
	if ($row['value'] == 'true') {
	  $this->prefs[$name] = true;
	  return true;
	} else if ($row['value'] == 'false') {
	  $this->prefs[$name] = false;
	  return false;
	} else {
	  $this->prefs[$name] = $row['value'];
	}
	return (isset($row['value']) ? $row['value'] : false);
      } else {
	return PLANWORLD_ERROR;
      }
    }


    /**
     * Clears a preference for this user.
     */
    function clearPreference ($name) {
      $query = "DELETE FROM preferences WHERE uid=" . $this->userID . " AND name LIKE '" . $name . "'";
      $this->dbh->query($query);
      unset($this->prefs[$name]);
    }

    /**
     * void setLastLogin ($ts)
     * update's users last login time to $ts (timestamp)
     */
    function setLastLogin ($ts) {
      $this->changed = true;
      $this->lastLogin = $ts;
    }

    /**
     * void setLastUpdate ($ts)
     * update's users last update time to $ts (timestamp)
     */
    function setLastUpdate ($ts) {
      $this->changed = true;
      $this->lastUpdate = $ts;
    }

    /**
     * void setLastIP ($ip)
     * update user's last known ip address
     */
    function setLastIP ($ip) {
      $this->changed = true;
      $this->last_ip = $ip;
    }

    /**
     * Get archive settings.
     * @public
     * @returns string
     */
    function getArchive () {
        return $this->archive;
    }
    
    /**
     * Set this user's archival settings
     * @param val (Y) Public / (P) private / (N) off archiving
     * @public
     * @returns bool
     */
    function setArchive ($val) {
        $val = strtoupper($val);
        if ($val == 'Y' || $val == 'P' || $val == 'N') {
            $this->archive = $val;
            $this->changed = true;
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Return the last time this user logged in.
     * @public
     * @returns int
     */
    function getLastLogin () {
        return $this->lastLogin;
    }
    
    /**
     * Return the last time this user updated his/her plan.
     * @public
     * @returns int
     */
    function getLastUpdate () {
        return $this->lastUpdate;
    }
    
    /**
     * Return formatted plan contents for display.
     * @param user User viewing plan.
     * @param plan Plan to display (if previewing).
     * @public
     * @returns Plan
     */
    function displayPlan (&$user, $plan=null, $ts=null) {
      $out = '<!-- ' . $this->getUserID() . '-->';
      if (!$user->planwatch->inPlanwatch($this) && !isset($plan)) {
	$out .= "<tt><a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t\" title=\"Add " . $this->username . " to my planwatch\">(Add to my planwatch)</a></tt><br />\n";
      } else if (!isset($plan)) {
	$out .= "<tt><a href=\"" . PW_URL_BASE . "add.php?add=" . $this->username . ";trans=t;remove=t\" title=\"Remove " . $this->username . " from my planwatch\">(Remove from my planwatch)</a></tt><br />\n";
      }

      $out .= "<tt>Login name: <strong>{$this->username}</strong>";
      if ($this->isUser() && $this->isSharedFor($user))
	$out .= " (<a href=\"" . PW_URL_INDEX . "?id=edit_plan;u={$this->username}\">edit</a>)";
      $out .= " (<a href=\"#\" onclick=\"return send('" . $this->username . "');\" title=\"send to " . $this->username . "\">send</a>)<br />\n";

      /* user doesn't exist */
      if (!$this->isUser() || ($this->lastLogin == 0 && $this->lastUpdate == 0)) {
	$out .= "Last login: ???<br />\n";
	$out .= "Last update: ???<br />\n";
	$out .= "Plan:<br />\n";
	$out .= "[Sorry, could not find \"{$this->username}\"]</tt>\n";
	return $out;
      } else if ($this->lastUpdate == 0) {
	$out .= "Last login: " . Planworld::getDisplayDate($this->lastLogin) . "<br />\n";
	$out .= "Last update: Never<br />\n";
	$out .= "Plan:<br />\n";
	$out .= "[No Plan]</tt>\n";
	return $out;
      }

      $out .= "Last login: " . Planworld::getDisplayDate($this->lastLogin) . "<br />\n";
      $out .= "Last updated: " . Planworld::getDisplayDate($this->lastUpdate);

      if (Archive::hasPublicEntries($this->userID) || $user->getUserID() == $this->userID) {
	$out .= " (<a href=\"" . PW_URL_INDEX . "?id=archiving;u=" . $this->username . "\" title=\"Archives\">archives</a>)";
      }

      $out .= "<br />\n";

      if (isset($ts)) {
	$out .= "Date posted: " . Planworld::getDisplayDate($ts);
	if ($name = Archive::getName($this->userID, $ts)) {
	  $out .= ' (<strong>' . Archive::getName($this->userID, $ts) . '</strong>)';
	}
	$out .= "<br />\n";
      }

      $out .= "Plan:</tt>\n";

      /* assemble text of plan */
      if (!isset($plan)) {
	$plan_txt = $this->getPlan($user, $ts);
      } else {
	// plan was passed as a parameter (probably previewing)
	$plan_txt = $plan;
      }

      /* only wordwrap if a text plan */
      if (preg_match('/^\<pre\>(.*)\<\/pre\>\s*$/misD', $plan_txt)) {
	$out .= Planworld::addLinks(wordwrap($plan_txt, 76, "\n", 1), $user->getUsername());
      } else {
	$out .= Planworld::addLinks($plan_txt, $user->getUsername());
      }

      return $out;
    }

    /**
     * Return the text of this user's plan.
     * @param ts Timestamp of the requested plan
     * @public
     * @returns Plan
     */
    function getPlan (&$user, $ts=null) {
      if (!isset($ts)) {

	if ($user->getUserID() != $this->userID) {
	  $this->addView();
	  Archive::addView($this->userID, $this->lastUpdate);
	  /* check for mutual snitch registration */
	  if ($user->getSnitch() && $this->snitch) {
	    $this->addSnitchView($user);
	  }
	}
	
	if (!isset($this->plan)) {
	  $query = "SELECT content FROM plans WHERE uid=" . $this->userID;

	  /* execute the query */
	  $result = $this->dbh->query($query);
	  if (isset($result) && !DB::isError($result)) {
	    $row = $result->fetchRow();
	    $this->plan = $row['content'];
	  } else {
	    return PLANWORLD_ERROR;
	  }
	}

	return $this->plan;

      } else {
	/* fetch it from the archives */
	if (Archive::isPublic($this->userID, $ts) || $user->getUserID() == $this->userID) {
	  if ($user->getUserID() != $this->userID) {
	    Archive::addView($this->userID, $ts);
	  }
	  return Archive::getEntry($this->userID, $ts);
	} else {
	  return "<strong>Error:</strong> You are not authorized to view this entry.";
	}
      }
    }

    /**
     * Saves $plan as this user's plan.
     * @param plan Text to save.
     * @param archive Archive settings for this plan.
     * @param timestamp Timestamp to save archive with.
     * @public
     */
    function setPlan ($plan, $archive='N', $name = '', $timestamp = null) {
      if (!isset($timestamp))
	$timestamp = mktime();

      /* save it in the archives */
      if ($archive == 'P' || $archive == 'Y') {
	Archive::saveEntry($this->userID, $timestamp, addslashes($plan), addslashes(htmlspecialchars($name)), ($archive == 'Y') ? true : false);
      }

      $oldplan = $this->getPlan($this);

      $query = "DELETE FROM plans WHERE uid=" . $this->userID;
      $this->dbh->query($query);

      /* format the journalled plan */
      if ($this->getPreference('journal')) {
	if (!$divider = $this->getPreference('journal_divider')) {
	  $divider = PW_DIVIDER;
	}
	if ($this->getPreference('journal_order') == 'new') {
	  $tmp = '';
	  for ($i=0; $i < $this->getPreference('journal_entries'); $i++) {
	    list($ts, $txt) = Archive::getEntryByIndex($this->userID, $i);
	    if ($ts == 0)
	      break;
	    $tmp .= Planworld::getDisplayDivider($divider, $ts) . "\n";
	    $tmp .= $txt . "\n";
	  }
	  $plan = $tmp;
	} else {
	  $tmp = '';
	  for ($i=$this->getPreference('journal_entries') - 1; $i>=0; $i--) {
	    list($ts, $txt) = Archive::getEntryByIndex($this->userID, $i);
	    if ($ts == 0)
	      break;
	    $tmp .= Planworld::getDisplayDivider($divider, $ts) . "\n";
	    $tmp .= $txt . "\n";
	  }
	  $plan = $tmp;
	}

      }

      /* process snoop references */
      Snoop::process($this, $plan, $oldplan);


      /* save the plan */
      $query = "INSERT INTO plans (uid, content) VALUES (" . $this->userID . ", '" . addslashes($plan) . "')";
      $this->dbh->query($query);

    }

    function repostPlan () {
      $oldplan = $this->getPlan($this);

      $query = "DELETE FROM plans WHERE uid=" . $this->userID;
      $this->dbh->query($query);

      /* format the journalled plan */
      if ($this->getPreference('journal')) {
	if (!$divider = $this->getPreference('journal_divider')) {
	  $divider = PW_DIVIDER;
	}
	if ($this->getPreference('journal_order') == 'new') {
	  $tmp = '';
	  for ($i=0; $i < $this->getPreference('journal_entries'); $i++) {
	    list($ts, $txt) = Archive::getEntryByIndex($this->userID, $i);
	    if ($ts == 0)
	      break;
	    $tmp .= Planworld::getDisplayDivider($divider, $ts) . "\n";
	    $tmp .= $txt . "\n";
	  }
	  $plan = $tmp;
	} else {
	  $tmp = '';
	  for ($i=$this->getPreference('journal_entries') - 1; $i>=0; $i--) {
	    list($ts, $txt) = Archive::getEntryByIndex($this->userID, $i);
	    if ($ts == 0)
	      break;
	    $tmp .= Planworld::getDisplayDivider($divider, $ts) . "\n";
	    $tmp .= $txt . "\n";
	  }
	  $plan = $tmp;
	}

      }

      /* process snoop references */
      Snoop::process($this, $plan, $oldplan);


      /* save the plan */
      $query = "INSERT INTO plans (uid, content) VALUES (" . $this->userID . ", '" . addslashes($plan) . "')";
      $this->dbh->query($query);

    }

    function previewPlan (&$user, $plan) {
      $timestamp = mktime();

      /* format the journalled plan */
      if ($this->getPreference('journal')) {
	if (!$divider = $this->getPreference('journal_divider')) {
	  $divider = PW_DIVIDER;
	}
	if ($this->getPreference('journal_order') == 'new') {
	  // show current plan
	  $tmp = Planworld::getDisplayDivider($divider, $timestamp) . "\n" . $plan . "\n";

	  // show archived plans
	  for ($i=0; $i < $this->getPreference('journal_entries'); $i++) {
	    list($ts, $txt) = Archive::getEntryByIndex($this->userID, $i);
	    if ($ts == 0)
	      break;
	    $tmp .= Planworld::getDisplayDivider($divider, $ts) . "\n";
	    $tmp .= $txt . "\n";
	  }
	  $plan = $tmp;
	} else {
	  $tmp = '';
	  for ($i=$this->getPreference('journal_entries') - 1; $i>=0; $i--) {
	    list($ts, $txt) = Archive::getEntryByIndex($this->userID, $i);
	    if ($ts == 0)
	      break;
	    $tmp .= Planworld::getDisplayDivider($divider, $ts) . "\n";
	    $tmp .= $txt . "\n";
	  }

	  // show current plan
	  $tmp .= Planworld::getDisplayDivider($divider, $timestamp) . "\n" . $plan . "\n";
	  $plan = $tmp;
	}

      }

      return $this->displayPlan($user, $plan);
    }

    /**
     * Loads a Planwatch object containing this users planwatch.
     * @public
     */
    function loadPlanwatch () {
      if (!isset($this->planwatch)) {
	$this->planwatch = new Planwatch($this);
      }
    }
    
    /**
     * Returns the number of snitch views that this user has had.
     * @public
     */
    function getNumSnitchViews () {
      $query = "SELECT COUNT(*) as count FROM snitch WHERE uid=" . $this->userID;

      /* execute the query */
      $result = $this->dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	$row = $result->fetchRow();
	return (int) $row['count'];
      } else {
	return PLANWORLD_ERROR;
      }
    }

    function getSnitchViews ($order='d', $dir='d') {

      /* assemble the query */
      $query = "SELECT snitch.s_uid, snitch.last_view, snitch.views, u2.username as s_name, u2.last_update FROM users, snitch LEFT JOIN users as u2 ON snitch.s_uid=u2.id WHERE snitch.uid=" . $this->userID . " AND users.id=" . $this->userID . " AND snitch.last_view > users.snitch_activated ORDER BY ";

      switch ($order) {
      case 'u':
	$query .= "u2.username ";
        break;
      case 'v':
	$query .= "snitch.views ";
        break;
      default:
	$query .= "snitch.last_view ";
      }

      if ($dir == 'a') {
	$query .= "ASC";
      } else {
	$query .= "DESC";
      }

      /* execute the query */
      if ($this->snitchDisplayNum > 0) {
	$result = $this->dbh->limitQuery($query, 0, $this->snitchDisplayNum);
	$this->dbh->limit_from = $this->dbh->limit_count = null;
      } else {
	$result = $this->dbh->query($query);
      }
      if (isset($result) && !DB::isError($result)) {
	/* load up this user's planwatch to do some checking against it */
	$this->loadPlanwatch();

	$return = array();
	while ($row = $result->fetchRow()) {
	  $return[] = array('ID' => (int) $row['s_uid'],
			    'Name' => $row['s_name'],
			    'Date' => (int) $row['last_view'],
			    'Views' => (int) $row['views'],
			    'LastUpdate' => (int) $row['last_update'],
			    'InPlanwatch' => (bool) $this->planwatch->inPlanwatch($row['s_name']));
	}
	return $return;
      } else {
	return PLANWORLD_ERROR;
      }
    }

    /**
     * Resets this user's snitch views.
     * @public
     * @returns void
     */
    function resetSnitchViews () {
      $query = "UPDATE snitch SET views=0 WHERE uid=" . $this->userID;
      $this->dbh->query($query);
    }

    /**
     * Is this user snitch registered?
     * @public
     * @returns bool
     */
    function getSnitch () {
        return $this->snitch;
    }

    /**
     * Does the user have Snitch Tracker on?
     * @public
     * @returns bool
     */
    function getSnitchTracker () {
      return $this->snitchTracker;
    }

    /**
     * Turn Snitch Tracker on or off.
     * @public
     * @param val Turn on / off.
     * @returns void
     */
    function setSnitchTracker ($val) {
        $this->snitchTracker = $val;
	$this->setPreference('snitchtracker', (($val) ? 'true' : 'false'));
    }
    
    /**
     * Clear snitch tracker entries.
     * @public
     * @returns void
     */
    function clearSnitchTracker () {
      $query = "DELETE FROM snitchtracker WHERE uid={$this->userID}";
      $this->dbh->query($query);
    }

    function getSnitchTrackerEntries () {
      $query = "SELECT users.username, snitchtracker.viewed FROM snitchtracker, users WHERE users.id=s_uid AND uid={$this->userID} ORDER BY viewed";

      /* execute the query */
      $result = $this->dbh->query($query);
      if (isset($result) && !DB::isError($result)) {
	$return = array();
	while ($row = $result->fetchRow()) {
	  $return[] = array($row['username'],
			    $row['viewed']);
	}
	return $return;
      } else {
	return PLANWORLD_ERROR;
      }
    }

    /**
     * Set snitch registration status.
     * @param val True / false registration
     * @public
     * @returns void
     */
    function setSnitch ($val) {
      $this->changed = true;
      if (!$val && $this->snitch) {
	$this->clearSnitch();
	$this->setSnitchTracker(false);
	$this->clearSnitchTracker();
      } else if ($val && $val != $this->snitch) {
	$this->startSnitch();
      } else {
	$this->changed = false;
      }
      $this->snitch = $val;
    }
    
    /**
     * Get number of users to display on snitch list.
     * @public
     * @returns int
     */
    function getSnitchDisplayNum () {
        return $this->snitchDisplayNum;
    }
    
    /**
     * Set number of users to display on snitch list.
     * @param num Number of users to display
     * @public
     * @returns void
     */
    function setSnitchDisplayNum ($num) {
        $this->snitchDisplayNum = $num;
        $this->changed = true;
    }
    
    /**
     * Returns a list of users who have referenced this user's plan.
     * @public
     * @returns array
     */
    function getSnoopRefs () {
        // no object variable needs to be instantiated (unless shared) as this needs
        // to be reloaded each time and is only relevant to the user
        include_once(dirname(__FILE__) . '/Snoop.php');
        return Snoop::getRefs($this);
    }
    
    /**
     * Get this user's preferred theme.
     * @public
     * @returns int
     */
    function getTheme () {
        return $this->theme;
    }
    
    /**
     * Set the theme that this user uses.
     * @param theme Theme to use
     * @public
     * @returns void
     */
    function setTheme ($theme) {
      $this->theme = $theme;
      $this->changed = true;
    }

    /**
     * Get this user's preferred timezone.
     * @public
     * @returns string
     */
    function getTimezone () {
      return $this->timezone;
    }

    /**
     * Set the local timezone for this user.
     * @param theme Timezone to use
     * @public
     * @returns void
     */
    function setTimezone ($timezone) {
      $this->setPreference('timezone', $timezone);
      $this->timezone = $timezone;
    }

    /**
     * Get this user's type
     * @public
     * @returns string
     */
    function getType () {
      return $this->type;
    }

    /**
     * Get this user's preferred skin.
     * @public
     * @returns int
     */
    function getSkin () {
        return $this->skin;
    }
    
    /**
     * Set the skin that this user uses.
     * @param skin Skin to use
     * @public
     * @returns void
     */
    function setSkin ($skin) {
        $this->skin = $skin;
    }

    /**
     * Returns this user's userid.
     * @public
     * @returns int
     */
    function getUserID () {
        return $this->userID;
    }
    
    /**
     * Returns this user's username.
     * @public
     * @returns string
     */
    function getUsername () {
        return addslashes($this->username);
    }
    
    /**
     * Returns this user's planwatch ordering.
     * @public
     * @returns string
     */
    function getWatchOrder () {
        return $this->watchOrder;
    }
    
    /**
     * Set this user's planwatch ordering.
     * @param type Type of ordering
     * @public
     * @returns void
     */
    function setWatchOrder ($type) {
        $this->watchOrder = $type;
        $this->changed = true;
    }
    
    /**
     * Is this user's plan world-accessible?
     * @public
     * @returns bool
     */
    function getWorld () {
        return $this->world;
    }
    
    /**
     * Set this user's world-accessibility
     * @param val True / false world-accessibility
     * @public
     * @returns void
     */
    function setWorld ($val) {
        $this->world = $val;
        $this->changed = true;
    }
}
?>
