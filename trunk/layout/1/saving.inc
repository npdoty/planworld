<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>planworld :: saving your plan...</title>
<meta http-equiv="REFRESH" content="0; URL=<?php echo "" . PW_URL_INDEX . "?id=" . $to_edit->getUsername(); ?>">
<style type="text/css">
<?php 
require('layout/1/themes/default/styles.css');
?>
</style>
</head>
<body class="content">
<!-- at beginning of body, assumes large browser.  tweak top and left
for location -->
<div id="waitDiv" style="position:absolute;left:380px;top:280px;visibility:hidden" align="center">
  <table cellpadding="0" cellspacing="0" width="250"><tr><td bgcolor="#000000">
  <table cellpadding="2" cellspacing="1" width="100%"><tr><td bgcolor="#F6C70B" align="center">
        <font color="#333333" face="Verdana, Arial, Helvetica, sans-serif" size="4"><strong>Saving your plan...</strong></font><br /> <img src="./layout/1/images/await.gif" border="0" width="200" height="20"><br />
        <font color="#444444" face="Verdana, Arial, Helvetica, sans-serif" size="2">Please Wait...</font>
      </td>
    </tr>
  </table>
  </td></tr>
  </table>
</div>

<script language="javascript" type="text/javascript">
  <!--
    var DHTML = (document.getElementById || document.all || document.layers);
    function ap_getObj(name) {
      if (document.getElementById) {
        return document.getElementById(name).style;
      } else if (document.all) {
        return document.all[name].style;
      } else if (document.layers) {
        return document.layers[name];
      }
    }
    function ap_showWaitMessage(div,flag)  {
      if (!DHTML)
        return;
      var x = ap_getObj(div);
      x.visibility = (flag) ? 'visible':'hidden'
      if(! document.getElementById)
        if(document.layers)
          x.left=280/2;
//      return true;
    }
    ap_showWaitMessage('waitDiv', 1);

  //-->
</script>
<!-- Content goes here -->

<!-- end content -->
</body>
</html>
