<?php
  if (!defined('IN_TBB')) die();
  
  $pollid = $_GET['id'];
  if (!is_numeric($pollid)) {
    print "Invalid poll ID.<br><a href='index.php'>Return to the main page</a>";
  } else {
    $pollid = intval($pollid); // just to be safe
    $pollquery = dbquery("SELECT * FROM polls WHERE id = $pollid");
    
    if (mysql_num_rows($pollquery) == 0) {
      print "No poll with this ID exists.<br><a href='index.php'>Return to the main page</a>";
    } else {
      $pollinfo = dbrow($pollquery);
      if (isset($_GET['vote'])) {
        $checkit = dbquery("SELECT id FROM votes WHERE poll=$pollid AND voter={$s[user][userid]}");
        if (mysql_num_rows($checkit) == 0) {
          $choice = intval($_GET['vote']);
          if ($choice >= 0 && $choice < $pollinfo[choicecount]) {
            $voteinfo = explode('|', $pollinfo[voteinfo]);
            $voteinfo[$choice]++;
            $newvoteinfo = implode('|', $voteinfo);
            dbquery("UPDATE polls SET voteinfo='$newvoteinfo', votecount = votecount + 1 WHERE id = $pollid");
            $time = time();
            dbquery("INSERT INTO votes (poll,voter,choice,date) VALUES ($pollid,{$s[user][userid]},$choice,$time)");
          }
          header("Location: index.php?showthread=$pollinfo[thread]");
        }
      }
    }
  }
?>