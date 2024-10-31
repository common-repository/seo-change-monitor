<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://www.crawlspider.com
 * @since      1.0.0
 *
 * @package    Seo_Change_Monitor
 * @subpackage Seo_Change_Monitor/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Seo_Change_Monitor
 * @subpackage Seo_Change_Monitor/includes
 * @author     Nilesh Jethwa <contact@infocaptor.com>
 */
class Seo_Change_Monitor_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() 
	{
		static::cron_deactivate();

	}

	public static function cron_deactivate() 
	{	
		 // find out when the last event was scheduled
		 $timestamp = wp_next_scheduled ('crawlspider_cscm_cron_job_hook');
		 // unschedule previous event if any
		 wp_unschedule_event ($timestamp, 'crawlspider_cscm_cron_job_hook');
	 } 

}
