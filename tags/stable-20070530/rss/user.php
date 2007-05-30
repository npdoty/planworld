<?php
$_base = dirname(__FILE__) . '/../';

require_once($_base . "lib/User.php");

$u = User::factory($_GET['u']);
$t = User::factory('rss');
header("Content-type: text/xml");
?>
<?php echo "<?xml version=\"1.0\"?>\n"; ?>
<rss version="2.0">
<channel>
<!-- required elements -->
<title><?php echo $u->getUsername(); ?>'s plan</title>
<link>http://planworld.net/</link>
<description><?php echo $u->getUsername(); ?>'s plan</description>

<lastBuildDate><?php echo date("D, j M Y H:i:s T", $u->getLastUpdate()); ?></lastBuildDate>
<docs>http://backend.userland.com/rss</docs>

<generator>planworld v3-dev</generator>
<webMaster>rss@planworld.net</webMaster>

<item>
<description><![CDATA[<?php echo Planworld::addLinks($u->getPlan($t)); ?>]]></description>
<pubDate><?php echo date("D, j M Y H:i:s T", $u->getLastUpdate()); ?></pubDate>
</item>
</channel>
</rss>
