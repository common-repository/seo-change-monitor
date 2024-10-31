<?php

namespace crawlspider_lite_cm\checks_tbl;


require_once plugin_dir_path( __FILE__ ) . 'class_util.php';
require_once plugin_dir_path( __FILE__ ) . 'class_lic.php';

use crawlspider_lite_cm\util    as util;
use crawlspider_lite_cm\lix as lix;

class cscm_checks_tbl
{
    static function display_me() 
    {
        echo __NAMESPACE__;
    }

    
    static function add_init_load_ajax_action()
    {
        add_action( 'wp_ajax_display_checks_tbl', __NAMESPACE__.'\cscm_checks_tbl::display_init_checks_tbl' );
        add_action( 'wp_ajax_edit_checks_tbl', __NAMESPACE__.'\cscm_checks_tbl::edit_checks_tbl' ); 
    }

    static function display_init_checks_tbl() 
    {

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

		
        $table_def=static::get_table_def();
        //print_r(cscm_get_sql_result($table_def["sql"]));
        //wp_die;
        //return;
        $table_data= util\cscm_fn::build_table_structure("cscm_checks_tbl",$table_def);

        //now get the custom checks table
        $table_def=static::get_table_def_default();
        $custom_table_data= util\cscm_fn::build_table_structure("cscm_default_checks_tbl",$table_def);
        //now piggy back custom data to the main data
        $table_data["dataset2"]=$custom_table_data["dataset"];
        $table_data["table_html2"]=$custom_table_data["table_html"];
        $table_data["lx_status"]=lix\cscm_lx::get_lx_status();
        echo json_encode($table_data);
        wp_die();
    }


    static function get_table_def()
    {
        global $wpdb;
        $table_list=array("cscm_checks_tbl");
        $table_list["cscm_checks_tbl"]["display_columns"]=array("ID","URL ID","Target URL","Check Type","Tag / Class / Xpath / Regex / String","Active","Notify Email","Action");
        $table_list["cscm_checks_tbl"]["sql"]=
        "        
        select xcd.url_check_id,xcd.list_url_id,xlou.target_url,xcd.check_type,xcd.check_name,xcd.check_active,xcd.notify_email,' ' Action
        FROM {$wpdb->prefix}xcm_check_details xcd
        ,    {$wpdb->prefix}xcm_list_of_urls xlou
        where xcd.check_defined_by<>'default'
        and xcd.list_url_id=xlou.list_url_id
        ";

        return $table_list["cscm_checks_tbl"];
    }

    static function get_table_def_default()
    {
        global $wpdb;
        $table_list=array("cscm_default_checks_tbl");
        $table_list["cscm_default_checks_tbl"]["display_columns"]=array("ID","Target URL","Check Type","Check Name","Active");
        $table_list["cscm_default_checks_tbl"]["sql"]=
        "select url_check_id,list_url_id,check_type,check_name,check_active
        FROM {$wpdb->prefix}xcm_check_details
        where check_defined_by='default'";

        return $table_list["cscm_default_checks_tbl"];
    }    

    
    static function edit_checks_tbl()
    {

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		

		
        //$url_jsonstr=$POST["form_data"];
		//[[\"\",\"1\",\"\",\"tag\",\"xyz###\",\"Y\",\"infocaptor@gmail.com\"]]
        $url_data = json_decode(stripslashes($_POST['form_data']),true);
		
		//sanitize after decoded json structure. The json contains ID and the URL, ID is numeric
		for ($i=0;$i<count($url_data);$i++)
		{
			$url_data[$i][0]=sanitize_key($url_data[$i][0]);
			$url_data[$i][1]=sanitize_text_field(($url_data[$i][1]));
			$url_data[$i][2]=sanitize_text_field(($url_data[$i][2]));
			$url_data[$i][3]=sanitize_text_field(($url_data[$i][3]));
			$url_data[$i][4]=sanitize_text_field(($url_data[$i][4]));
			$url_data[$i][5]=sanitize_text_field($url_data[$i][5]);
			$url_data[$i][6]=sanitize_email($url_data[$i][6]);
			
			
		}		
		
        $form_action=stripslashes(sanitize_text_field($_POST['form_action']));
        $url_id_list=array_column($url_data, 0); //first index is the id of the url
        $url_id_str=implode(",",$url_id_list);
    
        util\cscm_fn::log(__FUNCTION__,0,print_r($_POST,true));
        global $wpdb;
        

        if ($form_action=="toggle_status")
        {
            $lou_tbl_sql="update {$wpdb->prefix}xcm_check_details
                        set check_active= case 
                        when check_active='Y' then 'N' 
                        when check_active='N' then 'Y'
                        end
            where url_check_id in ({$url_id_str})
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql);
            
        }
        else if ($form_action=="default_checks_toggle_status")
        {
            //there is no separate handler for default checks so we added this here
            $lou_tbl_sql="update {$wpdb->prefix}xcm_check_details
                        set check_active= case 
                        when check_active='Y' then 'N' 
                        when check_active='N' then 'Y'
                        end
            where url_check_id in ({$url_id_str})
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql); 

            $table_def=static::get_table_def_default();
            $table_dataset =util\cscm_fn::get_sql_result($table_def["sql"]);
            
            echo json_encode($table_dataset);
            //echo $url_data[0][0];
            wp_die();

        }           
        else if ($form_action=="edit")
        {
            $url_data[0][4]=addslashes($url_data[0][4]);
            $url_data[0][6]=addslashes($url_data[0][6]);            
            $lou_tbl_sql="update {$wpdb->prefix}xcm_check_details
                        set 
                        check_type='{$url_data[0][3]}'
                        ,check_name='{$url_data[0][4]}'                        
                        ,check_active= '{$url_data[0][5]}'
                        ,notify_email='{$url_data[0][6]}'
            where url_check_id = '{$url_data[0][0]}'
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql);
            
        }	
        else if ($form_action=="delete")
        {
            $lou_tbl_sql="delete from {$wpdb->prefix}xcm_check_details
            where url_check_id in ({$url_id_str})
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql);
        }
        else if ($form_action=="add")
        {

            $url_table=$wpdb->prefix."xcm_check_details";
            $url_data[0][4]=addslashes($url_data[0][4]);
            $url_data[0][6]=addslashes($url_data[0][6]);
            $table_columns="(project_id,list_url_id      ,check_category,check_type     ,  check_name     ,    check_active,check_defined_by,creation_date,notify_email)";
        
            $insert_value_str="('0'    ,{$url_data[0][1]},'url'          ,'{$url_data[0][3]}',  '{$url_data[0][4]}',   'Y'         ,'admin'         ,now(), '{$url_data[0][6]}')";
            $insert_stmt_sql="INSERT IGNORE INTO {$url_table}
            {$table_columns}
            VALUES
            {$insert_value_str}";
            $update_results=$wpdb->get_results($insert_stmt_sql);
        }
    
        $table_def=static::get_table_def();
        $table_dataset =util\cscm_fn::get_sql_result($table_def["sql"]);
        
        echo json_encode($table_dataset);
        //echo $url_data[0][0];
        wp_die();
    }
    

}

?>