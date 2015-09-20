<?php
  if (!defined('IN_TBB')) die();
  
  $valid_actions = array('list', 'add', 'edit', 'delete', 'order', 'updateorders');
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
  
  print "<b>View/Edit Forums</b>";
  print "<div class='smallspacing'></div>";
  
  switch ($action) {
    case 'list':
      $categories = array(0 => array('name' => 'Uncategorised', 'power' => 1, 'order' => -1));
      $getcategories = dbquery("SELECT * FROM categories ORDER BY `order`");
      while ($row = dbrow($getcategories)) {
        $row[forums] = array();
        $categories[$row[id]] = $row;
      }
      
      $getforums = dbquery("SELECT * FROM forums ORDER BY `order`");
      while ($row = dbrow($getforums)) {
        $categories[$row[category]][forums][$row[id]] = $row;
      }
      
      foreach ($categories as $cat) {
        print "<div class='bigspacing'></div>";
        print "Category: <b>$cat[name]</b> (Viewable by {$powerlevels[$cat[power]]} ($cat[power]) and up)";
        print "<div class='bigspacing'></div>";
        print "<table class='styled' style='width: 100%; max-width: 800px; margin: 0 auto' cellpadding='0' cellspacing='0'>";
        print "<tr class='header'><td style='width: 10%'>Order</td><td>Forum</td><td style='width: 23%'>Powerlevel</td><td style='width: 20%'>Controls</td></tr>";
        
        if (is_array($cat[forums]) && count($cat[forums]) > 0) {
          foreach ($cat[forums] as $row) {
            $controls = "<a href='index.php?m=admin&act=forums&do=edit&id=$row[id]'>Edit</a> - <a href='index.php?m=admin&act=forums&do=delete&id=$row[id]'>Delete</a>";
            if ($row[group] == 0) {
              $power = "{$powerlevels[$row[view_power]]} ($row[view_power])";
            } else {
              $power = "<i>Member Group $row[group]</i>";
            }
            print "<tr><td>$row[order]</td><td>$row[name]</td><td>$power</td><td>$controls</td></tr>";
          }
        } else {
          print "<tr><td colspan='4'>No forums in this category!</td></tr>";
        }
        
        print "</table>";
        print "<div class='hugespacing'></div>";
      }
?>
<div class='bigspacing'></div>
<a href='index.php?m=admin&act=forums&do=order'>Change Forum Ordering</a> - <a href='index.php?m=admin&act=forums&do=add'>Add New Forum</a>
<?php
      break;
    case 'edit':
      $id = intval($_GET['id']);
      $getforum = dbquery("SELECT * FROM forums WHERE id = $id");
      if (mysql_num_rows($getforum) == 0) {
        print "No forum exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=forums&do=list'>Return to editing forums</a>";
      } else {
        $forum = dbrow($getforum);
        print "Editing the forum $forum[name]:";
        print " (<a href='index.php?m=admin&act=forums&do=list'>Return to editing forums</a>)";
        
        $getcategories = dbquery("SELECT * FROM categories");
        $categories = array('0' => 'Uncategorised');
        while ($row = dbrow($getcategories)) {
          $categories[$row[id]] = $row[name];
        }
        
        // if it returns a non-blank string, it's an error
        // if it returns true (check with ===) the forum has been edited successfully
        // if it returns nothing, just show the form
        $result = edit_forum();
        if ($result === true) {
          header("Location: index.php?m=admin&act=forums&do=list");
        } else {
          if ($result != '') {
            print '<b>The following errors occurred while editing the forum:<br>'.$result.'</b><br>The data has been saved.<hr>';
          }
          if (!isset($_POST['makeit'])) {
            $_POST[name] = $forum[name];
            $_POST[desc] = $forum[desc];
            $_POST[category] = $forum[category];
            $_POST[order] = $forum[order];
            $_POST[view_power] = $forum[view_power];
            $_POST[reply_power] = $forum[reply_power];
            $_POST[thread_power] = $forum[thread_power];
            $_POST[mod_power] = $forum[mod_power];
            $_POST[group] = $forum[group];
          }
?>
<div class='bigspacing'></div>
<form action='index.php?m=admin&act=forums&do=edit&id=<?=$id;?>' method='post'>
<table style='margin: 0 auto; width: 100%'>
  <tr>
    <td align='left' style='width: 30%'><b>Forum Name:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='70' maxlength='120' name='name' value="<?=htmlspecialchars($_POST['name']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' valign='top'><b>Description:</b><br>HTML usable!</td>
    <td align='left'><textarea rows='4' cols='70' name='desc'><?=htmlspecialchars($_POST['desc']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left'><b>Category:</b></td>
    <td align='left'>
<?php
  $selected = intval($_POST['category']);
  print "<select name='category'>";
  foreach ($categories as $ckey => $disp) {
    print "<option value='$ckey'";
    if ($ckey == $selected) print " selected='selected'";
    print ">$ckey: $disp</option>";
  }
  print "</select>";
?>
    </td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Order:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='3' maxlength='3' name='order' value="<?=htmlspecialchars($_POST['order']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr><td colspan='2'><b>Required Powerlevels</b></td></tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To view the forum:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('view_power', $_POST['view_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To reply to threads:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('reply_power', $_POST['reply_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To create threads:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('thread_power', $_POST['thread_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To moderate here:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('mod_power', $_POST['mod_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>Member Group:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='3' maxlength='3' name='group' value="<?=htmlspecialchars($_POST['group']);?>" class='textentry'> (Leave blank or set to 0 for none)</td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Update Forum' class='button'></td>
  </tr>
</table>
</form>
<?php
        }
      }
      break;
    case 'add':
      print "Add a new forum:";
      print " (<a href='index.php?m=admin&act=forums&do=list'>Return to editing forums</a>)";
      
      $getcategories = dbquery("SELECT * FROM categories");
      $categories = array('0' => 'Uncategorised');
      while ($row = dbrow($getcategories)) {
        $categories[$row[id]] = $row[name];
      }
      
      // if it returns a non-blank string, it's an error
      // if it returns true (check with ===) the forum has been created successfully
      // if it returns nothing, just show the form
      $result = add_forum();
      if ($result === true) {
        header("Location: index.php?m=admin&act=forums&do=list");
      } else {
        if ($result != '') {
          print '<b>The following errors occurred while creating the forum:<br>'.$result.'</b><br>The data has been saved.<hr>';
        }
?>
<div class='bigspacing'></div>
<form action='index.php?m=admin&act=forums&do=add&id=<?=$id;?>' method='post'>
<table style='margin: 0 auto; width: 100%'>
  <tr>
    <td align='left' style='width: 30%'><b>Forum Name:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='70' maxlength='120' name='name' value="<?=htmlspecialchars($_POST['name']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' valign='top'><b>Description:</b><br>HTML usable!</td>
    <td align='left'><textarea rows='4' cols='70' name='desc'><?=htmlspecialchars($_POST['desc']);?></textarea></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left'><b>Category:</b></td>
    <td align='left'>
<?php
  $selected = intval($_POST['category']);
  print "<select name='category'>";
  foreach ($categories as $ckey => $disp) {
    print "<option value='$ckey'";
    if ($ckey == $selected) print " selected='selected'";
    print ">$ckey: $disp</option>";
  }
  print "</select>";
?>
    </td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>Order:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='3' maxlength='3' name='order' value="<?=htmlspecialchars($_POST['order']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr><td colspan='2'><b>Required Powerlevels</b></td></tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To view the forum:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('view_power', $_POST['view_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To reply to threads:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('reply_power', $_POST['reply_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To create threads:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('thread_power', $_POST['thread_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>...To moderate here:</b></td>
    <td align='left' style='width: 70%'><?php powerleveldropbox('mod_power', $_POST['mod_power']); ?></td>
  </tr>
  <tr>
    <td align='left' style='width: 30%'><b>Member Group:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='3' maxlength='3' name='group' value="<?=htmlspecialchars($_POST['group']);?>" class='textentry'> (Leave blank or set to 0 for none)</td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Update Forum' class='button'></td>
  </tr>
</table>
</form>
<?php
      }
      break;
    case 'delete':
      $id = intval($_GET['id']);
      $getforum = dbquery("SELECT * FROM forums WHERE id = $id");
      if (mysql_num_rows($getforum) == 0) {
        print "No forum exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=forums&do=list'>Return to editing forums</a>";
      } else {
        $forum = dbrow($getforum);
        if (isset($_POST['do_delete'])) {
          dbquery("DELETE FROM forums WHERE id = $id");
          header("Location: index.php?m=admin&act=forums&do=list");
        } else {
          print "Deleting Forum: $forum[name]:";
          print " (<a href='index.php?m=admin&act=forums&do=list'>Return to editing forums</a>)";
          print "<br><br>";
          print "Are you *sure* you want to delete this forum?<br>";
          print "You have no way to get it back. Even if you recreate it, threads and posts within it will be set to the old forum ID and therefore won't show up.<br>";
          
          print "<form action='index.php?m=admin&act=forums&do=delete&id=$id' method='post'>";
          print "<input type='submit' name='do_delete' class='button' value='Delete Forum'>";
          print "</form>";
        }
      }
      break;
    case 'order':
      print "Editing forum orders: (<a href='index.php?m=admin&act=forums&do=list'>Return to editing forums</a>)";
      //print "<div class='bigspacing'></div>";
      print "<form action='index.php?m=admin&act=forums&do=updateorders' method='post'>";
      
      $categories = array(0 => array('name' => 'Uncategorised', 'power' => 1, 'order' => -1));
      $getcategories = dbquery("SELECT * FROM categories ORDER BY `order`");
      while ($row = dbrow($getcategories)) {
        $row[forums] = array();
        $categories[$row[id]] = $row;
      }
      
      $getforums = dbquery("SELECT * FROM forums ORDER BY `order`");
      while ($row = dbrow($getforums)) {
        $categories[$row[category]][forums][$row[id]] = $row;
      }
      
      foreach ($categories as $cat) {
        print "<div class='bigspacing'></div>";
        print "Category: <b>$cat[name]</b>";
        print "<div class='bigspacing'></div>";
        print "<table class='styled' style='width: 100%; max-width: 800px; margin: 0 auto' cellpadding='0' cellspacing='0'>";
        print "<tr class='header'><td style='width: 10%'>Order</td><td>Forum</td></tr>";
        
        foreach ($cat[forums] as $row) {
          print "<tr><td><input type='text' size='3' maxlength='3' name='order[$row[id]]' value='$row[order]' class='textentry'></td><td>$row[name]</td></tr>";
        }
        
        print "</table>";
        print "<hr>";
      }
      
      print "<input type='submit' name='makeit' value='Update Forum Orders' class='button'>";
      print "</form>";
      break;
    case 'updateorders':
      if (isset($_POST['order']) && is_array($_POST['order']) && count($_POST['order']) > 0) {
        foreach ($_POST['order'] as $id => $order) {
          $iid = intval($id);
          $iorder = intval($order);
          dbquery("UPDATE forums SET `order` = $iorder WHERE id = $iid");
        }
        header('Location: index.php?m=admin&act=forums&do=list');
      }
      break;
  }
  
  function edit_forum() {
    global $s; // self note: not having this is why so many functions mess up
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      if (!isset($_POST['name']) or $_POST['name'] == '')
        $error_string .= 'You didn\'t enter a forum name.<br>';
      
      if ($error_string != '') {
        return $error_string;
      } else {
        global $id;
        $insertname = iprotect($_POST['name']);
        $insertdesc = iprotect($_POST['desc']);
        $category = intval($_POST['category']);
        $order = intval($_POST['order']);
        $view_power = intval($_POST['view_power']);
        $reply_power = intval($_POST['reply_power']);
        $thread_power = intval($_POST['thread_power']);
        $mod_power = intval($_POST['mod_power']);
        $group = intval($_POST['group']);
        dbquery("UPDATE forums SET name='$insertname', `desc`='$insertdesc', category=$category, `order`=$order, view_power=$view_power, reply_power=$reply_power, thread_power=$thread_power, mod_power=$mod_power, `group`=$group WHERE id = $id");
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the forum has been edited successfully
    // if it returns nothing, just show the form
  }
  
  function add_forum() {
    global $s; // self note: not having this is why so many functions mess up
    
    if (isset($_POST[makeit])) {
      $error_string = '';

      if (!isset($_POST['name']) or $_POST['name'] == '')
        $error_string .= 'You didn\'t enter a forum name.<br>';
      
      if ($error_string != '') {
        return $error_string;
      } else {
        $insertname = iprotect($_POST['name']);
        $insertdesc = iprotect($_POST['desc']);
        $category = intval($_POST['category']);
        $order = intval($_POST['order']);
        $view_power = intval($_POST['view_power']);
        $reply_power = intval($_POST['reply_power']);
        $thread_power = intval($_POST['thread_power']);
        $mod_power = intval($_POST['mod_power']);
        $group = intval($_POST['group']);
        dbquery("INSERT INTO forums (name,`desc`,category,`order`,view_power,reply_power,thread_power,mod_power,`group`) VALUES ('$insertname','$insertdesc',$category,$order,$view_power,$reply_power,$thread_power,$mod_power,$group)");
        return true;
      }
    }
    
    // if it returns a non-blank string, it's an error
    // if it returns true (check with ===) the forum has been created successfully
    // if it returns nothing, just show the form
  }
?>