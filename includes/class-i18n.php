<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.crawlspider.com
 * @since      1.0.0
 *
 * @package    Seo_Change_Monitor
 * @subpackage Seo_Change_Monitor/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Seo_Change_Monitor
 * @subpackage Seo_Change_Monitor/includes
 * @author     Nilesh Jethwa <contact@infocaptor.com>
 */
class Seo_Change_Monitor_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'seo-change-monitor',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
