<?php
header("Content-type: text/xml");
// header("Content-type: text/plain");

$_base = dirname(__FILE__) . '/../';

require_once($_base . "config.php");
require_once($_base . "lib/User.php");
/* set some site-dependent constants */
define('PW_URL_BASE', "http://note.amherst.edu/planworld/");
define('PW_URL_INDEX', "http://note.amherst.edu/planworld/");

$token = $_GET["token"];
$u = User::factory($_GET["username"]);
?>
<?php echo "<" . "?xml version=\"1.0\"?" . ">"; ?>
<rss version="2.0"  
     xmlns:content="http://purl.org/rss/1.0/modules/content/">
<channel>
<!-- required elements -->
<title><?php echo $u->getUsername(); ?>'s planwatch</title>
<link>http://planworld.net/</link>
<description><?php echo $u->getUsername(); ?>'s planwatch</description>

<lastBuildDate><?php echo date("D, j M Y H:i:s T"); ?></lastBuildDate>
<docs>http://backend.userland.com/rss</docs>

<generator>planworld v3-dev</generator>
<webMaster>rss@planworld.net</webMaster>

<?php
if ( md5( "planworld:" . $u->getUserID() . "-" . $u->getUsername() ) != $token ) {
?>
<item>
  <title>Access Denied</title>
  <description>The token you provided is not valid for this user's planwatch.</description>
  <pubDate><?php echo date("D, j M Y H:i:s T"); ?></pubDate>
</item>
<?php
} else {
  //$u->setSnitch( false );
  $u->setWatchOrder('newest');

  $u->loadPlanwatch();
  foreach ( $u->planwatch->getList() as $group ) {
    if ( $group == "Send" || $group == "Snoop" )
      continue;
    foreach ( $group as $user => $data ) {
      if (($data[1]) > ($data[2])) {	//this checks to see if the plan is new
  		$planUser = User::factory( $user );
        list(,$host) = explode("@", $planUser->getUsername() );
        $plan_txt = $planUser->getPlan($u);
        if (Planworld::isText($plan_txt)) {
          $plan = Planworld::addLinks(wordwrap($plan_txt, 76, "\n", 1), $u->getUsername(), $host);
        } else {
          $plan = Planworld::addLinks($plan_txt, $u->getUsername(), $host);
        }
		$u->planwatch->markSeen($planUser);	//mark the plan as having been read

?>
<item>
  <title><?php echo $planUser->getUsername(); echo " - "; echo date("D, j M Y H:i:s T", $planUser->getLastUpdate()); ?></title>
  <link><?php echo PW_URL_INDEX."?id=".$planUser->getUsername();?></link>
  <description><?php echo htmlentities($plan); ?></description>
  <content:encoded><![CDATA[<?php echo $plan; ?>]]></content:encoded>
  <pubDate><?php echo date("D, j M Y H:i:s T", $planUser->getLastUpdate()); ?></pubDate>
</item>
<?php
      }
    }
  }
	$u->planwatch->save();	//save the fact that the plan has been read
}
?>
</channel>
</rss>
