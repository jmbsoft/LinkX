=>[lx_links]
link_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
site_url TEXT,
recip_url TEXT,
title TEXT,
description TEXT,
status ENUM('unconfirmed','pending','active','disabled'),
type ENUM('regular','premium','featured'),
expires DATETIME NOT NULL,
name VARCHAR(255),
email VARCHAR(255),
submit_ip VARCHAR(16),
keywords TEXT,
clicks INT NOT NULL DEFAULT 0,
comments INT NOT NULL DEFAULT 0,
screenshot TEXT,
ratings INT NOT NULL DEFAULT 0,
rating_total INT NOT NULL DEFAULT 0,
rating_avg FLOAT,
weight FLOAT,
date_added DATETIME,
date_modified DATETIME,
date_scanned DATETIME,
recip_required TINYINT NOT NULL DEFAULT 0,
allow_redirect TINYINT NOT NULL DEFAULT 0,
icons TEXT,
admin_comments TEXT,
username VARCHAR(32),
password VARCHAR(40),
has_recip TINYINT NOT NULL DEFAULT 0,
is_edited TINYINT NOT NULL DEFAULT 0,
edit_data MEDIUMTEXT,
FULLTEXT(title,description,keywords),
INDEX(site_url(100)),
INDEX(recip_url(100)),
INDEX(username),
INDEX(is_edited),
INDEX(date_added),
INDEX(rating_avg),
INDEX(clicks),
INDEX(expires),
INDEX(status)

=>[lx_link_confirms]
link_id INT NOT NULL PRIMARY KEY,
confirmation_id VARCHAR(40) NOT NULL,
date_added INT NOT NULL

=>[lx_link_field_defs]
field_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(32),
label TEXT,
type VARCHAR(64),
tag_attributes TEXT,
options TEXT,
validation INT NOT NULL DEFAULT 0,
validation_extras TEXT,
validation_message TEXT,
on_submit TINYINT NOT NULL DEFAULT 0,
on_edit TINYINT NOT NULL DEFAULT 0,
on_details TINYINT NOT NULL DEFAULT 0,
required TINYINT NOT NULL DEFAULT 0

=>[lx_link_fields]
link_id INT NOT NULL PRIMARY KEY

=>[lx_link_cats]
link_id INT NOT NULL,
category_id INT NOT NULL,
sorter INT,
INDEX(link_id),
INDEX(category_id)

=>[lx_link_ratings]
link_id INT NOT NULL,
username VARCHAR(32),
submit_ip VARCHAR(16) NOT NULL,
date_added INT NOT NULL,
INDEX(link_id),
INDEX(username),
INDEX(submit_ip),
INDEX(date_added)

=>[lx_news]
news_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
headline TEXT,
body TEXT,
date_added DATETIME

=>[lx_reports]
report_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
link_id INT NOT NULL,
message TEXT,
date_added DATETIME NOT NULL,
submit_ip VARCHAR(16),
INDEX(link_id)

=>[lx_link_comments]
comment_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
link_id INT NOT NULL,
username VARCHAR(32),
email VARCHAR(255),
name VARCHAR(255),
submit_ip VARCHAR(16),
date_added DATETIME,
status ENUM('pending','approved') NOT NULL,
comment TEXT,
FULLTEXT(comment),
INDEX(link_id),
INDEX(username),
INDEX(email),
INDEX(submit_ip)

=>[lx_reciprocals]
recip_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
identifier TEXT,
code TEXT,
regex TINYINT NOT NULL

=>[lx_blacklist]
blacklist_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
type CHAR(32) NOT NULL,
regex TINYINT NOT NULL,
value TEXT,
reason TEXT,
INDEX(type),
INDEX(value(50))

=>[lx_rejections]
email_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
identifier VARCHAR(128),
plain TEXT,
compiled TEXT

=>[lx_users]
username VARCHAR(32) NOT NULL PRIMARY KEY,
password VARCHAR(40),
name VARCHAR(255),
email VARCHAR(255),
date_added DATETIME,
date_modified DATETIME,
status ENUM('unconfirmed','pending','active','suspended'),
session CHAR(40),
session_start INT,
num_links INT NOT NULL DEFAULT 0,
recip_required TINYINT NOT NULL DEFAULT 0,
allow_redirect TINYINT NOT NULL DEFAULT 0,
weight FLOAT NOT NULL DEFAULT 0

=>[lx_user_confirms]
username VARCHAR(32) NOT NULL PRIMARY KEY,
confirmation_id VARCHAR(40) NOT NULL,
date_added INT NOT NULL,
INDEX(confirmation_id)

=>[lx_user_field_defs]
field_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(32),
label TEXT,
type VARCHAR(64),
tag_attributes TEXT,
options TEXT,
validation INT NOT NULL DEFAULT 0,
validation_extras TEXT,
validation_message TEXT,
on_create TINYINT NOT NULL DEFAULT 0,
on_edit TINYINT NOT NULL DEFAULT 0,
required TINYINT NOT NULL DEFAULT 0

=>[lx_user_fields]
username VARCHAR(32) NOT NULL PRIMARY KEY

=>[lx_captcha]
session VARCHAR(40) NOT NULL,
code VARCHAR(64) NOT NULL,
date_added INT NOT NULL,
INDEX(session)

=>[lx_categories]
category_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
name TEXT,
url_name TEXT,
description TEXT,
meta_description TEXT,
meta_keywords TEXT,
parent_id INT NOT NULL,
path TEXT NOT NULL,
path_parts MEDIUMTEXT NOT NULL,
path_hash CHAR(32) NOT NULL,
template CHAR(80),
crosslink_id INT,
related_ids TEXT,
subcategories INT NOT NULL,
links INT NOT NULL,
status ENUM('auto','approval','locked') NOT NULL,
hidden TINYINT NOT NULL DEFAULT 0,
FULLTEXT(name),
INDEX(path(255)),
INDEX(path_hash),
INDEX(parent_id)

=>[lx_administrators]
username CHAR(32) NOT NULL PRIMARY KEY,
password CHAR(40) NOT NULL,
session CHAR(40),
session_start INT,
name CHAR(80),
email CHAR(100),
type ENUM('administrator','editor') NOT NULL,
categories TEXT,
notifications INT,
rights INT

=>[lx_stored_values]
name VARCHAR(128) NOT NULL PRIMARY KEY,
value TEXT

=>[lx_scanner_configs]
config_id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
identifier VARCHAR(255),
current_status TEXT,
status_updated INT NOT NULL,
pid INT NOT NULL DEFAULT 0,
date_last_run DATETIME,
configuration TEXT

=>[lx_scanner_results]
config_id INT NOT NULL,
link_id INT NOT NULL,
site_url TEXT,
http_status VARCHAR(255),
date_scanned DATETIME NOT NULL,
action TEXT,
message TEXT,
INDEX(config_id),
INDEX(link_id),
INDEX(site_url(100)),
INDEX(http_status)

=>[lx_ads]
`ad_id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
`ad_url` TEXT,
`ad_html_raw` TEXT,
`ad_html` TEXT,
`weight` INT NOT NULL,
`raw_clicks` INT NOT NULL,
`unique_clicks` INT NOT NULL,
`times_displayed` INT NOT NULL,
`tags` TEXT,
FULLTEXT(`tags`)

=>[lx_ads_iplog]
`ad_id` INT NOT NULL,
`ip_address` INT NOT NULL,
`raw_clicks` INT NOT NULL,
`last_click` INT NOT NULL,
PRIMARY KEY(`ad_id`,`ip_address`)

=>[lx_ads_used_page]
`ad_id` INT NOT NULL PRIMARY KEY

=>[lx_search_terms]
`term_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
`term` TEXT,
`searches` INT,
`date_last_search` DATETIME,
INDEX(`term`(100)),
INDEX(`date_last_search`)