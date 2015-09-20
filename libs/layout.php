<?php if (!defined('IN_TBB')) die(); ?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <title>Treeki's Dev Board</title>
    <meta http-equiv='Content-Type' content='text/html;charset=utf-8' >
    <link href='favicon.png' type='image/png' rel='icon'>
    <link href='<?=$theme;?>style.css' type='text/css' rel='stylesheet'>
  </head>
  <body><div id='container'>
    <span style='color: #3aa0ff; margin: 6px 0px 2px 0px; display: block; font-size: 24px; font-weight: bold'>Treeki's Dev Board</span>
    <span style='color: #3aa0ff'>&lt;insert tagline here&gt;</span>
    <br>
    <div style='margin: 4px 0px; border-top: 1px solid #aad6ff; padding-top: 4px'>
      <a href='index.php'>Home</a>
      &middot; <a href='index.php?m=board'>Forum</a>
      &middot; <a href='index.php?m=users'>Member List</a>
      <?php if (!$s[logged_in]) { ?>&middot; <a href='index.php?m=login'>Log in</a><?php } ?>
      <?php if (!$s[logged_in]) { ?>&middot; <a href='index.php?m=register'>Register</a><?php } ?>
      <?php if ($s[logged_in]) { ?>&middot; <a href='index.php?m=messages'>Messages</a><?php } ?>
      <?php if ($s[logged_in]) { ?>&middot; <a href='index.php?m=usercp'>User CP</a><?php } ?>
      <?php if ($s[logged_in] && $s[user][powerlevel] >= $admincp_req) { ?>&middot; <a href='index.php?m=admin'>Admin</a><?php } ?>
      <?php if ($s[logged_in]) { ?>&middot; <a href='index.php?m=logout'>Log out</a><?php } ?>
      </div><div style='margin: 4px 0px; border-bottom: 1px solid #aad6ff; padding-bottom: 4px'><?php
      if ($s[logged_in]) {
        $userlink = userlink($s[user][userid], htmlspecialchars($s[user][username]), $s[user][powerlevel]);
        $get_unread_pms = dbquery("SELECT count(id) FROM pmessages WHERE recipient = {$s[user][userid]} AND exists_recipient = 1 AND pmread = 0");
        $getit = dbrow($get_unread_pms);
        $unread = $getit['count(id)'];
        $get_total_pms = dbquery("SELECT count(id) FROM pmessages WHERE recipient = {$s[user][userid]} AND exists_recipient = 1");
        $getit = dbrow($get_total_pms);
        $total = $getit['count(id)'];
        $uplural = ($unread == 1) ? '' : 's';
        $tplural = ($total == 1) ? '' : 's';
        if ($unread == 0) {
          $pmmessage = "You have no unread <a href='index.php?m=messages'>messages</a>; $total total.";
        } else {
          $pmmessage = "You have $unread unread <a href='index.php?m=messages'>message$uplural</a>; $total total.";
        }
        print "<b>You are currently logged in as $userlink.</b> $pmmessage";
      } else {
        print "<b>You are currently not logged in.</b>";
      }
      ?>
      </div><div style='margin: 4px 0px; border-bottom: 1px solid #aad6ff; padding-bottom: 4px'><?php
      print "<b>Online Users:</b> ";
      $timelimit = time() - 300;
      $getpeople = dbquery("SELECT userid,username,powerlevel FROM users WHERE lastactive > $timelimit");
      if (dbrows($getpeople) == 0) {
        print "Nobody.";
      } else {
        $comma = false;
        while ($row = dbrow($getpeople)) {
          if ($comma) print ', '; else $comma = true;
          print userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
        }
      }
      
      $bddate = date('d-m-%');
      $getpeople = dbquery("SELECT userid,username,powerlevel FROM users WHERE birthday LIKE '$bddate'");
      if (dbrows($getpeople) != 0) {
        print " &middot; <b>Today's Birthdays:</b> ";
        $comma = false;
        while ($row = dbrow($getpeople)) {
          if ($comma) print ', '; else $comma = true;
          print userlink($row[userid], htmlspecialchars($row[username]), $row[powerlevel]);
        }
      }
      
      ?>
    </div>
    <?php echo $page; ?>
    <div style='font-style: italic; color: #555; margin: 4px 0px; border-top: 1px solid #aad6ff; padding-top: 4px; text-align: right;'>perpetually unfinished, totally custom board software - &copy; 2009-2010 Treeki</div>
    <!-- random debug info: render time: <?php echo gettimeofday(true) - $start_time; ?> - mysql query count: <?php echo $query_count; ?> -->
  </div></body>
</html>
