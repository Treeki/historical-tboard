<?php
  if (!defined('IN_TBB')) die();
  
  if ($s[user][powerlevel] < $admincp_req) {
    print "You must be an admin to view this page.";
  } else {
  
  $valid_actions = array('idx', 'cats', 'forums', 'users');
  $action = $_GET['act'];
  if (!isset($_GET['act']) || $_GET['act'] == '') $action = 'idx';
  if (!in_array($action, $valid_actions)) $action = 'idx';
  
  print "<table style='margin: 0 auto; width: 100%'>";
  print "<tr>";
  print "<td style='width: 150px' valign='top'>";
  
  print "<table cellspacing='0' cellpadding='0' style='width: 100%'>";
  print "<tr><td><b>Admin CP Options:</b></td></tr>";
  print "<tr><td style='height: 2px'></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=admin&act=idx'>Admin Homepage</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=admin&act=cats'>Edit Categories</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=admin&act=forums'>Edit Forums</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=admin&act=users'>Manage Users</a></td></tr>";
  print "</table>";
  
  print "</td>";
  print "<td style='width: 10px'></td>";
  print "<td valign='top'>";
  
  include 'admin/'.$action.'.php';
  
  print "</td></tr></table>";
  
  }
?>