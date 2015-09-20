<?php
  if (!defined('IN_TBB')) die();
  
  $threadid = $_GET['id'];
  if (!is_numeric($threadid)) {
    print "Invalid thread ID.<br><a href='index.php'>Return to the main page</a>";
  } else {
    $threadid = intval($threadid); // just to be safe
    $threadquery = dbquery("SELECT * FROM threads WHERE id = $threadid");
    
    if (mysql_num_rows($threadquery) == 0) {
      print "No thread with this ID exists.<br><a href='index.php'>Return to the main page</a>";
    } else {
      $threadinfo = dbrow($threadquery);
      
      $checkifpoll = dbquery("SELECT id FROM polls WHERE thread = $threadid");
      if (mysql_num_rows($checkifpoll) != 0) {
        print "This thread already has a poll.<br><a href='index.php?showthread=$threadid'>Return to the thread</a>";
      } else {
        if ($threadinfo['authorid'] != $s['user']['userid']) {
          print "You can't add a poll to a thread you didn't create.<br><a href='index.php?showthread=$threadid'>Return to the thread</a>";
        } else {
          // if it returns a non-blank string, it's an error
          // if it returns true (check with === not ==) the poll has been created successfully
          // if it returns nothing, just show the form
          $result = add_poll();
          if ($result === true) {
            header("Location: index.php?showthread=$threadid");
          } else {
            if ($result != '') {
              print '<b>The following errors occurred while creating a poll:<br>'.$result.'</b><hr>';
            }
?>
<b>Adding a poll to <?=htmlspecialchars($threadinfo[name]);?>: (<a href='index.php?showthread=<?=$threadid;?>'>Return to Thread</a>)</b>
<form action='index.php?m=board&act=addpoll&id=<?=$threadid;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>Poll Question:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='70' maxlength='70' name='question' value="<?=htmlspecialchars($_POST['question']);?>" class='textentry'></td>
  </tr>
  <tr>
    <td align='left' valign='top'><b>Choices:</b><br>(Enter each on a separate line)</td>
    <td align='left'><textarea rows='6' cols='70' name='choices'><?=htmlspecialchars($_POST['choices']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td></td>
    <td align='left'>
      <input type='radio' name='pollviewable' value='private'<?php if ($_POST['pollviewable'] != 'public') print " checked='checked'"; ?>> Don't list people who voted<br>
      <input type='radio' name='pollviewable' value='public'<?php if ($_POST['pollviewable'] == 'public') print " checked='checked'"; ?>> Show voters beside each choice
    </td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Add Poll' class='button'></td>
  </tr>
</table>
</form>
<?php
          }
        }
      }
    }
  }
  
  function add_poll() {
    global $s; // self note: not having this is why so many functions mess up
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      if (!isset($_POST['question']) or $_POST['question'] == '')
        $error_string .= 'You didn\'t enter a question.<br>';
      
      if (!isset($_POST['choices']) or $_POST['choices'] == '')
        $error_string .= 'You didn\'t enter any choices.<br>';
      
      if ($error_string != '') {
        return $error_string;
      } else {
        global $threadid;
        $question = iprotect($_POST['question']);
        $choice_array = explode("\n", str_replace("\r", '', $_POST['choices']));
        $choices = iprotect(implode('|', $choice_array));
        $polldata_array = array();
        for ($i = 0; $i < count($choice_array); $i++) {
          $polldata_array[] = '0';
        }
        $polldata = iprotect(implode('|', $polldata_array));
        $viewable = 0;
        if ($_POST['pollviewable'] == 'public') $viewable = 1;
        $time = time();
        $choicecount = count($choice_array);
        dbquery("INSERT INTO polls (thread,question,date,choices,voteinfo,choicecount,votecount,userviewable) VALUES ($threadid,'$question',$time,'$choices','$polldata',$choicecount,0,$viewable)");
        print mysql_error();
        $pollid = mysql_insert_id();
        dbquery("UPDATE threads SET poll = $pollid WHERE id = $threadid");
        print mysql_error();
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the poll has been created successfully
    // if it returns nothing, just show the form
  }
?>