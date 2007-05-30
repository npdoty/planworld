<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title><?php echo getTitleString($planworld_target); ?></title>
    <style type="text/css">
      <!--
      A:link, A:visited, A:active {text-decoration: none;}
       -->
     </style>
  </head>
  <body bgcolor="white" vlink="d00000" link="d00000" alink="b0b0b0">
    <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="10">
      <tr>
        <td bgcolor="000000" width="25%" valign="top">
            <center>
              <img src="./layout/2/logo.jpg">
            </center>
            <form method="get" action="">
              <input type="text" name="id" maxlength=60>
              <input type="submit" value="Finger">
            </form>
                  
              <img src="./layout/2/util.jpg"><br> 
<!--                <a href="advsearch.cgi">Search Plans</a><br> -->
                <a href="?id=random">View a Random Plan</a><br>
                <a href="?id=whois">List Users</a><br>
              <br><img src="./layout/2/nav.jpg"><br>
                <a href="./">Welcome Screen</a><br>
<!--                <a href="">Login Screen</a> -->
        </td>

        <td valign="top" bgcolor="white">
<?php require('./layout/2/' . getIncludeFile($planworld_target)); ?>
        </td>
      </tr>
        <tr>
        <td bgcolor="000000">&nbsp;</td>
        <td bgcolor="white"><hr>
          <small>
            <a href="./layout/2/faq.html">Frequently Asked Questions About Plans</a><br>
            Direct all other questions, comments, and/or requests to:
            <a href="mailto:plans@grinnell.edu">Plans</a>
          </small>
        </td>
        </tr>
    </table>
  </body>
</html>
