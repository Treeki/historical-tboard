<?php
  if (!defined('IN_TBB')) die();
  
  $userid = $_GET['id'];
  if (!is_numeric($userid)) {
    print "Invalid user ID.<br><a href='index.php'>Return to the main page</a>";
  } else {
    $userid = intval($userid); // just to be safe
    $memberquery = dbquery("SELECT * FROM users WHERE userid = $userid");
    if (mysql_num_rows($memberquery) == 0) {
      print "No user with this ID exists.<br><a href='index.php'>Return to the main page</a>";
    } else {
      $candelete = false;
      if ($s[user][powerlevel] >= $admincp_req) {
        $candelete = true;
        if (isset($_GET['deleterep']) && $_GET['deleterep'] != '') {
          if (is_numeric($_GET['deleterep'])) {
            $del = intval($_GET['deleterep']);
            $getit = dbquery("SELECT * FROM reputation WHERE id = $del");
            if (mysql_num_rows($getit) != 0) {
              dbquery("DELETE FROM reputation WHERE id = $del");
              $repdata = dbrow($getit);
              dbquery("UPDATE users SET reputation = reputation - $repdata[rep] WHERE userid = $repdata[recipient]");
              $userdata[reputation] -= $repdata[rep];
            }
          }
        }
      }
      
      $userdata = dbrow($memberquery);
      $namelink = userlink($userdata[userid], htmlspecialchars($userdata[username]), $userdata[powerlevel]);
      print "<span style='font-size: 15px; font-weight: bold'>Reputation for $namelink (Total $userdata[reputation])</span><br>";
      print "<hr>";
      
      if ($s[logged_in] && $s[user][userid] != $userid) {
        if (isset($_POST['makeit'])) {
          if ($_POST['comment'] == '') {
            print "You must enter a comment in order to rate a user.<hr>";
          } else {
            $valid = array(2, 1, 0, -1, -2);
            $rep = intval($_POST['rep']);
            if (!in_array($rep, $valid)) {
              print "Invalid reputation.<hr>";
            } else {
              $comment = iprotect($_POST['comment']);
              $time = time();
              $checkifexists = dbquery("SELECT rep FROM reputation WHERE sender = {$s[user][userid]} AND recipient = $userid");
              if (mysql_num_rows($checkifexists) != 0) {
                $getit = dbrow($checkifexists);
                dbquery("UPDATE users SET reputation = reputation - $getit[rep] WHERE userid = $userid");
                dbquery("DELETE FROM reputation WHERE sender = {$s[user][userid]} AND recipient = $userid");
              }
              dbquery("INSERT INTO reputation (sender, recipient, rep, date, content) VALUES ({$s[user][userid]}, $userid, $rep, $time, '$comment')");
              dbquery("UPDATE users SET reputation = reputation + $rep WHERE userid = $userid");
              /*if ($userid == 3) {
                $getdistance = dbquery("SELECT reputation FROM users WHERE userid = $userid");
                $getit = dbrow($getdistance);
                $distance = 69 - $getit['reputation'];
                dbquery("UPDATE users SET reputation = reputation + $distance WHERE userid = $userid");
                dbquery("UPDATE reputation SET rep = rep + $distance WHERE id = 1343");
              }*/
              /*if ($userid == 428) {
                $getdistance = dbquery("SELECT reputation FROM users WHERE userid = $userid");
                $getit = dbrow($getdistance);
                $distance = 69 - $getit['reputation'];
                dbquery("UPDATE users SET reputation = reputation + $distance WHERE userid = $userid");
                dbquery("UPDATE reputation SET rep = rep + $distance WHERE id = 678");
              }*/
              header("Location: index.php?m=reputation&id=$userid");
            }
          }
        }
        print "<b>Post a Comment:</b><br>";
        print "<form action='index.php?m=reputation&id=$userid' method='post'>";
        print "<table cellpadding='1' cellspacing='2' style='margin: 0 auto; width: 550px'>";
        print "<tr>";
        print "<td style='color: #248900'><input type='radio' name='rep' value='2'> Positive (+2)</td>";
        print "<td style='color: #248900'><input type='radio' name='rep' value='1'> Positive (+1)</td>";
        print "<td style='color: #565656'><input type='radio' name='rep' value='0' checked='checked'> Neutral (0)</td>";
        print "<td style='color: #af3333'><input type='radio' name='rep' value='-1'> Negative (-1)</td>";
        print "<td style='color: #af3333'><input type='radio' name='rep' value='-2'> Negative (-2)</td>";
        print "</tr>";
        print "</table>";
        print "<input type='text' size='70' maxlength='120' name='comment' class='textentry'>";
        print " <input type='submit' name='makeit' value='Rate!' class='button'>";
        print "</form>";
        print "<hr>";
      }
      
      $getit = dbquery("SELECT reputation.*,users.userid,users.username,users.powerlevel FROM reputation LEFT JOIN users ON reputation.sender=users.userid WHERE recipient = $userid ORDER BY reputation.date desc");
      if (mysql_num_rows($getit) == 0) {
        print "No one has rated ".htmlspecialchars($userdata[username])." yet. :(";
      } else {
        $alternating = true;
        print "<table cellpadding='0' cellspacing='0' style='width: 100%' id='rep'>";
        while ($row = dbrow($getit)) {
          $alternating = !$alternating;
          if ($alternating)
            $alt = " class='rowalt'";
          else
            $alt = "";
          print "<tr$alt>";
          print "<td align='left' valign='top'>";
          $userlink = userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
          $date = parsedate($row[date]);
          print "<span style='font-size: 11px'>$userlink rated at $date:";
          if ($candelete) print " (<a href='index.php?m=reputation&id=$userid&deleterep=$row[id]'>delete</a>)";
          print "</span>";
          print "<div class='smallspacing'></div>";
          if ($row[rep] > 0) {
            $outcome = "<span style='color: #248900'>Positive (+$row[rep])</span>";
          } elseif ($row[rep] == 0) {
            $outcome = "<span style='color: #565656'>Neutral ($row[rep])</span>";
          } elseif ($row[rep] < 0) {
            $outcome = "<span style='color: #af3333'>Negative ($row[rep])</span>";
          }
          print "$outcome: ".getpost($row[content], true, true, false);
          print "</td>";
          print "</tr>";
        }
        print "</table>";
      }
    }
  }
?>
