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
      if ($s[user][powerlevel] < $foruminfo[thread_power] || !can_view_forum($foruminfo)) {
        print "You're not allowed to create threads in this forum.<br><a href='index.php'>Return to the main page</a>";
      } else {
        // if it returns a non-blank string, it's an error
        // if it returns a thread id (check with is_numeric) the thread has been created successfully
        // if it returns nothing, just show the form
        $result = create_thread();
        if (is_numeric($result)) {
          header("Location: index.php?showthread=$result");
        } else {
          if ($result != '') {
            print '<b>The following errors occurred while creating your thread:<br>'.$result.'</b><br>Your post data has been saved.<hr>';
          }
          if (isset($_POST['preview'])) {
            print "<b>Preview:</b>";
            $posttext = getpost($_POST['text'],true,true,false);
            display_post($s[user],'Posted',time(),$cmds,$posttext);
            print "<br>";
          }
?>
<b>Create a thread in <?=$foruminfo[name];?>: (<a href='index.php?showforum=<?=$forumid;?>'>Return to <?=$foruminfo[name];?></a>)</b>
<br><br>
<form action='index.php?m=board&act=newthread&id=<?=$forumid;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>Thread Title:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='70' maxlength='70' name='threadname' value="<?=htmlspecialchars($_POST['threadname']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left'><b>Thread Description:</b></td>
    <td align='left'><input type='text' size='70' maxlength='70' name='threaddesc' value="<?=htmlspecialchars($_POST['threaddesc']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' valign='top'><b>Thread Icon:</b></td>
    <td align='left'>
      <div class='posticonlist'>
      <table style='margin: 0 auto' cellpadding='2' cellspacing='1'>
      <tr><td colspan='4' align='left'><input type='radio' name='icon' value='none' checked='checked'> No Icon</td>
<?php
  $rowleft = 4;
  $skip = array(); // if any smilies shouldn't be available as posticons, add them to this array
  foreach ($smilies as $t => $r) {
    if (in_array($r, $skip)) continue;
    $picked = '';
    if ($r == $_POST['icon']) $picked = " checked='checked'";
    print "<td><input type='radio' name='icon' value='$r'$picked></td>";
    print "<td><img src='smilies/$r'></td>";
    $rowleft -= 1;
    if ($rowleft <= 0) {
      $rowleft = 6;
      print "</tr><tr>";
    }
  }
  print "</tr>";
?>
      </table>
      </div>
    </td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' valign='top'>
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
    <td colspan='2'><input type='submit' name='makeit' value='Post Thread' class='button'> <input type='submit' name='preview' value='Preview' class='button'></td>
  </tr>
</table>
</form>
<?php
        }
      }
    }
  }

  function create_thread() {
    global $s; // self note: not having this is why so many functions mess up
    global $foruminfo;
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      // validate thread title
      if (!(($_POST['threadname'] != '')&&(strlen($_POST['threadname']) <= 70)))
        $error_string .= 'Thread title was either not entered, or too long.<br>'."\n".
                         'It must be 70 characters or less.<br>'."\n";

      // validate thread description
      if ($_POST['threaddesc'] != '' && strlen($_POST['threaddesc']) > 70)
        $error_string .= 'Your thread description was too long.<br>'."\n".
                         'It must be 70 characters or less.<br>'."\n";

      if (!isset($_POST['text']) or $_POST['text'] == '')
        $error_string .= 'You didn\'t enter a post.<br>';
      
      if ($s[user][powerlevel] < $foruminfo[mod_power] && time() < ($s[user][lastposttime]+30))
        $error_string .= "You've already posted in the last 30 seconds.<br>";
      
      if ($error_string != '') {
        return $error_string;
      } else {
        $insertname = iprotect($_POST['threadname']);
        $insertdesc = iprotect($_POST['threaddesc']);
        $inserttext = iprotect($_POST['text']);
        global $forumid;
        $currenttime = time();
        global $smilies;
        if (in_array($_POST['icon'], $smilies)) {
          $newicon = $_POST['icon'];
        } else {
          $newicon = '';
        }
        $iname = iprotect($s[user][username]);
        dbquery("INSERT INTO threads (forum,name,`desc`,authorid,authorname,lastposterid,lastpostername,lastpostdate,icon) VALUES ($forumid,'$insertname','$insertdesc',{$s[user][userid]},'$iname',{$s[user][userid]},'$iname',$currenttime,'$newicon')");
        $threadid = mysql_insert_id();
        $postnum = $s[user][posts] + 1;
        dbquery("INSERT INTO posts (thread,authorid,authorname,postdate,posttext,postnum) VALUES ($threadid,{$s[user][userid]},'$iname',$currenttime,'$inserttext',$postnum)");
        
        // Wow Look At This Excellent Coding
        if ($foruminfo[name] == 'Spam') {
          dbquery("UPDATE users SET lastposttime = $currenttime WHERE userid = {$s[user][userid]}");
        } else {
          dbquery("UPDATE users SET posts = posts + 1, threads = threads + 1, lastposttime = $currenttime WHERE userid = {$s[user][userid]}");
        }
        dbquery("UPDATE forums SET lastposter = '$iname', lastposterid = {$s[user][userid]}, lastpostedin = '$insertname', lastpostedinid = $threadid, lastpostdate = $currenttime, threads = threads + 1, posts = posts + 1 WHERE id = $forumid");
        
        // IRC new thread reports go here
        // relevant info: $foruminfo[view_power], $s[user][username],
        // $_POST[threadname], $foruminfo[name], index.php?showthread=$threadid
        
        return $threadid;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns a thread id (check with is_numeric) the thread has been created successfully
    // if it returns nothing, just show the form
  }
?>