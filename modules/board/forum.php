<?php
  if (!defined('IN_TBB')) die();
  
  $forumid = $_GET['id'];
  if (!is_numeric($forumid)) {
    print "Invalid forum ID.<br><a href='index.php'>Return to the main page</a>";
  } else {
    $forumid = intval($forumid); // just to be safe
    $forumquery = dbquery("SELECT * FROM forums WHERE id = $forumid");
    
    if (mysql_num_rows($forumquery) == 0) {
      print "No forum with this ID exists.<br><a href='index.php'>Return to the main page</a>";
    } else {
      $foruminfo = dbrow($forumquery);
      $getlastread = dbquery("SELECT * FROM forumread WHERE forum = $forumid AND user = {$s[user][userid]}");
      if (dbrows($getlastread) == 0) {
        $lastread = 0;
      } else {
        $getit = dbrow($getlastread);
        $lastread = $getit[lastread];
      }
      
      //if ($s[user][powerlevel] < $foruminfo[view_power]) {
      if (!can_view_forum($foruminfo)) {
        print "You're not allowed to view this forum.<br><a href='index.php'>Return to the main page</a>";
      } else {
        $threadcountr = dbquery("SELECT COUNT(id) FROM threads WHERE forum = $forumid AND stickied = 0");
        $getit = dbrow($threadcountr);
        $threadcount = $getit['COUNT(id)'];
        $pagecount = ceil($threadcount / $threadspp);
        $pagenum = 1;
        if (isset($_GET['page']) && is_numeric($_GET['page'])) {
          $pagenum = intval($_GET[page]);
        }
        if ($pagenum > $pagecount) $pagenum = $pagecount;
        if ($pagenum < 1) $pagenum = 1;
        $poffset = ($pagenum - 1) * $threadspp;
        
        print "<b>Threads in $foruminfo[name]: (<a href='index.php?m=board'>Return to Forum Index</a>)</b>";
        print "<br>";
        print "<b>Pages:</b> ";
        pagination($pagecount, $pagenum, "index.php?showforum=$forumid");
        
        $stickyquery = dbquery("SELECT threads.id, threads.name, threads.desc, threads.authorid, threads.lastposterid, threads.lastpostdate, threads.replies, threads.locked, threads.icon, authorusers.username as authorname, authorusers.powerlevel as authorpower, lastposterusers.username as lastpostername, lastposterusers.powerlevel as lastposterpower, threadread.thread FROM threads LEFT JOIN users as authorusers ON threads.authorid=authorusers.userid LEFT JOIN users as lastposterusers on threads.lastposterid=lastposterusers.userid LEFT JOIN threadread ON threads.id=threadread.thread AND threadread.user={$s[user][userid]} WHERE threads.forum = $forumid AND threads.stickied = 1 ORDER BY threads.lastpostdate DESC");
        $threadquery = dbquery("SELECT threads.id, threads.name, threads.desc, threads.authorid, threads.lastposterid, threads.lastpostdate, threads.replies, threads.locked, threads.icon, authorusers.username as authorname, authorusers.powerlevel as authorpower, lastposterusers.username as lastpostername, lastposterusers.powerlevel as lastposterpower, threadread.thread FROM threads LEFT JOIN users as authorusers ON threads.authorid=authorusers.userid LEFT JOIN users as lastposterusers on threads.lastposterid=lastposterusers.userid LEFT JOIN threadread ON threads.id=threadread.thread AND threadread.user={$s[user][userid]} WHERE threads.forum = $forumid AND threads.stickied = 0 ORDER BY threads.lastpostdate DESC LIMIT $poffset,$threadspp");
        
        print "<table class='styled' id='forumview' style='width: 100%; max-width: 800px'>";
        print "<tr class='header'><td style='width: 24px'></td><td style='width: 24px'></td><td>Thread:</td><td style='width: 15%'>Creator:</td><td style='width: 10%'>Replies:</td><td style='width: 25%;'>Last Post:</td></tr>";
        
        $alternating = true;
        
        if (mysql_num_rows($stickyquery) != 0) {
          print "<tr><td colspan='6' class='subheader_top'><b>Pinned Threads:</b></td></tr>";
          showthreads($stickyquery, $alternating);
        }
        
        if (mysql_num_rows($threadquery) == 0) {
          print "<tr><td colspan='6'>This forum is empty.</td></tr>";
        } else {
          if (mysql_num_rows($stickyquery) != 0) {
            print "<tr><td colspan='6' class='subheader'><b>Normal Threads:</b></td></tr>";
          }
          showthreads($threadquery, $alternating);
        }
        
        print "</table>";
        
        print "<b>Pages:</b> ";
        pagination($pagecount, $pagenum, "index.php?showforum=$forumid");
        print "<br>";
        if ($s[user][powerlevel] >= $foruminfo[thread_power]) {
          print "<br>";
          print "<a href='index.php?m=board&act=newthread&id=$forumid' class='specialbutton'>New Thread</a>";
        }
      }
    }
  }
  
  function showthreads($threadquery, $alternating) {
    global $postspp, $s, $lastread, $theme;
    
    while ($thread = dbrow($threadquery)) {
      $thread[name] = htmlspecialchars($thread[name]);
      $thread[desc] = htmlspecialchars($thread[desc]);
      $alternating = !$alternating;
      if ($alternating) {
        print "<tr class='rowalt'>";
      } else {
        print "<tr>";
      }
      $dot = '';
      if ($thread[checkpostedin]) {
        $dot = 'dot';
      }
      if ($s[logged_in] && $thread[lastpostdate] >= $lastread && $thread[thread] != $thread[id]) {
        if ($thread[locked] == 1) {
          $threadicon = "<img src='{$theme}images/icon_threadlockedunread.png' alt='This thread has unread posts, and is locked.' title='This thread has unread posts, and is locked.'>";
        } else {
          $threadicon = "<img src='{$theme}images/icon_threadunread$dot.png' alt='This thread has unread posts.' title='This thread has unread posts.'>";
        }
      } else {
        if ($thread[locked] == 1) {
          $threadicon = "<img src='{$theme}images/icon_threadlocked.png' alt='This thread has no unread posts, and is locked.' title='This thread has no unread posts, and is locked.'>";
        } else {
          $threadicon = "<img src='{$theme}images/icon_thread$dot.png' alt='This thread has no unread posts.' title='This thread has no unread posts.'>";
        }
      }
      $pages = '';
      if ($thread[replies]+1 > $postspp) {
        $threadpagecount = ceil(($thread[replies]+1) / $postspp);
        $pages .= ' <span class=\'pages\'>(pages: ';
        if ($threadpagecount > 6) {
          $dleft = 3;
          $dright = $threadpagecount - 2;
        } else {
          $dleft = 7;
          $dright = -1;
        }
        $docomma = false;
        for ($cp = 1; $cp <= $threadpagecount; $cp++) {
          if ($cp > $dleft && $cp < $dright) continue;
          if ($docomma) $pages .= ', '; else $docomma = true;
          $pages .= "<a href='index.php?showthread=$thread[id]&page=$cp'>$cp</a>";
          if ($cp == $dleft) { $pages .= "..."; $docomma = false; }
        }
        $pages .= ')</span>';
      }
      print "<td>$threadicon</td>";
      $threadcustomicon = '';
      if ($thread[icon]) {
        $threadcustomicon = "<img src='smilies/$thread[icon]' alt='Icon'>";
      }
      print "<td>$threadcustomicon</td>";
      print "<td style='text-align: left'><a href='index.php?showthread=$thread[id]' style='font-size: 12px'>$thread[name]</a><br><div style='font-size: 11px; margin: 2px 0px 0px 2px'>$thread[desc]$pages</div></td>";
      $author = userlink($thread[authorid], htmlspecialchars($thread[authorname]), $thread[authorpower]);
      print "<td>$author</td>";
      print "<td>$thread[replies]</td>";
      print "<td style='font-size: 11px'>";
      $lastpostdate = parsedate($thread[lastpostdate]);
      print "$lastpostdate<br>";
      $lastposter = userlink($thread[lastposterid], htmlspecialchars($thread[lastpostername]), $thread[lastposterpower]);
      print "by $lastposter";
      print "</td>";
      print "</tr>";
    }
  }
?>
