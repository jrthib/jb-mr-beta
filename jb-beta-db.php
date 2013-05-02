<?php

function install_db() {

	if($this->db->get_var( "SHOW TABLES LIKE '" . $this->autoblog . "' ") != $this->autoblog) {
		$sql = "CREATE TABLE `" . $this->autoblog . "` (
		  	  `feed_id` bigint(20) NOT NULL auto_increment,
			  `site_id` bigint(20) default '1',
			  `blog_id` bigint(20) default '1',
			  `feed_meta` text,
			  `active` int(11) default NULL,
			  `nextcheck` bigint(20) default NULL,
			  `lastupdated` bigint(20) default NULL,
			  PRIMARY KEY  (`feed_id`),
			  KEY `site_id` (`site_id`),
			  KEY `blog_id` (`blog_id`),
			  KEY `nextcheck` (`nextcheck`)
			)";

		$this->db->query($sql);
	}

	update_autoblog_option('autoblog_installed', $this->build);

}

?>