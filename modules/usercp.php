<?php
  if (!defined('IN_TBB')) die();
  
  if (!$s[logged_in]) {
    print "You must be logged in to use the User CP.<br><a href='index.php'>Return to the main page</a>";
  } else {
  
  $valid_actions = array('menu', 'changepw', 'changesig', 'changeinfo', 'editprofile', 'avatar');
  $action = $_GET['act'];
  if (!isset($_GET['act']) || $_GET['act'] == '') $action = 'menu';
  
  print "<table style='margin: 0 auto; width: 100%'>";
  print "<tr>";
  print "<td style='width: 150px' valign='top'>";
  
  print "<table cellspacing='0' cellpadding='0'>";
  print "<tr><td><b>User CP Options:</b></td></tr>";
  print "<tr><td style='height: 2px'></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=usercp&act=changepw'>Change password</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=usercp&act=changesig'>Change signature</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=usercp&act=changeinfo'>Change your personal info</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=usercp&act=editprofile'>Edit profile</a></td></tr>";
  print "<tr><td align='left'><a href='index.php?m=usercp&act=avatar'>Change avatar</a></td></tr>";
  print "</table>";
  
  print "</td>";
  print "<td style='width: 10px'></td>";
  print "<td valign='top'>";
  
  switch ($action) {
    case 'menu':
      print "Choose an option from the left.";
      break;
    
    case 'changepw':
      // if it returns a non-blank string, it's an error
      // if it returns true (check with ===) the password has been changed successfully
      // if it returns nothing, just show the form
      $result = change_password();
      if ($result === true) {
        header("Location: index.php?m=login");
      } else {
        if ($result != '') {
          print '<b>The following errors occurred while changing your password:<br>'.$result.'</b><hr>';
        }
?>
<b>Change your password:</b><br>
Note: You will have to log in again once your password has been changed.<br>
<form action='index.php?m=usercp&act=changepw' method='post'>
<table style='margin: 0 auto; width: 100%'>
  <tr>
    <td align='left' style='width: 30%'><b>Current Password:</b></td>
    <td align='left' style='width: 70%'><input type='password' size='30' maxlength='30' name='currentpw' value='' class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>New Password:</b></td>
    <td align='left' style='width: 70%'><input type='password' size='30' maxlength='30' name='newpw' value='' class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Retype New Password:</b></td>
    <td align='left' style='width: 70%'><input type='password' size='30' maxlength='30' name='newpw_retype' value='' class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Change Password' class='button'></td>
  </tr>
</table>
</form>
<?php
      }
      break;
    
    case 'changesig':
      if (isset($_POST[makeit])) {
        $displaysig = $_POST['text'];
        $newsig = iprotect($_POST['text']);
        dbquery("UPDATE users SET signature = '$newsig' WHERE userid = {$s[user][userid]}");
      } else {
        $displaysig = $s['user']['signature'];
      }
?>
<b>Change your signature:</b><br>
<form action='index.php?m=usercp&act=changesig' method='post'>
<textarea rows='8' cols='70' name='text'><?=htmlspecialchars($displaysig);?></textarea>
<br>
<input type='submit' name='makeit' value='Change Signature' class='button'>
</form>
<?php
      break;
    
    case 'changeinfo':
      if (isset($_POST[makeit])) {
        $displayinfo = $_POST['text'];
        $newinfo = iprotect($_POST['text']);
        dbquery("UPDATE users SET info = '$newinfo' WHERE userid = {$s[user][userid]}");
      } else {
        $displayinfo = $s['user']['info'];
      }
?>
<b>Change your personal info:</b><br>
<form action='index.php?m=usercp&act=changeinfo' method='post'>
<textarea rows='10' cols='70' name='text'><?=htmlspecialchars($displayinfo);?></textarea>
<br>
<input type='submit' name='makeit' value='Update Info' class='button'>
</form>
<?php
      break;
    
    case 'avatar':
      // if it returns a non-blank string, it's an error
      // if it returns true (check with ===) the avatar has been changed successfully
      // if it returns nothing, just show the form
      $result = change_avatar();
      if ($result === true) {
        header("Location: index.php?m=usercp&act=avatar");
      } else {
        if ($result != '') {
          print '<b>The following errors occurred while changing your avatar:<br>'.$result.'</b><hr>';
        }
        print "<b>Change your avatar: (<a href='index.php?m=usercp'>Return to User CP</a>)</b><br>";
        if ($s[user][hasavatar] == 1) {
          print "<img src='avatars/{$s[user][userid]}.{$s[user][avatarext]}' alt='Avatar'><br>";
        }
?>
Allowed formats: PNG, GIF, JPG<br>
Maximum filesize: 250kb<br>
Maximum image size: 150x150<br>
<form action='index.php?m=usercp&act=avatar' method='post' enctype='multipart/form-data'>
<input type='hidden' name='MAX_FILE_SIZE' value='256000' />
<input type='file' name='avatarfile' class='textentry'>
<br>
<input type='submit' name='makeit' value='Upload Avatar' class='button'>
<input type='submit' name='deleteavatar' value='Remove Avatar' class='button'>
</form>
<?php
      }
      break;
    
    case 'editprofile':
      $days = array('01' => '1', '02' => '2', '03' => '3', '04' => '4', '05' => '5',
                    '06' => '6', '07' => '7', '08' => '8', '09' => '9', '10' => '10',
                    '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15',
                    '16' => '16', '17' => '17', '18' => '18', '19' => '19', '20' => '20',
                    '21' => '21', '22' => '22', '23' => '23', '24' => '24', '25' => '25',
                    '26' => '26', '27' => '27', '28' => '28', '29' => '29', '30' => '30', '31' => '31');
      $months = array('01' => 'January', '02' => 'February', '03' => 'March', '04' => 'April',
                      '05' => 'May', '06' => 'June', '07' => 'July', '08' => 'August',
                      '09' => 'September', '10' => 'October', '11' => 'November', '12' => 'December');
      $years = array('70' => '1970', '71' => '1971', '72' => '1972', '73' => '1973', '74' => '1974',
                     '75' => '1975', '76' => '1976', '77' => '1977', '78' => '1978', '79' => '1979',
                     '80' => '1980', '81' => '1981', '82' => '1982', '83' => '1983', '84' => '1984',
                     '85' => '1985', '86' => '1986', '87' => '1987', '88' => '1988', '89' => '1989',
                     '90' => '1990', '91' => '1991', '92' => '1992', '93' => '1993', '94' => '1994',
                     '95' => '1995', '96' => '1996', '97' => '1997', '98' => '1998', '99' => '1999',
                     '00' => '2000', '01' => '2001', '02' => '2002', '03' => '2003', '04' => '2004',
                     '05' => '2005', '06' => '2006', '07' => '2007', '08' => '2008', '09' => '2009');
      
      if (isset($_POST['makeit'])) {
        $utitle = iprotect($_POST['usertitle']);
        $quote = iprotect($_POST['quote']);
        $bday = '';
        if ($_POST['birthday'] == 'enable') {
          $bd_day = $_POST['birthday_day'];
          $bd_month = $_POST['birthday_month'];
          $bd_year = $_POST['birthday_year'];
          if (isset($days[$bd_day]) && isset($months[$bd_month]) && isset($years[$bd_year])) {
            $bday = $bd_day.'-'.$bd_month.'-'.$bd_year;
          }
        }
        
        dbquery("UPDATE users SET usertitle='$utitle', quote='$quote', birthday='$bday' WHERE userid={$s[user][userid]}");
        
        //header("Location: index.php?m=usercp&act=editprofile");
      }
      
      if (!isset($_POST['birthday']) && $_POST['birthday'] != 'enable' && $_POST['birthday'] != 'disable') {
        if ($s[user][birthday] == '') {
          $_POST['birthday'] = 'disable';
          $_POST['birthday_day'] = '';
          $_POST['birthday_month'] = '';
          $_POST['birthday_year'] = '';
        } else {
          $_POST['birthday'] = 'enable';
          $bd = explode('-', $s[user][birthday]);
          $_POST['birthday_day'] = $bd[0];
          $_POST['birthday_month'] = $bd[1];
          $_POST['birthday_year'] = $bd[2];
        }
      }
      if (!isset($_POST['usertitle'])) $_POST['usertitle'] = $s[user][usertitle];
      if (!isset($_POST['quote'])) $_POST['quote'] = $s[user][quote];
?>
<b>Edit your profile:</b><br>
<form action='index.php?m=usercp&act=editprofile' method='post'>
<table style='margin: 0 auto; width: 100%'>
  <tr>
    <td align='left' style='width: 30%'><b>User Title:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='50' name='usertitle' value="<?=htmlspecialchars($_POST['usertitle']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left'><b>Quote:</b></td>
    <td align='left'><input type='text' size='30' maxlength='80' name='quote' value="<?=htmlspecialchars($_POST['quote']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' valign='top'><b>Birthday:</b></td>
    <td align='left'>
      <input type='radio' name='birthday' value='disable'<?php if ($_POST['birthday'] == 'disable') print ' checked=\'checked\''; ?>>
      Don't use<br>
      <input type='radio' name='birthday' value='enable'<?php if ($_POST['birthday'] == 'enable') print ' checked=\'checked\''; ?>>
      Show under Today's Birthdays:
<?php
  $display = array('birthday_day' => $days, 'birthday_month' => $months, 'birthday_year' => $years);
  foreach ($display as $fieldname => $choices) {
    $chosen = $_POST[$fieldname];
    print "<select name='$fieldname'>";
    foreach ($choices as $ckey => $disp) {
      print "<option value='$ckey'";
      if ($ckey == $chosen) print " selected='selected'";
      print ">$disp</option>";
    }
    print "</select>";
  }
?>
    </td>
    </td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Update Profile' class='button'></td>
  </tr>
</table>
</form>
<?php
  }
  
  print "</td></tr></table>";
  
  }
  
  function change_password() {
    global $s; // self note: not having this is why so many functions mess up
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      if ($s[user][pwhash] != md5(md5($s[user]['salt']).md5($_POST[currentpw])))
        $error_string .= 'Your current password was incorrect.<br>';
      
      if ($_POST[newpw] != $_POST[newpw_retype])
        $error_string .= 'The two passwords you entered didn\'t match.<br>';
      
      if ($error_string != '') {
        return $error_string;
      } else {
        $newpw = md5(md5($s[user][salt]).md5($_POST[newpw]));
        dbquery("UPDATE users SET pwhash = '$newpw' WHERE userid = {$s[user][userid]}");
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the password has been changed successfully
    // if it returns nothing, just show the form
  }

  function change_avatar() {
    global $s; // self note: not having this is why so many functions mess up
    global $avatarpath;
    
    if (isset($_POST[makeit])) {
      $error_string = '';
      
      $filename = stripslashes($_FILES['avatarfile']['name']);
      $checkext = strtolower(substr($filename,strlen($filename)-3));
      
      if ($checkext != 'jpg' && $checkext != 'png' && $checkext != 'gif') {
        $error_string .= 'The file you uploaded doesn\'t seem to be a supported format.<br>';
      } else {
        $fsize = filesize($_FILES['avatarfile']['tmp_name']);
        if ($fsize > 256000) {
          $error_string .= 'The file you uploaded is too big.<br>';
        } else {
          $vals = false;
          
          $checkit = false;
          if ($checkext == 'png') {
            $checkit = @imagecreatefrompng($_FILES['avatarfile']['tmp_name']);
          }
          if ($checkext == 'gif') {
            $checkit = @imagecreatefromgif($_FILES['avatarfile']['tmp_name']);
          }
          if ($checkext == 'jpg') {
            $checkit = @imagecreatefromjpeg($_FILES['avatarfile']['tmp_name']);
          }
          
          if ($checkit !== false) {
            if (imagesx($checkit) <= 150 && imagesy($checkit) <= 150) {
              $vals = true;
            } else {
              $error_string .= 'The avatar you uploaded is too big.<br>';
            }
            imagedestroy($checkit);
          }
          
          if ($vals) {
            if (move_uploaded_file($_FILES['avatarfile']['tmp_name'], $avatarpath.$s[user][userid].'.'.$checkext) !== false) {
              if ($checkext != 'png') @unlink($avatarpath.$s[user][userid].'.png');
              if ($checkext != 'gif') @unlink($avatarpath.$s[user][userid].'.gif');
              if ($checkext != 'jpg') @unlink($avatarpath.$s[user][userid].'.jpg');
              dbquery("UPDATE users SET hasavatar = 1 WHERE userid = {$s[user][userid]}");
              dbquery("UPDATE users SET avatarext = '$checkext' WHERE userid = {$s[user][userid]}");
            } else {
              $error_string .= 'Unknown error occurred.<br>';
            }
          } elseif ($error_string == '') {
            $error_string .= 'The image seems to be in an invalid format.<br>';
          }
        }
      }
      
      if ($error_string != '') {
        return $error_string;
      } else {
        return true;
      }
    }
    
    if (isset($_POST[deleteavatar])) {
      @unlink($avatarpath.$s[user][userid].'.png');
      @unlink($avatarpath.$s[user][userid].'.gif');
      @unlink($avatarpath.$s[user][userid].'.jpg');
      dbquery("UPDATE users SET hasavatar = 0 WHERE userid = {$s[user][userid]}");
      return true;
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the avatar has been changed successfully
    // if it returns nothing, just show the form
  }
?>
