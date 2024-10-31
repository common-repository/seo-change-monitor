<?php

namespace crawlspider_lite_cm\analysis;



require_once plugin_dir_path( __FILE__ ) . 'class_util.php';
require_once plugin_dir_path( __FILE__ ) . 'class_diff.php';
require_once plugin_dir_path( __FILE__ ) . 'class_email.php';


use crawlspider_lite_cm\util    as util;
use crawlspider_lite_cm\diff    as diff;
use crawlspider_lite_cm\email   as email;

class cscm_analysis
{

    static function add_init_load_ajax_action()
    {
       // add_action( 'wp_ajax_get_list_of_alerts', __NAMESPACE__.'\cscm_analysis::get_list_of_alerts' );
        
    }

    static function get_check_priority()
    {
              

        $html_checks=array("title"=>"High"
        ,"h1"=>"High","h2"=>"Medium","h3"=>"Low"
                        ,"a"=>"High"
                        ,"meta"=>"Medium","link"=>"Medium","script"=>"Low"
                        ,"img"=>"Low","iframe"=>"Low","style"=>"Low","body"=>"Medium"
                        ,"tag"=>"High","class"=>"High"
                        ,"xpath"=>"High","regex"=>"High","string"=>"High"
                        ,"ssl_cert"=>"High"
                        ,"ip"=>"High","www_test"=>"High"
                    ,"https_test"=>"High"
                    ,"robot_txt"=>"High","sitemap"=>"High"                
                    );
        return $html_checks;                

    }

    static function get_check_displayname()
    {

        $html_checks=array("title"=>"Page Title"
        ,"h1"=>"H1 Tag","h2"=>"H2 Tag","h3"=>"H3 Tag"
                        ,"a"=>"Anchor Link"
                        ,"meta"=>"Meta Tag","link"=>"Link Tag","script"=>"Javascript"
                        ,"img"=>"Image Tag","iframe"=>"IFrame Tag","style"=>"CSS Style Tag","body"=>"Body Content"
                        ,"tag"=>"Tag","class"=>"CSS Class"
                        ,"xpath"=>"XPATH Search","regex"=>"REGEX Search","string"=>"Search String"
                        ,"ssl_cert"=>"SSL Certificate"
                        ,"ip"=>"IP DNS Record","www_test"=>"WWW Redirection Test"
                    ,"https_test"=>"HTTPS Redirection Test"
                    ,"robot_txt"=>"ROBOT.txt File","sitemap"=>"Sitemap Definition"                
                    );
        return $html_checks;                

    }

    static function get_alert_value_row($result_row,$seq,$alert_text,$test_priority,$diff_text,$text_body)
    {
        //test_type goes into test_name due to xpath, regex and test_name goes into test_str
        $values_row="
        ('{$seq}',{$result_row->list_url_id}, '{$result_row->target_url}','{$result_row->new_batch}','{$result_row->old_batch}','{$result_row->test_type}','{$result_row->test_name}','{$alert_text}' ,'{$test_priority}' ,'pending' ,'{$diff_text}' ,'{$text_body}' ,now())

		";
        return $values_row;
    }


    static function get_alert_insert_sql($values_sql)
    {
        global $wpdb;
        $insert_sql="
        insert into {$wpdb->prefix}xcm_alert_details
        (seq,list_url_id  ,target_url ,batch_id ,prev_batch_id  ,test_name  ,test_str, alert_text  ,priority ,action_status ,diff_status , text_body , creation_date)
        values
        {$values_sql}
		";
        return $insert_sql;
    }


    static function update_diff_status($url_summary_id,$status)
    {
        global $wpdb;

        $diff_complete_sql="
        update  {$wpdb->prefix}xcm_url_summary
        set diff_status='{$status}'
        where url_summary_id='{$url_summary_id}'              
		";
        return $diff_complete_sql;
    }

    static function update_summary_status($url_summary_id,$status)
    {
        global $wpdb;

        $diff_complete_sql="
        update  {$wpdb->prefix}xcm_url_summary
        set action_status='{$status}'
        where url_summary_id='{$url_summary_id}'              
		";
        return $diff_complete_sql;
    }

    static function get_alert_processing_sql($p_type,$batch_dtls)
    {
        global $wpdb;
        util\cscm_fn::log(__FUNCTION__,0,"Fetch SQL : ".$p_type);

        if ($p_type=="diff_count")
        {
            $diff_sql="
            select s1.url_summary_id,s1.list_url_id,s1.target_url,s1.batch_id new_batch,s2.batch_id old_batch,s1.test_type,s1.test_name,s2.text_body old_text,s1.text_body new_text,s1.element_count new_count, s2.element_count old_count, s1.element_count - s2.element_count diff_element_count
            from {$wpdb->prefix}xcm_url_summary s1
            , {$wpdb->prefix}xcm_url_summary s2
            where s1.list_url_id=s2.list_url_id
            and s1.test_name=s2.test_name
            and s1.element_count <> s2.element_count
            and s2.batch_id='{$batch_dtls["prev_batch_id"]}'
            and s1.batch_id='{$batch_dtls["batch_id"]}'
            and s1.diff_status is null
			";

        }
        else if  ($p_type=="missing_items")
        {  

            $diff_sql="
            select s1.url_summary_id,s1.list_url_id,s1.target_url,s1.batch_id new_batch,'' old_batch,s1.test_type,s1.test_name,'' old_text,s1.text_body new_text,s1.element_count new_count, 0 old_count, 0 diff_element_count
            from {$wpdb->prefix}xcm_url_summary s1
            where s1.test_name in ('title','h1','h2')
            and s1.batch_id='{$batch_dtls["batch_id"]}'
            and s1.action_status ='pending'
            and s1.element_count=0
            order by s1.url_summary_id
			";

        }
        else if  ($p_type=="diff_text")
        {  

            $diff_sql="
            select s1.url_summary_id,s1.list_url_id,s1.target_url,s1.batch_id new_batch,s2.batch_id old_batch,s1.test_type,s1.test_name,s2.text_body old_text,s1.text_body new_text,s1.element_count new_count, s2.element_count old_count, s1.element_count - s2.element_count diff_element_count
            from {$wpdb->prefix}xcm_url_summary s1
            , {$wpdb->prefix}xcm_url_summary s2
            where s1.list_url_id=s2.list_url_id
            and s1.test_name=s2.test_name
            and s1.text_body <> s2.text_body
            and s2.batch_id='{$batch_dtls["prev_batch_id"]}'
            and s1.batch_id='{$batch_dtls["batch_id"]}'
            and s1.diff_status is null
			";

        }
        else if  ($p_type=="failed_items")
        {

            $diff_sql="
            select s1.url_summary_id,s1.list_url_id,s1.target_url,s1.batch_id new_batch,'' old_batch,s1.test_type,s1.test_name,'' old_text,s1.text_body new_text,s1.element_count new_count, 0 old_count, 0 diff_element_count
            from {$wpdb->prefix}xcm_url_summary s1
            where s1.batch_id='{$batch_dtls["batch_id"]}'
            and s1.action_status ='pending'
            and (return_status='fail' or test_status='fail')
            order by s1.url_summary_id
			";

        }
        else if  ($p_type=="ssl_items")
        {

            $diff_sql="
            select case when days_left <= 15 and days_left>0 then 'HIGH Alert : SSL expiring soon'
            when days_left>15 then 'SSL Expiring soon'
            when days_left<0 then 'URGENT: SSL Invalid or Expired'
            end ssl_alert_text, s.*
            from
            (
                select datediff(s1.expire_date,now()) days_left, DATE_FORMAT(s1.expire_date,'%b %D %Y') expire_date, s1.url_summary_id,s1.list_url_id,s1.target_url,s1.batch_id new_batch,'' old_batch,s1.test_type,s1.test_name,'' old_text,s1.text_body new_text,s1.element_count new_count, 0 old_count, 0 diff_element_count
                from {$wpdb->prefix}xcm_url_summary s1
                where s1.test_name in ('ssl_cert')
                and s1.batch_id='{$batch_dtls["batch_id"]}'
                and s1.action_status ='pending'
                and expire_date<= NOW() + INTERVAL 45 DAY
            ) as s
			";
 
        }        
        else if  ($p_type=="list_of_alerts")
        {

            $diff_sql="
            select * 
            from
            (
            select concat(alr.list_url_id,'_',alr.batch_id) url_key
            ,concat(alr.batch_id,' : ',alr.target_url) url_key_disp
            ,case 
                when diff_status='Change Count' then 
                    'Tag Count Change Summary'
                else alert_text
            end alert_key    
            ,case 
                when diff_status='Change Count' then 
                    concat(upper(alr.test_name),' Tag : ',alr.alert_text) 
                else text_body
            end alert_body  
            , alr.* from {$wpdb->prefix}xcm_alert_details alr
            ) as x
            order by batch_id,url_key,alert_key
			";
        }
        else if  ($p_type=="list_of_alerts_batch")
        {

            $diff_sql="
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
            and s.batch_id='{$batch_dtls["batch_id"]}'
            order by lk.priority,s.alert_id desc
            limit 50
			";
        }

        return $diff_sql;

    }

    static function update_batch_analysis_status($batch_id,$analysis_status)
    {
        global $wpdb;
		$update_sql="update {$wpdb->prefix}xcm_batch_runs 
		set analysis_status ='{$analysis_status}'
		where batch_id='{$batch_id}'
		";

        $result=util\cscm_fn::run_dml_sql($update_sql);
        return $result;
    }
    static function update_batch_send_alert_status($batch_id,$alert_status)
    {
        global $wpdb;
		$update_sql="update {$wpdb->prefix}xcm_batch_runs 
		set alert_status ='{$alert_status}'
		where batch_id='{$batch_id}'
		";

        $result=util\cscm_fn::run_dml_sql($update_sql);
        return $result;
    }

    static function process_diff_count($batch_dtls)
    {
        global $wpdb;

        $diff_sql=self::get_alert_processing_sql('diff_count',$batch_dtls);
            
        $results=$wpdb->get_results($diff_sql); 
        $check_priority=self::get_check_priority();
        if (!isset($check_priority)) $check_priority="NA";
        //get element count difference
        $values_sql="";
        $diff_row_count=count($results);
        util\cscm_fn::log(__FUNCTION__,0," Diff Count : ".$diff_row_count);
        for($i=0;$i< $diff_row_count; $i++)
        {        
            $diff_elem_count=$results[$i]->diff_element_count;
            $test_priority=$check_priority[$results[$i]->test_name];

            if ($diff_elem_count<0)  $alert_text=($diff_elem_count*-1)." Missing";
            else $alert_text=($diff_elem_count)." New Added";

            $diff_text="Change Count";
            $values_row=self::get_alert_value_row($results[$i],'D0',$alert_text,$test_priority,$diff_text,'');

            if ($i<$diff_row_count-1) $values_row.=" , ";
            $values_sql.=$values_row;
        }    
        if ($diff_row_count>0)
        {
            $insert_sql=self::get_alert_insert_sql($values_sql);
            $result=util\cscm_fn::run_dml_sql($insert_sql); //add diff count summary into alert table
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$insert_sql);
        }
    }

    static function process_missing_items($batch_dtls)
    {
        global $wpdb;


        $diff_sql=self::get_alert_processing_sql('missing_items',$batch_dtls);

        util\cscm_fn::log(__FUNCTION__,0,$diff_sql);
        $results=$wpdb->get_results($diff_sql); 
        $check_priority=self::get_check_priority();
        if (!isset($check_priority)) $check_priority="NA";

        for($i=0;$i<count($results); $i++)
        {

            /************************************************GET Diff Tokens ************************* */
            util\cscm_fn::log(__FUNCTION__,0,"============================={$results[$i]->test_name}============================");

            $test_priority=$check_priority[$results[$i]->test_name];

            $alert_text=strtoupper($results[$i]->test_name)." Tag "."Missing";
            $values_sql=self::get_alert_value_row($results[$i],'M0',$alert_text,$test_priority,'Missing','');

            //util\cscm_fn::log(__FUNCTION__,0," - ".$values_sql);
            //insert each diff values into alert table

            $insert_sql=self::get_alert_insert_sql($values_sql);
   
            $result=util\cscm_fn::run_dml_sql($insert_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$insert_sql);                


            //mark the url summary id as diff complete
            $diff_complete_sql=self::update_summary_status($results[$i]->url_summary_id,'complete');
            $result=util\cscm_fn::run_dml_sql($diff_complete_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$diff_complete_sql);                
 

            util\cscm_fn::log(__FUNCTION__,0,"=============================  END {$results[$i]->test_name} ============================");    

        }//for results loop
        

    }


    static function process_diff_text($batch_dtls)
    {
        global $wpdb;

        $diff_sql=self::get_alert_processing_sql('diff_text',$batch_dtls);

        util\cscm_fn::log(__FUNCTION__,0,$diff_sql);
        $results=$wpdb->get_results($diff_sql); 
        $check_priority=self::get_check_priority();
        if (!isset($check_priority)) $check_priority="NA";

        for($i=0;$i<count($results); $i++)
        {
            $old_text=$results[$i]->old_text;
            $new_text=$results[$i]->new_text;

            /************************************************GET Diff Tokens ************************* */
            util\cscm_fn::log(__FUNCTION__,0,"============================={$results[$i]->test_name}============================");
            $changes=diff\cscm_diff::get_diff_changes($results[$i]->test_name." Tag",$old_text,$new_text);
            
            $diff_arry=$changes["token_arry"];
            util\cscm_fn::log(__FUNCTION__,0,print_r($diff_arry,true));
            $test_priority=$check_priority[$results[$i]->test_name];
            $diff_cnt=count($diff_arry);


            if ($diff_cnt>0)
            {
                $values_sql= "";
                for ($d=0; $d<$diff_cnt; $d++)
                {
                    $alert_text=strtoupper($results[$i]->test_name)." Tag ".$diff_arry[$d]['state'];
                    $values_row=self::get_alert_value_row($results[$i],"C".$d,$alert_text,$test_priority,$diff_arry[$d]['state'],$diff_arry[$d]['str']);

                    if ($d<$diff_cnt-1) $values_row.=" , ";
                    $values_sql.=$values_row;
                }
            }
            else //the text are different but the tokenizer was somehow not able to break the tokens so just insert the full html text
            {
                $alert_text=strtoupper($results[$i]->test_name)." Tag "."Changed";
                $values_sql=self::get_alert_value_row($results[$i],"C0",$alert_text,$test_priority,'Changed',$changes["final_html"]);
            }
            //util\cscm_fn::log(__FUNCTION__,0," - ".$values_sql);
            //insert each diff values into alert table

            $insert_sql=self::get_alert_insert_sql($values_sql);

               // util\cscm_fn::log(__FUNCTION__,0," - ".$insert_sql);
   
            $result=util\cscm_fn::run_dml_sql($insert_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$insert_sql);                


            //mark the url summary id as diff complete
            $diff_complete_sql=self::update_diff_status($results[$i]->url_summary_id,'complete');
            $result=util\cscm_fn::run_dml_sql($diff_complete_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$diff_complete_sql);                
 

            util\cscm_fn::log(__FUNCTION__,0,"=============================  END {$results[$i]->test_name} ============================");    

        }//for results loop
        


        
        //now pick up rows for further analysis like count check, expire date check etc

    }


    static function process_failed_items($batch_dtls)
    {
        global $wpdb;
        $diff_sql=self::get_alert_processing_sql('failed_items',$batch_dtls);

        util\cscm_fn::log(__FUNCTION__,0,$diff_sql);
        $results=$wpdb->get_results($diff_sql); 
        $check_priority=self::get_check_priority();
        if (!isset($check_priority)) $check_priority="NA";

        for($i=0;$i<count($results); $i++)
        {

            /************************************************GET Diff Tokens ************************* */
            util\cscm_fn::log(__FUNCTION__,0,"============================={$results[$i]->test_name}============================");

            $test_priority=$check_priority[$results[$i]->test_name];

            $alert_text=strtoupper($results[$i]->test_name)." Test "."Failed";
            $values_sql=self::get_alert_value_row($results[$i],'F0',$alert_text,$test_priority,'Failed',$results[$i]->new_text);

            //util\cscm_fn::log(__FUNCTION__,0," - ".$values_sql);
            //insert each diff values into alert table

            $insert_sql=self::get_alert_insert_sql($values_sql);
   
            $result=util\cscm_fn::run_dml_sql($insert_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$insert_sql);                


            //mark the url summary id as diff complete
            $diff_complete_sql=self::update_summary_status($results[$i]->url_summary_id,'complete');
            $result=util\cscm_fn::run_dml_sql($diff_complete_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$diff_complete_sql);                
 

            util\cscm_fn::log(__FUNCTION__,0,"=============================  END {$results[$i]->test_name} ============================");    

        }//for results loop
        

    }

    static function process_ssl_items($batch_dtls)
    {
        global $wpdb;
        $diff_sql=self::get_alert_processing_sql('ssl_items',$batch_dtls);

        util\cscm_fn::log(__FUNCTION__,0,$diff_sql);
        $results=$wpdb->get_results($diff_sql); 
        $check_priority=self::get_check_priority();
        if (!isset($check_priority)) $check_priority="NA";

        for($i=0;$i<count($results); $i++)
        {

            /************************************************GET Diff Tokens ************************* */
            util\cscm_fn::log(__FUNCTION__,0,"============================={$results[$i]->test_name}============================");

            $test_priority=$check_priority[$results[$i]->test_name];

            $alert_text=$results[$i]->ssl_alert_text;
            $values_sql=self::get_alert_value_row($results[$i],'S0',$alert_text,$test_priority,'Days Left: '.$results[$i]->days_left.' , Expire: '.$results[$i]->expire_date,'');

            //util\cscm_fn::log(__FUNCTION__,0," - ".$values_sql);
            //insert each diff values into alert table

            $insert_sql=self::get_alert_insert_sql($values_sql);
   
            $result=util\cscm_fn::run_dml_sql($insert_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$insert_sql);                


            //mark the url summary id as diff complete
            $diff_complete_sql=self::update_summary_status($results[$i]->url_summary_id,'complete');
            $result=util\cscm_fn::run_dml_sql($diff_complete_sql);
            if ($result=="fail") util\cscm_fn::log(__FUNCTION__,0,"ERROR Database SQL : ".$diff_complete_sql);                
 

            util\cscm_fn::log(__FUNCTION__,0,"=============================  END {$results[$i]->test_name} ============================");    

        }//for results loop
        

    }

    static function process_diff($batch_dtls)
    {
        global $wpdb;

        if ($batch_dtls["prev_batch_id"]=="null") 
        {
            //only process current batch findings
            self::process_missing_items($batch_dtls);
            self::process_failed_items($batch_dtls);
            self::process_ssl_items($batch_dtls);
            
        }
        else
        {

            self::process_diff_count($batch_dtls);
            self::process_diff_text($batch_dtls);
            self::process_missing_items($batch_dtls);
            self::process_failed_items($batch_dtls);
            self::process_ssl_items($batch_dtls);
        }
        self::update_batch_analysis_status($batch_dtls["batch_id"],'complete');

        
        //now pick up rows for further analysis like count check, expire date check etc

    }

    static function get_list_of_alerts()
    {
        global $wpdb;

        $alert_sql=self::get_alert_processing_sql('list_of_alerts');        
        $result=util\cscm_fn::get_sql_result_a($alert_sql);
        echo json_encode($result);
        wp_die();
    }

    static function send_alerts_by_batch($batch_dtls)
    {
        $email_addr=get_option('admin_email');
        if (!$email_addr)
        {
            util\cscm_fn::log(__FUNCTION__,0,"**** Email Not found ****");                
            return;
        }        


        
        $title = "CrawlSpider Monitor Alert : Scan# {$batch_dtls['batch_id']}";
        $body = 'Alert generated at '.date("F j, Y, g:i a");   
        $body.=" 
        
        You can review all the alerts within your Wordpress dashboard. Remember to review and clear all the alerts.";
        $body.="
        Alert details
        ";

        global $wpdb;

        $alert_sql=self::get_alert_processing_sql('list_of_alerts_batch',$batch_dtls);        
        $results=$wpdb->get_results($alert_sql); 
        //util\cscm_fn::log(__FUNCTION__,0,"RAW SQL alerts = ".print_r($results,true)); 
        /*for($i=0;$i<count($results); $i++)
        {
            $body.="URL={$results[$i]->target_url} , Alert={$results[$i]->test_alert_status}
            ";
        }   
        */     
        $body=  email\cscm_em::get_email_top_body_01();

        $body.=  email\cscm_em::get_email_table_title_row_02("CrawlSpider: SEO Monitor Alerts");

        $body.=  email\cscm_em::get_email_table_center_datatable_row($results);  

        $body.=  email\cscm_em::get_email_table_footer("https://www.crawlspider.com");    

       // util\cscm_fn::log(__FUNCTION__,0,"Alert Body".$body);
        $content_type = function() { return 'text/html'; };
        add_filter( 'wp_mail_content_type', $content_type );
        wp_mail( $email_addr, $title, $body );
        remove_filter( 'wp_mail_content_type', $content_type );

        self::update_batch_send_alert_status($batch_dtls['batch_id'],"complete");

    }
    
}

?>