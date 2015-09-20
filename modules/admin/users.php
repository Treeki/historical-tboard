<?php
  if (!defined('IN_TBB')) die();
  
  $valid_actions = array('list', 'edit', 'changeun', 'changepw', 'updateprofile');
  $action = $_GET['do'];
  if (!isset($_GET['do']) || $_GET['do'] == '') $action = 'list';
  
  function powerleveldropbox($fieldname='power', $selected=null) {
    global $powerlevels;
    print "<select name='$fieldname'>";
    foreach ($powerlevels as $ckey => $disp) {
      print "<option value='$ckey'";
      if ($ckey == $selected) print " selected='selected'";
      print ">$ckey: $disp</option>";
    }
    print "</select>";
  }
  
  print "<b>View/Edit Users</b>";
  print "<div class='smallspacing'></div>";
  
  switch ($action) {
    case 'list':
      if (isset($_POST['finduser'])) {
        $search = iprotect($_POST['user']);
        $findit = dbquery("select userid,username,powerlevel,email,regip from users where username like '%$search%' order by userid");
        if (mysql_num_rows($findit) == 0) {
          print "No users matching ".htmlspecialchars($_POST['user'])." were found.<hr>";
        } elseif (mysql_num_rows($findit) == 1) {
          $getit = dbrow($findit);
          header('Location: index.php?m=admin&act=users&do=edit&id='.$getit[userid]);
        } else {
          print "All users matching ".htmlspecialchars($_POST['user']).":";
          print "<div class='bigspacing'></div>";
          print "<table class='styled' style='width: 100%; max-width: 800px; margin: 0 auto' cellpadding='0' cellspacing='0'>";
          print "<tr class='header'><td style='width: 10%'>ID</td><td>Username</td><td style='width: 30%'>Email</td><td style='width: 30%'>IP Address</td></tr>";
          
          while ($row = dbrow($findit)) {
            $fname = format_name(htmlspecialchars($row[username]), $row[powerlevel]);
            $user = "<a href='index.php?m=admin&act=users&do=edit&id=$row[userid]' class='userlink' style='$fname[1]'>$fname[0]</a>";
            $email = htmlspecialchars($row[email]);
            print "<tr><td>$row[userid]</td><td>$user</td><td>$email</td><td>$row[regip]</td></tr>";
          }
          
          print "</table>";
          print "<hr>";
        }
      }
      
      print "<form action='index.php?m=admin&act=users&do=list' method='post'>";
      print "<b>Search for a user:</b><br>";
      print "Enter part or all of a username to search for it.<br>";
      print "<input type='text' name='user' size='30' class='textentry'>";
      print "<input type='submit' name='finduser' value='Search!' class='button'>";
      print "</form>";
      
      break;
    case 'edit':
      $id = intval($_GET['id']);
      $getuser = dbquery("SELECT * FROM users WHERE userid = $id");
      if (mysql_num_rows($getuser) == 0) {
        print "No user exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=users&do=list'>Return to editing users</a>";
      } else {
        $user = dbrow($getuser);
        $showusername = htmlspecialchars($user[username]);
        print "Editing the user $showusername:";
        print " (<a href='index.php?m=admin&act=users&do=list'>Return to editing users</a>)";
?>
<hr>
<b>User Info:</b><br>
Registration IP: <?=$user['regip'];?><br>
Last Used IP: <?=$user['lastip'];?><br>
<hr>
<b>Change Username:</b>
<div class='smallspacing'></div>
<form action='index.php?m=admin&act=users&do=changeun&id=<?=$id;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>New Username:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='30' name='name' value="<?=htmlspecialchars($user['username']);?>" class='textentry'></td>
  </tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Change Username' class='button'></td>
  </tr>
</table>
</form>

<hr>
<b>Change Password:</b>

<form action='index.php?m=admin&act=users&do=changepw&id=<?=$id;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>New Password:</b></td>
    <td align='left' style='width: 70%'><input type='password' size='30' maxlength='30' name='newpw' class='textentry'></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>Retype Password:</b></td>
    <td align='left' style='width: 70%'><input type='password' size='30' maxlength='30' name='newpwretype' class='textentry'></td>
  </tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Change Password' class='button'></td>
  </tr>
</table>
</form>

<hr>
<b>Edit Profile:</b>

<form action='index.php?m=admin&act=users&do=updateprofile&id=<?=$id;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Powerlevel:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('powerlevel', $user['powerlevel']); ?></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Post Count:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='10' maxlength='10' name='posts' value='<?=$user['posts'];?>' class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Thread Count:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='10' maxlength='10' name='threads' value='<?=$user['threads'];?>' class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Email Address:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='80' name='email' value="<?=htmlspecialchars($user['email']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>User Title:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='50' name='usertitle' value="<?=htmlspecialchars($user['usertitle']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Avatar Options:</b></td>
    <td align='left' style='width: 70%'><input type='checkbox' name='hasavatar' value='haveit'<?php if ($user['hasavatar'] == 1) print " checked='checked'"; ?>> Has an Avatar</td>
  </tr>
  <tr>
    <td align='right' style='width: 30%'>Extension:</td>
    <td align='left' style='width: 70%'><input type='text' size='3' maxlength='3' name='avatarext' value="<?=htmlspecialchars($user['avatarext']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Location:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='30' name='location' value="<?=htmlspecialchars($user['location']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Quote:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='30' maxlength='80' name='quote' value="<?=htmlspecialchars($user['quote']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Birthday:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='8' maxlength='8' name='birthday' value="<?=$user['birthday'];?>" class='textentry'> (Format: MM-DD-YY)</td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' colspan='2'><b>Signature:</b></td>
  </tr>
  <tr>
    <td align='left' colspan='2'><textarea rows='8' cols='70' name='signature'><?=htmlspecialchars($user['signature']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' colspan='2'><b>Personal Info:</b></td>
  </tr>
  <tr>
    <td align='left' colspan='2'><textarea rows='8' cols='70' name='info'><?=htmlspecialchars($user['info']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Member Groups:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='20' maxlength='10' name='groups' value="<?=htmlspecialchars($user['groups']);?>" class='textentry'> (Numbers; Comma Separated)</td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Update Profile' class='button'></td>
  </tr>
</table>
</form>
<?php
      }
      
      break;
    case 'changeun':
      $id = intval($_GET['id']);
      $getuser = dbquery("SELECT * FROM users WHERE userid = $id");
      if (mysql_num_rows($getuser) == 0) {
        print "No user exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=users&do=list'>Return to editing users</a>";
      } else {
        $user = dbrow($getuser);
        $newun = iprotect($_POST['name']);
        $oldun = iprotect($user['username']);
        if ($newun != '') {
          $checkifexists = dbquery("SELECT userid FROM users WHERE username = '$newun'");
          if (mysql_num_rows($checkifexists) != 0) {
            $showuser = htmlspecialchars($_POST['name']);
            $showolduser = htmlspecialchars($user['username']);
            print "The username $showuser is taken.<br>";
            print "<a href='index.php?m=admin&act=users&do=edit&id=$id'>Return to editing $showolduser</a>";
          } else {
            dbquery("update users set username = '$newun' where userid = $id");
            dbquery("update forums set lastposter = '$newun' where lastposterid = $id");
            dbquery("update threads set lastpostername = '$newun' where lastposterid = $id");
            dbquery("update threads set authorname = '$newun' where authorid = $id");
            header("Location: index.php?m=admin&act=users&do=edit&id=$id");
          }
        } else {
          header("Location: index.php?m=admin&act=users&do=edit&id=$id");
        }
      }
      break;
    case 'changepw':
      $id = intval($_GET['id']);
      $getuser = dbquery("SELECT * FROM users WHERE userid = $id");
      if (mysql_num_rows($getuser) == 0) {
        print "No user exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=users&do=list'>Return to editing users</a>";
      } else {
        $user = dbrow($getuser);
        $newhash = md5(md5($user['salt']).md5($_POST[newpw]));
        if ($_POST[newpw] != $_POST[newpwretype]) {
          $showuser = htmlspecialchars($user['username']);
          print "The two passwords you entered didn't match.<br>";
          print "<a href='index.php?m=admin&act=users&do=edit&id=$id'>Return to editing $showuser</a>";
        } else {
          dbquery("update users set pwhash = '$newhash' where userid = $id");
          header("Location: index.php?m=admin&act=users&do=edit&id=$id");
        }
      }
      break;
    case 'updateprofile':
      $id = intval($_GET['id']);
      $getuser = dbquery("SELECT * FROM users WHERE userid = $id");
      if (mysql_num_rows($getuser) == 0) {
        print "No user exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=users&do=list'>Return to editing users</a>";
      } else {
        $user = dbrow($getuser);
        $powerlevel = intval($_POST[powerlevel]);
        $posts = intval($_POST[posts]);
        $threads = intval($_POST[threads]);
        $email = iprotect($_POST[email]);
        $usertitle = iprotect($_POST[usertitle]);
        $hasavatar = 0;
        if ($_POST[hasavatar] == 'haveit') $hasavatar = 1;
        $avatarext = iprotect($_POST[avatarext]);
        $location = iprotect($_POST[location]);
        $quote = iprotect($_POST[quote]);
        $birthday = '';
        if ($_POST[birthday] != '') {
          $validify = explode('-', $_POST[birthday]);
          if (count($validify) == 3) {
            $b = array();
            $b[0] = str_pad(intval($validify[0]),2,'0',STR_PAD_LEFT);
            $b[1] = str_pad(intval($validify[1]),2,'0',STR_PAD_LEFT);
            $b[2] = str_pad(intval($validify[2]),2,'0',STR_PAD_LEFT);
            $birthday = implode('-', $b);
          }
        }
        $signature = iprotect($_POST[signature]);
        $info = iprotect($_POST[info]);
        $rgroups = array();
        if ($_POST[groups] != '') {
          $sgroups = explode(',', $_POST[groups]);
          foreach ($sgroups as $x) {
            $g = intval(trim($x));
            if ($g > 0) {
              $rgroups[] = $g;
            }
          }
        }
        $groups = implode(',', $rgroups);
        dbquery("update users set powerlevel=$powerlevel,posts=$posts,threads=$threads,email='$email',usertitle='$usertitle',hasavatar=$hasavatar,avatarext='$avatarext',location='$location',quote='$quote',birthday='$birthday',signature='$signature',info='$info',groups='$groups' where userid = $id");
        header("Location: index.php?m=admin&act=users&do=edit&id=$id");
      }
      break;
  }
?>