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
      $userinfo = dbrow(dbquery("SELECT * FROM users WHERE userid = $postinfo[authorid]"));
      $threadinfo = dbrow(dbquery("SELECT * FROM threads WHERE id = $postinfo[thread]"));
      $foruminfo = dbrow(dbquery("SELECT * FROM forums WHERE id = $threadinfo[forum]"));
      $threadinfo[name] = htmlspecialchars($threadinfo[name]);
      if ($s[user][powerlevel] < $foruminfo[mod_power] && $s[user][userid] != $postinfo[authorid]) {
        print "You're not allowed to edit this post.<br><a href='index.php?showthread=$threadid'>Return to the thread</a>";
      } else {
        // if it returns a non-blank string, it's an error
        // if it returns true (check with ===) the post has been edited successfully
        // if it returns nothing, just show the form
        $result = edit_post();
        if ($result === true) {
          header("Location: index.php?showthread=$threadinfo[id]&post=$postid#post$postid");
        } else {
          if ($result != '') {
            print '<b>The following errors occurred while editing your post:<br>'.$result.'</b><br>Your post data has been saved.<hr>';
          }
          if (isset($_POST['preview'])) {
            print "<b>Preview:</b>";
            $posttext = getpost($_POST['text'],true,true,false);
            display_post($s[user],'Posted',$postinfo[postdate],$cmds,$posttext);
            print "<br>";
          }
          if (!isset($_POST['text'])) {
            $_POST[text] = $postinfo[posttext];
          }
?>
<b>Editing <?=htmlspecialchars($userinfo[username]);?>'s post in <?=$threadinfo[name];?>: (<a href='index.php?showthread=<?=$threadinfo[id];?>&post=<?=$postid;?>'>Return to <?=$threadinfo[name];?></a>)</b>
<br>
<form action='index.php?m=board&act=editpost&id=<?=$postid;?>' method='post'>
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
    <td align='left'><?php post_toolbar(); ?><textarea rows='12' cols='70' name='text' id='typehere'><?=htmlspecialchars($_POST['text']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Edit Post' class='button'> <input type='submit' name='preview' value='Preview' class='button'></td>
  </tr>
</table>
</form>
<?php
        }
      }
    }
  }

  function edit_post() {
    global $s; // self note: not having this is why so many functions mess up
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      if (!isset($_POST['text']) or $_POST['text'] == '')
        $error_string .= 'You didn\'t enter a post.<br>';
      
      if ($error_string != '') {
        return $error_string;
      } else {
        $timeformatted = parsedate(time());
        $inserttext = iprotect($_POST['text']);//."\n\n[size=1][Edited by {$s[user][username]} at $timeformatted.][/size]");
        $un = htmlspecialchars($s[user][username]);
        $editinfo = iprotect("Last edited by $un at $timeformatted");
        global $postid;
        dbquery("UPDATE posts SET posttext = '$inserttext', editinfo='$editinfo' WHERE id = $postid");
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the post has been edited successfully
    // if it returns nothing, just show the form
  }
?>