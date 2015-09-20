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
      
      if ($s[logged_in]) {
        $getlastread = dbquery("SELECT * FROM forumread WHERE forum = $threadinfo[forum] AND user = {$s[user][userid]}");
        if (dbrows($getlastread) == 0) {
          $lastread = 0;
        } else {
          $getit = dbrow($getlastread);
          $lastread = $getit[lastread];
        }
        
        if (time() > $lastread) {
          // possibly unread
          $checkit = dbquery("SELECT * FROM threadread WHERE thread = $threadid AND user = {$s[user][userid]}");
          if (dbrows($checkit) == 0) {
            dbquery("INSERT INTO threadread (thread,user,forum) VALUES ($threadid,{$s[user][userid]},$threadinfo[forum])");
            $checkagain = dbquery("SELECT COUNT(user) FROM threadread WHERE forum = $threadinfo[forum] AND user = {$s[user][userid]}");
            $getit = dbrow($checkagain);
            $readcount = $getit['COUNT(user)'];
            $checkunread = dbquery("SELECT COUNT(id) FROM threads WHERE forum = $threadinfo[forum] AND lastpostdate > $lastread");
            $getit = dbrow($checkunread);
            $totalcount = $getit['COUNT(id)'];
            if ($readcount >= $totalcount) {
              dbquery("DELETE FROM forumread WHERE forum = $threadinfo[forum] AND user = {$s[user][userid]}");
              $currenttime = time();
              dbquery("INSERT INTO forumread (forum,user,lastread) VALUES ($threadinfo[forum],{$s[user][userid]},$currenttime)");
              dbquery("DELETE FROM threadread WHERE forum = $threadinfo[forum] AND user = {$s[user][userid]}");
            }
          }
        }
      }
      
      if ($s[user][powerlevel] < $foruminfo[view_power] || !can_view_forum($foruminfo)) {
        print "You're not allowed to view threads in this forum.<br><a href='index.php'>Return to the main page</a>";
      } else {
        $postcountr = dbquery("SELECT COUNT(id) FROM posts WHERE thread = $threadid");
        $getit = dbrow($postcountr);
        $postcount = $getit['COUNT(id)'];
        $pagecount = ceil($postcount / $postspp);
        $pagenum = 1;
        
        if (isset($_GET['page']) && is_numeric($_GET['page'])) {
          $pagenum = intval($_GET[page]);
        }
        
        if ($_GET['page'] == 'last') {
          $pagenum = $pagecount;
        }
        
        if (isset($_GET['post']) && is_numeric($_GET['post'])) {
          $showpost = intval($_GET['post']);
          $checkcount = dbquery("SELECT COUNT(id) FROM posts WHERE thread = $threadid AND id <= $showpost");
          $getit = dbrow($checkcount);
          $offset = $getit['COUNT(id)'] - 1;
          $pagenum = floor($offset / $postspp) + 1;
        }
        
        if ($pagenum < 1) $pagenum = 1;
        if ($pagenum > $pagecount) $pagenum = $pagecount;
        $poffset = ($pagenum - 1) * $postspp;
        
        $postquery = dbquery("SELECT posts.id,posts.postdate,posts.posttext,posts.postnum,posts.editinfo".postbox_query_fields." FROM posts LEFT JOIN users ON posts.authorid=users.userid WHERE thread = $threadid ORDER BY posts.id LIMIT $poffset,$postspp");
        print "<b>Viewing Thread: <a href='index.php?showforum=$foruminfo[id]'>$foruminfo[name]</a> &raquo; $threadinfo[name]</b>";
        print "<br>";
        
        if ($s[user][powerlevel] >= $foruminfo[mod_power]) {
          print "<form action='index.php?m=board&act=mod&id=$threadid' method='post'>";
          print "Moderation Actions:";
          if ($threadinfo[locked] == 0) {
            print "<input type='submit' name='lock' value='Lock Thread' class='button'>";
          } else {
            print "<input type='submit' name='unlock' value='Unlock Thread' class='button'>";
          }
          if ($threadinfo[stickied] == 0) {
            print "<input type='submit' name='sticky' value='Pin Thread' class='button'>";
          } else {
            print "<input type='submit' name='unstick' value='Unpin Thread' class='button'>";
          }
          print "<input type='submit' name='move' value='Move Thread' class='button'>";
          print "<input type='submit' name='delete' value='Delete Thread' class='button'>";
          print "</form>";
        }
        
        if ($threadinfo[poll] != 0) {
          $pollinfo = dbrow(dbquery("SELECT * FROM polls WHERE id = $threadinfo[poll]"));
          $pollinfo[question] = htmlspecialchars($pollinfo[question]);
          print "<table class='styled' cellpadding='0' cellspacing='0' style='width: 600px'>";
          if ($pollinfo[votecount] == 1) $plural = ''; else $plural = 's';
          print "<tr class='header'><td colspan='3'>Poll: $pollinfo[question] ($pollinfo[votecount] vote$plural)</td></tr>";
          $choices = explode('|', $pollinfo[choices]);
          $alreadyvoted = false;
          $checkit = dbquery("SELECT id FROM votes WHERE poll=$threadinfo[poll] AND voter={$s[user][userid]}");
          if (mysql_num_rows($checkit) != 0) {
            $alreadyvoted = true;
          }
          if ($pollinfo[userviewable]) {
            $matchvotes = array();
            $idx = 0;
            foreach ($choices as $choice) {
              $matchvotes[$idx] = array();
              $idx++;
            }
            $getvotes = dbquery("SELECT votes.choice,users.userid,users.username,users.powerlevel FROM votes LEFT JOIN users ON votes.voter=users.userid WHERE votes.poll=$threadinfo[poll]");
            while ($row = dbrow($getvotes)) {
              $choice = intval($row[choice]);
              $matchvotes[$choice][] = userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
            }
            $alternating = true;
            $idx = 0;
            foreach ($choices as $choice) {
              $alternating = !$alternating;
              $choice = htmlspecialchars($choice);
              $vote = count($matchvotes[$idx]);
              if ($vote == 1) $plural = ''; else $plural = 's';
              if ($alternating) $rowalt = " class='rowalt'"; else $rowalt = '';
              $userdetails = implode(', ', $matchvotes[$idx]);
              if ($userdetails != '') $userdetails = ': '.$userdetails;
              $votelink = '';
              if ($s[logged_in] && !$alreadyvoted) {
                $votelink = " - <a href='index.php?m=board&act=poll&id=$threadinfo[poll]&vote=$idx'>Vote</a>";
              }
              print "<tr$rowalt><td>$choice ($vote vote$plural)$userdetails$votelink</td></tr>";
              $idx++;
            }
          } else {
            $votes = explode('|', $pollinfo[voteinfo]);
            $choices = array_combine($choices, $votes);
            $alternating = true;
            $idx = 0;
            foreach ($choices as $choice => $vote) {
              $alternating = !$alternating;
              $choice = htmlspecialchars($choice);
              if ($vote == 1) $plural = ''; else $plural = 's';
              if ($alternating) $rowalt = " class='rowalt'"; else $rowalt = '';
              if ($s[logged_in] && !$alreadyvoted) {
                $votelink = " - <a href='index.php?m=board&act=poll&id=$threadinfo[poll]&vote=$idx'>Vote</a>";
              }
              print "<tr$rowalt><td>$choice ($vote vote$plural)$votelink</td></tr>";
              $idx++;
            }
          }
          print "</table>";
          print "<br>";
        } else {
          if ($threadinfo[authorid] == $s[user][userid]) {
            print "<a href='index.php?m=board&act=addpoll&id=$threadid'>Add Poll</a><br>";
          }
        }
        
        print "<b>Pages:</b> ";
        pagination($pagecount, $pagenum, "index.php?showthread=$threadid");
        
        print "<br>";
        while ($post = dbrow($postquery)) {
          $posttext = getpost($post[posttext],true,true,false);
          $cmds = '';
          if ($post[editinfo]) {
            $cmds = " &middot; $post[editinfo]";
          }
          if ($s[user][powerlevel] >= $foruminfo[reply_power]) {
            $cmds .= " &middot; <a href='index.php?m=board&act=postreply&id=$threadid&quote=$post[id]'>Quote</a>";
          }
          if ($s[user][userid] == $post[userid] || $s[user][powerlevel] >= $foruminfo[mod_power]) {
            $cmds .= " &middot; <a href='index.php?m=board&act=editpost&id=$post[id]'>Edit</a>";
          }
          if ($s[user][powerlevel] >= $foruminfo[mod_power]) {
            $cmds .= " &middot; <a href='#' onClick='if (confirm(\"Are you sure you want to delete this post?\") == true) { window.location = \"index.php?m=board&act=modpost&id=$post[id]&func=delete\"; }'>Delete</a>";
          }
          print "<a name='post$post[id]'></a>";
          display_post($post,'Posted',$post[postdate],$cmds,$posttext);
        }
        print "<b>Pages:</b> ";
        pagination($pagecount, $pagenum, "index.php?showthread=$threadid");
        print "<br><br>";
        
        $can_reply = false;
        $locked = false;
        if ($s[user][powerlevel] >= $foruminfo[reply_power] && $threadinfo[locked] == 0) {
          $can_reply = true;
        }
        if ($threadinfo[locked] == 1) {
          $locked = true;
        }
        if ($s[user][powerlevel] >= $foruminfo[mod_power]) {
          $can_reply = true;
        }
        if ($can_reply) {
          if ($locked) {
            print "<i>Note: This thread is locked, but you are able to post here because you are a moderator in this forum.</i><br>";
          }
          //print "<a href='index.php?m=board&act=postreply&id=$threadid' class='specialbutton'>Post Reply</a>";
?>
<form action='index.php?m=board&act=postreply&id=<?=$threadid;?>' method='post'>
<b>Post Reply:</b><br>
<textarea rows='7' cols='70' name='text'></textarea>
<br>
<input type='submit' name='makeit' value='Post Reply' class='button'> <input type='submit' name='preview' value='Preview (Full Mode)' class='button'>
</form>
<?php
        } elseif ($locked) {
          print "This thread is locked.";
        }
      }
    }
  }
?>