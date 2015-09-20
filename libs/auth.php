<?php
  if (!defined('IN_TBB')) die();
  
  function do_auth() {
    global $s;
    
    // ok, are they logged in through the session?
    // using a while loop that runs once as a hackish
    // way of letting me break out of it - if there was
    // a way to do that with an If statement, I would
    // use it instead..
    $s[logged_in] = 0;
    while ($_SESSION[logged_in] == 1) {
      //echo '<br />Logging in via session...<br />';
      // they claim to be logged in - let's validate it
      $checkun = iprotect($_SESSION[username]);
      $rchecktheuser = dbquery("SELECT * FROM users WHERE username = '$checkun'");
      if (dbrows($rchecktheuser) != 1) break;
      $checktheuser = dbrow($rchecktheuser);
      $passiv = base64_decode(stripslashes($checktheuser[lastivused]));
      if ($passiv == '') break;
      $newpass = mcrypt_decrypt(MCRYPT_BLOWFISH, PassEncodeKey, base64_decode($_SESSION[password]), MCRYPT_MODE_CBC, $passiv);
      $newpass = trim($newpass);
      if ($checktheuser[pwhash] == md5(md5($checktheuser['salt']).md5($newpass))) {
        //echo 'Hash matched, user is fine - successfully logged in as '.stripslashes($checkun).'<br />Click <a href=\'index.php?m=logout\'>here</a> to log out<br />';
        $s[logged_in] = 1;
        $s[user] = $checktheuser;
      }
      break;
    }

    while ($_COOKIE[logged_in] == 1 and $s[logged_in] != 1) {
      //echo '<br />Logging in via cookie...<br />';
      // they claim to be logged in via a cookie - let's validate it
      $checkun = iprotect($_COOKIE[username]);
      $rchecktheuser = dbquery("SELECT * FROM users WHERE username = '$checkun'");
      if (dbrows($rchecktheuser) != 1) break;
      $checktheuser = dbrow($rchecktheuser);
      $passiv = $checktheuser[lastivused];
      $passiv = base64_decode(stripslashes($passiv));
      if ($passiv == '') break;
      $newpass = mcrypt_decrypt(MCRYPT_BLOWFISH, PassEncodeKey, base64_decode($_COOKIE[password]), MCRYPT_MODE_CBC, $passiv);
      $newpass = trim($newpass);
      if ($checktheuser[pwhash] == md5(md5($checktheuser['salt']).md5($newpass))) {
        $_SESSION[logged_in] = 1;
        $_SESSION[username] = $_COOKIE[username];
        $_SESSION[password] = $_COOKIE[password];
        $s[logged_in] = 1;
        $s[user] = $checktheuser;
        //echo 'Hash matched, user is fine - successfully logged in as '.stripslashes($checkun).'<br />Click <a href=\'index.php?m=logout\'>here</a> to log out<br />';
      }
      break;
    }
    
    if (!$s[logged_in]) {
      global $guest_user;
      $s[user] = $guest_user;
    }
  }

  function do_login() {
    global $s;

    if (isset($_POST[login_submit])) {
      $checkun = iprotect($_POST[login_un]);
      $rchecktheuser = dbquery("SELECT * FROM users WHERE username = '$checkun'");
      if (dbrows($rchecktheuser) != 1) {
        return 'Sorry, the specified user doesn\'t exist. Please try again.';
      }
      $checktheuser = dbrow($rchecktheuser);
      if ($checktheuser[pwhash] == md5(md5($checktheuser['salt']).md5($_POST[login_pw]))) {
        // authenticated correctly!
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $storediv = addslashes(base64_encode($iv));
        $encryptedpw = base64_encode(mcrypt_encrypt(MCRYPT_BLOWFISH, PassEncodeKey, $_POST[login_pw], MCRYPT_MODE_CBC, $iv));
        dbquery("UPDATE users SET lastivused = '$storediv' WHERE username = '$checkun'");
        $cookietime = time() + 15768000;
        makecookie('logged_in', 1, $cookietime);
        makecookie('username', $_POST[login_un], $cookietime);
        makecookie('password', $encryptedpw, $cookietime);
        $_SESSION[logged_in] = 1;
        $_SESSION[username] = $_POST[login_un];
        $_SESSION[password] = $encryptedpw;
        $s[logged_in] = 1;
        return true;
      } else {
        return 'Wrong password, sorry.';
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with === not ==) you've logged in successfully
    // if it returns nothing, just show the form
  }
  
  function do_register() {
    if (isset($_POST[reg])) {
      $error_string = '';

      // validate username
      if (!(($_POST['un'] != '')&&(strlen($_POST['un']) <= 30)))
        $error_string .= 'Username was either not entered, or too long.<br>'."\n".
                         'It must be 30 characters or less.<br>'."\n";

      // check if username is taken
      if ($error_string == '' && username_exists(iprotect($_POST['un'])))
        $error_string .= 'This username is taken; please enter another one.<br>';

      // validate password
      if (!(($_POST['pw'] != '')&&(strlen($_POST['pw']) < 31)&&(strlen($_POST['pw']) > 5)))
        $error_string .= 'Password was either not entered, or too long.<br>'."\n".
                         'It must be between 6 and 30 characters.<br>'."\n";
      
      if ($_POST[pw] != $_POST[retypepw])
        $error_string .= 'The two passwords you entered didn\'t match.<br>';
      
      // validate email
      if ($_POST[email] == '')
        $error_string .= 'You didn\'t enter an email address.<br>';

      if ($error_string != '') {
        return $error_string;
      } else {
        $insertun = iprotect($_POST['un']);
        $insertemail = iprotect($_POST['email']);
        $vals = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnnopqrstuvwxyz0123456789';
        $csalt = '';
        for ($i = 0; $i < 8; $i++) {
          $csalt .= $vals[mt_rand(0,strlen($vals)-1)];
        }
        //$insertpw = sha1($_POST['pw']);
        $insertpw = md5(md5($csalt).md5($_POST['pw']));
        $currenttime = time();
        $ip = $_SERVER['REMOTE_ADDR'];
        dbquery("INSERT INTO users (username,pwhash,salt,powerlevel,joindate,email,regip) VALUES ('$insertun','$insertpw','$csalt',5,$currenttime,'$insertemail','$ip')");
        $userid = mysql_insert_id();
        
        // new user IRC reports go here: $userid, $_POST[un], $_POST[email], $ip
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with === not ==) the account has been created successfully
    // if it returns nothing, just show the form
  }

  function do_logout() {
    global $s;

    if ($s[logged_in] == 1) {
      session_destroy();
      $cookietime = time() - 3600;
      makecookie('logged_in', '', $cookietime);
      makecookie('username', '', $cookietime);
      makecookie('password', '', $cookietime);
      header('Location: index.php');
    }
  }
  
  function username_exists($un) {
    $checkit = dbquery("SELECT * FROM users WHERE username = '$un'");
    if (dbrows($checkit) != 0) {
      return true;
    }
    return false;
  }
?>
