<?php
  if (!defined('IN_TBB')) die();
  
  $valid_actions = array('list', 'profile');
  $action = $_GET['act'];
  if (!isset($_GET['act']) || $_GET['act'] == '') $action = 'list';
  
  switch ($action) {
    case 'list':
      $usercountr = dbquery("SELECT COUNT(userid) FROM users");
      $getit = dbrow($usercountr);
      $usercount = $getit['COUNT(userid)'];
      $userspp = 30;
      $pagecount = ceil($usercount / $userspp);
      $pagenum = 1;
      
      if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $pagenum = intval($_GET[page]);
      }
      
      if ($pagenum < 1) $pagenum = 1;
      if ($pagenum > $pagecount) $pagenum = $pagecount;
      $uoffset = ($pagenum - 1) * $userspp;
      
      $memberquery = dbquery("SELECT userid,username,powerlevel,posts FROM users ORDER BY userid LIMIT $uoffset,$userspp");
      print "<b>Pages:</b> ";
      pagination($pagecount, $pagenum, "index.php?m=users");
      print "<div class='bigspacing'></div>";
      
      print "<table class='styled' style='width: 500px; margin: 0 auto'>";
      print "<tr class='header'><td style='width: 48px'>ID</td><td>Username</td><td>Group</td></tr>";
      while ($row = dbrow($memberquery)) {
        $user = userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
        print "<tr><td>$row[userid]</td><td>$user</td><td>{$powerlevels[$row[powerlevel]]}</td></tr>";
      }
      print "</table>";
      
      print "<div class='bigspacing'></div>";
      print "<b>Pages:</b> ";
      pagination($pagecount, $pagenum, "index.php?m=users");
    break;
    
    case 'profile':
      $userid = $_GET['id'];
      if (!is_numeric($userid)) {
        print "Invalid user ID.<br><a href='index.php'>Return to the main page</a>";
        break;
      }
      
      $userid = intval($userid); // just to be safe
      $memberquery = dbquery("SELECT * FROM users WHERE userid = $userid");
      if (mysql_num_rows($memberquery) == 0) {
        print "No user with this ID exists.<br><a href='index.php'>Return to the main page</a>";
      } else {
        $member = dbrow($memberquery);
        
        //$member[username] = htmlspecialchars($member[username]);
        $namelink = userlink($member[userid], htmlspecialchars($member[username]), $member[powerlevel]);
        
        print "<table class='styled' style='width: 100%; margin: 0px auto; border: 0px' cellpadding='0' cellspacing='0'>";
        print "<tr><td colspan='2' style='font-size: 15px; font-weight: bold'>Profile for $namelink</td></tr>";
        print "<tr>";
        // left bit
        print "<td style='width: 50%' valign='top'>";
        print "<table class='styled' style='width: 100%'>";
        
        print "<tr class='header'><td>Profile Info</td></tr>";
        print "<tr><td style='text-align: left'>";
        if ($member[hasavatar] == 1) {
          print "<img src='avatars/$member[userid].$member[avatarext]' alt='Avatar' style='display: block; margin: 0 auto'>";
          print "<div class='bigspacing'></div>";
        }
        if ($member[usertitle]) {
          $member[usertitle] = htmlspecialchars($member[usertitle]);
          print "<div style='text-align: center'>$member[usertitle]</div>";
        }
        print "<b>Rank:</b> {$powerlevels[$member[powerlevel]]}<br>";
        if ($member[posts] == 1) $plural1 = ''; else $plural1 = 's';
        if ($member[threads] == 1) $plural2 = ''; else $plural2 = 's';
        print "<b>Posts:</b> $member[posts] post$plural1, $member[threads] thread$plural2<br>";
        $joindate = parsedate($member[joindate]);
        $lastactivity = parsedate($member[lastactive]);
        print "<b>Joined:</b> $joindate<br>";
        print "<b>Last activity:</b> $lastactivity<br>";
        if ($member[quote]) {
          $member[quote] = htmlspecialchars($member[quote]);
          print "<b>Quote:</b> $member[quote]<br>";
        }
        $replink = replink($member[userid], $member[reputation]);
        print "<b>Reputation:</b> $replink<br>";
        print "</td></tr>";
        
        print "<tr class='header'><td>Actions</td></tr>";
        print "<tr><td>";
        print "<a href='index.php?m=messages&act=send&target=$member[userid]'>Send PM</a>";
        print " - ";
        print "<a href='index.php?m=reputation&id=$member[userid]'>Rate User</a>";
        print "</td></tr>";
        
        print "</table></td>";
        // end left bit
        // right bit
        print "<td style='width: 50%' valign='top'>";
        print "<table class='styled' style='width: 100%'>";
        print "<tr class='header'><td colspan='2'>Personal Info</td></tr>";
        if ($member[info]) {
          $info = getpost($member[info],true,true,false);
          print "<tr><td colspan='2' style='text-align: left'>$info</td></tr>";
        } else {
          print "<tr><td colspan='2'>None. You can go to your User CP to edit this area!</td></tr>";
        }
        print "<tr class='header'><td colspan='2'>Signature</td></tr>";
        if ($member[signature]) {
          $sig = getpost($member[signature],true,true,false);
          print "<tr><td colspan='2' style='text-align: left'>$sig</td></tr>";
        } else {
          print "<tr><td colspan='2'>(none)</td></tr>";
        }
        print "</table></td>";
        // end right bit
        print "</table>";
        print "<br><a href='index.php?m=users'>Return to the member list</a>";
      }
    break;
  }
?>
