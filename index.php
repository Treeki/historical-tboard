<?php
  date_default_timezone_set('UTC');
  error_reporting(E_ALL & ~E_NOTICE);
  define('IN_TBB', 1);
  define('PassEncodeKey', '¿ëmxW¬ÎÝ©•Or$:0\ÿûÂ÷¼¤§R}>86ŒŽ°«');
  $s = array();
  
  // this is just for interest
  $start_time = gettimeofday(true);
  $query_count = 0;
  
  // strip everything if magic_quotes_gpc is used. no it's not supposed to sound so wrong.
  if (get_magic_quotes_gpc()) {
    foreach ($_GET as $stripit => $value) $_GET[$stripit] = stripslashes($value);
    foreach ($_POST as $stripit => $value) $_POST[$stripit] = stripslashes($value);
    foreach ($_COOKIE as $stripit => $value) $_COOKIE[$stripit] = stripslashes($value);
  }
  
  // include required libraries
  include 'libs/function.php';
  include 'libs/auth.php';
  
  // config variables
  $sql_host = 'localhost';
  $sql_user = 'xxx';
  $sql_pass = 'xxx';
  $sql_db = 'tb2010';
  
  $cookieurl = '/tb2010/';
  $cookiedomain = 'localhost';
  
  $threadspp = 15;
  $postspp = 15;
  
  // forum ID to show news posts from in the portal
  // to disable them, set it to -1
  $portalforum = 1;
  
  $admincp_req = 20;
  
  $theme = 'themes/default/';
  
  $powerlevels = array(
    0 => 'Banned',
    1 => 'Guest',
    5 => 'Member',
    15 => 'Moderator',
    20 => 'Administrator',
    50 => 'Owner'
    );
  
  $guest_user = array(
    'userid' => 0,
    'username' => 'Guest',
    'powerlevel' => '1'
    );
  
  $avatarpath = '/data/www/tb2010/avatars/';
  
  // connect to SQL
  function dump_sql_error() {
    print '<html><head><style type=\'text/css\'>body { font-family: Arial, sans-serif; text-align: center; }</style></head><body><b>Error occurred while connecting to the database:</b><br />'.mysql_error().'<br /><br />We hope to have it fixed shortly.</body></html>';
    exit();
  }
  
  $sql_conn = @mysql_connect($sql_host, $sql_user, $sql_pass) or dump_sql_error();
  @mysql_select_db($sql_db) or dump_sql_error();
  
  session_start();
  ob_start();
  
  // authenticate the user
  do_auth();
  
  $s[user][groups_raw] = $s[user][groups];
  $s[user][groups] = explode(',', $s[user][groups]);
  
  if ($s[user][powerlevel] == 0) {
    print '<html><head><style type=\'text/css\'>body { font-family: Arial, sans-serif; text-align: center; }</style></head><body>Your account is banned. :)</body></html>';
    ob_end_flush();
    exit();
  }
  
  // update last activity
  if ($s[logged_in]) {
    $currenttime = time();
    $ip = $_SERVER['REMOTE_ADDR'];
    dbquery("UPDATE users SET lastactive = $currenttime, lastip = '$ip' WHERE userid = {$s[user][userid]}");
  }
  
  // quick shortcuts
  if (isset($_GET['showuser']) && $_GET['showuser'] != '') {
    $_GET['m'] = 'users';
    $_GET['act'] = 'profile';
    $_GET['id'] = $_GET['showuser'];
  }
  
  if (isset($_GET['showforum']) && $_GET['showforum'] != '') {
    $_GET['m'] = 'board';
    $_GET['act'] = 'forum';
    $_GET['id'] = $_GET['showforum'];
  }
  
  if (isset($_GET['showthread']) && $_GET['showthread'] != '') {
    $_GET['m'] = 'board';
    $_GET['act'] = 'thread';
    $_GET['id'] = $_GET['showthread'];
  }
  
  $valid_modules = array('main', 'board', 'login', 'logout', 'register', 'users', 'messages', 'usercp', 'bbcode_help', 'admin', 'reputation');
  $module = $_GET['m'];
  if (!isset($_GET['m']) || $_GET['m'] == '') $module = 'main';
  if (!in_array($module, $valid_modules)) $module = 'main';
  
  require 'modules/'.$module.'.php';
  
  // render the page
  $page = ob_get_clean();
  require 'libs/layout.php';
  
  // final cleanup
  mysql_close($sql_conn);
?>
