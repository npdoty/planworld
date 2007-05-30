<style type="text/css">
body {
	background-color: #FFFFFF;
	margin-left: 0px;
	margin-right: 0px;
	margin-bottom: 0px;
	margin-top: 0px;
}
body,p,td {
	font-family: Arial, Helvetica, Sans-serif;
	font-size: 13px;
	color: #000000;
	border: 0px;
}
a {
	text-decoration: none;
	color: #003366;
	font-weight: bold;
}
hr {
	color: #000000;
}
<?php
/* silly work-around for forcing the display of fixed-width fonts */
if ((strstr($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4') && !strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) || strstr($_SERVER['HTTP_USER_AGENT'], "Mac")) {
?>
pre {
    font-family: fixed-width;
}
tt {
    font-family: fixed-width;
}
<?php
} // if ((strstr($_SERVER['HTTP_USER_AGENT'], 'Mozilla/4') && !strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) || strstr($_SERVER['HTTP_USER_AGENT'], "Mac"))
?>
td.navbar {
	background-color: #CCCCCC;
}
a.navbar {
	color: #003366;
	font-weight: bold;
}

.border {
	background-color: #000000;
}

.title {
	font-size: 20px;
	font-weight: bold;
}
.subtitle {
	font-size: 16px;
	font-weight: bold;
}
.small {
	font-size: 11px;
	font-weight: bold;
}
.header {
	background-color: #336699;
}
.trailer {
	background-color: #CCCCCC;
}
.content {
	background-color: #FFFFCC;
}
.new {
	font-size: 11px;
	font-weight: bold;
	color: #CC0000;
}
.welcome {
	color: #FFFFFF;
}
.columnheader {
	color: #FFFFFF;
	background-color: #336699;
	font-weight: bold;
}
.entry {
	background-color: #FFFFCC;
}
.planwatch {
	background-color: #AABBCC;
	font-size: 12px;
}
.description {
	background-color: #AABBCC;
}
.error {
    color: #FF0000;
  font-weight: bold;
}
.inputTextarea {}
.inputButton {}
.inputTextBox {}
.inputRadio {}
.inputCheckbox {}
</style>
