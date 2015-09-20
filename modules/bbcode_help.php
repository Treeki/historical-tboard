<?php
  if (!defined('IN_TBB')) die();
  $page = <<<END
- [BBCODE_OVERRIDE]b]text[BBCODE_OVERRIDE]/b]: [b]bold[/b]
- [BBCODE_OVERRIDE]i]text[BBCODE_OVERRIDE]/i]: [i]italics[/i]
- [BBCODE_OVERRIDE]u]text[BBCODE_OVERRIDE]/u]: [u]underline[/u]
- [BBCODE_OVERRIDE]s]text[BBCODE_OVERRIDE]/s]: [s]strikethrough[/s]
- [BBCODE_OVERRIDE]url]http://www.google.com[BBCODE_OVERRIDE]/url]: [url]http://www.google.com[/url]
- [BBCODE_OVERRIDE]url=http://www.google.com]masked URL[BBCODE_OVERRIDE]/url]: [url=http://www.google.com]masked URL[/url]
- [BBCODE_OVERRIDE]spoiler]text[BBCODE_OVERRIDE]/spoiler]:
[spoiler]text[/spoiler]
- [BBCODE_OVERRIDE]spoiler=NSMB]Bowser dies[BBCODE_OVERRIDE]/spoiler]: [spoiler=NSMB]Bowser dies[/spoiler]
- [BBCODE_OVERRIDE]quote]quoted text[BBCODE_OVERRIDE]/quote]: [quote]quoted text[/quote]
- [BBCODE_OVERRIDE]quote=person]quoted text[BBCODE_OVERRIDE]/quote]: [quote=person]quoted text[/quote]
- [BBCODE_OVERRIDE]code]code[BBCODE_OVERRIDE]/code]: [code]code[/code]
- [BBCODE_OVERRIDE]size=10px]small text[BBCODE_OVERRIDE]/size]: [size=10px]small text[/size]
END;
  print "<div style='text-align: left; width: 80%; padding: 2px; margin: 0 auto; border: 1px solid #aad6ff'>";
  print getpost($page, true, true, true);
  print "</div>";
?>