<?php
  if (!defined('IN_TBB')) die();
?>
<table style='width: 100%; margin: 0'>
  <tr>
    <td colspan='2'>
      Welcome to Treeki's development board!<br>
      This is just for testing Treeki's custom forum software which will most likely never be finished.
    </td>
  </tr>
  <tr>
    <td style='width: 70%' valign='top'>
<?php
  if ($portalforum > -1) {
    $getnews = dbquery("SELECT * FROM threads WHERE forum = $portalforum ORDER BY id DESC LIMIT 5");
    while ($thread = dbrow($getnews)) {
      $thread[name] = htmlspecialchars($thread[name]);
      $threadid = $thread[id];
      $post = dbrow(dbquery("SELECT posts.id,posts.postdate,posts.posttext,users.userid,users.username,users.powerlevel FROM posts LEFT JOIN users ON posts.authorid=users.userid WHERE thread = $threadid ORDER BY postdate LIMIT 1"));
      $author = userlink($post[userid], htmlspecialchars($post[username]), $post[powerlevel]);
      $postdate = parsedate_short($post[postdate]);
      print "<div class='portalheader'><b>$thread[name]</b> (<i>$postdate by $author</i>)</div>";
      $posttext = getpost($post[posttext],true,true,false);
      print "<div class='portalbox'>";
      print $posttext;
      print "<div style='text-align: center; padding: 2px; margin: 2px 0px; border-top: 1px solid #aad6ff'><a href='index.php?showthread=$threadid'>view original thread</a> ($thread[replies] replies)</div>";
      print "</div>";
    }
  }
?>
    </td>
    <td style='width: 30%' valign='top'>
      <div class='portalheader'>Unused Box</div>
      <div class='portalbox' style='text-align: center'>
        Stuff can go here if you want. Edit modules/main.php.
      </div>
<?php
  $groupbit = '';
  if ($s[user][groups_raw] != 0) {
    $groupbit = " OR forums.group IN ({$s[user][groups_raw]})";
  }
  $getcanview = dbquery("SELECT forums.id,forumread.lastread FROM forums LEFT JOIN forumread ON forums.id=forumread.forum AND forumread.user={$s[user][userid]} WHERE (forums.view_power <= {$s[user][powerlevel]} AND forums.group = 0) OR forums.mod_power <= {$s[user][powerlevel]}$groupbit");
  $canview_arr = array();
  $lastread = array();
  while ($row = dbrow($getcanview)) {
    $canview_arr[] = $row[id];
    $lastread[$row[id]] = intval($row[lastread]);
  }
  $canview = implode(',',$canview_arr);
  
  $eachone = array(
    array('Newest Threads', 'ORDER BY threads.id DESC LIMIT 5', '', 'author'),
    array('Latest Posts', 'ORDER BY threads.lastpostdate DESC LIMIT 5', '&page=last', 'lastposter')
  );
  foreach ($eachone as $doit) {
?>
      <div class='portalheader'><?=$doit[0];?></div>
      <div class='portalbox'>
<?php
    $getthreads = dbquery("SELECT threads.id, threads.name, threads.{$doit[3]}id, threads.forum, threads.lastpostdate, authorusers.username as authorname, authorusers.powerlevel as authorpower, threadread.thread FROM threads LEFT JOIN users as authorusers ON threads.{$doit[3]}id=authorusers.userid LEFT JOIN threadread ON threads.id=threadread.thread AND threadread.user={$s[user][userid]} WHERE threads.forum IN ($canview) $doit[1]");
    while ($row = dbrow($getthreads)) {
      $row[name] = htmlspecialchars($row[name]);
      $author = userlink($row[$doit[3].'id'], htmlspecialchars($row[authorname]), $row[authorpower]);
      
      $unread = '';
      if ($s[logged_in] && $row[lastpostdate] > $lastread[$row[forum]] && $row[id] != $row[thread]) {
        $icon = "<img src='{$theme}images/icon_unreadtiny.png' alt='This thread has unread posts.' title='This thread has unread posts.'>";
      } else {
        $icon = "<img src='{$theme}images/icon_tiny.png' alt='This thread has no unread posts.' title='This thread has no unread posts.'>";
      }
      
      print "$icon <a href='index.php?showthread=$row[id]$doit[2]'>$row[name]</a> by $author<br>";
    }
?>
      </div>
<?php
  }
?>
      <div class='portalheader'>Board Statistics</div>
      <div class='portalbox'>
<?php
  $getcounts = dbquery("SELECT count(userid) FROM users");
  $getit = dbrow($getcounts);
  $users = $getit['count(userid)'];
  $getcounts = dbquery("SELECT count(id) FROM threads");
  $getit = dbrow($getcounts);
  $threads = $getit['count(id)'];
  $getcounts = dbquery("SELECT count(id) FROM posts");
  $getit = dbrow($getcounts);
  $posts = $getit['count(id)'];
  print "This board has $users members, who have made $threads threads and $posts posts.";
?>
      </div>
    </td>
  </tr>
</table>
