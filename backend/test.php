<?php
include('XML/RPC.php');

$users = array('madvani','jwdavidson','jlodom');

// $f=new XML_RPC_Message('users.getLastUpdate', array(new XML_RPC_Value($thestate, "string")));
//$f=new XML_RPC_Message('stats.getNumHits'); // , array(new XML_RPC_Value(3600, 'int')));
//                   array(new XML_RPC_Value(array(new XML_RPC_Value(6,'int'), new XML_RPC_Value(1,'int')), "array")));
// array(xmlrpc_encode($thestate)));

 $f = new XML_RPC_Message('users.getLastUpdate', array(xml_rpc_encode($users)));
// $f = new XML_RPC_Message('users.getLastUpdate', array(new XML_RPC_Value('snfitzsimmon', 'string')));

// $f = new XML_RPC_Message('planworld.online');
// $f = new XML_RPC_Message('plan.getText', array(new XML_RPC_Value('snfitzsimmon', 'string')));

// $c=new XML_RPC_Client("/backend/", "planwatch.org", 80);
$c=new XML_RPC_Client("/planworld/backend/", "note.amherst.edu", 80);
$c->setDebug(1);
$r=$c->send($f);
$v=$r->value();
if (!$r->faultCode()) {
	print "State number ". $thestate . " is <br><pre>" .
		print_r(XML_RPC_decode($v)) . "</pre><BR>";
	print "<HR>I got this value back<BR><pre>" .
        htmlentities($r->serialize()). "</pre><HR>\n";
} else {
	print "Fault: ";
	print "Code: " . $r->faultCode() . 
	" Reason '" .$r->faultString()."'<BR>";
}
?>
</body>
</html>
