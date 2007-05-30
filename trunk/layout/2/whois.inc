<?php
list($users, $count) = getAllUsers('plans');
?>
	  <center><h1>Plans Account List</h1></center>
            There are now <b><?php echo $count; ?></b> people with plans accounts.<br>
The following people have plans accounts:
<table>
<tr>
<td>
<ul>
<?php
for ($i=0; $i<sizeof($users); $i++) {
  if ($i % 100 == 0 && $i != 0) {
    echo "</ul>\n</td>\n<td>\n<ul>\n";
  }
?>
<li><a href="?id=<?php echo $users[$i]; ?>"><?php echo $users[$i]; ?></a></li>
<?php
}
?>
</ul>
</tr>
</tr>
</table>