<?php

namespace crawlspider_lite_cm\alerts_detail_tbl;


require_once plugin_dir_path( __FILE__ ) . 'class_util.php';

use crawlspider_lite_cm\util    as util;

class cscm_alerts_detail_tbl
{
    
    static function add_init_load_ajax_action()
    {
        add_action( 'wp_ajax_display_alerts_detail_tbl', __NAMESPACE__.'\cscm_alerts_detail_tbl::display_init_alerts_detail_tbl' );
        add_action( 'wp_ajax_edit_alerts_detail_tbl', __NAMESPACE__.'\cscm_alerts_detail_tbl::edit_alerts_detail_tbl' ); 
    }

    static function display_init_alerts_detail_tbl() 
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
        $table_data= util\cscm_fn::build_table_structure("cscm_alerts_detail_tbl",$table_def);


        echo json_encode($table_data);
        wp_die();
    }


    static function get_table_def()
    {
        global $wpdb;
        $table_list=array("cscm_alerts_detail_tbl");
        $table_list["cscm_alerts_detail_tbl"]["display_columns"]=array("Alert ID","URL ID","SEQ","URL","Batch ID","Test/Check Type","Alert Text","Priority","Alert Details","Change/Diff");
        $table_list["cscm_alerts_detail_tbl"]["sql"]= 
        "
        SELECT 
            s.alert_id,s.list_url_id,s.seq,s.target_url,s.batch_id,s.test_name
            ,case when s.diff_status='Change Count' then
                concat(s.alert_text,' : ',lk.display_text)
            else 
                    concat(lk.display_text,' : ',s.diff_status)
            end test_alert_status
            ,lk.priority,s.text_body,s.diff_status
            FROM {$wpdb->prefix}xcm_alert_details s
            ,   {$wpdb->prefix}xcm_check_lkp lk
            where s.test_name=lk.check_type
            and s.action_status='pending'
            order by lk.priority,s.alert_id desc
            limit 500
        ";      
/*
        "select alert_id,list_url_id,seq,target_url,batch_id,test_name,alert_text,priority,text_body,diff_status
        from {$wpdb->prefix}xcm_alert_details
        where action_status='pending'
        ";*/

        return $table_list["cscm_alerts_detail_tbl"];
    }

    
    static function edit_alerts_detail_tbl()
    {
        //$url_jsonstr=$POST["form_data"];

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}		


        $url_data = json_decode(stripslashes(sanitize_textarea_field($_POST['form_data'])),true);
        $form_action=stripslashes(sanitize_text_field($_POST['form_action']));
		
        $url_id_list=array_column($url_data, 0); //first index is the id of the url
        $url_id_str=implode(",",$url_id_list);
    
        util\cscm_fn::log(__FUNCTION__,0,print_r($_POST,true));
        global $wpdb;
        

        if ($form_action=="clear_status")
        {
            $lou_tbl_sql="update {$wpdb->prefix}xcm_alert_details
                        set action_status='reviewed'
            where alert_id in ({$url_id_str})
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql);
            
        }
        else if ($form_action=="clear_all_status")
        {
            //there is no separate handler for default checks so we added this here
            $lou_tbl_sql="update {$wpdb->prefix}xcm_alert_details
                        set action_status='reviewed'
                        where action_status<>'reviewed'            
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql); 

            //now erase all reviewed rows
            $lou_tbl_sql="delete from {$wpdb->prefix}xcm_alert_details
                        where creation_date <  DATE_sub(now(),INTERVAL 7 DAY)
            ";
            $update_results=$wpdb->get_results($lou_tbl_sql); 

        }           
    
        $table_def=static::get_table_def();
		 util\cscm_fn::log(__FUNCTION__,0,$table_def["sql"]);
        $table_dataset =util\cscm_fn::get_sql_result($table_def["sql"]);
        
        echo json_encode($table_dataset);
        //echo $url_data[0][0];
        wp_die();
    }
    

}

?>