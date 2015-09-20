<?php
  $renderthis = imageCreateFromPNG('icon_forumunread.png');
  
  if (isset($_GET['number']) && $_GET['number'] != '') {
    $numberpositions = array(0,6,10,16,22,28,34,40,46,52);
    $numbersizes = array(6,4,6,6,6,6,6,6,6,6);
    $numbers = imageCreateFromPNG('numbers.png');
    $getit = strval(intval($_GET['number']));
    $renderx = 19;
    for ($idx = strlen($getit) - 1; $idx >= 0; $idx--) {
      $thisone = intval($getit[$idx]);
      $renderx -= $numbersizes[$thisone] - 1;
      imageCopy($renderthis, $numbers, $renderx, 11, $numberpositions[$thisone], 0, $numbersizes[$thisone], 9);
    }
    imageDestroy($numbers);
  }
  
  header('Content-type: image/png');
  imagePNG($renderthis);
  imageDestroy($renderthis);
?>
