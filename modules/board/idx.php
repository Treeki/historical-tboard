<?php
  if (!defined('IN_TBB')) die();
  
  if ($s[logged_in] && $_POST['markread']) {
    dbquery("DELETE FROM threadread WHERE user = {$s[user][userid]}");
    dbquery("DELETE FROM forumread WHERE user = {$s[user][userid]}");
    $getflist = dbquery("SELECT id FROM forums WHERE view_power <= {$s[user][powerlevel]}");
    $addthem = array();
    $time = time();
    while ($row = dbrow($getflist)) {
      $addthem[] = "($row[id],{$s[user][userid]},$time)";
    }
    dbquery("INSERT INTO forumread (forum,user,lastread) VALUES ".implode(', ', $addthem));
  }
  
  $getcategories = dbquery("SELECT * FROM categories WHERE power <= {$s[user][powerlevel]} ORDER BY `order`");
  while ($row = dbrow($getcategories)) {
    print "<b>$row[name]</b>";
    print "<table class='styled' style='width: 90%; max-width: 800px'>";
    print "<tr class='header'><td style='width: 32px'></td><td>Forum:</td><td style='width: 10%'>Posts:</td><td style='width: 30%'>Last Post:</td></tr>";
    $getforums = dbquery("SELECT forums.*, users.username, users.powerlevel FROM forums LEFT JOIN users ON forums.lastposterid=users.userid WHERE view_power <= {$s[user][powerlevel]} AND category = $row[id] ORDER BY `order`");
    $alternating = true;
    while ($forum = dbrow($getforums)) {
      if (!can_view_forum($forum)) continue;
      
      $getlastread = dbquery("SELECT * FROM forumread WHERE forum = $forum[id] AND user = {$s[user][userid]}");
      if (dbrows($getlastread) == 0) {
        $lastread = 0;
      } else {
        $getit = dbrow($getlastread);
        $lastread = $getit[lastread];
      }
      $checkagain = dbquery("SELECT COUNT(user) FROM threadread WHERE forum = $forum[id] AND user = {$s[user][userid]}");
      $getit = dbrow($checkagain);
      $readcount = $getit['COUNT(user)'];
      $checkunread = dbquery("SELECT COUNT(id) FROM threads WHERE forum = $forum[id] AND lastpostdate > $lastread");
      $getit = dbrow($checkunread);
      $totalcount = $getit['COUNT(id)'];
      if ($s[logged_in] && $totalcount > $readcount) {
        $unread = $totalcount - $readcount;
        $forumicon = "<img src='{$theme}images/forumicon.php?number=$unread' alt='This forum has $unread unread threads.' title='This forum has $unread unread threads.'>";
      } else {
        $forumicon = '';
        $forumicon = "<img src='{$theme}images/icon_forumread.png' alt='This forum has no unread threads.' title='This forum has no unread threads.'>";
      }
      $alternating = !$alternating;
      if ($alternating) {
        print "<tr class='rowalt'>";
      } else {
        print "<tr>";
      }
      print "<td style='text-align: center'>$forumicon</td>";
      print "<td style='font-size: 12px'><a href='index.php?showforum=$forum[id]' style='font-size: 14px; font-weight: bold'>$forum[name]</a><br>$forum[desc]</td>";
      print "<td style='font-size: 11px'>$forum[threads] threads<br>$forum[posts] posts</td>";
      if ($forum[lastpostdate] == 0) {
        print "<td>Never</td>";
      } else {
        if (strlen($forum[lastpostedin]) > 35) {
          $forum[lastpostedin] = substr($forum[lastpostedin], 0, 32).'...';
        }
        $forum[lastpostedin] = htmlspecialchars($forum[lastpostedin]);
        $lastpostdate = parsedate($forum[lastpostdate]);
        print "<td>";
        print "<a href='index.php?showthread=$forum[lastpostedinid]&page=last'>$forum[lastpostedin]</a><br>";
        print "$lastpostdate<br>";
        $lastposter = userlink($forum[lastposterid], htmlspecialchars($forum[lastposter]), $forum[powerlevel]);
        print "by $lastposter";
        print "</td>";
      }
      print "</tr>";
    }
    print "</table>";
    print "<div class='bigspacing'></div>";
  }
  
  if ($s[logged_in]) {
    print "<form action='index.php?m=board' method='post'>";
    print "<input type='submit' name='markread' class='button' value='Mark all threads/forums as read'>";
    print "</form>";
  }
?>
