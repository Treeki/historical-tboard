<?php
  if (!defined('IN_TBB')) die();
  
  if ($s[logged_in] == 1) {
    print 'You already have an account.<br><a href=\'index.php\'>Return to the main page</a>';
  } else {
    // if it returns a non-blank string, it's an error
    // if it returns true (check with === not ==) the account has been created successfully
    // if it returns nothing, just show the form
    $result = do_register();
    if ($result === true) {
      print 'Your account has been created!<br>You can now log in with the username and password you entered.<br><a href=\'index.php?m=login\'>Return to the login page</a>';
    } else {
      if ($result != '') {
        print '<b>The following errors occurred while creating your account:<br>'.$result.'</b><hr>';
      }
?>
<form action='index.php?m=register' method='post'>
<b>Username:</b> <i>max. 30 characters</i><br>
<input type='text' size='30' maxlength='30' name='un' value='' class='textentry'><br>
<b>Password:</b> <i>min. 6 characters, max. 30 characters</i><br>
<input type='password' size='30' maxlength='30' name='pw' value='' class='textentry'><br>
<b>Retype your password:</b><br>
<input type='password' size='30' maxlength='30' name='retypepw' value='' class='textentry'><br>
<b>Email address:</b><br>
<input type='text' size='30' maxlength='80' name='email' value='' class='textentry'><br>
<input type='submit' name='reg' value='Register' class='button'>
</form>
<?php
    }
  }
?>