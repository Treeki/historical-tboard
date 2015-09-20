<?php
  if (!defined('IN_TBB')) die();
  
  function dbquery($query) {
    global $query_count;
    $query_count++;
    return mysql_query($query);
  }
  
  function dbrow($result) {
    return mysql_fetch_assoc($result);
  }
  
  function dbrows($result) {
    return mysql_num_rows($result);
  }
  
  // add slashes to POST/GET'd values if needed
  function iprotect($str) {
    //if (get_magic_quotes_gpc()) return $str; else return addslashes($str);
    return mysql_real_escape_string($str);
  }
  
  //function iunprotect($str) {
    //if (get_magic_quotes_gpc()) return stripslashes($str); else return $str;
  //}
  
  function makecookie($name, $value, $time) {
    setcookie($name, $value, $time, $cookieurl, $cookiedomain, false, true);
  }
  
  function parsedate($date) {
    return date('g:i:s a, d M o', $date);
  }
  
  function parsedate_short($date) {
    return date('d M o', $date);
  }
  
  $namecolours = array(
    0 => '999999',
    5 => '248900',
    15 => '0057ae',
    20 => '05adad',
    50 => '410091',
    );
  
  function format_name($name, $power) {
    global $namecolours;
    $estyle = '';
    if (isset($namecolours[$power])) {
        $estyle = 'color: #'.$namecolours[$power];
    }

    if ($power == 0) { $name = "<s>$name</s>"; }
    if ($power >= 10) { $name = "<b>$name</b>"; }
    return array($name, $estyle);
  }
  
  function userlink($id, $name, $power) {
    $fname = format_name($name, $power);
    return "<a href='index.php?showuser=$id' class='userlink' style='$fname[1]'>$fname[0]</a>";
  }
  
  function userlink_big($id, $name, $power) {
    $fname = format_name($name, $power);
    return "<a href='index.php?showuser=$id' class='userlink' style='font-size: 12px;$fname[1]'>$fname[0]</a>";
  }
  
  function replink($id, $rep) {
    if ($rep > 0) {
      return "<a href='index.php?m=reputation&id=$id' style='color: #248900'>+$rep</a>";
    } elseif ($rep == 0) {
      return "<a href='index.php?m=reputation&id=$id' style='color: #565656'>$rep</a>";
    } elseif ($rep < 0) {
      return "<a href='index.php?m=reputation&id=$id' style='color: #af3333'>$rep</a>";
    }
  }
  
  function pagination($pagecount, $pagenum, $url) {
    if ($pagenum == 1) {
      echo "<span style='color: #666'>&laquo; Previous</span> &middot; ";
    } else {
      echo "<a href='$url&page=".($pagenum-1)."'>&laquo; Previous</a> &middot; ";
    }
    if ($pagecount == 0) echo "None?!";
    $docomma = false;
    for ($gothroughpages = 1; $gothroughpages <= $pagecount; $gothroughpages++) {
      if ($docomma) echo ", "; else $docomma = true;
      if ($gothroughpages == $pagenum) {
        echo "<b>$gothroughpages</b>";
      } else {
        echo "<a href='$url&page=$gothroughpages'>$gothroughpages</a>";
      }
    }
    if ($pagenum >= $pagecount) {
      echo " &middot; <span style='color: #666'>Next &raquo;</span>";
    } else {
      echo " &middot; <a href='$url&page=".($pagenum+1)."'>Next &raquo;</a>";
    }
  }
  
  function getpost($post, $bbcode, $smileys, $html) {
    $procpost = $post;
    if (!$html) $procpost = htmlentities($procpost, ENT_QUOTES, 'UTF-8');
    $procpost = nl2br($procpost);
    if ($smileys) $procpost = makesmileys($procpost);
    if ($bbcode) $procpost = parsebbcode($procpost);
    return $procpost;
  }
  
$smilies = array();
$smilies[':)'] = 'smile.png';
  
  function makesmileys($text) {
    global $smilies;
    foreach ($smilies as $r => $t) {
      $text = str_replace($r, "<img src='smilies/$t'>", $text);
    }
    return $text;
  }
  
  function parsebbcode($text) {
    $bbsearch = array('/\[b](.*?)\[\/b]/s',
                    '/\[i](.*?)\[\/i]/s',
                    '/\[u](.*?)\[\/u]/s',
                    '/\[s](.*?)\[\/s]/s',
                    '/\[url](.*?)\[\/url]/s',
                    '/\[url=(.*?)](.*?)\[\/url]/s',
                    '/\[img=(.*?)](.*?)\[\/img]/',
                    '/\[img](.*?)\[\/img]/i',
                    '/\[spoiler](.*?)\[\/spoiler]/s',
                    '/\[spoiler=(.*?)](.*?)\[\/spoiler]/s',
//                      '/\[quote](.*?)\[\/quote]/s',
//                      '/\[quote=(.*?)](.*?)\[\/quote]/s',
                    '/\[code](.*?)\[\/code]/s',
                    '/\[code=(.*?)](.*?)\[\/code]/s',
                    '/\[pre](.*?)\[\/pre]/s',
                    '/\[center](.*?)\[\/center]/s',
                    '/\[align=(.*?)](.*?)\[\/align]/s',
                    '/\[color=(.*?)](.*?)\[\/color]/s',
                    '/\[font=(.*?)](.*?)\[\/font]/s',
                    '/\[size=(xx-small|x-small|small|medium|large|x-large|xx-large)\](.*?)\[\/size\]/s',
                    '/\[size=(.*?)](.*?)\[\/size]/s',
                    '/\[hr]/',
                    );
    $bbreplace = array('<b>$1</b>',
                    '<i>$1</i>',
                    '<u>$1</u>',
                    '<s>$1</s>',
                    '<a href=\'$1\'>$1</a>',
                    '<a href=\'$1\'>$2</a>',
                    '<img src=\'$1\' alt="$2" title="$2" />',
                    '<img src=\'$1\' alt="User posted image" />',
                    '<table class=\'bq\'><tr><td class=\'sheader\'><b>Spoiler:</b> <i>(highlight to read)</i></td></tr><tr><td class=\'spoilercell\'>$1</td></tr></table>',
                    '<table class=\'bq\'><tr><td class=\'sheader\'><b>Spoiler about $1:</b> <i>(highlight to read)</i></td></tr><tr><td class=\'spoilercell\'>$2</td></tr></table>',
//                       '<table class=\'bq\'><tr><td class=\'sheader\'><b>Quote:</b></td></tr><tr><td>$1</td></tr></table>',
//                       '<table class=\'bq\'><tr><td class=\'sheader\'><b>Quote:</b> <i>($1)</i></td></tr><tr><td>$2</td></tr></table>',
                    '<table class=\'bq\'><tr><td class=\'sheader\'><b>Code:</b></td></tr><tr><td><pre style=\'margin: 2px\'>$1</pre></td></tr></table>',
                    '<table class=\'bq\'><tr><td class=\'sheader\'><b>Code:</b> <i>($1)</i></td></tr><tr><td><pre style=\'margin: 2px\'>$2</pre></td></tr></table>',
                    '<pre>$1</pre>',
                    '<div align=\'center\'>$1</div>',
                    '<div align=\'$1\'>$2</div>',
                    '<font color=\'$1\'>$2</font>',
                    '<font face=\'$1\'>$2</font>',
                    '<span style=\'font-size: $1\'>$2</span>',
                    '<font size=\'$1\'>$2</font>',
                    '<div class=\'separator\'><hr></div>'
                    );
    return str_replace('[BBCODE_OVERRIDE]', '[', preg_replace($bbsearch, $bbreplace, parse_quotes(parse_list($text))));
  }
  
  function parse_quotes($text) {
    for (;;) {
      $qpos = strrpos($text, '[quote');
      if ($qpos === false) break;
      $qepos = strpos($text, '[/quote]', $qpos);
      if ($qepos === false) break;
      if ($text[$qpos+6] == '=') {
        $text = substr_replace($text, '<table class=\'bq\'><tr><td class=\'sheader\'><b>Quote:</b> <i>(', $qpos, 7);
        $paramendpos = strpos($text, ']', $qpos);
        $text = substr_replace($text, ')</i></td></tr><tr><td>', $paramendpos, 1);
      } else {
        $text = substr_replace($text, '<table class=\'bq\'><tr><td class=\'sheader\'><b>Quote:</b></td></tr><tr><td>', $qpos, 7);
      }
      $qepos = strpos($text, '[/quote]', $qpos); // get the position again since it must have changed
      $text = substr_replace($text, '</td></tr></table>', $qepos, 8);
    }
    return $text;
  }
  
  function parse_list($text) {
    for (;;) {
      $qpos = strrpos($text, '[list]');
      if ($qpos === false) break;
      $qepos = strpos($text, '[/list]', $qpos);
      if ($qepos === false) break;
      $getlist = substr($text, $qpos + 6, $qepos - $qpos - 6);
      $text = substr_replace($text, '<ul>', $qpos, 6); $qpos -= 2; $qepos -= 2;
      $text = substr_replace($text, '</ul>', $qepos, 7);
      $text = substr_replace($text, '<li>'.str_replace('[*]', '</li><li>', $getlist).'</li>', $qpos + 6, strlen($getlist));
      $text = str_replace('<ul><li></li>', '<ul>', $text);
    }
    return $text;
  }
  
  function post_toolbar() {
?>
<script type='text/javascript'>
  function tagtext(tag) {
    var a = document.getElementById('typehere');
    if (a.selectionStart == a.selectionEnd) {
      alert("You must select some text to format.");
    } else {
      var newtext = a.value.substring(0,a.selectionStart);
      newtext += "[" + tag + "]";
      newtext += a.value.substring(a.selectionStart, a.selectionEnd);
      newtext += "[/" + tag + "]";
      newtext += a.value.substring(a.selectionEnd);
      a.value = newtext;
    }
  }
  
  function makelink() {
    var a = document.getElementById('typehere');
    if (a.selectionStart == a.selectionEnd) {
      alert("You must select some text to make into a link.");
    } else {
      var link = prompt("Enter the URL for the link you want to use.");
      if (link != null && link != "") {
        var newtext = a.value.substring(0,a.selectionStart);
        newtext += "[url=" + link + "]";
        newtext += a.value.substring(a.selectionStart, a.selectionEnd);
        newtext += "[/url]";
        newtext += a.value.substring(a.selectionEnd);
        a.value = newtext;
      }
    }
  }
</script>
<input type='button' value='Bold' onClick='tagtext("b")' class='button'>
<input type='button' value='Italic' onClick='tagtext("i")' class='button'>
<input type='button' value='Underline' onClick='tagtext("u")' class='button'>
<input type='button' value='Strikethrough' onClick='tagtext("s")' class='button'>
<input type='button' value='Link' onClick='makelink()' class='button'>
<br><?php
  }
  
  function display_post($userdata,$datetype,$postdate,$cmds,$posttext) {
    global $powerlevels;
    
    $author = userlink_big($userdata[userid], htmlspecialchars($userdata[username]), $userdata[powerlevel]);
    $postdate = parsedate($postdate);
    $joindate = parsedate_short($userdata[joindate]);
    $sig = '';
    if ($userdata['signature'] != '') {
      $sig = '<hr>';
      $sig .= getpost($userdata['signature'],true,true,false);
    }
    $avatar = '';
    if ($userdata['hasavatar'] == 1) {
      $avatar = "<img src='avatars/$userdata[userid].$userdata[avatarext]' alt='Avatar'><br>";
    }
    $utitle = htmlspecialchars($userdata[usertitle]);
    print "<table class='post' width='100%'>";
    print "<tr>";
    print "<td rowspan='2' valign='top' class='postsidebar'>";
    print "<b>$author</b>";
    if ($utitle) print "<div class='smallspacing'></div>$utitle";
    if ($avatar) {
      print "<div class='bigspacing'></div>";
      print "$avatar";
    }
    print "<div class='bigspacing'></div>";
    print "<span class='label'>Posts:</span> $userdata[posts]<br>";
    print "<span class='label'>Joined:</span> $joindate<br>";
    if ($userdata[powerlevel] > 5) {
      print "<span class='label'>Rank:</span> {$powerlevels[$userdata[powerlevel]]}<br>";
    }
    $replink = replink($userdata[userid], $userdata[reputation]);
    print "<span class='label'>Reputation:</span> $replink<br>";
    // custom postbit fields go here
    print "</td>";
    print "<td class='postdate' height='1'>$datetype $postdate$cmds</td>";
    print "</tr>";
    print "<tr><td valign='top' class='postcontent'>$posttext$sig</td></tr>";
    print "</table>";
  }
  
  // custom postbit fields go here, too
  define('postbox_query_fields', ',users.userid,users.username,users.powerlevel,users.posts,users.usertitle,users.joindate,users.reputation,users.hasavatar,users.avatarext,users.signature');
  define('postbox_query_fields_alone', 'userid,username,powerlevel,posts,usertitle,joindate,reputation,hasavatar,avatarext,signature');
  
  function can_view_forum($foruminfo) {
    global $s;
    if ($foruminfo[view_power] > $s[user][powerlevel]) return false;
    if ($foruminfo[mod_power] <= $s[user][powerlevel]) return true;
    if ($foruminfo[group] == 0) return true;
    if (!in_array($foruminfo[group], $s[user][groups])) return false;
    
    return true;
  }
?>
