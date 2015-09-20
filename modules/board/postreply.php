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
      if ($s[user][powerlevel] < $foruminfo[reply_power] || !can_view_forum($foruminfo)) {
        print "You're not allowed to reply to threads in this forum.<br><a href='index.php?showthread=$threadid'>Return to the thread</a>";
      } elseif ($threadinfo[locked] && $s[user][powerlevel] < $foruminfo[mod_power]) {
        print "This thread has been locked.<br><a href='index.php?showthread=$threadid'>Return to the thread</a>";
      } else {
        // if it returns a non-blank string, it's an error
        // if it returns true (check with ===) the reply has been posted successfully
        // if it returns nothing, just show the form
        $result = post_reply();
        if ($result === true) {
          $postid = mysql_insert_id();
          header("Location: index.php?showthread=$threadid&post=$postid#post$postid");
        } else {
          if ($result != '') {
            print '<b>The following errors occurred while posting your reply:<br>'.$result.'</b><br>Your post data has been saved.<hr>';
          }
          if (isset($_POST['preview'])) {
            print "<b>Preview:</b>";
            $posttext = getpost($_POST['text'],true,true,false);
            display_post($s[user],'Posted',time(),$cmds,$posttext);
            print "<br>";
          }
          if (isset($_GET['quote'])) {
            $quoteid = intval($_GET[quote]);
            $getquote = dbquery("SELECT * FROM posts WHERE id = $quoteid AND thread = $threadid");
            if (dbrows($getquote) != 0) { // ignore the quote if it's an invalid id
              $quotepost = dbrow($getquote);
              $quotetime = parsedate($quotepost[postdate]);
              $quote = "[quote=$quotepost[authorname] ($quotetime)]$quotepost[posttext][/quote]\n\n";
            }
          }
?>
<b>Replying to <?=htmlspecialchars($threadinfo[name]);?>: (<a href='index.php?showthread=<?=$threadid;?>'>Return to Thread</a>)</b>
<br>
<form action='index.php?m=board&act=postreply&id=<?=$threadid;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td valign='top'>
      <b>Post Text:</b><br>
      <div class='smileylist'>
        <table cellpadding='2' cellspacing='0' class='smileylist' style='width: 100%'>
          <tr><td style='width: 50%; font-size: 10px'>Code</td><td style='width: 50%; font-size: 10px'>Smiley</td></tr>
<?php
  foreach ($smilies as $t => $r) {
    print "<tr><td>$t</td><td><img src='smilies/$r'></td></tr>";
  }
?>
        </table>
      </div>
    </td>
    <td align='left'><?php post_toolbar(); ?><textarea rows='12' cols='70' name='text' id='typehere'><?=htmlspecialchars($quote);?><?=htmlspecialchars($_POST['text']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Post Reply' class='button'> <input type='submit' name='preview' value='Preview' class='button'></td>
  </tr>
</table>
</form>
<?php
        }
      }
    }
  }

  function post_reply() {
    global $s; // self note: not having this is why so many functions mess up
    global $foruminfo;
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      if (!isset($_POST['text']) or $_POST['text'] == '')
        $error_string .= 'You didn\'t enter a post.<br>';
      
      // bypasses forum games
      if ($s[user][powerlevel] < $foruminfo[mod_power] && time() < ($s[user][lastposttime]+30) && $foruminfo[id] != 15)
        $error_string .= "You've already posted in the last 30 seconds.<br>";
      
      if ($error_string != '') {
        return $error_string;
      } else {
        $inserttext = iprotect($_POST['text']);
        global $threadid, $threadinfo;
        $currenttime = time();
        $postnum = $s[user][posts] + 1;
        
        // IRC post reports go here
        // relevant info: $foruminfo[view_power], $s[user][username],
        // $threadinfo[name], index.php?showthread=$threadid&page=last
        
        $iname = iprotect($s[user][username]);
        dbquery("UPDATE threads SET lastpostername = '$iname', lastposterid = {$s[user][userid]}, lastpostdate = $currenttime, replies = replies + 1 WHERE id = $threadid");
        
        // ahhhhhhhh more great coding
        if ($foruminfo[name] == 'Spam') {
          dbquery("UPDATE users SET lastposttime = $currenttime WHERE userid = {$s[user][userid]}");
        } else {
          dbquery("UPDATE users SET posts = posts + 1, lastposttime = $currenttime WHERE userid = {$s[user][userid]}");
        }
        
        $threadinfo[name] = iprotect($threadinfo[name]);
        dbquery("UPDATE forums SET lastposter = '$iname', lastposterid = {$s[user][userid]}, lastpostedin = '$threadinfo[name]', lastpostedinid = $threadid, lastpostdate = $currenttime, posts = posts + 1 WHERE id = $threadinfo[forum]");
        dbquery("DELETE FROM threadread WHERE thread = $threadid");
        dbquery("INSERT INTO posts (thread,authorid,authorname,postdate,posttext,postnum) VALUES ($threadid,{$s[user][userid]},'$iname',$currenttime,'$inserttext',$postnum)");
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the reply has been posted successfully
    // if it returns nothing, just show the form
  }
?>