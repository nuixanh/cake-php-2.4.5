create database sitemon DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_general_ci;
GRANT ALL PRIVILEGES ON sitemon.* TO 'sitemon'@'service' IDENTIFIED BY 's!i@t#emon';
FLUSH PRIVILEGES;

CREATE TABLE `users` (
  `id` varchar(36) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(50) NOT NULL,
  `timezone` varchar(50),
  `lang` varchar(5),
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `last_session` varchar(36) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `sites` (
  `id` varchar(36) NOT NULL,
  `user_id` varchar(36) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(250) NOT NULL,
  `last_monitor_status` tinyint(4) NOT NULL,
  `active` tinyint(4) NOT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  `next_monitor_time` timestamp NULL DEFAULT NULL,
  `last_monitor_time` timestamp NULL DEFAULT NULL,
  `interval` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`) USING BTREE,
  CONSTRAINT `sites_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `hits` (
  `id` varchar(36) NOT NULL,
  `site_id` varchar(36) NOT NULL,
  `http_code` int(11) NOT NULL,
  `url` varchar(250) NOT NULL,
  `connect_time` double(11,0) NOT NULL,
  `total_time` double(11,0) NOT NULL,
  `primary_ip` varchar(36) DEFAULT NULL,
  `created` timestamp NULL DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`site_id`) USING BTREE,
  CONSTRAINT `hits_sites` FOREIGN KEY (`site_id`) REFERENCES `sites` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;