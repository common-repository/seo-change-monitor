<?php

$xcm_list_of_tables=array("xcm_tbl_list_of_url","xcm_tbl_url_summary","xcm_tbl_url_details","xcm_tbl_batch_runs","xcm_tbl_check_details","xcm_tbl_alert_details"
							,"xcm_insert_defaults_tbl_check_details"
							,"xcm_tbl_check_lookup"
							,"xcm_insert_defaults_tbl_check_lookup");


$xcm_list_of_tables_delete=array("xcm_tbl_list_of_url_delete","xcm_tbl_url_summary_delete","xcm_tbl_url_details_delete"
							,"xcm_tbl_batch_runs_delete","xcm_tbl_check_details_delete","xcm_tbl_alert_details_delete"
							,"xcm_tbl_check_lookup_delete"
							);
							
//$xcm_list_of_upgrades=array("xcm_alter_tbl_alert_details");							
$xcm_list_of_upgrades=array();							

function xcm_tbl_batch_runs_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_batch_runs;";
	return $sql;
}

function xcm_tbl_batch_runs($params)
{

$sql = "CREATE TABLE IF NOT EXISTS {$params["prefix"]}xcm_batch_runs (
  batch_seq_id int(11) NOT NULL AUTO_INCREMENT,
  batch_id varchar(100) NOT NULL,
  batch_status varchar(30) ,  
  analysis_status varchar(30) ,  
  alert_status varchar(30) ,  
  email_status varchar(30),
  creation_date datetime NOT NULL,
  created_by int(11) NULL,
  PRIMARY KEY (batch_seq_id),
  UNIQUE KEY xcm_batch_runs_uk (batch_id)
) {$params["charset_collate"]};" ;

return $sql;

}

function xcm_tbl_list_of_url_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_list_of_urls;";
	return $sql;
}

function xcm_tbl_list_of_url($params)
{

$sql = "CREATE TABLE IF NOT EXISTS {$params["prefix"]}xcm_list_of_urls (
  list_url_id int(11) NOT NULL AUTO_INCREMENT,
  project_id varchar(100) NOT NULL,
  site_code varchar(255) NOT NULL,
  target_url varchar(300) NOT NULL,
  target_url_hash varchar(150) NOT Null,
  url_type varchar(30) NOT NULL,
  active varchar(1) NOT NULL,
  batch_id varchar(30) ,
  batch_status varchar(30) ,  
  batch_processed_checks longtext,
  last_status_code varchar(10),
  creation_date datetime NOT NULL,
  created_by int(11) NULL,
  last_update_date datetime NOT NULL,
  last_updated_by int(11) NULL,
  notify_email varchar(100),
  PRIMARY KEY (list_url_id),
  UNIQUE KEY xcm_list_of_urls_uk (target_url_hash)
) {$params["charset_collate"]};" ;

return $sql;

}

function xcm_tbl_url_summary_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_url_summary;";
	return $sql;
}

function xcm_tbl_url_summary($params)
{
$sql = "CREATE TABLE IF NOT EXISTS  {$params["prefix"]}xcm_url_summary (
  url_summary_id int(11) NOT NULL AUTO_INCREMENT,
  list_url_id int(11),
  target_url varchar(300) NOT NULL,
  test_category varchar(30),
  test_type varchar(30),
  test_name varchar(30),  
  return_status varchar(10),
  test_status varchar(10),  
  action_status varchar(30),
  diff_status varchar(30),
  error_try_count int(11),
  text_body longtext,
  element_count int(11),
  expire_date datetime,
  batch_id varchar(30) ,
  creation_date datetime,
  test_execution_time decimal(20,10),
  PRIMARY KEY (url_summary_id),
  UNIQUE KEY xcm_url_summary_uk (list_url_id,batch_id,test_name),
  KEY xcm_url_summary_ni (batch_id)
) {$params["charset_collate"]};" ;

return $sql;

}


function xcm_tbl_url_details_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_url_details;";
	return $sql;
}

function xcm_tbl_url_details($params)
{


$sql = "CREATE TABLE IF NOT EXISTS {$params["prefix"]}xcm_url_details (
  url_detail_id int(11) NOT NULL AUTO_INCREMENT,
  url_summary_id int(11),  
  list_url_id int(11),
  page_tag_type varchar(100),
  meta_tag_name varchar(300),  
  seq_num int(11),
  text_body longtext,
  link_url varchar(300),
  link_rel varchar(200),
  link_target varchar(100),
  tag_class varchar(200),
  tag_id varchar(100), 
  tag_title varchar(300),  
  img_width varchar(10),  
  img_height varchar(10),  
  img_alt varchar(300),
  img_srcset varchar(300),
  img_sizes varchar(300),  
  creation_date datetime,
  PRIMARY KEY (url_detail_id)
) {$params["charset_collate"]};" ;

return $sql;

}

function xcm_tbl_alert_details_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_alert_details;";
	return $sql;
}

function xcm_tbl_alert_details($params)
{


$sql = "CREATE TABLE IF NOT EXISTS {$params["prefix"]}xcm_alert_details (
  alert_id int(11) NOT NULL AUTO_INCREMENT,
  list_url_id int(11),
  target_url varchar(300),
  batch_id varchar(30) ,
  prev_batch_id varchar(30) ,
  seq varchar(5),
  test_name varchar(30),  
  test_str  varchar(100),
  alert_text varchar(200),
  priority varchar(30),
  diff_status varchar(100),  
  action_status varchar(30), 
  email_status varchar(30),
  text_body longtext,  
  creation_date datetime,
  target_host varchar(150),
  text_body_hash varchar(50),
  UNIQUE KEY xcm_alert_details_uk (list_url_id,batch_id,test_name,test_str,seq),
  KEY xcm_alert_details_ni (batch_id),
  PRIMARY KEY (alert_id)
) {$params["charset_collate"]};" ;

return $sql;

}

function xcm_tbl_check_details_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_check_details;";
	return $sql;
}

function xcm_tbl_check_details($params)
{


$sql = "CREATE TABLE IF NOT EXISTS  {$params["prefix"]}xcm_check_details (
  url_check_id int(11) NOT NULL AUTO_INCREMENT,
  project_id varchar(100) NOT NULL,
  list_url_id int(11),
  notify_email varchar(100),
  check_category varchar(10),
  check_type varchar(50),
  check_name varchar(100),
  check_body varchar(300),
  check_active varchar(1),
  check_defined_by varchar(30),
  creation_date datetime,
  PRIMARY KEY (url_check_id),
  UNIQUE KEY xcm_check_details_uk (list_url_id,check_name)
) {$params["charset_collate"]};" ;

return $sql;

}

function xcm_insert_defaults_tbl_check_details($params)
{
$sql="
insert ignore into {$params["prefix"]}xcm_check_details(list_url_id,check_category,check_type,check_name,check_defined_by,creation_date,check_active)
values
(-1,'root','root','ssl_cert','default',now(),'Y'),
(-1,'root','root','ip','default',now(),'Y'),
(-1,'root','root','www_test','default',now(),'Y'),
(-1,'root','root','https_test','default',now(),'Y'),
(-1,'url','url','title','default',now(),'Y'),
(-1,'url','url','meta','default',now(),'Y'),
(-1,'url','url','link','default',now(),'Y'),
(-1,'url','url','script','default',now(),'N'),
(-1,'url','url','img','default',now(),'Y'),
(-1,'url','url','iframe','default',now(),'N'),
(-1,'url','url','style','default',now(),'N')
";

return $sql;
}

function xcm_tbl_check_lookup_delete($params)
{
	$sql="DROP TABLE IF EXISTS {$params["prefix"]}xcm_check_lkp;";
	return $sql;
}

function xcm_tbl_check_lookup($params)
{


$sql = "CREATE TABLE IF NOT EXISTS  {$params["prefix"]}xcm_check_lkp (
  check_type varchar(50),
  priority varchar(10),
  display_text varchar(100),
  UNIQUE KEY xcm_check_lkp_uk (check_type)
) {$params["charset_collate"]};" ;

return $sql;

}

function xcm_insert_defaults_tbl_check_lookup($params)
{
$sql="
insert ignore into {$params["prefix"]}xcm_check_lkp(check_type,priority,display_text)
values
('title','High','Page Title'),
('h1','High','H1 Tag'),
('h2','High','H2 Tag'),
('h3','Low','H3 Tag'),
('a','High','Anchor Link'),
('meta','High','Meta Tag'),
('link','High','Link Tag'),
('script','Low','Javascript'),
('img','Low','Image Tag'),
('iframe','Low','IFrame Tag'),
('style','Low','CSS Style Tag'),
('body','High','Body Content'),
('tag','High','Tag'),
('class','High','CSS Class'),
('xpath','High','XPATH Search'),
('regex','High','REGEX Search'),                
('string','High','Search String'),                
('ssl_cert','High','SSL Certificate'),                
('ip','High','IP DNS Record'),                
('www_test','High','WWW Redirection Test'),                
('https_test','High','HTTPS Redirection Test'),                
('robot_txt','High','ROBOT.txt File'),                
('sitemap','High','Sitemap Definition') 
";

return $sql;
}

function xcm_alter_tbl_alert_details($params)
{


$sql = "Alter table {$params["prefix"]}xcm_alert_details 
add column target_host varchar(150),
add column text_body_hash varchar(50)";

return $sql;

}

?>