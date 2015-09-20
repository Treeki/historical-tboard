<?php
  if (!defined('IN_TBB')) die();
  
  if ($s[logged_in] == 1) {
    do_logout();
  } else {
    print 'You\'re not logged in.<br><a href=\'index.php\'>Return to the main page</a>';
  }
?>