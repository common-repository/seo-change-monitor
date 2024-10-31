<?php

namespace crawlspider_lite_cm;

/**
 *
 * @link              https://www.crawlspider.com
 * @since             1.4
 * @package           Seo_Change_Monitor_lite
  *
 * @wordpress-plugin
 * Plugin Name:       SEO Change Monitor Lite
 * Plugin URI:        https://www.crawlspider.com/seo-monitoring-tool-content-change-tracking/
 * Description:       Monitor and protect your SEO traffic. Get notified when your site breaks.
 * Version:           1.4
 * Requires at least: 3.3
 * Requires PHP:      5.4
 * Author:            CrawlSpider
 * Author URI:        https://www.crawlspider.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       seo-change-monitor-lite
 * Domain Path:       /languages
 */

//AUM Ganapataye Namah
// If this file is called directly, abort.
//CrawlSpider SEO Change Monitor for WordPress
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'CSCM_SEO_CHANGE_MONITOR_VERSION', '1.4' );
defined(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS') or define(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS','cscm-options-seo-monitor'); 
defined(__NAMESPACE__.'CSCM_PLUGIN_NAME') or define(__NAMESPACE__.'CSCM_PLUGIN_NAME','SEO Change Monitor Lite : Crawlspider'); 



require_once plugin_dir_path( __FILE__ ) . 'includes/class_util.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class_lic.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class_checks_tbl.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class_analysis.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class_alerts_tbl.php';


//use crawlspider_lite_cm\analysis    as analysis;

use crawlspider_lite_cm\util    as util;
use crawlspider_lite_cm\checks_tbl as checks_tbl;
use crawlspider_lite_cm\analysis as analysis;
use crawlspider_lite_cm\alerts_detail_tbl as alerts_tbl;
use crawlspider_lite_cm\lix as lix;
//util\cscm_fn::log(__FUNCTION__,0,util\cscm_fn::CSCM_TOKEN_SEP);

checks_tbl\cscm_checks_tbl::add_init_load_ajax_action();
//analysis\cscm_analysis::add_init_load_ajax_action();
alerts_tbl\cscm_alerts_detail_tbl::add_init_load_ajax_action();
lix\cscm_lx::add_init_load_ajax_action();

$cscm_phpglb=array();


// 1.1
// hint: registers all our custom shortcodes on init
//add_action('init', __NAMESPACE__.'\cscm_register_shortcodes');



/*
// hint: register ajax actions
add_action('wp_ajax_nopriv_cscm_save_subscription', 'cscm_save_subscription'); // regular website visitor
add_action('wp_ajax_cscm_save_subscription', 'cscm_save_subscription'); // admin user
*/

// 1.5
// load external files to public website
add_action('wp_enqueue_scripts', __NAMESPACE__.'\cscm_public_scripts');

//**admin scripts are loaded only if the options page of this plugin is loaded
// 1.7 
// hint: register our custom menus
add_action('admin_menu', __NAMESPACE__.'\cscm_set_config_page'); //<-- This also loads admin_enqueue_scripts
//add_action('init', __NAMESPACE__.'\cscm_set_config_page'); //<-- This also loads


// 1.9
// register plugin options
add_action('admin_init', __NAMESPACE__.'\cscm_register_options');


//cron schedules
add_filter('cron_schedules',__NAMESPACE__.'\cscm_cron_schedules');
cscm_cron_activation() ;

register_activation_hook( __FILE__, __NAMESPACE__.'\cscm_activate' );
register_deactivation_hook( __FILE__, __NAMESPACE__.'\cscm_deactivate' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ic-dataactivator.php
 */
function cscm_activate() {
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-activator.php';
	\Seo_Change_Monitor_Activator::activate();
	cscm_setup_tables();
	cscm_add_initial_set_of_urls();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ic-datadeactivator.php
 */
function cscm_deactivate() {
	
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-deactivator.php';
	\Seo_Change_Monitor_Deactivator::deactivate();
}





function cscm_set_config_page()
{


		global $cscm_ns_options;
 		//add_submenu_page( string $parent_slug, string $page_title                           , string $menu_title                                 , string $capability   , string $menu_slug                             , callable $function = '' )
		//add_options_page( $page_title, $menu_title, $capability, $menu_slug, $function);
		$cscm_admin_page = add_options_page( 'SEO Monitor Lite by CrawlSpider', 'CrawlSpider : SEO Monitor Lite', 'manage_options', 'cscm-seo-change-monitor', __NAMESPACE__.'\cscm_show_plugin_options' );
	    
		
		// Load the JS conditionally only for the  admin page of CrawlSpider
        add_action( 'load-' . $cscm_admin_page, __NAMESPACE__.'\cscm_load_onlyadmin_js' );		
		//add_action('admin_enqueue_scripts', __NAMESPACE__.'\cscm_woo_admin_scripts');
	
}

//load this only if admin page is invoked
function cscm_load_onlyadmin_js()
{
	add_action('admin_enqueue_scripts', __NAMESPACE__.'\cscm_load_admin_js_scripts');
}

function cscm_get_checks_tab()
{
	$ajax_gif='<img src="'.plugin_dir_url( __FILE__ ).'/admin/ajax-loader.gif">'  ;
	$checks_tab='
	<div id="checks-tab">
	<div class="wrap">

		<!--label for="users_can_register">
		<input name="users_can_register" type="checkbox" id="users_can_register" value="1">
			Anyone can register</label-->
			<fieldset class="cscm_fieldset">
	   
			<legend>List of Custom Checks available in the advanced version</legend>
			<ul>
				<li> Setup scan alert based on HTML tags of your choice
				<li> Setup scan alert based on HTML CSS elements of your choice
				<li> Setup scan alert using XPATH
				<li> Setup scan alert using REGEX
			</ul>
			The custom checks offer greater flexibility such as monitoring competitor price changes and website modifications.
			</fieldset>			


	</div>		
	</div><!--id="checks-tab"-->

	
	<div id="default-checks-tab">
	<div class="wrap">

			<fieldset class="cscm_fieldset">
	   
			<legend>List of Default Checks</legend>
			<div id="cscm_default_checks_tbl_parent">
			  '.$ajax_gif.'
			</div>
			</fieldset>			


	</div>		
	</div><!--id="default-checks-tab"-->	
';

return $checks_tab;

}



function cscm_get_alerts_detail_tab()
{
	$ajax_gif='<img src="'.plugin_dir_url( __FILE__ ).'/admin/ajax-loader.gif">'  ;
	$alerts_tab='
	<div id="alerts-detail-tab">
	<div class="wrap">

			<fieldset class="cscm_fieldset">
	   
			<legend>Alert Details [Please review and clear the Alerts. Use the CSV export to download the list] </legend>
			<div id="cscm_alerts_detail_tbl_parent">
			  '.$ajax_gif.'
			</div>
			</fieldset>			


	</div>		
	</div><!--id="alerts-detail-tab"-->

';

return $alerts_tab;

}



function cscm_get_alerts_tab_html()
{
	$alerts_html='
<div id="accordion1" role="tablist" aria-multiselectable="true">
</div>		  
';

	return $alerts_html;
}

function cscm_show_plugin_options()
{

	global $cscm_phpglb;

	//check if admin user
	$cscm_phpglb['my_file_ver']  = date("ymd-Gis", filemtime(  __FILE__ )  );
	$cscm_phpglb['plugin_name']=constant(__NAMESPACE__.'CSCM_PLUGIN_NAME');  

	$current_user = wp_get_current_user();
	 if (!in_array('administrator',  $current_user->roles))
	 {
		$cscm_phpglb["is_admin_role"]="N";
	 }
	 else $cscm_phpglb["is_admin_role"]="Y";

	
	$current_version=CSCM_SEO_CHANGE_MONITOR_VERSION;
	//check if all installation went fine
	//collect some basic paths and urls
	$plugin_dir_path=plugin_dir_path( __FILE__ );
	$base_file= __FILE__;
	$plugin_dir=__DIR__;
	$dir_base=basename($plugin_dir);
	$plugin_base_url=plugins_url()."/".$dir_base;
    $button_style="font-size:28px;height:56px;";
	$content_dir_path = wp_normalize_path(WP_CONTENT_DIR);
	
	

	$debug_html=" ";
	$register_html=" ";


$info_html='
<ul>
  <li><a href="https://www.crawlspider.com?x=seo-change-monitor" target="_blank">CrawlSpider Website</a>
</ul>
 
';
	 
$options_html='

<div class="accordion" id="setup_options_accordion">
  <div class="card">
    <div class="card-header" id="headingOne" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
         Database Setup   
    </div>

    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#setup_options_accordion">
      <div class="card-body">
		<h3>Database Setup</h3>
		<div class="alert alert-warning fade show " role="alert">
			Your database objects should have been already installed during plugin activation. You do not need to click on the following buttons unless instructed to. 
		</div>	<!--alert-->
		<p>Click this button to complete any unfinished Database setup</p>
		<button type="button" class="btn btn-primary"  id="cscm_start_install"> Install Database Objects </button>
		<button type="button" class="btn btn-danger"  id="cscm_start_uninstall"> Remove Database Objects </button>        
      </div>
    </div>
  </div> <!--card end-->
  
  
  <div class="card">
    <div class="card-header" id="headingTwo" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
          Manual Scan
    </div>

    <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#setup_options_accordion">
      <div class="card-body">
		<hr>
		<div class="alert alert-warning fade show " role="alert">
			A scan is already scheduled during activation. Use this if you need to run adhoc scan.
		</div>	<!--alert-->

		<button type="button" id="cscm_run_scan_now" class="btn btn-primary cscm-button">Run Manual Scan Now</button>
      </div>
    </div>
  </div> <!--card end-->
  

  
</div>
 
';

$options_tab='
<div id="options-tab">'
   .$options_html.
'</div><!--id="option-tab"-->';

$info_tab='
<div id="info-tab">'
   .$info_html
   .$register_html
	.'</div><!--id="info-tab"-->';

$alerts_html=cscm_get_alerts_tab_html();
$alerts_tab='
<div id="alerts-tab">
   <div id="change_alert_box"></div>'
   .$alerts_html.'
</div><!--id="alerts-tab"-->

';

$alerts_detail_tbl=cscm_get_alerts_detail_tab();

$checks_tab=cscm_get_checks_tab();
	
/*global $submenu;
$menu_list=print_r($submenu, true); 	*/
$config_html='
<div id="cscm-main">


<div id="result_header">

</div>

<script>
cscm_global={};


</script>
	<h2>'.util\cscm_fn::sanitize_output($cscm_phpglb['plugin_name']).' - v'.util\cscm_fn::sanitize_output($current_version).'</h2>
	
</div><!--id="cscm-main"-->
	
<br class="clear" />
<!-- Modal -->
<div class="modal fade" id="lxstatus_modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lxstatus_modalTitle">Modal title</h5>
        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="lxstatus_modalbody">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!--LX alert-->
<div style="display:none;" class="alert alert-warning fade show lxstatus_alert" role="alert">
	<div class="lxmessg"></div>
</div>	<!--alert-->



<div id="cscm-tabs">
  <ul>
    
    <li><a href="#setup-tab">URL Details</a></li>

	<li><a href="#checks-tab">Custom Checks</a></li>
	<li><a href="#default-checks-tab">Default Checks</a></li>
	<!--li><a href="#alerts-tab">Alert Events</a></li-->
	<li><a href="#alerts-detail-tab">Alert Details</a></li>
	<li><a href="#options-tab">Setup Options</a></li>
	<li><a href="#info-tab">About</a></li>
  </ul>
  
  
<br class="clear" />
';

    util\cscm_fn::display_output($config_html);	
	
	
	cscm_options_admin_page(); //echo the admin settings
	util\cscm_fn::display_output($info_tab);	
	util\cscm_fn::display_output($checks_tab);
	//echo $alerts_tab;
	util\cscm_fn::display_output($alerts_detail_tbl);
	util\cscm_fn::display_output($options_tab);
	util\cscm_fn::display_output( "</div> <!--cscm-tabs-->"); //end all the tabs wrapper
}




function cscm_load_admin_js_scripts() 
{
	$dir_base=basename(__DIR__);
	$plugin_base_url=plugins_url()."/".$dir_base;
  
	wp_enqueue_style('jquery-ui-css-style',$plugin_base_url. '/admin/css/jquery-ui.min.css', array(), false, 'screen');	

	//wp_enqueue_style( 'jquery-ui-css-style' );	
	wp_enqueue_style('cscm-admin-ui', $plugin_base_url. '/admin/css/admin.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');	

	
	wp_enqueue_script('jquery-ui-datepicker', '', array('jquery-ui-core', 'jquery'));
	wp_enqueue_script('jquery-ui-tabs', '', array('jquery-ui-core', 'jquery'));
	wp_enqueue_script('jquery-ui-dialog', '', array('jquery-ui-core', 'jquery'));
	wp_enqueue_script('jquery-ui-button', '', array('jquery-ui-core', 'jquery'));
	wp_enqueue_script('jquery-ui-sortable', '', array('jquery-ui-core', 'jquery'));

	/////copied from alteditor
	
	wp_enqueue_style('cscm_bootstrap_css', $plugin_base_url. '/admin/css/other/bootstrap-5.1.3.min.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		
	//default datatable theme

	wp_enqueue_style('cscm_datatables_css', $plugin_base_url. '/admin/css/other/jquery.dataTables.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		

	wp_enqueue_style('cscm_bootstrapicons_css', $plugin_base_url. '/admin/css/other/bootstrap-icons-1.8.3/bootstrap-icons.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		
	
	wp_enqueue_style('cscm_fontawesomeicons_css', $plugin_base_url. '/admin/css/other/font-awesome.min.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		

	
	wp_enqueue_style('cscm_datatables_button_css', $plugin_base_url. '/admin/css/other/buttons.jqueryui.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		


	wp_enqueue_style('cscm_datatables_select_css', $plugin_base_url. '/admin/css/other/select.dataTables.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		


	wp_enqueue_style('cscm_datatables_responsive_css', $plugin_base_url. '/admin/css/other/responsive.dataTables.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION, array(), false, 'screen');		
	
	wp_register_script('cscm_datatables_js',  plugins_url('/admin/js/other/jquery.dataTables.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_js');
	
	wp_register_script('cscm_datatables_button_js', plugins_url('/admin/js/other/dataTables.buttons.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_button_js');
	
	wp_register_script('cscm_datatables_jszip_js', plugins_url('/admin/js/other/jszip.min.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_jszip_js');	

	wp_register_script('cscm_datatables_pdfmake_js',  plugins_url('/admin/js/other/pdfmake.min.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_pdfmake_js');	
	
	wp_register_script('cscm_datatables_vfs_fonts_js', plugins_url('/admin/js/other/vfs_fonts.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_vfs_fonts_js');		
	
	

	wp_register_script('cscm_datatables_button_print_js', plugins_url('/admin/js/other/buttons.print.min.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_button_print_js');

	wp_register_script('cscm_datatables_button_html5_js',  plugins_url('/admin/js/other/buttons.html5.min.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_button_html5_js');	
	

	wp_register_script('cscm_datatables_select_js',  plugins_url('/admin/js/other/dataTables.select.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_select_js');
	
	wp_register_script('cscm_bootstrap_js', plugins_url('/admin/js/other/bootstrap-5.1.3.bundle.min.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_bootstrap_js');
	
	wp_register_script('cscm_datatables_responsive_js', plugins_url('/admin/js/other/dataTables.responsive.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__) , array('jquery'),'',true);
	wp_enqueue_script('cscm_datatables_responsive_js');

	wp_register_script('cscm_alteditor_ajax', plugins_url('/admin/js/dataTables.altEditor.free.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_alteditor_ajax');
	/////copied from alteditor	

	//following is working datatable setup

	wp_register_script('cscm_admin_ajax', plugins_url('/admin/js/admin.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
	wp_enqueue_script('cscm_admin_ajax');
	wp_localize_script( 'cscm_admin_ajax', 'ajax_object',
            array(
				 'ajax_url' => admin_url( 'admin-ajax.php' )
				, 'ajax_gif' => plugin_dir_url( __FILE__ ).'/admin/ajax-loader.gif') );
	
}

function cscm_get_default_columns()
{
	//list all the columns the user has option to select from. This is what will be shown in the draggable selection
	$default_columns=array(
		'ID' => array ("title" =>"ID"),
		'post_author' => array ("title" =>"Author"),
		'post_date' => array ("title" =>"Date"),
		'post_title' => array ("title" =>"Topic"),
		'post_category' => array ("title" =>"Category"),
		'post_tag' => array ("title" =>"Tag"),
		'post_content' => array ("title" =>"Content"),
		'post_excerpt' => array ("title" =>"Summary"),
		'post_modified' => array ("title" =>"Modified"),
		'comment_count' => array ("title" =>"Comment #")
	);

	return $default_columns;

}

function cscm_get_initial_column_set()
{
	$init_col_set=array("ID","post_title","post_date","post_author","post_category");
	return $init_col_set;
}

function cscm_get_initial_columns()
{
	$available_columns=cscm_get_default_columns();
	$initial_set=cscm_get_initial_column_set();
	//select a list of columns to show as default selection to the user
	for ($ic=0;$ic<count($initial_set); $ic++)
	{
		$initial_columns[$initial_set[$ic]]=$available_columns[$initial_set[$ic]];	
	}
	return $initial_columns;
}

function cscm_get_default_column_property()
{

	
	return json_encode(cscm_get_default_columns(), JSON_PRETTY_PRINT);

}

// 6.9
// hint: returns the requested page option value or it's default
function cscm_get_option( $option_name ) 
{
	
	// setup return variable
	$option_value = '';	
	
	
	try 
	{
		
		// get default option values
		// get the requested option
		switch( $option_name ) 
		{
			
			case 'cscm_table_shortcode':
				//$option_value = (get_option('cscm_etl_start_date')) ? get_option('cscm_etl_start_date') : date('Y-m-d', strtotime('-2 year'));
				$option_value = get_option('cscm_table_shortcode');
				if (!$option_value)
				{
					$option_value="[crawlspider_table id=101 cols=ID,post_author,post_date,post_title,post_category]";
					update_option('cscm_table_shortcode', $option_value);
				}
				break;
			case 'cscm_list_of_urls':			
				
				$option_value = get_option('cscm_list_of_urls');
				util\cscm_fn::log(__FUNCTION__,0," get_option('cscm_list_of_urls') = ".$option_value);
				
				if (!$option_value)
				{
					$option_value= cscm_get_default_column_property();
					util\cscm_fn::log(__FUNCTION__,0," ".__LINE__." Failed get_option('cscm_list_of_urls') = ".$option_value);
					update_option('cscm_list_of_urls', $option_value);
				}	
							
			break;
			case 'cscm_header_style':			
			
				$option_value = get_option('cscm_header_style');
				if (!$option_value)
				{
					$option_value='background-color:white;color:black;';
					update_option('cscm_header_style', $option_value);
				}				
			break;
			case 'cscm_content_length':			
		
				$option_value = get_option('cscm_content_length');
				if (!$option_value)
				{
					$option_value=30;
					update_option('cscm_content_length', $option_value);
				}				
			break;
			case 'cscm_excerpt_length':			
	
				$option_value = get_option('cscm_excerpt_length');
				if (!$option_value)
				{
					$option_value=30;
					update_option('cscm_excerpt_length', $option_value);
				}				
			break;																
			case 'cscm_table_seq':			
	
				$option_value = get_option('cscm_table_seq');
				if (!$option_value)
				{
					$option_value=101;
					update_option('cscm_table_seq', $option_value);
				}				
			break;				

			
		}
		
	} catch( Exception $e) {
		
		// php error
		
	}
	
	// return option value or it's default
	return $option_value;
	
}


// hint: get's the current options and returns values in associative array
function cscm_get_current_options() {
	
	// setup our return variable
	$current_options = array();
	
	try {
	
		// build our current options associative array
		$current_options = array(
			'cscm_table_shortcode'  => esc_attr(cscm_get_option('cscm_table_shortcode')),
			'cscm_header_style' 	=> esc_attr(cscm_get_option('cscm_header_style')),
			'cscm_content_length' 	=> cscm_get_option('cscm_content_length'),
			'cscm_excerpt_length' 	=> cscm_get_option('cscm_excerpt_length'),
			'cscm_list_of_urls' 	=> cscm_get_option('cscm_list_of_urls'),
			'cscm_table_seq' 		=> cscm_get_option('cscm_table_seq')
			
		);
	
	} catch( Exception $e ) {
		
		// php error
	
	}
	
	// return current options
	return $current_options;
	
}


// hint: registers all our plugin options
function cscm_register_options() 
{
	// plugin options: The group name should be matching the menu slug 
	register_setting(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'), 'cscm_table_shortcode');
	register_setting(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'), 'cscm_header_style');
	register_setting(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'), 'cscm_content_length');
	register_setting(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'), 'cscm_excerpt_length');
	register_setting(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'), 'cscm_list_of_urls');
	register_setting(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'), 'cscm_table_seq');	
	

}

function cscm_get_column_property_array()
{
	try
	{
		util\cscm_fn::log(__FUNCTION__,0," TRY section ");
		$col_property=cscm_get_option('cscm_list_of_urls');
		util\cscm_fn::log(__FUNCTION__,0," TRY section value =".$col_property);
	}
	catch (Exception $e) //JSON messed up so switch to default values
	{
		util\cscm_fn::log(__FUNCTION__,0," exception = ");
		$col_property=cscm_get_default_column_property(); //
		util\cscm_fn::log(__FUNCTION__,0," exception  value =".$col_property);
	}

	return json_decode($col_property,true);

}


function cscm_get_col_selector_ui()
{
	return;
}



// hint: plugin options admin page
function cscm_options_admin_page() 
{
	global $cscm_phpglb;
	
	// get the default values for our options
//	$options = cscm_get_current_options();
	//$table_html=cscm_display_urls();
	//NOTE add restriction for non admin roles $cscm_phpglb["is_admin_role"]="Y";
	
	//NOTE: Make sure any new options are added in the activator with default values
	util\cscm_fn::display_output('<div id="setup-tab"> <!--SETUP TAB BEGIN-->');
	//echo cscm_get_col_selector_ui();	
	
	util\cscm_fn::display_output('<form action="options.php" id="cscm_settings_form" method="post">');
		
		
			util\cscm_fn::log(__FUNCTION__,0,"option value =".constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'));
			
			// outputs a unique nounce for our plugin options
			settings_fields(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'));
			// generates a unique hidden field with our form handling url
			//@do_settings_fields(constant(__NAMESPACE__.'CSCM_PLUGIN_OPTIONS'));
			$cscm_nonce_security=wp_nonce_field( 'cscm_crawlspider_nonce_security', 'cscm_crawlspider_nonce_security' );
			util\cscm_fn::display_output('<table class="form-table">
			
				<tbody>


					<tr>
						<!--th scope="row"><label for="cscm_list_of_urls" class="cscm-input-label">Column Properties </label></th-->
						<td>

						<p><strong>Bulk URLs : Enter urls, one on each line</strong></p>
							<textarea  id="cscm_list_of_urls" name="cscm_list_of_urls" rows="5" style="width:70%;font-family:Courier New;" placeholder="e.g https://www.crawlspider.com"></textarea>
							<p><button type="button" id="cscm_save_settings" class="btn btn-primary cscm-button">Add URLs to Monitor</button><!--button type="button" id="cscm_add_post_urls" class="btn btn-primary cscm-button">Add Post URLs</button> <button type="button" id="cscm_add_page_urls" class="btn btn-primary cscm-button">Add Page URLs</button--> 
							<span id="cscm_add_url_status" style="font-size: 90%; font-style: italic; color: steelblue; display: none;">URLs are added. Please save them</span>
							</p>
						</td>
					</tr>
							

			
				</tbody>
				
			  </table>
			  <!--button type="button" id="cscm_save_settings" class="btn btn-primary cscm-button">Add URLs to Monitor</button-->
			  
			  <span id="cscm_save_status" style="font-size: 90%; font-style: italic; color: steelblue; display: none;">Settings are saved</span>
			  <p></p>
			');
		
			// outputs the WP submit button html
			//@submit_button(); 
		
	   util\cscm_fn::display_output('	  
	   </form>
	   <div>
	   <!--button type="button" id="cscm_toggle_url" class="btn btn-primary cscm-button">Toggle URL Status</button>
	   <button type="button" id="cscm_delete_url" class="btn btn-primary cscm-button">Delete URL</button-->
		<br>
	   </div>
	   <fieldset class="cscm_fieldset">
	   
	   <legend>List of Domains and URLs</legend>
	   <div id="cscm_list_of_urls_tbl_parent">
	 	<img src="'.plugin_dir_url( __FILE__ ).'/admin/ajax-loader.gif">  
	   </div>
	   </fieldset>
	</div><!--id="setup-tab"-->
	');
	
}



function cscm_public_scripts()
{
//wp_enqueue_style( 'jquery-ui-css-style' );	
//	wp_enqueue_style('cscm-admin-ui', plugins_url('/admin/css/ic-dataadmin.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array(), false, 'screen');	

wp_enqueue_style('cscm_css_public', plugins_url('/admin/css/public.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array(), false, 'screen');	
wp_enqueue_style('cscm_datatables_css_public', plugins_url('/admin/js/datatables/datatables.min.css?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array(), false, 'screen');	
wp_register_script('cscm_datatables_js_public', plugins_url('/admin/js/datatables/datatables.min.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
wp_enqueue_script('cscm_datatables_js_public');
//wp_register_script('cscm_table_builder_public', plugins_url('/admin/js/ic-datapublic.js?v='.CSCM_SEO_CHANGE_MONITOR_VERSION,__FILE__), array('jquery'),'',true);
//wp_enqueue_script('cscm_table_builder_public');

}

/*
////test
add_action( 'admin_enqueue_scripts',  __NAMESPACE__.'\my_enqueue' );
function my_enqueue($hook) 
{
	if ($hook!="settings_page_cscm-seo-change-monitor") return;
        
	wp_enqueue_script( 'ajax-script', plugins_url( '/admin/js/admin.js', __FILE__ ), array('jquery') );

	// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
	wp_localize_script( 'ajax-script', 'ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'we_value' => 1234,'hook' =>$hook ) );
}
*/

// Same handler function...
add_action( 'wp_ajax_setup_tables', __NAMESPACE__.'\cscm_install_db_objects' );
function cscm_install_db_objects() {
	global $wpdb;
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

		
	$output=__NAMESPACE__.'\cscm_setup_tables';
        util\cscm_fn::display_output( $output()); 
	wp_die();
}

add_action( 'wp_ajax_remove_tables', __NAMESPACE__.'\cscm_uninstall_db_objects' );
function cscm_uninstall_db_objects() {
	global $wpdb;
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

	
	$output=__NAMESPACE__.'\cscm_uninstall_tables';
        util\cscm_fn::display_output( $output()); 
	wp_die();
}

add_action( 'wp_ajax_get_post_urls', __NAMESPACE__.'\cscm_get_post_urls' );
function cscm_get_post_urls() 
{
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

	
	global $wpdb;
	//$urls =  $_POST['whatever'] ;
	$urls=cscm_get_link_details('post',10);
	$url_output=implode("\r\n",$urls);
	util\cscm_fn::display_output( $url_output);
	wp_die();
}

add_action( 'wp_ajax_get_page_urls', __NAMESPACE__.'\cscm_get_page_urls' );
function cscm_get_page_urls() 
{
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));	

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

	
	global $wpdb;
	//$urls =  $_POST['whatever'] ;
	$urls=cscm_get_link_details('page',10);
	$url_output=implode("\r\n",$urls);
	util\cscm_fn::display_output( $url_output);
	wp_die();
}

add_action( 'wp_ajax_display_list_of_urls_tbl', __NAMESPACE__.'\cscm_display_list_of_urls_tbl' );
function cscm_display_list_of_urls_tbl() 
{
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));	

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		



	$table_def=cscm_get_table_def("cscm_list_of_urls_tbl");
	//print_r(cscm_get_sql_result($table_def["sql"]));
	//wp_die;
	//return;
	//$table_html=cscm_build_table_structure("cscm_list_of_urls_tbl",$table_def);
	$table_data= util\cscm_fn::build_table_structure("cscm_list_of_urls_tbl",$table_def);
	util\cscm_fn::display_output( json_encode($table_data));
	wp_die();

}

add_action( 'wp_ajax_edit_list_of_urls_tbl', __NAMESPACE__.'\cscm_edit_list_of_urls_tbl' );

function cscm_edit_list_of_urls_tbl()
{
	//$url_jsonstr=$POST["form_data"];
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));	

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

	
	$url_data = json_decode(stripslashes($_POST['form_data']),true);
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));
	util\cscm_fn::log(__FUNCTION__,5,"URL_DATA =".print_r($url_data,true));
	//sanitize after decoded json structure. The json contains ID , ID is numeric
	for ($i=0;$i<count($url_data);$i++)
	{
		$url_data[$i][0]=sanitize_key($url_data[$i][0]);
		$url_data[$i][2]=sanitize_text_field($url_data[$i][2]);
		$url_data[$i][3]=sanitize_text_field($url_data[$i][3]);
		$url_data[$i][4]=sanitize_email($url_data[$i][4]);
	}
	
	$form_action=stripslashes(sanitize_text_field($_POST['form_action']));
	$url_id_list=array_column($url_data, 0); //first index is the id of the url
	$url_id_str=addslashes(implode(",",$url_id_list));


	
	
	
	global $wpdb;

	if ($form_action=="toggle_status")
	{
		
		$lou_tbl_sql="update {$wpdb->prefix}xcm_list_of_urls
					set active= case 
					when active='Y' then 'N' 
					when active='N' then 'Y'
					end
		where list_url_id in ({$url_id_str})
		";
		util\cscm_fn::log(__FUNCTION__,5,$lou_tbl_sql);
		$update_results=$wpdb->get_results($lou_tbl_sql);
		
	}
	else if ($form_action=="edit")
	{
		//sanitize
		if ($url_data[0][3]!='Y') $url_data[0][3]='N';
		/*	
		$lou_tbl_sql="update {$wpdb->prefix}xcm_list_of_urls
					set active= '{$url_data[0][3]}'
					, notify_email='{$url_data[0][4]}'
		where list_url_id in ({$url_data[0][0]})
		";
		*/
		$lou_tbl_sql="update {$wpdb->prefix}xcm_list_of_urls
					set active= %s
					, notify_email=%s
		where list_url_id in (%d)
		";		
		util\cscm_fn::log(__FUNCTION__,5,"url_data[0][4] =".$url_data[0][4]);
		$lou_tbl_sql_prep=$wpdb->prepare($lou_tbl_sql,$url_data[0][3],$url_data[0][4],$url_data[0][0]);
	
		
		util\cscm_fn::log(__FUNCTION__,5,$lou_tbl_sql_prep);
		$update_results=$wpdb->get_results($lou_tbl_sql_prep);
		
	}	
	else if ($form_action=="delete")
	{
		$lou_tbl_sql="delete from {$wpdb->prefix}xcm_list_of_urls
		where list_url_id in ({$url_id_str})
		";
		util\cscm_fn::log(__FUNCTION__,5,$lou_tbl_sql);
		$update_results=$wpdb->get_results($lou_tbl_sql);
	}
	else if ($form_action=="add")
	{
		$url_details=cscm_get_input_url_details($url_data[0][1],$url_data[0][4]);
		$insert_value_str=$url_details["row_str"];
		$lou_tbl_sql=cscm_lou_tbl_get_insert_sql($insert_value_str);
		$update_results=$wpdb->get_results($lou_tbl_sql);
	}
	else if ($form_action=="list_active_urls")
	{
		$table_def=cscm_get_table_def("cscm_list_of_urls_tbl");
		$table_def["sql"].=" 
		where active='Y'";
		$table_data =  cscm_get_sql_result($table_def["sql"]);
		util\cscm_fn::display_output( json_encode($table_data));
		
		wp_die();
		return;

	}

	
	$table_data=cscm_get_table_data("cscm_list_of_urls_tbl");
    util\cscm_fn::display_output( json_encode($table_data));
	
	wp_die();
}


add_action( 'wp_ajax_delete_urls_tbl', __NAMESPACE__.'\cscm_delete_urls_tbl' );

function cscm_delete_urls_tbl()
{
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));	

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

	
	//$url_jsonstr=$POST["form_data"];
	$url_data = json_decode(stripslashes(sanitize_textarea_field($_POST['form_data'])),true);
	
	//sanitize after decoded json structure. The json contains ID and the URL, ID is numeric
	for ($i=0;$i<count($url_data);$i++)
	{
		$url_data[$i][0]=sanitize_key($url_data[$i][0]);
		$url_data[$i][1]=esc_url_raw(($url_data[$i][1]));
		$url_data[$i][3]=sanitize_text_field($url_data[$i][3]);
		$url_data[$i][4]=sanitize_email($url_data[$i][4]);
	}
	
	$url_id_list=array_column($url_data, 0); //first index is the id of the url
	$url_id_str=implode(",",$url_id_list);

	//util\cscm_fn::log(__FUNCTION__,0,print_r($url_data,true));
	global $wpdb;

	$toggle_sql="delete from {$wpdb->prefix}xcm_list_of_urls
	where list_url_id in ({$url_id_str})
	";
	$update_results=$wpdb->get_results($toggle_sql);

	$table_data=cscm_get_table_data("cscm_list_of_urls_tbl");
    util\cscm_fn::display_output( json_encode($table_data));
	//echo $url_data[0][0];
	wp_die();
}


function cscm_get_table_data($table_name)
{
	$table_args=cscm_get_table_def($table_name);
	$result_set =  cscm_get_sql_result($table_args["sql"]);
	return $result_set;
}

//list of urls table insert str
function cscm_lou_tbl_get_insert_value_str($textarea_urls)
{
	$urls=preg_split('/\r\n|\n|\r/', $textarea_urls);
	$insert_value_str="";
	$current_row_str="";
	for ($u=0; $u<count($urls); $u++)
	{
		$url_str_parts=explode(",",$urls[$u]);
		$url_str=$url_str_parts[0];
		if (!empty($url_str_parts[1])) $notify_email=$url_str_parts[1];
		else $notify_email="";

		$url_details=cscm_get_input_url_details($url_str,$notify_email);
		if ($u<count($urls)-1) $url_details["row_str"].=","; //separate each value statement

		$insert_value_str.=$url_details["row_str"];
	}

	return $insert_value_str;
	
}

function cscm_lou_tbl_get_insert_sql($insert_value_str)
{
	global $wpdb;
	$url_table=$wpdb->prefix."xcm_list_of_urls";

	$table_columns="(project_id,site_code,target_url,target_url_hash,url_type,active,creation_date,last_update_date,notify_email)";

	$insert_stmt_sql="INSERT IGNORE INTO {$url_table}
	{$table_columns}
	VALUES
	{$insert_value_str}";

	return $insert_stmt_sql;
}

function cscm_get_input_url_details($url_str,$notify_email)
{
	$url_details=[];
	$url_str=rtrim($url_str,"/");
	$url_parts=parse_url($url_str);

	if (empty($url_parts["path"])) $url_type="root"; // if no path then treat it like root domain
	else $url_type="url";

	util\cscm_fn::log(__FUNCTION__,0,print_r($url_parts,true));
	//check if scheme exists, if not then default it
	if (empty($url_parts['scheme'])) $url_str="https://".$url_str;
	
	$url_str=esc_url_raw($url_str);
	
	$target_url_hash=hash('md5',$url_str);	
	$url_details['url_str']=$url_str;
	$url_details['target_url_hash']=$target_url_hash;
	$url_details['url_type']=$url_type;

	$url_str=addslashes($url_str);
	$notify_email=addslashes($notify_email);

	$row_str="(0,0,'{$url_str}','{$target_url_hash}','{$url_type}','Y',now(),now(),'{$notify_email}')";
	$url_details['row_str']=$row_str;
	util\cscm_fn::log(__FUNCTION__,0,print_r($url_details,true));
	return $url_details;

}


add_action( 'wp_ajax_save_option_settings', __NAMESPACE__.'\cscm_save_option_settings' );
function cscm_save_option_settings() 
{
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));	
	global $wpdb;

	$params = array();
	//echo $_POST['form_data'];
	util\cscm_fn::log(__FUNCTION__,0,print_r($_POST,true));
	parse_str($_POST['form_data'], $params);
	//echo $params['cscm_list_of_urls'];
	//update_option('cscm_list_of_urls',$params['cscm_list_of_urls'] );
	//$urls=preg_split('/\r\n|\n|\r/', $params['cscm_list_of_urls']);
	$insert_value_str=cscm_lou_tbl_get_insert_value_str($params['cscm_list_of_urls']);
	$insert_stmt_sql=cscm_lou_tbl_get_insert_sql($insert_value_str);

	util\cscm_fn::log(__FUNCTION__,0,"============== Insert Stmt Begin ===========");
	util\cscm_fn::log(__FUNCTION__,0,$insert_stmt_sql);
	util\cscm_fn::log(__FUNCTION__,0,"============== Insert Stmt End ===========");
	$wpdb->query($insert_stmt_sql);
	$error_warning="Warning: ".print_r($wpdb->get_results( 'SHOW WARNINGS;' ),true)." - Error: ".$wpdb->last_error;
	util\cscm_fn::display_output( $error_warning);

	wp_die();
}

add_action( 'wp_ajax_run_scan_now', __NAMESPACE__.'\cscm_run_scan_now' );
function cscm_run_scan_now() 
{
	util\cscm_fn::log(__FUNCTION__,5,print_r($_POST,true));
	global $wpdb;
	//$url_sql="select list_url_id,target_url,target_url_hash,url_type from {$wpdb->prefix}xcm_list_of_urls where active='Y'";
	//$results=$wpdb->get_results($url_sql);
	$return_str="";
	require_once plugin_dir_path( __FILE__ ) . 'includes/checks_lib.php';
	$batch_dtls=cscm_get_batch_details();
	$batch_id=$batch_dtls["batch_id"];
	if (!isset($batch_id))
	{
		util\cscm_fn::log(__FUNCTION__,0,"*** ERROR - Batch id is null. Cannot run any scans ***");		
		return;

	}

	util\cscm_fn::log(__FUNCTION__,0,"#########################>  RUN SCAN BEGIN  <#######################");	
	util\cscm_fn::log(__FUNCTION__,0,print_r($batch_dtls,true));

    if ($batch_dtls["batch_status"]!="snapshot_complete")
	{
		util\cscm_fn::log(__FUNCTION__,0,$batch_id." Snapshot collection");
		cscm_get_snapshot_for_urls($batch_dtls);
	}
	
	if ($batch_dtls["analysis_status"]!="complete")	
	{
		util\cscm_fn::log(__FUNCTION__,0,$batch_id." Analysis and doing diff checks");
		require_once plugin_dir_path( __FILE__ ) . 'includes/class_analysis.php';
		analysis\cscm_analysis::process_diff($batch_dtls);
		
	}
	
	if ($batch_dtls["alert_status"]!="complete")	
	{
		util\cscm_fn::log(__FUNCTION__,0,$batch_id." Sending Alerts");
		analysis\cscm_analysis::send_alerts_by_batch($batch_dtls);
	}	
	
}

// create a scheduled event (if it does not exist already)
function cscm_cron_activation() 
{
		
	add_action('crawlspider_cscm_cron_job_hook',__NAMESPACE__.'\cscm_scan_monitor_schedule'); //associate the cron_job to the actual function that will run every day/hour

	if( !wp_next_scheduled( 'crawlspider_cscm_cron_job_hook' ) ) //check if cron hook is defined, if defined it will send the next run time. if not defined then define a new schedule
	{  
	   
	   wp_schedule_event( time()  ,'5min', 'crawlspider_cscm_cron_job_hook' );	
	}

}


function cscm_scan_monitor_schedule() //called by infocaptor_cron_job_hook via wp cron
{
	//function to call the stored proc
	util\cscm_fn::log(__FUNCTION__,0,"<----------------> Running from Cron <-------------->");
	cscm_run_scan_now() ;

}

function cscm_cron_schedules($schedules){
    if(!isset($schedules["5min"])){
        $schedules["5min"] = array(
            'interval' => 5*60,
            'display' => __('Once every 5 minutes'));
    }
    if(!isset($schedules["30min"])){
        $schedules["30min"] = array(
            'interval' => 30*60,
            'display' => __('Once every 30 minutes'));
    }
    return $schedules;
}


function cscm_get_snapshot_for_urls($batch_dtls)
{
	$batch_id=$batch_dtls["batch_id"];
	//cscm_reset_url_batch_status($batch_id); //if the urls were partially complete or inprogress then reset to not_started
	$results=cscm_get_urls_for_processing($batch_id);
	
	for($i=0;$i<count($results); $i++)
	//for($i=0;$i<1; $i++)
	{
		$target_url=$results[$i]->target_url;
		$target_url_parts=parse_url($target_url);
		
		
		$html_str=cscm_get_html_content($results[$i],$target_url_parts,$batch_id);
		if (!$html_str)
		{
			util\cscm_fn::log(__FUNCTION__,0,"skipping due to error :".$target_url);
			continue;
		}
		$dom=cscm_get_dom_doc($html_str);
	//		cscm_collect_content($results[$i],$target_url_parts,$batch_id);
		
		cscm_start_scan($results[$i],$target_url_parts,$batch_id,$dom,$html_str);

		//$return_str=cscm_scan_and_save($results[$i],$target_url_parts,$batch_id);
	}
	//update_option('cscm_last_batch_status', 'scan_complete');
	cscm_update_batch($batch_id,'snapshot_complete');
	//echo "Snapshot Complete - Batch :".$batch_id;

}


function cscm_get_batch_details()
{
	global $wpdb;
	//usually a scan will run once per day
	//smallest scan will be every hour
	//on demand scan will always work within the hour granularity
	$get_new_batch_id=false;
	//$batch_id = get_option('cscm_last_batch_id');
	//$batch_status = get_option('cscm_last_batch_status');
	$batch_dtls=array();

	$batch_sql="SELECT TIMESTAMPDIFF(MINUTE, r.creation_date, now()) minutes_passed, r.* FROM {$wpdb->prefix}xcm_batch_runs r
	order by r.batch_seq_id desc
	limit 2";
	$batch_results=$wpdb->get_results($batch_sql);
	$batch_cnt=count($batch_results);
	if ($batch_cnt> 0)
	{
		//$get_new_batch_id=false;
		$batch_id=$batch_results[0]->batch_id;
		$batch_status=$batch_results[0]->batch_status;
		$batch_dtls["batch_id"]	= $batch_id;
		$batch_dtls["batch_status"]	= $batch_status;
		if ($batch_cnt>1) 
		{
			$batch_dtls["prev_batch_id"]=$batch_results[1]->batch_id;
			$batch_dtls["prev_batch_status"]=$batch_results[1]->batch_status;
		}	
		else $batch_dtls["prev_batch_id"]="null";

		if (empty($batch_results[0]->analysis_status)) $batch_dtls["analysis_status"]="null";
		else $batch_dtls["analysis_status"]	= $batch_results[0]->analysis_status;

		if (empty($batch_results[0]->alert_status)) $batch_dtls["alert_status"]="null";
		else $batch_dtls["alert_status"]	= $batch_results[0]->alert_status;
		
		if ($batch_status=="snapshot_complete" 
			&& $batch_dtls["analysis_status"]=="complete"
			&& $batch_dtls["alert_status"]=="complete"
			) 
			$get_new_batch_id=true; //if the last scan completed fully by going through all the urls then it will be marked as 'scan_complete' so now get a new batch id
	}
	else
	{
		$get_new_batch_id=true;
	}

	//if we determined the previous batch completed all tasks, we check if it is due time to run another batch
	if (($batch_cnt> 0)&&($get_new_batch_id && $batch_results[0]->minutes_passed < util\cscm_fn::CSCM_BATCH_FREQ_MINS))
	{
		//not ready so we do not create new batch
		$get_new_batch_id=false;
		util\cscm_fn::log(__FUNCTION__,0,"**No need to run a new scan** minutes passed {$batch_results[0]->minutes_passed} , current Freq in minutes = ".util\cscm_fn::CSCM_BATCH_FREQ_MINS);
	}
/*	
	if (!$batch_status) $batch_status="not_started"; //whatever is the batchid, the scan was killed before it could start or record anything
	
	if (!$batch_id) $get_new_batch_id=true;  //if the scan is running for the first time then generate new batch id
*/	
	
	//else return $batch_id; //if the last scan was aborted or only partially completed then return the last batch id

	if ($get_new_batch_id)
	{
		$batch_id=date(util\cscm_fn::CSCM_BATCH_FORMAT);
		$batch_status="not_started";
		$batch_dtls["batch_id"]	= $batch_id;
		$batch_dtls["batch_status"]	= $batch_status;		
		//update_option('cscm_last_batch_id', $batch_id);
		//update_option('cscm_last_batch_status', 'not_started');
		try
		{

			$url_sql="insert into {$wpdb->prefix}xcm_batch_runs (batch_id,batch_status , creation_date,created_by) 
			values ('{$batch_id}','{$batch_status}',now(),0)
			ON DUPLICATE KEY UPDATE batch_status='{$batch_status}'
			";
			
			$results=$wpdb->get_results($url_sql); 
	
			//now mark the URL with the batch and status 
			//since this is a new batch we reset all URLs
			//6/21/21 : Added to condition to skip completed URLs. If a new urls are added after the batch just finished and 
			//batch_id is still in the same time window, then we skip the ones that are already processed
			$update_sql="update  {$wpdb->prefix}xcm_list_of_urls x
			set  x.batch_status='not_started'		
			, x.batch_processed_checks=null
			where x.active='Y'
			and not exists            
            ( select 1 
              from (select y.list_url_id 
				 from  {$wpdb->prefix}xcm_list_of_urls as y
				 where y.batch_id='{$batch_id}'
				 and y.batch_status='complete') as z
                 where z.list_url_id=x.list_url_id) 
			";
			util\cscm_fn::log(__FUNCTION__,0,$update_sql);
			$results=$wpdb->get_results($update_sql);	
	
		}
		catch (Exception $e) 
		{
			util\cscm_fn::log(__FUNCTION__,0,"error insert");
		}  

		return $batch_dtls;
	}
	else 
	{
		util\cscm_fn::log(__FUNCTION__,0," Marking any new urls added since last run ");
		//we need to see if there are new urls added after the batch is completed. Since the next batch will run 24hrs later we need 
		//to take care of urls and let the user run a manual scan

		$new_urls="select count(*) new_cnt from {$wpdb->prefix}xcm_list_of_urls x
		where x.active='Y'
		and x.batch_id is null
		and x.batch_status is null";
		$results=$wpdb->get_results($new_urls); 
		util\cscm_fn::log(__FUNCTION__,0,print_r($results,true));
		if ($results[0]->new_cnt==0)
		{
			//there are no new urls to process so return the batch as it is
			return $batch_dtls;
		}

		$updt_batch_sql="update {$wpdb->prefix}xcm_batch_runs
		set batch_status='pending'
		,analysis_status='pending'
		,alert_status='pending'
		where batch_id='{$batch_dtls["batch_id"]}'
		";

		$results=$wpdb->get_results($updt_batch_sql); 
		if ( strlen($wpdb->last_error)>1)
			util\cscm_fn::log(__FUNCTION__,0,$wpdb->last_error);

		//now touch the batch records so that it can kick off a scan for the new urls
		$batch_dtls["batch_status"]="pending"; 
		$batch_dtls["analysis_status"]="pending";
		$batch_dtls["alert_status"]="pending";
		return $batch_dtls;
	}
}





function cscm_setup_tables()
{
	global $wpdb;
	$params["charset_collate"] = " ";//$wpdb->get_charset_collate();
	$params["prefix"] = $wpdb->prefix;
	//return plugin_dir_path( __FILE__ ) ."includes/scm_db_tables.php";
	require_once plugin_dir_path( __FILE__ ) ."includes/scm_db_tables.php";
	$install_status=" ";
	$error_cnt=0;
	for ($i=0; $i<count($xcm_list_of_tables); $i++)
	{
		try
		{
			util\cscm_fn::log(__FUNCTION__,0," - ".$xcm_list_of_tables[$i]);

			$sql=$xcm_list_of_tables[$i]($params);
			util\cscm_fn::log(__FUNCTION__,0," SQL = ".$sql);
			//if (strpos($xcm_list_of_tables[$i], 'xcm_tbl') === 0) 
			$result=$wpdb->get_results($sql); //do a ddl action
			
			if ((false === $result) || strlen($wpdb->last_error)>1)
			{
				$install_status.=" Error with ".$xcm_list_of_tables[$i]." : ".$wpdb->last_error." , ";
				$error_cnt++;
				util\cscm_fn::log(__FUNCTION__,0,$wpdb->last_error);
				util\cscm_fn::log(__FUNCTION__,0," ***Error*** ".$xcm_list_of_tables[$i]);
			}	

		}
		catch (Exception $e)
		{
			util\cscm_fn::log(__FUNCTION__,0," - ".$xcm_list_of_tables[$i]);
			util\cscm_fn::log(__FUNCTION__,0," - ".$sql);
		}


	}
	
	for ($i=0; $i<count($xcm_list_of_upgrades); $i++)
	{
		try
		{
			util\cscm_fn::log(__FUNCTION__,0," - ".$xcm_list_of_upgrades[$i]);

			$sql=$xcm_list_of_upgrades[$i]($params);
			util\cscm_fn::log(__FUNCTION__,0," SQL = ".$sql);
			//if (strpos($xcm_list_of_tables[$i], 'xcm_tbl') === 0) 
			$result=$wpdb->get_results($sql); //do a ddl action
			
			if ((false === $result) || strlen($wpdb->last_error)>1)
			{
				$install_status.=" Error with ".$xcm_list_of_upgrades[$i]." : ".$wpdb->last_error." , ";
				$error_cnt++;
				util\cscm_fn::log(__FUNCTION__,0,$wpdb->last_error);
				util\cscm_fn::log(__FUNCTION__,0," ***Error*** ".$xcm_list_of_upgrades[$i]);
			}	

		}
		catch (Exception $e)
		{
			util\cscm_fn::log(__FUNCTION__,0," - ".$xcm_list_of_upgrades[$i]);
			util\cscm_fn::log(__FUNCTION__,0," - ".$sql);
		}

		//file_put_contents("xcm_error.txt",$sql,FILE_APPEND);

//    		return $success;
	}

	if ($error_cnt>0) return $install_status;
	else return "SUCCESS";
}	

function cscm_uninstall_tables()
{
	global $wpdb;
	$params["charset_collate"] = " ";//$wpdb->get_charset_collate();
	$params["prefix"] = $wpdb->prefix;
	//return plugin_dir_path( __FILE__ ) ."includes/scm_db_tables.php";
	require_once plugin_dir_path( __FILE__ ) ."includes/scm_db_tables.php";
	$install_status=" ";
	$error_cnt=0;
	for ($i=0; $i<count($xcm_list_of_tables_delete); $i++)
	{
		try
		{
			util\cscm_fn::log(__FUNCTION__,0," - ".$xcm_list_of_tables_delete[$i]);

			$sql=$xcm_list_of_tables_delete[$i]($params);
			util\cscm_fn::log(__FUNCTION__,0," SQL = ".$sql);
			//if (strpos($xcm_list_of_tables[$i], 'xcm_tbl') === 0) 
			$result=$wpdb->get_results($sql); //do a ddl action
			
			if ((false === $result) || strlen($wpdb->last_error)>1)
			{
				$install_status.=" Error with ".$xcm_list_of_tables_delete[$i]." : ".$wpdb->last_error." , ";
				$error_cnt++;
				util\cscm_fn::log(__FUNCTION__,0,$wpdb->last_error);
				util\cscm_fn::log(__FUNCTION__,0," ***Error*** ".$xcm_list_of_tables_delete[$i]);
			}	

		}
		catch (Exception $e)
		{
			util\cscm_fn::log(__FUNCTION__,0," - ".$xcm_list_of_tables_delete[$i]);
			util\cscm_fn::log(__FUNCTION__,0," - ".$sql);
		}

		//file_put_contents("xcm_error.txt",$sql,FILE_APPEND);

//    		return $success;
	}
	
	if ($error_cnt>0) return $install_status;
	else return "SUCCESS";
}	


function cscm_register_shortcodes() 
{
	
	add_shortcode('cscm_shortcode', __NAMESPACE__.'\cscm_shortcode_handler');
	
}

function cscm_get_post_details()
{
	$args = array(
		'numberposts' => 10,
		'post_type'   => 'post'
	  );
	   
	  $post_list = get_posts( $args );
	  return $post_list;
}

function cscm_get_post_details_sql($table_args)
{
	global $wpdb;

	$result = $wpdb->get_results (
        "
        SELECT * 
        FROM  {$wpdb->posts}
		WHERE post_type =  'post'
		and post_status='publish'
		order by post_modified desc
		limit {$table_args['row_count']}
        "
		);
	return $result;	
}


function cscm_get_sql_result($sql)
{
	global $wpdb;
	util\cscm_fn::log(__FUNCTION__,0,"BEGIN SQL =".$sql);
	$result = $wpdb->get_results ($sql,ARRAY_N);
	util\cscm_fn::log(__FUNCTION__,0,"END SQL");
	return $result;	
}

function cscm_get_link_details($post_type,$row_count)
{
	global $wpdb;

	$post_list = $wpdb->get_results (
        "
        SELECT * 
        FROM  {$wpdb->posts}
		WHERE post_type =  '{$post_type}'
		and post_status='publish'
		order by post_modified desc
		limit {$row_count}
        "
		);
	//return $result;	
	$post_links=array();
	for ($p=0; $p<count($post_list); $p++)
	{
		$post_links[]=get_permalink( $post_list[$p] );
	}

	return $post_links;

}

function cscm_get_initial_set_of_urls()
{
	
	$post_links=cscm_get_link_details('post','10');
	array_unshift($post_links, get_site_url()); //add the site root url to the top of the list
	return $post_links;
}

function cscm_add_initial_set_of_urls()
{
	global $wpdb;

	$urls=cscm_get_initial_set_of_urls();
	util\cscm_fn::log(__FUNCTION__,0,print_r($urls,true));
	$url_cnt=count($urls);
	$insert_value_str="";
	for ($u=0; $u<$url_cnt; $u++)
	{
		$url_details=cscm_get_input_url_details($urls[$u],"");
		if ($u<$url_cnt-1) $url_details["row_str"].=","; //separate each value statement

		$insert_value_str.=$url_details["row_str"];
	}
	$lou_tbl_sql=cscm_lou_tbl_get_insert_sql($insert_value_str);
	$update_results=$wpdb->get_results($lou_tbl_sql);

}

function cscm_get_cell_value($post_record,$display_column)
{
	setup_postdata( $post_record );
	//$raw_cell_value=$post_record->$display_column;

	if ($display_column=="post_category")
	{

		$display_cell_value=get_the_category_list( ', ', '', $post_record->ID  );//,get_the_category( $raw_cell_value );
		
	}
	else if ($display_column=="post_tag")
	{

		$display_cell_value=get_the_tag_list( '', ' , ','', $post_record->ID  );//,get_the_category( $raw_cell_value );

	}	
	else if ($display_column=="post_author")
	{

		$author_url=esc_url( get_author_posts_url( $post_record->post_author ) );
		$author_display_name=get_the_author();
		$author_link_hover="Articles by {$author_display_name}";
		$display_cell_value="<a href='{$author_url}' title='{$author_link_hover}' rel='author'>{$author_display_name}</a>";

	}
	else if ($display_column=="post_title")
	{

		$post_title=get_the_title( $post_record );
		$post_link=get_permalink( $post_record );
		$display_cell_value = "<a href='{$post_link}'>{$post_title}</a>";

	}
	else if ($display_column=="post_date")
	{

		$post_date=get_the_date( 'Y-m-d' );
		$display_cell_value=$post_date;

	}
	else if ($display_column=="add_to_cart")
	{

		$home_url=get_home_url();
		$display_cell_value="<a href='{$home_url}/?add-to-cart={$post_record->ID}'>Add to Cart</a>";

	}	
	
	else if ($display_column=="post_content")
	{
		$post_content=$post_record->post_content;
		$content_length=cscm_get_option( 'cscm_content_length' ) ;
		$display_cell_value=wp_trim_words( $post_content, $content_length, '...' );
	}
	else if ($display_column=="post_excerpt")
	{
		$post_content=$post_record->post_excerpt;
		$content_length=cscm_get_option( 'cscm_excerpt_length' ) ;
		$display_cell_value=wp_trim_words( $post_content, $content_length, '...' );
	}			
	else
	{

		$display_cell_value=$post_record->$display_column;

	}

	return $display_cell_value;

}

function cscm_get_table_col_width()
{
	$table_column_width='
	<colgroup>
	  <col  style="width: 5%;">
	  <col  style="width: 5%;">
	  <col  style="width: 10%;">
	  <col  style="width: 50%;">
	  <col  style="width: 20%;">
	  <col  style="width: 10%;">
	 </colgroup>
';
	return $table_column_width;
}

function cscm_get_table_header($table_args)
{
	
 // $column_property=cscm_get_column_property_array();
  //util\cscm_fn::log(__FUNCTION__,0," column properties = ".print_r($column_property,true));
  $table_header_style="background-color:white;color:black;";
  $table_header="	 <thead style='{$table_header_style}'>";
  
  $table_header."<tr>";

  for ($dc=0; $dc < count($table_args['display_columns']) ; $dc++)
  {
	  $display_column_name=$table_args['display_columns'][$dc]; //get the column name

	  //now use the column name to find the display title from json property
	  //$display_title=$column_property[$display_column_name]["title"]; //grab the title index from the multi array 
	  $table_header.="<td>".$display_column_name."</td>";//<tr><td>ID</td> <td>Author</td> <td>Date</td> <td>Title</td> <td>Category</td> <td>Tags</td></tr>
  }	  
  $table_header."</tr>";
  
  $table_header.="</thead>";
  return $table_header;
}

function cscm_build_table($table_args)
{

	$table_id=$table_args['table_elem_id'];
	$display_columns=$table_args['display_columns'];
	$display_row_count=$table_args['display_columns'];


	  $post_list =  cscm_get_post_details_sql($table_args);//cscm_get_post_details_sql();
	  $table_row=" ";
	  $table_header=cscm_get_table_header($table_args);
	/*  "	 <thead style='background-color:steelblue;color:white;'>
		  		<tr><td>ID</td> <td>Author</td> <td>Date</td> <td>Title</td> <td>Category</td> <td>Tags</td></tr>
  		</thead>
	  ";
  */
	  $table_body="<tbody>";
	  $row_class=array('odd','even');
	  for ($p=0; $p<count($post_list); $p++)
	  {

		  $row_stripe_class=$row_class[$p % 2];
		  
		  $table_row.="<tr>";
		  for ($dc=0;$dc<count($display_columns); $dc++)
		  {
			  $table_cell_value=cscm_get_cell_value($post_list[$p],$display_columns[$dc]);
			$table_row.="<td>{$table_cell_value}</td>";
		  }	  
		  $table_row.="</tr>"; 
		  $table_body.=$table_row;
		  
		  $table_row=" ";
	  }
	  $table_body.="</tbody>";
	  wp_reset_postdata();
	  $table_html="
	  <table id='{$table_id}' id_name='{$table_id}' cellpadding='5' cellspacing='0' style='font-size:90%;' class='ic_pivot crawlspider_datatable display' >"
	  			//.cscm_get_table_col_width()
	  			.$table_header.$table_body."</table>";
	  return $table_html;
}



function cscm_build_table_structure($table_elem_id,$table_args)
{

	$table_id=$table_elem_id;
	$display_columns=$table_args['display_columns'];
	$display_row_count=100;


	  $result_set =  cscm_get_sql_result($table_args["sql"]);//cscm_get_post_details_sql();
	  $table_row=" ";
	  $table_header=cscm_get_table_header($table_args);
	/*  "	 <thead style='background-color:steelblue;color:white;'>
		  		<tr><td>ID</td> <td>Author</td> <td>Date</td> <td>Title</td> <td>Category</td> <td>Tags</td></tr>
  		</thead>
	  ";
  */
	  $table_body="<tbody>";
	  $row_class=array('odd','even');
	  for ($p=0; $p<count($result_set); $p++)
	  {

		  $row_stripe_class=$row_class[$p % 2];
		  
		  $table_row.="<tr>";
		  for ($dc=0;$dc<count($display_columns); $dc++)
		  {
			  $table_cell_value=$result_set[$p][$dc];//cscm_get_cell_value($post_list[$p],$display_columns[$dc]);
			$table_row.="<td>{$table_cell_value}</td>";
		  }	  
		  $table_row.="</tr>"; 
		  $table_body.=$table_row;
		  
		  $table_row=" ";
	  }
	  $table_body.="</tbody>";
	  //wp_reset_postdata();
	  $table_html="<table id='{$table_id}' id_name='{$table_id}' cellpadding='5' cellspacing='0' style='width:100%' class='cscm_datatable display' >"
	  			//.cscm_get_table_col_width()
	  			.$table_header.$table_body."</table>";
	  return $table_html;
}

function cscm_get_table_style($table_id)
{
	$table_style='
		<style>
			#{$table_id} {
				font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
				border-collapse: collapse;
				width: 100%;
			}
			
			#{$table_id} td, #customers th {
				border: 1px solid #ddd;
				padding: 8px;
			}
			
			#{$table_id} tr:nth-child(even){background-color: yellow;}
			
			#{$table_id} tr:hover {background-color: #ddd;}
			
			#{$table_id} th {
				padding-top: 12px;
				padding-bottom: 12px;
				text-align: left;
				background-color: #4CAF50;
				color: white;
			}
			.row1Color {background-color:green;}
			.row2Color {background-color:yellow;}
		  </style>
';
return $table_style;
}

function cscm_shortcode_handler($args, $content="")
{
	if( isset($args['id']) ) $shortcode_id = $args['id'];
	if( isset($args['cols']) ) $column_str = $args['cols'];
	if( isset($args['rows']) ) $row_count = intval($args['rows']);
	if ($row_count<=0) $row_count=10;

	$column_arry=explode(",",$column_str);
	$table_args["shortcode_id"]=$shortcode_id;
	$table_args["display_columns"]=$column_arry;
	$table_elem_id="ic-datatable-{$shortcode_id}";
	$table_args["table_elem_id"]=$table_elem_id;
	$table_args["row_count"]=$row_count;

$table_html=cscm_build_table($table_args);
  return $table_html;
}

function cscm_display_urls()
{
	$column_str="ID,post_author,post_date,post_title,post_category";
	$column_arry=explode(",",$column_str);
	$shortcode_id="cscm_list_of_urls";

	$table_args["shortcode_id"]=$shortcode_id;
	$table_args["display_columns"]=$column_arry;
	$table_elem_id="ic-datatable-{$shortcode_id}";
	$table_args["table_elem_id"]=$table_elem_id;
	$table_args["row_count"]=10;

   $table_html=cscm_build_table($table_args);
  return $table_html;
}

function cscm_get_table_def($table_name)
{
	global $wpdb;
	$table_list=array("cscm_list_of_urls_tbl");
	$table_list["cscm_list_of_urls_tbl"]["display_columns"]=array("ID","URL","Type","Active","Notify Email","Batch ID","Status Code","Batch Status","Action");
	$table_list["cscm_list_of_urls_tbl"]["sql"]=
	"select list_url_id,target_url,url_type,active,notify_email
	,batch_id,last_status_code, batch_status,' ' Action
	FROM {$wpdb->prefix}xcm_list_of_urls
	where project_id=0
	";

	return $table_list["cscm_list_of_urls_tbl"];
}





?>