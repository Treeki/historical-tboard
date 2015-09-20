<?php
  if (!defined('IN_TBB')) die();
  
  print "<b>Welcome to the Admin CP.</b><br>";
  print "You can leave notes here.";
  if (isset($_POST['makeit'])) {
    if ($_POST['data'] == '') {
      print "<div class='bigspacing'></div>";
      print "You must enter text in order to add a note.<hr>";
    } else {
      $data = iprotect($_POST['data']);
      $time = time();
      dbquery("INSERT INTO adminnotes (author, notedate, data) VALUES ({$s[user][userid]}, $time, '$data')");
      header("Location: index.php?m=admin&act=idx");
    }
  }
  
  print "<div class='bigspacing'></div>";
  print "<b>Post an Admin Note:</b><br>";
  print "<form action='index.php?m=admin&act=idx' method='post'>";
  print "<textarea rows='4' cols='70' name='data'></textarea>";
  print "<br>";
  print "<input type='submit' name='makeit' value='Add Note' class='button'>";
  print "</form>";
  print "<hr>";
  
  $getnotes = dbquery("select adminnotes.*,users.userid,users.username,users.powerlevel from adminnotes left join users on adminnotes.author=users.userid order by adminnotes.notedate");
  if (mysql_num_rows($getnotes) == 0) {
    print "Apparently no one has posted any notes yet.";
  } else {
    $alternating = true;
    print "<table cellpadding='0' cellspacing='0' style='width: 100%' id='rep'>";
    while ($row = dbrow($getnotes)) {
      $alternating = !$alternating;
      if ($alternating)
        $alt = " class='rowalt'";
      else
        $alt = "";
      print "<tr$alt>";
      print "<td align='left' valign='top'>";
      $userlink = userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
      $date = parsedate($row[notedate]);
      print "<span style='font-size: 11px'>$userlink posted at $date:</span>";
      print "<div class='smallspacing'></div>";
      print getpost($row[data], true, true, false);
      print "</td>";
      print "</tr>";
    }
    print "</table>";
  }
?>