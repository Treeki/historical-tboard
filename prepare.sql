CREATE TABLE IF NOT EXISTS `adminnotes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `data` text NOT NULL,
  `author` int(10) unsigned NOT NULL,
  `notedate` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(60) NOT NULL,
  `power` tinyint(3) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `id` (`id`,`power`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `forumread` (
  `forum` tinyint(3) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `lastread` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`forum`,`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `forums` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` text NOT NULL,
  `desc` text NOT NULL,
  `lastposter` varchar(30) NOT NULL,
  `lastpostedin` varchar(70) NOT NULL,
  `lastposterid` int(10) unsigned NOT NULL,
  `lastpostedinid` int(10) unsigned NOT NULL,
  `lastpostdate` int(10) unsigned NOT NULL,
  `threads` int(10) unsigned NOT NULL,
  `posts` int(10) unsigned NOT NULL,
  `view_power` tinyint(3) unsigned NOT NULL,
  `reply_power` tinyint(3) unsigned NOT NULL,
  `thread_power` tinyint(3) unsigned NOT NULL,
  `mod_power` tinyint(3) unsigned NOT NULL,
  `order` tinyint(3) unsigned NOT NULL,
  `category` tinyint(3) unsigned NOT NULL,
  `group` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `category` (`category`),
  KEY `category_2` (`category`,`view_power`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `pmessages` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(70) NOT NULL,
  `sender` int(10) unsigned NOT NULL,
  `recipient` int(10) unsigned NOT NULL,
  `exists_sender` tinyint(3) unsigned NOT NULL,
  `exists_recipient` tinyint(3) unsigned NOT NULL,
  `pmread` tinyint(3) unsigned NOT NULL,
  `text` text NOT NULL,
  `sentdate` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `polls` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `thread` int(10) unsigned NOT NULL,
  `question` varchar(150) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `choices` text NOT NULL,
  `voteinfo` text NOT NULL,
  `choicecount` int(10) unsigned NOT NULL,
  `votecount` int(10) unsigned NOT NULL,
  `userviewable` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `thread` int(10) unsigned NOT NULL,
  `authorid` int(10) unsigned NOT NULL,
  `authorname` varchar(30) NOT NULL,
  `postdate` int(10) unsigned NOT NULL,
  `posttext` text NOT NULL,
  `postnum` int(10) unsigned NOT NULL,
  `postip` varchar(20) NOT NULL,
  `editinfo` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `thread` (`thread`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `reputation` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `sender` int(10) unsigned NOT NULL,
  `recipient` int(10) unsigned NOT NULL,
  `rep` int(11) NOT NULL,
  `date` int(10) unsigned NOT NULL,
  `content` varchar(250) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `recipient` (`recipient`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `threadread` (
  `thread` int(10) unsigned NOT NULL,
  `user` int(10) unsigned NOT NULL,
  `forum` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY  (`thread`,`user`),
  KEY `forum` (`forum`,`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `threads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `forum` tinyint(3) unsigned NOT NULL,
  `name` varchar(70) NOT NULL,
  `desc` varchar(70) NOT NULL,
  `authorid` int(10) unsigned NOT NULL,
  `authorname` varchar(30) NOT NULL,
  `lastposterid` int(10) unsigned NOT NULL,
  `lastpostername` varchar(30) NOT NULL,
  `lastpostdate` int(10) unsigned NOT NULL,
  `replies` int(10) unsigned NOT NULL,
  `locked` tinyint(4) NOT NULL,
  `stickied` tinyint(4) NOT NULL,
  `icon` varchar(32) NOT NULL,
  `poll` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `forum` (`forum`),
  KEY `forum_2` (`forum`,`lastpostdate`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `users` (
  `userid` int(10) unsigned NOT NULL auto_increment,
  `username` varchar(30) NOT NULL,
  `pwhash` varchar(40) NOT NULL,
  `salt` varchar(8) NOT NULL,
  `lastivused` varchar(12) NOT NULL,
  `email` varchar(80) NOT NULL,
  `powerlevel` tinyint(3) unsigned NOT NULL,
  `joindate` int(10) unsigned NOT NULL,
  `posts` int(10) unsigned NOT NULL,
  `threads` int(10) unsigned NOT NULL,
  `lastposttime` int(10) unsigned NOT NULL,
  `lastactive` int(10) unsigned NOT NULL,
  `usertitle` varchar(50) NOT NULL,
  `hasavatar` tinyint(1) NOT NULL,
  `avatarext` varchar(3) NOT NULL,
  `signature` text NOT NULL,
  `birthday` varchar(8) NOT NULL,
  `location` varchar(30) NOT NULL,
  `quote` varchar(80) NOT NULL,
  `info` text NOT NULL,
  `reputation` int(11) NOT NULL,
  `regip` varchar(20) NOT NULL,
  `lastip` varchar(20) NOT NULL,
  `theme` tinyint(3) unsigned NOT NULL,
  `groups` varchar(100) NOT NULL,
  PRIMARY KEY  (`userid`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `votes` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `poll` int(10) unsigned NOT NULL,
  `voter` int(10) unsigned NOT NULL,
  `choice` int(10) unsigned NOT NULL,
  `date` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `poll` (`poll`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;
