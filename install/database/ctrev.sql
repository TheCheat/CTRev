CREATE TABLE IF NOT EXISTS `admin_cats` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item` (`item`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `admin_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `admin_modules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `cat` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `link` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cat` (`cat`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `allowed_ft` (
  `name` varchar(200) NOT NULL,
  `types` text NOT NULL,
  `max_filesize` int(10) unsigned NOT NULL DEFAULT '0',
  `max_width` int(10) unsigned NOT NULL DEFAULT '0',
  `max_height` int(10) unsigned NOT NULL DEFAULT '0',
  `MIMES` text NOT NULL,
  `makes_preview` enum('1','0') NOT NULL DEFAULT '0',
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Не нуждается в админке, ибо не нужно. Всегда Ваш, К.О.';

CREATE TABLE IF NOT EXISTS `bans` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `period` int(12) unsigned NOT NULL DEFAULT '0',
  `byuid` int(10) unsigned NOT NULL DEFAULT '0',
  `reason` varchar(255) NOT NULL DEFAULT '',
  `ip_f` int(11) unsigned DEFAULT NULL,
  `ip_t` int(11) unsigned DEFAULT NULL,
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `to_time` int(12) unsigned NOT NULL DEFAULT '0',
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `first_last` (`ip_f`,`ip_t`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `blocks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `file` varchar(200) NOT NULL,
  `pos` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('top','bottom','left','right') NOT NULL DEFAULT 'top',
  `tpl` varchar(200) NOT NULL DEFAULT '',
  `module` text,
  `settings` text,
  `enabled` enum('1','0') NOT NULL DEFAULT '1',
  `group_allowed` text,
  PRIMARY KEY (`id`),
  KEY `enabled` (`enabled`),
  KEY `file` (`file`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `toid` int(10) unsigned NOT NULL,
  `type` enum('torrents') NOT NULL DEFAULT 'torrents',
  `added` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `res_id` (`toid`,`type`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `bots` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `firstip` int(12) unsigned NOT NULL DEFAULT '0',
  `lastip` int(12) unsigned NOT NULL DEFAULT '0',
  `agent` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `captcha` (
  `key` varchar(10) NOT NULL,
  `session_id` varchar(32) NOT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `created` int(12) unsigned NOT NULL DEFAULT '0',
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `descr` text,
  `transl_name` varchar(200) NOT NULL,
  `post_allow` enum('1','0') NOT NULL DEFAULT '1',
  `type` enum('torrents') NOT NULL DEFAULT 'torrents',
  `pattern` int(11) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `transl_name` (`transl_name`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chat` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poster_id` int(10) unsigned NOT NULL DEFAULT '0',
  `posted_time` int(12) unsigned NOT NULL DEFAULT '0',
  `edited_time` int(12) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `edited_time` (`edited_time`),
  KEY `posted_time` (`posted_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `chat_deleted` (
  `id` int(10) unsigned NOT NULL,
  `time` int(12) unsigned NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `time` (`time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='логи чата, какие сообщения были удалены';

CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poster_id` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(200) NOT NULL DEFAULT 'NO SUBJECT',
  `text` text NOT NULL,
  `posted_time` int(12) unsigned NOT NULL DEFAULT '0',
  `toid` int(10) unsigned NOT NULL,
  `type` enum('torrents','users') NOT NULL DEFAULT 'torrents',
  `edited_time` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `toid` (`toid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `config` (
  `name` varchar(200) NOT NULL,
  `value` text NOT NULL,
  `type` enum('int','string','text','date','folder','radio','select','checkbox','other') NOT NULL DEFAULT 'text',
  `allowed` text,
  `cat` varchar(50) NOT NULL DEFAULT 'other',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`name`),
  KEY `cat` (`cat`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `countries` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `image` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `downloaded` (
  `tid` int(10) unsigned NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `finished` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`uid`),
  KEY `finished` (`finished`),
  KEY `tid` (`tid`,`finished`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `default` enum('1','0') NOT NULL DEFAULT '0',
  `acp_modules` text,
  `pm_count` int(10) unsigned NOT NULL DEFAULT '50',
  `notdeleted` enum('1','0') NOT NULL DEFAULT '0',
  `system` enum('1','0') NOT NULL DEFAULT '0',
  `guest` enum('1','0') NOT NULL DEFAULT '0',
  `bot` enum('1','0') NOT NULL DEFAULT '0',
  `name` varchar(50) NOT NULL,
  `color` varchar(20) NOT NULL DEFAULT '',
  `perms` text,
  `sort` tinyint(3) NOT NULL DEFAULT '0',
  `torrents_count` int(5) unsigned NOT NULL DEFAULT '0',
  `karma_count` int(5) unsigned NOT NULL DEFAULT '0',
  `bonus_count` int(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `groups_perm` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `perm` varchar(200) NOT NULL,
  `dvalue` tinyint(2) unsigned NOT NULL DEFAULT '0',
  `allowed` enum('1','2','3') NOT NULL DEFAULT '1',
  `cat` varchar(50) NOT NULL DEFAULT 'other',
  PRIMARY KEY (`id`),
  UNIQUE KEY `perm` (`perm`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `invites` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `to_userid` int(10) unsigned NOT NULL DEFAULT '0',
  `invite_id` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `invite_id` (`invite_id`),
  KEY `user_id` (`user_id`),
  KEY `to_userid` (`to_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(250) NOT NULL,
  `type` enum('user','admin','system','other') NOT NULL DEFAULT 'user',
  `time` int(12) unsigned NOT NULL DEFAULT '0',
  `byuid` int(10) unsigned NOT NULL DEFAULT '0',
  `byip` int(12) unsigned NOT NULL DEFAULT '0',
  `touid` int(10) unsigned NOT NULL DEFAULT '0',
  `descr` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `type` (`type`),
  KEY `byid` (`byuid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `mailer` (
  `user` int(10) unsigned NOT NULL,
  `toid` int(10) unsigned NOT NULL,
  `type` enum('torrents','category') NOT NULL DEFAULT 'torrents',
  `interval` int(12) unsigned NOT NULL DEFAULT '0',
  `last_check` int(12) unsigned NOT NULL DEFAULT '0',
  `is_new` enum('1','0') NOT NULL DEFAULT '1',
  UNIQUE KEY `res` (`toid`,`type`,`user`),
  KEY `is_new` (`is_new`),
  KEY `interval` (`interval`),
  KEY `last_check` (`last_check`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poster_id` int(10) unsigned NOT NULL DEFAULT '0',
  `posted_time` int(12) unsigned NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  `title` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `added` (`posted_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `patterns` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `pattern` mediumtext NOT NULL COMMENT 'Да, да, я знаю, что нельзя ТАК делать, но ТАК будет быстрее, да и удобнее.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `peers` (
  `peer_id` varchar(20) NOT NULL,
  `tid` int(10) unsigned NOT NULL DEFAULT '0',
  `ip` int(10) unsigned NOT NULL DEFAULT '0',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `uploaded` bigint(20) unsigned NOT NULL DEFAULT '0',
  `seeder` enum('1','0') NOT NULL DEFAULT '0',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tid`,`uid`),
  UNIQUE KEY `peer_id` (`peer_id`,`tid`),
  KEY `seeder` (`tid`,`seeder`),
  KEY `torrent` (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `plugins` (
  `file` varchar(30) NOT NULL,
  `settings` text NULL,
  PRIMARY KEY (`file`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `pmessages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(200) NOT NULL,
  `text` text NOT NULL,
  `sender` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(12) unsigned NOT NULL DEFAULT '0',
  `receiver` int(10) unsigned NOT NULL DEFAULT '0',
  `unread` enum('1','0') NOT NULL DEFAULT '1',
  `deleted` enum( '0', '1', '2' ) NOT NULL DEFAULT '0' COMMENT '0-none,1-sender,2-reciever',
  PRIMARY KEY (`id`),
  KEY `time` (`time`),
  KEY `sender` (`sender`,`deleted`),
  KEY `reciever` (`receiver`,`unread`,`deleted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `polls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(200) NOT NULL,
  `toid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('torrents') NOT NULL DEFAULT 'torrents',
  `answers` text NOT NULL,
  `show_voted` enum('1','0') NOT NULL DEFAULT '1',
  `change_votes` enum('1','0') NOT NULL DEFAULT '1',
  `poll_ends` int(12) unsigned NOT NULL DEFAULT '0',
  `max_votes` int(10) unsigned NOT NULL DEFAULT '0',
  `poster_id` int(10) unsigned NOT NULL DEFAULT '0',
  `posted_time` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `poll_votes` (
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `question_id` int(10) unsigned NOT NULL,
  `user_ip` int(12) unsigned NOT NULL DEFAULT '0',
  `answers_id` text NOT NULL,
  UNIQUE KEY `question_id` (`question_id`,`user_id`,`user_ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `toid` int(10) unsigned NOT NULL,
  `stoid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('torrents','users') NOT NULL DEFAULT 'torrents',
  `stype` enum('torrents') NOT NULL DEFAULT 'torrents',
  `user` int(12) unsigned NOT NULL DEFAULT '0',
  `value` tinyint(1) NOT NULL DEFAULT '0',
  `ip` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `torus` (`toid`,`type`,`user`,`ip`,`stoid`,`stype`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `read_torrents` (
  `torrent_id` int(12) unsigned NOT NULL,
  `user_id` int(12) unsigned NOT NULL,
  UNIQUE KEY `torrents_id` (`torrent_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `sessions` (
  `sid` varchar(32) NOT NULL DEFAULT '',
  `uid` int(10) unsigned NOT NULL DEFAULT '0',
  `userdata` text NOT NULL,
  `ip` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(12) unsigned NOT NULL DEFAULT '0',
  `login_trying` smallint(3) unsigned NOT NULL DEFAULT '0',
  `trying_time` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`sid`),
  KEY `time` (`time`),
  KEY `login_trying` (`login_trying`),
  KEY `def` (`ip`,`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `smilies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `image` varchar(225) NOT NULL,
  `name` varchar(200) NOT NULL,
  `show_bbeditor` enum('1','0') NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `image` (`image`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `static` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `url` varchar(150) NOT NULL,
  `title` varchar(250) NOT NULL,
  `bbcode` enum('1','0') NOT NULL DEFAULT '0',
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `stats` (
  `name` varchar(200) NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `torrents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `info_hash` varbinary(40) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `filelist` text NOT NULL,
  `tags` varchar(500) DEFAULT NULL,
  `category_id` text NOT NULL,
  `size` bigint(20) unsigned NOT NULL DEFAULT '0',
  `announce_stat` text,
  `announce_list` text,
  `posted_time` int(12) unsigned NOT NULL DEFAULT '0',
  `last_active` int(12) unsigned NOT NULL DEFAULT '0',
  `comm_count` int(10) unsigned NOT NULL DEFAULT '0',
  `downloaded` int(10) unsigned NOT NULL DEFAULT '0',
  `leechers` int(10) unsigned NOT NULL DEFAULT '0',
  `seeders` int(10) unsigned NOT NULL DEFAULT '0',
  `banned` enum('2','1','0') NOT NULL DEFAULT '0' COMMENT '2 - в т.ч. запрет на редактирование',
  `status` varchar(50) NOT NULL DEFAULT '0',
  `status_by` int(10) unsigned NOT NULL DEFAULT '0',
  `poster_id` int(10) unsigned NOT NULL DEFAULT '0',
  `rate_count` int(10) unsigned NOT NULL DEFAULT '0',
  `rnum_count` int(10) unsigned NOT NULL DEFAULT '0',
  `price` decimal(5,2) unsigned DEFAULT '10.00',
  `sticky` enum('1','0') NOT NULL DEFAULT '0',
  `on_top` enum('1','0') DEFAULT '0',
  `screenshots` text NOT NULL,
  `edit_reason` varchar(250) NOT NULL DEFAULT '',
  `last_edit` int(12) unsigned NOT NULL DEFAULT '0',
  `editor_id` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `info_hash` (`info_hash`),
  UNIQUE KEY `name` (`title`),
  KEY `owner` (`poster_id`),
  KEY `tags` (`tags`(333)),
  KEY `on_top` (`on_top`),
  KEY `sticky` (`sticky`),
  KEY `posted_time` (`posted_time`),
  FULLTEXT KEY `title` (`title`),
  FULLTEXT KEY `title_content` (`title`,`content`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(40) NOT NULL,
  `username_lower` varchar(40) NOT NULL,
  `password` varchar(32) NOT NULL,
  `salt` varchar(32) NOT NULL,
  `email` varchar(80) NOT NULL,
  `new_email` varchar(200) NOT NULL DEFAULT '',
  `confirm_key` varchar(32) NOT NULL DEFAULT '',
  `confirmed` enum('3','2','1','0') NOT NULL DEFAULT '0',
  `registered` int(12) unsigned NOT NULL DEFAULT '0',
  `last_visited` int(12) unsigned NOT NULL DEFAULT '0',
  `settings` text COMMENT 'show_age, website, icq, skype, country, town, name, surname, announce_pk',
  `admin_email` enum('1','0') NOT NULL DEFAULT '0',
  `user_email` enum('1','0') NOT NULL DEFAULT '0',
  `mailer_interval` int(10) unsigned NOT NULL DEFAULT '0',
  `dst` enum('1','0') NOT NULL DEFAULT '0',
  `timezone` int(3) NOT NULL DEFAULT '0',
  `ip` int(10) unsigned NOT NULL DEFAULT '0',
  `group` tinyint(3) NOT NULL DEFAULT '0',
  `old_group` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `add_permissions` text,
  `bonus_count` decimal(65,2) unsigned NOT NULL DEFAULT '300.00',
  `avatar` varchar(250) NOT NULL DEFAULT '',
  `gender` enum('u','m','f') NOT NULL DEFAULT 'u',
  `birthday` int(12) unsigned DEFAULT '0',
  `passkey` varchar(32) NOT NULL DEFAULT '',
  `refered_by` int(10) unsigned NOT NULL DEFAULT '0',
  `karma_count` int(10) NOT NULL DEFAULT '0',
  `torrents_count` int(10) unsigned NOT NULL DEFAULT '0',
  `comm_count` int(10) unsigned NOT NULL DEFAULT '0',
  `hidden` enum('1','0') NOT NULL DEFAULT '0',
  `warnings_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username_lower` (`username_lower`),
  UNIQUE KEY `passkey` (`passkey`),
  KEY `status_added` (`confirmed`,`registered`),
  KEY `ip` (`ip`),
  KEY `last_access` (`last_visited`),
  KEY `user` (`id`,`confirmed`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `warnings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(10) unsigned NOT NULL,
  `reason` varchar(200) NOT NULL,
  `byuid` int(10) unsigned NOT NULL DEFAULT '0',
  `time` int(12) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `zebra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `to_userid` int(10) unsigned NOT NULL DEFAULT '0',
  `type` enum('f','b') NOT NULL DEFAULT 'f',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userfriend` (`user_id`,`to_userid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `admin_cats` (`id`, `item`, `name`) VALUES
(1, 1, 'short_actions'),
(2, 1, 'short_config'),
(3, 1, 'short_other'),
(4, 2, 'users'),
(5, 2, 'bans'),
(6, 3, 'content'),
(7, 3, 'languages'),
(8, 3, 'static_pages'),
(9, 4, 'styles'),
(10, 4, 'blocks'),
(11, 5, 'config'),
(12, 5, 'logs'),
(13, 6, 'plugins');

INSERT INTO `admin_items` (`id`, `name`) VALUES
(1, 'main'),
(2, 'users'),
(3, 'content'),
(4, 'styles'),
(5, 'system'),
(6, 'plugins');

INSERT INTO `admin_modules` (`id`, `cat`, `name`, `link`) VALUES
(1, 1, 'users_manage', 'module=users'),
(2, 1, 'groups_manage', 'module=groups'),
(3, 1, 'cats_manage', 'module=cats'),
(4, 1, 'logs_manage', 'module=logs'),
(5, 2, 'configuration_main', 'module=config&type=other'),
(6, 2, 'configuration_users', 'module=config&type=users'),
(7, 2, 'configuration_mail', 'module=config&type=mail'),
(8, 2, 'configuration_all', 'module=config'),
(9, 3, 'mass_mail', 'module=massmail'),
(10, 4, 'groups_list', 'module=groups'),
(11, 4, 'users_search', 'module=users&act=search'),
(12, 4, 'users', 'module=users'),
(13, 4, 'unco', 'module=users&act=unconfirmed'),
(14, 4, 'bots', 'module=bots'),
(15, 5, 'bans', 'module=bans'),
(16, 5, 'ban_add', 'module=bans&act=add'),
(17, 5, 'warnings', 'module=warnings'),
(18, 5, 'warning_add', 'module=warnings&act=add'),
(19, 6, 'categories', 'module=cats'),
(20, 6, 'patterns', 'module=patterns'),
(21, 6, 'smilies', 'module=smilies'),
(22, 7, 'languages', 'module=lang'),
(23, 7, 'languages_search', 'module=lang&act=search'),
(24, 8, 'sp_create', 'module=spages&act=add'),
(25, 8, 'sp_view', 'module=spages'),
(26, 9, 'styles', 'module=styles'),
(27, 9, 'styles_search', 'module=styles&act=search'),
(28, 10, 'blocks', 'module=blocks'),
(29, 10, 'blocks_add', 'module=blocks&act=add'),
(30, 11, 'configuration_main', 'module=config&type=other'),
(31, 11, 'configuration_users', 'module=config&type=users'),
(32, 11, 'configuration_mail', 'module=config&type=mail'),
(33, 11, 'configuration_cache', 'module=config&type=cache'),
(34, 11, 'configuration_torrents', 'module=config&type=torrents'),
(35, 11, 'configuration_all', 'module=config'),
(36, 12, 'logs_all', 'module=logs'),
(37, 12, 'logs_system', 'module=logs&type=system'),
(38, 12, 'logs_admin', 'module=logs&type=admin'),
(39, 12, 'logs_user', 'module=logs&type=user'),
(40, 12, 'logs_other', 'module=logs&type=other'),
(41, 12, 'logs_clear', 'module=logs&act=clear'),
(42, 13, 'plugins', 'module=plugins'),
(43, 13, 'plugins_add', 'module=plugins&act=add');

INSERT INTO `allowed_ft` (`name`, `types`, `max_filesize`, `max_width`, `max_height`, `MIMES`, `makes_preview`) VALUES
('images', 'jpg;jpeg;png;gif', 2097152, 0, 0, 'image/jpeg;image/png;image/gif', '1'),
('avatars', 'jpg;jpeg;png;gif', 65536, 100, 100, 'image/jpeg;image/png;image/gif', '0'),
('torrents', 'torrent', 2097152, 0, 0, 'application/x-bittorrent', '0');

INSERT INTO `bans` (`id`, `period`, `byuid`, `reason`, `ip_f`, `ip_t`, `uid`, `to_time`, `email`) VALUES
(1, 0, 1, '4ever bannaned!', 0, 0, 0, 0, '*@*.ru');

INSERT INTO `blocks` (`id`, `title`, `file`, `pos`, `type`, `tpl`, `module`, `settings`, `enabled`, `group_allowed`) VALUES
(3, 'Ссылки', 'links', 0, 'left', '0', '', 'a:1:{s:5:"links";a:3:{s:28:"CTRev: A bit of (R)evolution";s:25:"http://ctrev.cyber-tm.ru/";s:25:"Project Cyberhype Tracker";s:23:"http://old.cyber-tm.ru/";s:42:"Официальный сайт Cyber-Team";s:19:"http://cyber-tm.ru/";}}', '1', ''),
(7, 'Новости', 'news', 0, 'top', '0', 'index', 'a:0:{}', '1', ''),
(5, 'Нижний блок', 'downm', 0, 'bottom', 'downer_block', '', 'N;', '1', ''),
(8, 'Чат', 'chat', 1, 'top', '0', 'index', 'a:0:{}', '1', ''),
(2, 'Календарь', 'calendar', 1, 'left', '', '', 'N;', '1', ''),
(9, 'Торренты', 'torrents_simple', 2, 'top', 'torrents_block', 'index', 'a:3:{s:4:"cats";a:3:{s:6:"Всё";s:11:"1|3|4|6|7|8";s:8:"Игры";s:5:"1|3|4";s:8:"Софт";s:5:"6|7|8";}s:5:"limit";s:2:"10";s:14:"max_title_symb";s:3:"100";}', '1', ''),
(4, 'Опросы', 'polls', 2, 'left', '', '', 'N;', '1', ''),
(1, 'Торренты', 'torrents', 3, 'top', 'all_blocks', 'index', 'a:0:{}', '0', ''),
(6, 'Тест параметров', 'simple_block', 3, 'left', '0', 'index', 'a:9:{s:4:"par1";s:13:"stringsqweqwe";s:5:"par1t";s:44:"Blah-Blah-Blah, Mr. Freeman, Blah-Blah-Blah.";s:4:"par2";s:4:"1213";s:4:"par3";s:1:"3";s:5:"par35";s:1:"1";s:4:"par4";a:4:{i:0;s:12:"stringsqweqw";i:1;s:6:"qweqwe";i:2;s:7:"weqwdas";i:3;s:6:"qweqwe";}s:4:"par5";a:2:{i:0;s:6:"121312";i:1;s:5:"21231";}s:4:"par6";a:3:{s:8:"strweqwe";s:5:"tests";s:10:"asdaweqeqw";s:4:"test";s:8:"qweqweqw";s:7:"testers";}s:4:"par7";a:3:{i:3;s:6:"tests1";i:1231;s:8:"testers1";i:12312;s:6:"tests1";}}', '', '6');

INSERT INTO `categories` (`id`, `parent_id`, `name`, `descr`, `transl_name`, `post_allow`, `type`, `pattern`, `sort`) VALUES
(1, 0, 'Игры', 'Такое же простое описание простой категории', 'games', '0', 'torrents', 1, 0),
(6, 0, 'Софт', 'Софт и все, все, все', 'software', '0', 'torrents', 1, 0),
(7, 6, 'PC', 'Софт для PC', 'pcsoft', '1', 'torrents', 1, 0),
(8, 6, 'PPC', 'Blahblahblah', 'ppcsoft', '1', 'torrents', 1, 0),
(3, 1, 'Action', 'AND MOAR!', 'action', '1', 'torrents', 1, 1),
(4, 1, 'PC', 'MOAR!!!', 'pcgames', '1', 'torrents', 1, 2);

INSERT INTO `config` (`name`, `value`, `type`, `allowed`, `cat`, `sort`) VALUES
('annadress', '', 'text', '', 'announce', 1),
('announce_interval', '30', 'int', '', 'announce', 2),
('minbonus', '25', 'int', '', 'announce', 3),
('maxbonus', '50', 'int', '', 'announce', 4),
('maxbonus_mb', '500', 'int', '', 'announce', 5),
('cache_on', '1', 'radio', '1;0', 'cache', 1),
('cache_oldtime', '6', 'int', '', 'cache', 2),
('delay_queries', '300', 'int', '', 'cache', 3),
('delay_userupdates', '180', 'int', '', 'cache', 4),
('cache_pollvotes', '1', 'radio', '1;0', 'cache', 5),
('clearonvote_pollcache', '1', 'radio', '1;0', 'cache', 6),
('memcache_server', 'localhost:11211', 'string', '', 'cache', 7),
('cache_details', '1', 'radio', '1;0', 'cache', 8),
('cache_modsettings', '1', 'radio', '1;0', 'cache', 9),
('cleanup_each', '12', 'int', '', 'cleanup', 1),
('session_clear', '2', 'int', '', 'cleanup', 2),
('del_oldtorrents', '365', 'int', '', 'cleanup', 3),
('del_inactive', '1750', 'int', '', 'cleanup', 4),
('clear_warn_period', '90', 'int', '', 'cleanup', 5),
('clean_rt_interval', '7', 'int', '', 'cleanup', 6),
('clean_peers_interval', '1', 'int', '', 'cleanup', 7),
('chat_autoclear', '24', 'int', '', 'cleanup', 8),
('comm_perpage', '15', 'int', '', 'comments', 1),
('min_comm_symb', '5', 'int', '', 'comments', 2),
('antispam_time', '5', 'int', '', 'comments', 3),
('dc_prevent', '1', 'radio', '1;0', 'comments', 4),
('dc_maxtime', '600', 'int', '', 'comments', 5),
('dc_text', '[b]Добавлено спустя %time_after%[/b]', 'text', '', 'comments', 6),
('check_rimage', '0', 'radio', '1;0', 'files', 1),
('makes_preview', '1', 'radio', '1;0', 'files', 2),
('preview_width', '200', 'int', '', 'files', 3),
('preview_height', '300', 'int', '', 'files', 4),
('preview_postfix', '_preview', 'string', '', 'files', 5),
('avatars_folder', 'upload/avatars', 'string', '', 'files', 6),
('torrents_folder', 'upload/torrents', 'string', '', 'files', 7),
('screenshots_folder', 'upload/torrents/images', 'string', '', 'files', 8),
('smilies_folder', 'upload/pic/smilies', 'text', '', 'files', 9),
('zodiac_folder', 'upload/pic/zodiac', 'string', '', 'files', 10),
('countries_folder', 'upload/pic/flag', 'string', '', 'files', 11),
('smtp_method', 'default', 'select', 'default;external', 'mail', 1),
('smtp_host', '', 'string', '', 'mail', 2),
('smtp_port', '25', 'int', '', 'mail', 3),
('smtp_user', '', 'string', '', 'mail', 4),
('smtp_password', '', 'string', '', 'mail', 5),
('mailer_per_once', '50', 'int', '', 'mail', 6),
('multitracker_on', '1', 'radio', '1;0', 'multitrack', 1),
('DHT_on', '-1', 'radio', '-1;0;1', 'multitrack', 2),
('get_peers_interval', '6', 'int', '', 'multitrack', 3),
('getpeers_after_upload', '0', 'radio', '1;0', 'multitrack', 4),
('get_pk', 'rutracker.org ?uk=\r\nrutracker.net ?uk=', 'text', '', 'multitrack', 5),
('news_max', '7', 'int', '', 'news', 1),
('news_autodelete', '1', 'radio', '1;0', 'news', 2),
('site_title', 'CTRev: A bit of (R)evolution', 'string', '', 'other', 1),
('baseurl', '/CTRev', 'string', '', 'other', 2),
('contact_email', 'cybertmdev@gmail.com', 'string', '', 'other', 3),
('site_online', '1', 'radio', '1;0', 'other', 4),
('use_blocks', '1', 'radio', '1;0', 'other', 5),
('plugins_on', '1', 'radio', '1;0', 'other', 6),
('comments_on', '1', 'radio', '1;0', 'other', 7),
('rating_on', '1', 'radio', '1;0', 'other', 8),
('polls_on', '1', 'radio', '1;0', 'other', 9),
('mailer_on', '1', 'radio', '1;0', 'other', 10),
('site_autoon', '0', 'date', 'ymdhis', 'other', 11),
('siteoffline_reason', 'Технические работы.', 'text', '', 'other', 12),
('default_lang', 'ru', 'folder', 'languages', 'other', 13),
('default_style', 'CTRev', 'folder', 'themes', 'other', 14),
('table_perpage', '30', 'int', '', 'other', 15),
('show_process', '1', 'radio', '1;0', 'other', 16),
('check_mx_email', '0', 'radio', '0;1', 'other', 17),
('chat_maxmess', '50', 'int', '', 'other', 18),
('chat_clearlogs', '100', 'int', '', 'other', 19),
('secret_key', 'int getRandomNumber() {\r\nreturn 4; // Guaranteed to be random. Choosen by fair dice roll.\r\n}', 'text', '', 'other', 20),
('max_pmessages', '50', 'int', '', 'pm', 1),
('min_message_symb', '5', 'int', '', 'pm', 2),
('max_message_symb', '300', 'int', '', 'pm', 3),
('allowed_register', '1', 'radio', '1;0', 'register', 1),
('allowed_invite', '1', 'radio', '1;0', 'register', 2),
('use_captcha', '1', 'radio', '1;0', 'register', 3),
('private_key', '', 'string', '', 'register', 4),
('public_key', '6Ld5rQYAAAAAAGEv5do0SOc6lfIGReVMank7yZ18', 'string', '', 'register', 5),
('confirm_email', '0', 'radio', '1;0', 'register', 6),
('confirm_admin', '0', 'radio', '1;0', 'register', 7),
('bonus_per_invited', '100', 'int', '', 'register', 8),
('pre_search_title_only', '0', 'radio', '1;0', 'search', 1),
('max_symb_after_word', '200', 'int', '', 'search', 2),
('max_search_symb', '600', 'int', '', 'search', 3),
('furl', '1', 'radio', '1;0', 'seo', 1),
('use_bots', '1', 'radio', '1;0', 'seo', 2),
('my_meta', '', 'text', '', 'seo', 3),
('max_meta_descr_symb', '500', 'int', '', 'seo', 4),
('additional_announces', 'http://retracker.local/announce', 'text', '', 'torrents', 1),
('allowed_screenshots', '3', 'select', '3;2;1', 'torrents', 2),
('max_screenshots', '10', 'int', '', 'torrents', 3),
('max_torrent_price', '50', 'int', '', 'torrents', 4),
('max_sc_symb', '500', 'int', '', 'torrents', 5),
('max_rss_items', '20', 'int', '', 'torrents', 6),
('torrents_perpage', '5', 'int', '', 'torrents', 7),
('table_torrents_perpage', '30', 'int', '', 'torrents', 8),
('watermark_text', 'Watermark', 'string', '', 'torrents', 9),
('watermark_pos', 'rb', 'string', '', 'torrents', 10),
('ip_binding', '1', 'radio', '1;0', 'users', 1),
('allowed_avatar', '3', 'select', '3;2;1', 'users', 2),
('max_trylogin', '5', 'int', '', 'users', 3),
('logintime_interval', '3600', 'int', '', 'users', 4),
('online_interval', '600', 'int', '', 'users', 5),
('use_ipbans', '1', 'radio', '1;0', 'users', 6),
('warn2ban', '5', 'int', '', 'users', 7),
('warn2ban_days', '7', 'int', '', 'users', 8),
('last_profile_comments', '15', 'int', '', 'users', 9),
('last_profile_torrents', '15', 'int', '', 'users', 10),
('bonus_by_default', '300', 'int', '', 'users', 11);

INSERT INTO `countries` (`id`, `name`, `image`) VALUES
(87, 'Antigua Barbuda', 'antiguabarbuda.gif'),
(33, 'Belize', 'belize.gif'),
(59, 'Burkina Faso', 'burkinafaso.gif'),
(10, 'Denmark', 'denmark.gif'),
(91, 'Senegal', 'senegal.gif'),
(76, 'Trinidad & Tobago', 'trinidadandtobago.gif'),
(20, 'Австралия', 'australia.gif'),
(36, 'Австрия', 'austria.gif'),
(27, 'Албания', 'albania.gif'),
(34, 'Алжир', 'algeria.gif'),
(12, 'Англия', 'uk.gif'),
(35, 'Ангола', 'angola.gif'),
(66, 'Андора', 'andorra.gif'),
(19, 'Аргентина', 'argentina.gif'),
(53, 'Афганистан', 'afghanistan.gif'),
(80, 'Багамы', 'bahamas.gif'),
(83, 'Барбадос', 'barbados.gif'),
(16, 'Бельгия', 'belgium.gif'),
(84, 'Бенгладеш', 'bangladesh.gif'),
(101, 'Болгария', 'bulgaria.gif'),
(65, 'Босния', 'bosniaherzegovina.gif'),
(18, 'Бразилия', 'brazil.gif'),
(74, 'Вануату', 'vanuatu.gif'),
(72, 'Венгрия', 'hungary.gif'),
(71, 'Венесуела', 'venezuela.gif'),
(75, 'Вьетнам', 'vietnam.gif'),
(7, 'Германия', 'germany.gif'),
(77, 'Гондурас', 'honduras.gif'),
(32, 'Гонк Конг', 'hongkong.gif'),
(41, 'Греция', 'greece.gif'),
(42, 'Гуатемала', 'guatemala.gif'),
(40, 'Доминиканская Республика', 'dominicanrep.gif'),
(100, 'Египт', 'egypt.gif'),
(43, 'Израиль', 'israel.gif'),
(26, 'Индия', 'india.gif'),
(13, 'Ирландия', 'ireland.gif'),
(102, 'Исла де Муерто', 'jollyroger.gif'),
(22, 'Испания', 'spain.gif'),
(9, 'Италия', 'italy.gif'),
(82, 'Камбоджа', 'cambodia.gif'),
(5, 'Канада', 'canada.gif'),
(78, 'Киргистан', 'kyrgyzstan.gif'),
(57, 'Кирибати', 'kiribati.gif'),
(8, 'Китай', 'china.gif'),
(52, 'Кного', 'congo.gif'),
(96, 'Колумбия', 'colombia.gif'),
(99, 'Коста Рика', 'costarica.gif'),
(51, 'Куба', 'cuba.gif'),
(85, 'Лаос', 'laos.gif'),
(98, 'Латвия', 'latvia.gif'),
(97, 'Леванон', 'lebanon.gif'),
(67, 'Литва', 'lithuania.gif'),
(31, 'Люксембург', 'luxembourg.gif'),
(68, 'Македония', 'macedonia.gif'),
(39, 'Малайзия', 'malaysia.gif'),
(24, 'Мексика', 'mexico.gif'),
(62, 'Науру', 'nauru.gif'),
(60, 'Нигерия', 'nigeria.gif'),
(69, 'Нидерландские Антиллы', 'nethantilles.gif'),
(15, 'Нидерланды', 'netherlands.gif'),
(21, 'Новая Зеландия', 'newzealand.gif'),
(11, 'Норвегия', 'norway.gif'),
(44, 'Пакистан', 'pakistan.gif'),
(88, 'Парагвая', 'paraguay.gif'),
(81, 'Перу', 'peru.gif'),
(14, 'Польша', 'poland.gif'),
(23, 'Португалия', 'portugal.gif'),
(49, 'Пуерто Рико', 'puertorico.gif'),
(3, 'Россия', 'russia.gif'),
(73, 'Румуния', 'romania.gif'),
(93, 'Северная Корея', 'northkorea.gif'),
(47, 'Сейшельские Острова', 'seychelles.gif'),
(46, 'Сербия', 'serbia.gif'),
(25, 'Сингапур', 'singapore.gif'),
(63, 'Словакия', 'slovenia.gif'),
(90, 'СССР', 'ussr.gif'),
(2, 'США', 'usa.gif'),
(48, 'Тайвань', 'taiwan.gif'),
(89, 'Тайланд', 'thailand.gif'),
(92, 'Того', 'togo.gif'),
(64, 'Туркменистан', 'turkmenistan.gif'),
(54, 'Турция', 'turkey.gif'),
(55, 'Узбекистан', 'uzbekistan.gif'),
(70, 'Украина', 'ukraine.gif'),
(86, 'Уругвай', 'uruguay.gif'),
(58, 'Филиппины', 'philippines.gif'),
(4, 'Финляндия', 'finland.gif'),
(6, 'Франция', 'france.gif'),
(94, 'Хорватия', 'croatia.gif'),
(45, 'Чехия', 'czechrep.gif'),
(50, 'Чили', 'chile.gif'),
(56, 'Швейцария', 'switzerland.gif'),
(1, 'Швеция', 'sweden.gif'),
(79, 'Эквадор', 'ecuador.gif'),
(95, 'Эстония', 'estonia.gif'),
(37, 'Югославия', 'yugoslavia.gif'),
(28, 'Южная Африка', 'southafrica.gif'),
(29, 'Южная Корея', 'southkorea.gif'),
(38, 'Южные Самоа', 'westernsamoa.gif'),
(30, 'Ямайка', 'jamaica.gif'),
(17, 'Япония', 'japan.gif');

INSERT INTO `groups` (`id`, `default`, `acp_modules`, `pm_count`, `notdeleted`, `system`, `guest`, `bot`, `name`, `color`, `perms`, `sort`, `torrents_count`, `karma_count`, `bonus_count`) VALUES
(1, '0', '', 50, '1', '0', '1', '0', 'group_guest', '#707070', '33:0;32:0;7:0;6:0;17:0;4:0;3:0;2:1;24:0;25:0;23:0;21:0;15:0;10:0;9:0', 0, 0, 0, 0),
(7, '1', NULL, 50, '1', '0', '0', '0', 'group_user', '#000000', '', 1, 0, 0, 0),
(2, '0', NULL, 50, '0', '0', '0', '0', 'group_uploader', '#6600ff', '27:1', 2, 10, 0, 0),
(3, '0', NULL, 50, '0', '0', '0', '0', 'group_silver', '#5c5c5c', '27:1;26:1', 3, 0, 0, 0),
(4, '0', NULL, 50, '0', '0', '0', '0', 'group_gold', '#ffcc00', '27:1;26:2', 4, 0, 0, 0),
(5, '0', NULL, 50, '0', '0', '0', '0', 'group_moderator', '#0060ff', '33:3;30:1;32:2;29:1;28:1;7:2;6:2;18:1;27:1;4:2;3:2;26:2;20:1;22:1;19:1;12:1;10:2;9:2;8:2', 5, 0, 0, 0),
(8, '0', 'bans;warnings;users;logs;spages', 50, '0', '0', '0', '0', 'group_super_moderator', '#1ae615', '35:1;33:3;30:2;32:2;29:2;28:1;7:2;6:2;1:1;18:1;27:1;4:2;3:2;26:2;20:1;22:1;19:1;12:1;10:2;9:2;8:3', 6, 0, 0, 0),
(6, '0', NULL, 50, '1', '1', '0', '0', 'group_administrator', '#e65710', '35:1;33:3;30:2;32:2;29:2;28:1;7:2;6:2;1:2;18:1;27:1;4:2;3:2;26:2;20:1;24:0;25:0;22:1;19:1;12:1;10:2;9:2;8:3', 7, 0, 0, 0);

INSERT INTO `groups_perm` (`id`, `perm`, `dvalue`, `allowed`, `cat`) VALUES
(31, 'chat', 2, '2', 'blocks'),
(35, 'chat_sprivate', 0, '1', 'blocks'),
(33, 'del_chat', 1, '3', 'blocks'),
(30, 'del_news', 0, '2', 'blocks'),
(32, 'edit_chat', 1, '2', 'blocks'),
(29, 'edit_news', 0, '2', 'blocks'),
(28, 'news', 0, '1', 'blocks'),
(5, 'comment', 2, '2', 'comments'),
(7, 'del_comm', 1, '2', 'comments'),
(6, 'edit_comm', 1, '2', 'comments'),
(1, 'acp', 0, '2', 'other'),
(18, 'masspm', 0, '1', 'other'),
(17, 'pm', 1, '1', 'other'),
(27, 'ct_price', 0, '1', 'torrents'),
(4, 'del_torrents', 1, '2', 'torrents'),
(3, 'edit_torrents', 1, '2', 'torrents'),
(26, 'free', 0, '2', 'torrents'),
(20, 'msticky_torrents', 0, '1', 'torrents'),
(2, 'torrents', 2, '2', 'torrents'),
(24, 'bebanned', 1, '1', 'users'),
(25, 'bedeleted', 1, '1', 'users'),
(23, 'behidden', 1, '1', 'users'),
(22, 'hiddenu', 0, '1', 'users'),
(21, 'invite', 1, '1', 'users'),
(19, 'not_allowed', 0, '1', 'users'),
(11, 'profile', 1, '1', 'users'),
(15, 'usearch', 1, '1', 'users'),
(12, 'viewip', 0, '1', 'users'),
(10, 'del_polls', 1, '2', 'voting'),
(9, 'edit_polls', 1, '2', 'voting'),
(8, 'polls', 1, '3', 'voting'),
(13, 'vote', 1, '1', 'voting'),
(14, 'votersview', 1, '1', 'voting');

INSERT INTO `patterns` (`id`, `name`, `pattern`) VALUES
(1, 'Общий', 'a:4:{i:0;a:5:{s:4:"name";s:17:"*Название";s:5:"rname";s:4:"name";s:4:"type";s:5:"input";s:5:"descr";s:0:"";s:8:"formdata";s:79:"{form.title}{this.$value}\r\n{form.content}[b]Название: [/b]{this.$value}";}i:1;a:5:{s:4:"name";s:42:"*Оригинальное название";s:5:"rname";s:5:"rname";s:4:"type";s:5:"input";s:5:"descr";s:0:"";s:8:"formdata";s:107:"{form.title} / {this.$value}\r\n{form.content}[b]Оригинальное название: [/b]{this.$value}";}i:2;a:6:{s:4:"name";s:7:"*Год";s:5:"rname";s:4:"year";s:4:"type";s:5:"input";s:5:"descr";s:0:"";s:8:"formdata";s:72:"{form.title} ({this.$value})\r\n{form.content}[b]Год: [/b]{this.$value}";s:4:"size";i:5;}i:3;a:5:{s:4:"name";s:17:"*Описание";s:5:"rname";s:5:"descr";s:4:"type";s:8:"textarea";s:5:"descr";s:37:"Тест. Вместе с <b>HTML</b>";s:8:"formdata";s:54:"{form.content}[b]Описание: [/b]\r\n{this.$value}";}}');

INSERT INTO `smilies` (`id`, `code`, `image`, `name`, `show_bbeditor`, `sort`) VALUES
(1, ':-)', 'smile1.gif', 'Smile', '1', 0),
(3, ':-D', 'grin.gif', 'Grin', '1', 1),
(4, ':lol:', 'laugh.gif', 'Laugh', '1', 2),
(5, ':w00t:', 'w00t.gif', 'W00t', '1', 3),
(6, ':-P', 'tongue.gif', 'Tongue', '1', 4),
(7, ';-)', 'wink.gif', 'Wink', '1', 5),
(8, ':-|', 'noexpression.gif', 'Noexpression', '1', 6),
(9, ':-/', 'confused.gif', 'Confused', '1', 7),
(10, ':-(', 'sad.gif', 'Sad', '1', 8),
(11, ':cry:', 'cry.gif', 'Cry', '1', 9),
(12, ':-O', 'ohmy.gif', 'Ohmy', '1', 10),
(13, '8-)', 'cool1.gif', 'Cool', '1', 11),
(14, ':blush:', 'blush.gif', 'Blush', '1', 12),
(15, ':yes:', 'yes.gif', 'Yes', '1', 13),
(16, ':no:', 'no.gif', 'No', '1', 14),
(17, ':?:', 'question.gif', 'Question', '1', 15),
(18, ':!:', 'excl.gif', 'Excl', '1', 16),
(19, ':geek:', 'geek.gif', 'Geek', '1', 17),
(20, ':rolleyes:', 'rolleyes.gif', 'Rolleyes', '1', 18),
(21, ':crazy:', 'crazy.gif', 'Crazy', '1', 19),
(22, ':angry:', 'angry.gif', 'Angry', '1', 20),
(23, ':sorry:', 'sorry.gif', 'Sorry', '1', 21),
(24, ':hi:', 'hi.gif', 'Hi', '1', 22);

INSERT INTO `stats` (`name`, `value`) VALUES
('last_clean_rt', '1345397627'),
('last_cleanup', '1345729210'),
('max_online', '1'),
('max_online_time', '1345397628');