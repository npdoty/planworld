<?php
/**
 * $Id: stats.php,v 1.1 2002/01/21 18:28:24 seth Exp $
 * Fetch stats about other systems
 */

/** this also serves as a test / demo of the xmlrpc-epi client */

require_once(dirname(__FILE__) . '/epi-utils.php');
require_once('../config.php');
require_once('../functions.php');
connectToDatabase();

if (empty($_GET)) {
  echo "please specify a node on the querystring<br />\n";
  exit();
}

$info = getNodeInfo($_GET['node']);


$method = "stats.getNumViews";
$params = "";
$server = array('host' => $info['Hostname'],
		'uri'  => $info['Path'],
		'port' => $info['Port']);

function remoteStat ($server, $method, $params='') {
  return  xu_rpc_http_concise(array('method' => $method,
				       'args'   => $params, 
				       'host'   => $server['host'], 
				       'uri'    => $server['uri'], 
				       'port'   => $server['port'], 
				       'debug'  => $debug,
				       'output' => $output));
}

$online = remoteStat($server, 'planworld.online');

?>
<html>
<body>
Statistics for node '<?php echo $_GET['node']; ?>':<br />
Total hits: <?php echo number_format(remoteStat($server, 'stats.getNumHits')); ?><br />
Total views: <?php echo number_format(remoteStat($server, 'stats.getNumViews')); ?><br />
Number of users: <?php echo number_format(remoteStat($server, 'stats.getNumUsers', 'login')); ?><br />
Number of plans: <?php echo number_format(remoteStat($server, 'stats.getNumPlans')); ?><br />
Number of plans (updated in the last hour): <?php echo number_format(remoteStat($server, 'stats.getNumPlans', 3600)); ?><br />
Number of snitch registered users: <?php echo number_format(remoteStat($server, 'stats.getNumSnitchRegistered')); ?><br />
Users online (<?php echo sizeof($online); ?>): <?php print_r($online); ?><br />
Users: <?php print_r(remoteStat($server, 'planworld.whois','plans')); ?><br />
</body>
</html>