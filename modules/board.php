<?php
  if (!defined('IN_TBB')) die();
  
  $valid_actions = array('idx', 'forum', 'thread', 'newthread', 'postreply', 'editpost', 'mod', 'modpost', 'poll', 'addpoll');
  $action = $_GET['act'];
  if (!isset($_GET['act']) || $_GET['act'] == '') $action = 'idx';
  if (!in_array($action, $valid_actions)) $action = 'idx';
  
  require 'board/'.$action.'.php';
?>