<?php
  if (!defined('IN_TBB')) die();
  
  $threadid = $_GET['id'];
  if (!is_numeric($threadid)) {
    print "Invalid thread ID.<br><a href='index.php'>Return to the main page</a>";
  } else {
    $threadid = intval($threadid); // just to be safe
    $threadquery = dbquery("SELECT * FROM threads WHERE id = $threadid");
    
    if (mysql_num_rows($threadquery) == 0) {
      print "No thread with this ID exists. This thread may have been deleted.<br><a href='index.php'>Return to the main page</a>";
    } else {
      $threadinfo = dbrow($threadquery);
      $foruminfo = dbrow(dbquery("SELECT * FROM forums WHERE id = $threadinfo[forum]"));
      $threadinfo[name] = htmlspecialchars($threadinfo[name]);
      
      if ($s[user][powerlevel] < $foruminfo[mod_power]) {
        print "You are not allowed to moderate threads in this forum.<br><a href='index.php'>Return to the main page</a>";
      } else {
        $action = false;
        
        if (isset($_POST[move])) {
          print "<b>Moving Thread:</b> $threadinfo[name]: (<a href='index.php?showthread=$threadid'>Return to Thread</a>)<br>";
          print "<br>";
          print "<b>Available Forums:</b><br>";
          print "<form action='index.php?m=board&act=mod&id=$threadid' method='post'>";
          print "<table style='margin: 0 auto'>";
          $getforums = dbquery("select id,name from forums");
          while ($row = dbrow($getforums)) {
            $row[name] = htmlspecialchars($row[name]);
            print "<tr><td><input type='radio' name='target' value='$row[id]'></td><td align='left'>$row[name]</td></tr>";
          }
          print "</table>";
          print "<input type='submit' name='do_move' class='button' value='Move Thread'>";
          print "</form>";
        } elseif (isset($_POST[delete])) {
          print "<b>Deleting Thread:</b> $threadinfo[name]: (<a href='index.php?showthread=$threadid'>Return to Thread</a>)<br>";
          print "<br>";
          print "Are you *sure* you want to delete this thread?<br>";
          print "<form action='index.php?m=board&act=mod&id=$threadid' method='post'>";
          print "<input type='submit' name='do_delete' class='button' value='Delete Thread'>";
          print "</form>";
        } elseif (isset($_POST[lock])) {
          dbquery("UPDATE threads SET locked = 1 WHERE id = $threadid");
          $action = true;
        } elseif (isset($_POST[unlock])) {
          dbquery("UPDATE threads SET locked = 0 WHERE id = $threadid");
          $action = true;
        } elseif (isset($_POST[sticky])) {
          dbquery("UPDATE threads SET stickied = 1 WHERE id = $threadid");
          $action = true;
        } elseif (isset($_POST[unstick])) {
          dbquery("UPDATE threads SET stickied = 0 WHERE id = $threadid");
          $action = true;
        } elseif (isset($_POST[do_move])) {
          $newforum = $_POST[target];
          if (!is_numeric($newforum)) {
            print 'Invalid forum ID.';
          } else {
            $newforum = intval($newforum);
            dbquery("UPDATE threads SET forum = $newforum WHERE id = $threadid");
            $newlastthread = dbrow(dbquery("SELECT threads.*,users.username FROM threads LEFT JOIN users ON threads.lastposterid=users.userid WHERE forum = $newforum ORDER BY lastpostdate DESC LIMIT 1"));
            $newlastthread[name] = iprotect($newlastthread[name]);
            $newlastthread[lastpostername] = iprotect($newlastthread[lastpostername]);
            $getpostcount = dbrow(dbquery("SELECT COUNT(id) FROM posts WHERE thread = $threadid"));
            $subtract = $getpostcount['COUNT(id)'];
            dbquery("UPDATE forums SET lastposterid = $newlastthread[lastposterid], lastposter = '$newlastthread[lastpostername]', lastpostedin = '$newlastthread[name]', lastpostedinid = $newlastthread[id], lastpostdate = $newlastthread[lastpostdate], threads = threads + 1, posts = posts + $subtract WHERE id = $newforum");
            $newlastthread = dbrow(dbquery("SELECT threads.*,users.username FROM threads LEFT JOIN users ON threads.lastposterid=users.userid WHERE forum = $threadinfo[forum] ORDER BY lastpostdate DESC LIMIT 1"));
            $newlastthread[name] = iprotect($newlastthread[name]);
            $newlastthread[lastpostername] = iprotect($newlastthread[lastpostername]);
            if ($newlastthread) {
              dbquery("UPDATE forums SET lastposterid = $newlastthread[lastposterid], lastposter = '$newlastthread[lastpostername]', lastpostedin = '$newlastthread[name]', lastpostedinid = $newlastthread[id], lastpostdate = $newlastthread[lastpostdate], threads = threads - 1, posts = posts - $subtract WHERE id = $threadinfo[forum]");
            } else {
              dbquery("UPDATE forums SET lastposterid = 0, lastposter = '', lastpostedin = '', lastpostedinid = 0, lastpostdate = 0, threads = threads - 1, posts = posts - $subtract WHERE id = $threadinfo[forum]");
            }
            $action = true;
          }
        } elseif (isset($_POST[do_delete])) {
          $getpostcount = dbrow(dbquery("SELECT COUNT(id) FROM posts WHERE thread = $threadid"));
          $subtract = $getpostcount['COUNT(id)'];
          dbquery("DELETE FROM posts WHERE thread = $threadid");
          dbquery("DELETE FROM threads WHERE id = $threadid");
          dbquery("DELETE FROM threadread WHERE thread = $threadid");
          $newlastthread = dbrow(dbquery("SELECT threads.*,users.username FROM threads LEFT JOIN users ON threads.lastposterid=users.userid WHERE forum = $threadinfo[forum] ORDER BY lastpostdate DESC LIMIT 1"));
          $newlastthread[name] = iprotect($newlastthread[name]);
          $newlastthread[lastpostername] = iprotect($newlastthread[lastpostername]);
          if ($newlastthread) {
            dbquery("UPDATE forums SET lastposterid = $newlastthread[lastposterid], lastposter = '$newlastthread[lastpostername]', lastpostedin = '$newlastthread[name]', lastpostedinid = $newlastthread[id], lastpostdate = $newlastthread[lastpostdate], threads = threads - 1, posts = posts - $subtract WHERE id = $threadinfo[forum]");
          } else {
            dbquery("UPDATE forums SET lastposterid = 0, lastposter = '', lastpostedin = '', lastpostedinid = 0, lastpostdate = 0, threads = threads - 1, posts = posts - $subtract WHERE id = $threadinfo[forum]");
          }
          header("Location: index.php?showforum=$threadinfo[forum]");
        }
        
        if ($action) {
          header("Location: index.php?showthread=$threadid");
        }
      }
    }
  }
?>