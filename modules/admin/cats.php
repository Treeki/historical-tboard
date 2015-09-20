<?php
  if (!defined('IN_TBB')) die();
  
  $valid_actions = array('list', 'add', 'edit', 'update', 'delete', 'order', 'updateorders');
  $action = $_GET['do'];
  if (!isset($_GET['do']) || $_GET['do'] == '') $action = 'list';
  
  function powerleveldropbox($selected=null) {
    global $powerlevels;
    print "<select name='power'>";
    foreach ($powerlevels as $ckey => $disp) {
      print "<option value='$ckey'";
      if ($ckey == $selected) print " selected='selected'";
      print ">$ckey: $disp</option>";
    }
    print "</select>";
  }
  
  print "<b>View/Edit Categories</b>";
  print "<div class='smallspacing'></div>";
  
  switch ($action) {
    case 'list':
      print "<table class='styled' style='width: 100%; max-width: 800px; margin: 0 auto' cellpadding='0' cellspacing='0'>";
      print "<tr class='header'><td style='width: 10%'>Order</td><td>Category</td><td style='width: 23%'>Powerlevel</td><td style='width: 20%'>Controls</td></tr>";
      
      $getcategories = dbquery("SELECT * FROM categories ORDER BY `order`");
      while ($row = dbrow($getcategories)) {
        $controls = "<a href='index.php?m=admin&act=cats&do=edit&id=$row[id]'>Edit</a> - ";
        $controls .= "<a href='#' onClick='if (confirm(\"Are you sure you want to delete this category?\") == true) { window.location = \"index.php?m=admin&act=cats&do=delete&id=$row[id]\"; }'>Delete</a>";
        
        print "<tr><td>$row[order]</td><td>$row[name]</td><td>{$powerlevels[$row[power]]} ($row[power])</td><td>$controls</td></tr>";
      }
      
      print "</table>";
?>
<div class='bigspacing'></div>
<a href='index.php?m=admin&act=cats&do=order'>Change Category Ordering</a>
<hr>
<b>Add Category:</b>
<br>
<form action='index.php?m=admin&act=cats&do=add' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>Category Name:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='60' maxlength='60' name='name' class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
    <td align='left' colspan='2'>
      <b>Required Powerlevel to View:</b>&nbsp;&nbsp;&nbsp;<?php powerleveldropbox(); ?>
      &nbsp;&nbsp;&nbsp;
      <b>Order:</b>&nbsp;&nbsp;&nbsp;<input type='text' size='3' maxlength='3' name='order' class='textentry'>
    </td>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Add Category' class='button'></td>
  </tr>
</table>
</form>
<?php
      break;
    case 'edit':
      $id = intval($_GET['id']);
      $getcat = dbquery("SELECT * FROM categories WHERE id = $id");
      if (mysql_num_rows($getcat) == 0) {
        print "No category exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=cats&do=list'>Return to editing categories</a>";
      } else {
        $cat = dbrow($getcat);
        print "Editing the category $cat[name]:";
        print " (<a href='index.php?m=admin&act=cats&do=list'>Return to editing categories</a>)";
?>
<div class='bigspacing'></div>
<form action='index.php?m=admin&act=cats&do=update&id=<?=$id;?>' method='post'>
<table style='margin: 0 auto; width: 80%'>
  <tr>
    <td align='left' style='width: 30%'><b>Category Name:</b></td>
    <td align='left' style='width: 70%'><input type='text' size='60' maxlength='60' name='name' value="<?=htmlspecialchars($cat['name']);?>" class='textentry'></td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td align='left' colspan='2'>
      <b>Required Powerlevel to View:</b>&nbsp;&nbsp;&nbsp;<?php powerleveldropbox($cat['power']); ?>
      &nbsp;&nbsp;&nbsp;
      <b>Order:</b>&nbsp;&nbsp;&nbsp;<input type='text' size='3' maxlength='3' name='order' value="<?=$cat['order'];?>" class='textentry'>
    </td>
  </tr>
  <tr class='separator'><td colspan='2'><hr></td></tr>
  <tr>
    <td colspan='2'><input type='submit' name='makeit' value='Update Category' class='button'></td>
  </tr>
</table>
</form>
<?php
      }
      
      break;
    case 'add':
      if ($_POST['name'] != '') {
        $name = iprotect($_POST['name']);
        $power = intval($_POST['power']);
        $order = intval($_POST['order']);
        dbquery("INSERT INTO categories (name,power,`order`) VALUES ('$name',$power,$order)");
      }
      header('Location: index.php?m=admin&act=cats&do=list');
      break;
    case 'update':
      $id = intval($_GET['id']);
      $getcat = dbquery("SELECT * FROM categories WHERE id = $id");
      if (mysql_num_rows($getcat) == 0) {
        print "No category exists with this ID.<br>";
        print "<a href='index.php?m=admin&act=cats&do=list'>Return to editing categories</a>";
      } else {
        if ($_POST['name'] != '') {
          $name = iprotect($_POST['name']);
          $power = intval($_POST['power']);
          $order = intval($_POST['order']);
          dbquery("UPDATE categories SET name='$name', power=$power, `order`=$order WHERE id=$id");
        }
        header('Location: index.php?m=admin&act=cats&do=list');
      }
      break;
    case 'delete':
      $id = intval($_GET['id']);
      dbquery("DELETE FROM categories WHERE id=$id");
      header('Location: index.php?m=admin&act=cats&do=list');
      break;
    case 'order':
      print "Editing category orders: (<a href='index.php?m=admin&act=cats&do=list'>Return to editing categories</a>)";
      print "<div class='bigspacing'></div>";
      print "<form action='index.php?m=admin&act=cats&do=updateorders' method='post'>";
      
      print "<table class='styled' style='width: 100%; max-width: 800px; margin: 0 auto' cellpadding='0' cellspacing='0'>";
      print "<tr class='header'><td style='width: 15%'>Order</td><td>Category</td></tr>";
      
      $getcategories = dbquery("SELECT * FROM categories ORDER BY `order`");
      while ($row = dbrow($getcategories)) {
        print "<tr><td><input type='text' size='3' maxlength='3' name='order[$row[id]]' value='$row[order]' class='textentry'></td><td>$row[name]</td></tr>";
      }
      
      print "</table>";
      print "<input type='submit' name='makeit' value='Update Category Orders' class='button'>";
      print "</form>";
      break;
    case 'updateorders':
      if (isset($_POST['order']) && is_array($_POST['order']) && count($_POST['order']) > 0) {
        foreach ($_POST['order'] as $id => $order) {
          $iid = intval($id);
          $iorder = intval($order);
          dbquery("UPDATE categories SET `order` = $iorder WHERE id = $iid");
        }
        header('Location: index.php?m=admin&act=cats&do=list');
      }
      break;
  }
?>