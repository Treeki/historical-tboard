<?php
  if (!defined('IN_TBB')) die();
  
  if ($s[logged_in] == 1) {
    print 'You\'re already logged in.<br><a href=\'index.php\'>Return to the main page</a>';
  } else {
    // if it returns a non-blank string, it's an error
    // if it returns true (check with === not ==) you've logged in successfully
    // if it returns nothing, just show the form
    $result = do_login();
    
    if ($result === true) {
      header('Location: index.php');
    } else {
      if ($result != '') {
        print '<b>'.$result.'</b><hr>';
      }
?>
<form action='index.php?m=login' method='post'>
<b>Username:</b>
<input type='text' size='30' maxlength='30' name='login_un' value='' class='textentry'><br>
<b>Password:</b>
<input type='password' size='30' maxlength='30' name='login_pw' value='' class='textentry'><br>
<input type='submit' name='login_submit' value='Log in' class='button'>
</form>
<?php
    }
  }
?>
