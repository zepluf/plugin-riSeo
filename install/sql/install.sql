CREATE TABLE IF NOT EXISTS `seo_meta` (
  `seo_id` int(11) NOT NULL AUTO_INCREMENT,
  `main_page` varchar(255) NOT NULL,
  `page_id` int(11) NOT NULL,
  PRIMARY KEY  (`seo_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `seo_meta_data` (
  `seo_meta_data_id` int(11) NOT NULL AUTO_INCREMENT,
  `seo_id` int(11) NOT NULL,
  `meta_name` varchar(255) NOT NULL,
  `meta_content` varchar(255) NOT NULL,
  PRIMARY KEY  (`seo_meta_data_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;