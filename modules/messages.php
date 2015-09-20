<?php
  if (!defined('IN_TBB')) die();
  
  $valid_actions = array('list', 'view', 'send', 'delete');
  $action = $_GET['act'];
  if (!isset($_GET['act']) || $_GET['act'] == '') $action = 'list';
  
  switch ($action) {
    case 'list':
      if (!$s[logged_in]) {
        print "You must be logged in to view your private messages.<br><a href='index.php'>Return to the main page</a>";
      } else {
        if ($_GET['sent'] == '1') {
          $other = 'recipient';
          $me = 'sender';
          $other_f = 'Recipient';
          $me_f = 'Sender';
        } else {
          $other = 'sender';
          $me = 'recipient';
          $other_f = 'Sender';
          $me_f = 'Recipient';
        }
        
        $pmquery = dbquery("SELECT pmessages.id,pmessages.title,pmessages.$other,pmessages.pmread,users.userid,users.username,users.powerlevel FROM pmessages LEFT JOIN users ON pmessages.$other=users.userid WHERE pmessages.$me = {$s[user][userid]} AND pmessages.exists_$me = 1 ORDER BY sentdate DESC");
        
        print "<b>Private Messages:</b><br>";
        if ($_GET['sent'] == '1') {
          $received = "<a href='index.php?m=messages'>Received</a>";
          $sent = "<b>Sent</b>";
          $sentflag = "&returntosent=1";
        } else {
          $received = "<b>Received</b>";
          $sent = "<a href='index.php?m=messages&sent=1'>Sent</a>";
          $sentflag = '';
        }
        print "<a href='index.php?m=messages&act=send'>Send a Message</a>";
        print " &middot; View: $received &middot; $sent";
        print "<table class='styled' style='width: 100%; max-width: 800px'>";
        print "<tr class='header'><td style='width: 24px'></td><td>Title:</td><td style='width: 20%'>$other_f:</td><td style='width: 20%'>Controls:</td></tr>";
        if (dbrows($pmquery) == 0) {
          if ($_GET['sent'] == 1) {
            print "<td colspan='4'>It seems that you haven't sent any private messages to anyone. Go talk to people!</td>";
          } else {
            print "<td colspan='4'>It seems that no one has sent you any private messages. :&lt;</td>";
          }
        } else {
          while ($row = dbrow($pmquery)) {
            $user = userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
            $row[title] = htmlspecialchars($row[title]);
            if ($row[pmread] == 1) { // reminder: only the recipient should be able to set a PM as "read" when it's viewed, not the sender
              $icon = "<img src='{$theme}images/icon_thread.png' alt='This message has been read.'>";
            } else {
              $icon = "<img src='{$theme}images/icon_threadunread.png' alt='This message has not been read.'>";
            }
            $controls = "<a href='#' onClick='if (confirm(\"Are you sure you want to delete this message?\") == true) { window.location = \"index.php?m=messages&act=delete&id=$row[id]$sentflag\"; }'>Delete</a>";
            print "<tr><td>$icon</td><td><a href='index.php?m=messages&act=view&id=$row[id]'>$row[title]</a></td><td>$user</td><td>$controls</td></tr>";
          }
        }
        print "</table>";
      }
      break;
    case 'view':
      $pmid = $_GET['id'];
      if (!is_numeric($pmid)) {
        print "Invalid message ID.<br><a href='index.php?m=messages'>Return to your private messages</a>";
        break;
      }
      
      $pmid = intval($pmid); // just to be safe
      $pmquery = dbquery("SELECT * FROM pmessages WHERE id = $pmid");
      if (mysql_num_rows($pmquery) == 0) {
        print "Either no message with this ID exists, or it's not your message.<br><a href='index.php?m=messages'>Return to your private messages</a>";
      } else {
        $pm = dbrow($pmquery);
        
        $canread = false;
        if (($pm[exists_sender] == 1 && $pm[sender] == $s[user][userid])) {
          $canread = true;
        }
        if (($pm[exists_recipient] == 1 && $pm[recipient] == $s[user][userid])) {
          $canread = true;
        }
        
        if (!$canread) {
          print "Either no message with this ID exists, or it's not your message.<br><a href='index.php?m=messages'>Return to your private messages</a>";
        } else {
          $pm[title] = htmlspecialchars($pm[title]);
         
          print "<b>Private Message: $pm[title]</b>";
          print "<br>";
          
          $getuserinfo = dbquery("SELECT ".postbox_query_fields_alone." FROM users WHERE userid = $pm[sender]");
          $userinfo = dbrow($getuserinfo);
          
          if ($pm[recipient] == $s[user][userid]) {
            dbquery("UPDATE pmessages SET pmread = 1 WHERE id = $pmid");
          }
          
          $posttext = getpost($pm[text],true,true,false);
          $cmds = " &middot; <a href='index.php?m=messages&act=send&reply=$pm[id]&target=$pm[sender]'>Reply</a>";
          display_post($userinfo,'Sent',$pm[sentdate],$cmds,$posttext);
          print "<br>";
          
          if ($pm[recipient] != $s[user][userid] && $pm[sender] == $s[user][userid]) {
            $sentflag = '&sent=1';
          } else {
            $sentflag = '';
          }
          print "<a href='index.php?m=messages$sentflag'>Return to your private messages</a>";
        }
      }
      break;
    case 'send':
      if (!$s[logged_in]) {
        print "You must be logged in to send private messages.<br><a href='index.php'>Return to the main page</a>";
      } else {
        // if it returns a non-blank string, it's an error
        // if it returns true (check with ===) the PM has been sent successfully
        // if it returns nothing, just show the form
        $result = send_pm();
	// self-note: I failed here where I should have used ===, I used ==
        if ($result === true) {
          header("Location: index.php?m=messages&sent=1");
        } else {
          if ($result != '') {
            print '<b>The following errors occurred while sending your PM:<br>'.$result.'</b><br>Your message has been saved.<hr>';
          }
          if (isset($_POST['preview'])) {
            print "<b>Preview:</b>";
            $posttext = getpost($_POST['text'],true,true,false);
            display_post($s[user],'Sent',time(),$cmds,$posttext);
            print "<br>";
          }
          if (isset($_GET['reply'])) {
            $quoteid = intval($_GET[reply]);
            $getquote = dbquery("SELECT pmessages.*, users.username FROM pmessages LEFT JOIN users ON pmessages.sender=users.userid WHERE id = $quoteid AND ((sender = {$s[user][userid]} AND exists_sender = 1) or (recipient = {$s[user][userid]} AND exists_recipient = 1))");
            if (dbrows($getquote) != 0) { // ignore the quote if it's an invalid id
              $quotepost = dbrow($getquote);
              $quotetime = parsedate($quotepost[sentdate]);
              $quote = "[quote=$quotepost[username] ($quotetime)]$quotepost[text][/quote]\n\n";
            }
          }
          if (isset($_GET['target'])) {
            $userid = intval($_GET[target]);
            $getuser = dbquery("SELECT username FROM users WHERE userid = $userid");
            if (dbrows($getuser) != 0) {
              $targetuser = dbrow($getuser);
              $_POST['recipient'] = $targetuser[username];
            }
          }
?>
<b>Send a private message:</b>
<br>
<form action='index.php?m=messages&act=send' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>Recipient:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='30' name='recipient' value="<?=htmlspecialchars($_POST['recipient']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left'><b>Message Title:</b></td>
    <td align='left'><input type='text' size='70' maxlength='70' name='title' value="<?=htmlspecialchars($_POST['title']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' valign='top'>
      <b>Message Text:</b><br>
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
    <td colspan='2'><input type='submit' name='makeit' value='Send Message' class='button'> <input type='submit' name='preview' value='Preview' class='button'></td>
  </tr>
</table>
</form>
<?php
        }
      }
      break;
    case 'delete':
      $pmid = $_GET['id'];
      if (!is_numeric($pmid)) {
        print "Invalid message ID.<br><a href='index.php?m=messages'>Return to your private messages</a>";
        break;
      }
      
      $pmid = intval($pmid); // just to be safe
      $pmquery = dbquery("SELECT * FROM pmessages WHERE id = $pmid");
      if (mysql_num_rows($pmquery) == 0) {
        print "Either no message with this ID exists, or it's not your message.<br><a href='index.php?m=messages'>Return to your private messages</a>";
      } else {
        $pm = dbrow($pmquery);
        
        $canread = false;
        if (($pm[exists_sender] == 1 && $pm[sender] == $s[user][userid])) {
          $canread = true;
        }
        if (($pm[exists_recipient] == 1 && $pm[recipient] == $s[user][userid])) {
          $canread = true;
        }
        
        if (!$canread) {
          print "Either no message with this ID exists, or it's not your message.<br><a href='index.php?m=messages'>Return to your private messages</a>";
        } else {
          $sentflag = "";
          if ($_GET['returntosent'] == 1) {
            $sentflag = "&sent=1";
          }
          
          if (($pm[exists_sender] == 1 && $pm[sender] == $s[user][userid])) {
            dbquery("UPDATE pmessages SET exists_sender = 0 WHERE id = $pmid");
          }
          
          if (($pm[exists_recipient] == 1 && $pm[recipient] == $s[user][userid])) {
            dbquery("UPDATE pmessages SET exists_recipient = 0 WHERE id = $pmid");
          }
          
          dbquery("DELETE FROM pmessages WHERE id = $pmid AND exists_sender = 0 AND exists_recipient = 0");
          header("Location: index.php?m=messages$sentflag");
        }
      }
      break;
  }

function send_pm() {
  global $s; // self note: not having this is why so many functions mess up
  
  if (isset($_POST[makeit])) {
    $error_string = '';
    
    $recipient = iprotect($_POST['recipient']);
    $getrec = dbquery("SELECT userid FROM users WHERE username = '$recipient'");
    if (dbrows($getrec) == 0) {
      $error_string .= 'No user named '.htmlspecialchars($_POST['recipient']).' seems to exist.<br>'."\n";
    } else {
      $getit = dbrow($getrec);
      $recipientid = $getit[userid];
    }
    
    if (!(($_POST['title'] != '')&&(strlen($_POST['title']) <= 70)))
      $error_string .= 'Message title was either not entered, or too long.<br>'."\n".
                       'It must be 70 characters or less.<br>'."\n";

    if (!isset($_POST['text']) or $_POST['text'] == '')
      $error_string .= 'You didn\'t enter a message.<br>';
    
    if ($error_string != '') {
      //print 'WE HANDLED AN ERROR IT WAS '.$error_string;
      return $error_string;
    } else {
      print 'THERE WAS NO ERROR';
      $inserttitle = iprotect($_POST['title']);
      $inserttext = iprotect($_POST['text']);
      $currenttime = time();
      dbquery("INSERT INTO pmessages (title,sender,recipient,exists_sender,exists_recipient,pmread,text,sentdate) VALUES ('$inserttitle',{$s[user][userid]},$recipientid,1,1,0,'$inserttext',$currenttime)");
      // WHY THE FUCK DOES THIS NOT TRIGGER
      //print mysql_error();
      return true;
    }
  }
  
  // if it returns a non-blank string, it's an error
  // if it returns true (check with ===) the PM has been sent successfully
  // if it returns nothing, just show the form
}
?>
