<?php
  if (!defined('IN_TBB')) die();
  
  $postid = $_GET['id'];
  if (!is_numeric($postid)) {
    print "Invalid post ID.<br><a href='index.php'>Return to the main page</a>";
  } else {
    $postid = intval($postid); // just to be safe
    $postquery = dbquery("SELECT * FROM posts WHERE id = $postid");
    
    if (mysql_num_rows($postquery) == 0) {
      print "No post with this ID exists. This post may have been deleted.<br><a href='index.php'>Return to the main page</a>";
    } else {
      $postinfo = dbrow($postquery);
      $threadinfo = dbrow(dbquery("SELECT * FROM threads WHERE id = $postinfo[thread]"));
      $foruminfo = dbrow(dbquery("SELECT * FROM forums WHERE id = $threadinfo[forum]"));
      $threadinfo[name] = htmlspecialchars($threadinfo[name]);
      
      if ($s[user][powerlevel] < $foruminfo[mod_power]) {
        print "You are not allowed to moderate posts in this forum.<br><a href='index.php'>Return to the main page</a>";
      } else {
        if ($_GET[func] == 'delete') {
          dbquery("DELETE FROM posts WHERE id = $postid");
          $newlastpost = dbrow(dbquery("SELECT posts.authorid,posts.authorname,posts.postdate,users.username FROM posts LEFT JOIN users ON posts.authorid=users.userid WHERE thread = $postinfo[thread] ORDER BY id DESC LIMIT 1"));
          $newlastpost[authorname] = iprotect($newlastpost[authorname]);
          dbquery("UPDATE threads SET lastposterid = $newlastpost[authorid], lastpostername = '$newlastpost[authorname]', lastpostdate = $newlastpost[postdate], replies = replies - 1 WHERE id = $postinfo[thread]");
          $newlastthread = dbrow(dbquery("SELECT threads.*,users.username FROM threads LEFT JOIN users ON threads.lastposterid=users.userid WHERE forum = $threadinfo[forum] ORDER BY lastpostdate DESC LIMIT 1"));
          $newlastthread[name] = iprotect($newlastthread[name]);
          $newlastthread[lastpostername] = iprotect($newlastthread[lastpostername]);
          dbquery("UPDATE forums SET lastposterid = $newlastthread[lastposterid], lastposter = '$newlastthread[lastpostername]', lastpostedin = '$newlastthread[name]', lastpostedinid = $newlastthread[id], lastpostdate = $newlastthread[lastpostdate], posts = posts - 1 WHERE id = $threadinfo[forum]");
        }
        header("Location: index.php?showthread=$threadinfo[id]&post=$postid#post$postid");
      }
    }
  }
?>