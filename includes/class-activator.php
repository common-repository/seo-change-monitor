<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.crawlspider.com
 * @since      1.0.0
 *
 * @package    Seo_Change_Monitor
 * @subpackage Seo_Change_Monitor/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Seo_Change_Monitor
 * @subpackage Seo_Change_Monitor/includes
 * @author     Nilesh Jethwa <contact@infocaptor.com>
 */
class Seo_Change_Monitor_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() 
	{
		//static::setup_db();
	}

	public static function setup_db()
	{
		global $wpdb;
		$params["charset_collate"] = $wpdb->get_charset_collate();
		$params["prefix"] = $wpdb->prefix;
		require_once plugin_dir_path( __FILE__ ) ."scm_db_tables.php";
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		for ($i=0; $i<count($xcc_list_of_tables); $i++)
		{
			$sql=$xcc_list_of_tables[$i]($params);
			dbDelta($sql);	

			file_put_contents("xcc_error.txt",$sql,FILE_APPEND);
//    		return $success;
		}
	}	

}
