<?php

namespace crawlspider_lite_cm\lix;

require_once plugin_dir_path( __FILE__ ) . 'commons.php';
require_once plugin_dir_path( __FILE__ ) . 'class_util.php';
require_once plugin_dir_path( __FILE__ ) . 'class_lx_helper.php';



use crawlspider_lite_cm\util    as util;
use crawlspider_lite_cm\lxhelp as lxhelp;

class cscm_lx
{

 

    static function get_lserver()
    {
        global $prop;
        util\cscm_fn::log(__FUNCTION__,0,print_r($prop,true));
        util\cscm_fn::log(__FUNCTION__,0,"lx server = ".$prop["lx_server"]);
        $response = util\cscm_fn::request_get_url($prop["lx_server"]);
        
        $lurl=json_decode($response->body);
        return $lurl[0];
    }

    static function get_install_id()
    {
        
      return base64_encode(preg_replace("/[^0-9a-zA-Z]/","_",dirname($_SERVER['SCRIPT_FILENAME'])));  
  
    }

   

    static function set_lx_register($on,$em,$ur,$br,$id)
    {
        global $prop;
        //locator is a dynamic end point
        $lx_locator=self::get_lserver();
        
        //form the full call
        $end_point=$lx_locator."?mode={$prop['lx_mode']}&or={$on}&em={$em}&ur={$ur}&br={$br}&id={$id}";
        util\cscm_fn::log(__FUNCTION__,0,"end point = ".$end_point);
        $response = util\cscm_fn::request_get_url($end_point);
        $content=$response->body;
        util\cscm_fn::log(__FUNCTION__,0,"lserver return value = ".print_r($content,true));

        if ($content)
        {
            //break up the return and see if all valid
            $ld = json_decode($content,true);
            


            if ($ld['STATUS']=="SUCCESS") 
            {
            
                $keyA=json_decode($ld['key'],true);
                $keyFileData="<?php\n\n";
                $d="$";
                foreach ($keyA as $k => $v)
                {
                    $encode_v = base64_encode($k.":".$v);
                    $keyFileData.="{$d}ax['{$k}'] = '{$encode_v}';\n";
                    
                    
                }
                $keyFileData.="\n\n?>";
                $lx_dir=util\cscm_fn::get_local_path($prop["lx_dir"]);
                $lx_file="{$lx_dir}/{$prop['lx_file']}";
                util\cscm_fn::to_file($lx_file,$keyFileData,"w");	
                $data['STATUS']="SUCCESS";
                $data['message']="Registration successful";
                return $data;
            }
            else
            {
                $data['STATUS']="FAILURE";
                $data['message']="  Error:  Invalid key  ";
                return $data;
            }
            
        }
        else
        {

            $data['STATUS']="FAILURE";
            $data['message']="  Error:  Please contact support https://www.crawlspider.com  ";
            return $data;
        }        
    }

    static function get_lx_status()
    {
        global $prop;
        global $wpdb;
        //util\cscm_fn::log(__FUNCTION__,0,print_r($prop,true));

        $lx_dir=util\cscm_fn::get_local_path($prop["lx_dir"]);
        $lx_file="{$lx_dir}/{$prop['lx_file']}";
        util\cscm_fn::log(__FUNCTION__,5," lx file path  = ".$lx_file);
        if (file_exists($lx_file)) $lx_file_exists=true;
        else $lx_file_exists=false;

        if ($lx_file_exists)
        {
            util\cscm_fn::log(__FUNCTION__,0," lx file exists");
            //scan the lx file
            include ($lx_file);
            foreach ($ax as $k => $v)
            {
                $decode_combo=base64_decode($v);
                $decode_v = explode(":",$decode_combo);
                
                $dk1=lxhelp\cscm_lxhelp::decode($k,$prop["lx_xi"]);
                $dv1=lxhelp\cscm_lxhelp::decode($decode_v[1],$prop["lx_xi"]);
        
                $lx[$dk1]=$dv1;
        
            }

            $exp_dt=$lx["expire_date"];
            $today=time();
            
            $status["status"]="invalid";
            if (!empty($exp_dt))
            {
                $status["who"]=$lx[ $prop["lx_who"] ];
                $status["contact_em"]=$lx[ $prop["lx_contact_em"] ];
                
                $status["trx"]=$lx[ $prop["lx_trx"] ];
                $status["type"]=$lx['type'];                             

                $status["when"]=$lx[ $prop["lx_when"] ];
                if ($exp_dt<$today) 
                { 
                    $status["status"]="valid";
                    return $status;
                }
                else return $status["status"];
            }
            else return $status;
            
            
        }
        else
        {
            /*
            $sql="SELECT date_add(creation_date,INTERVAL 15 DAY) exp_dt,case when datediff(date_add(creation_date,INTERVAL 15 DAY),now()) < 0 then 'invalid_temp' else 'valid_temp' end status
            FROM {$wpdb->prefix}xcm_check_details
            where check_defined_by='default' and check_name='ssl_cert'";
            $result=util\cscm_fn::get_sql_result_a($sql);
            util\cscm_fn::log(__FUNCTION__,0,"SQL ssl_cert result = ".print_r($result,true));
			*/
            $status["who"]="none";
            $status["contact_em"]="none";       
            $status["trx"]="none";
            $status["type"]="Temp";

            $status["when"]='';
            $status["status"]='valid_temp';
            return $status;
        }

    }


    static function unset_lx()
    {
		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}	
		
        global $prop;
        $curr_lx=self::get_lx_status();
        $lx_dir=util\cscm_fn::get_local_path($prop["lx_dir"]);
        $lx_file="{$lx_dir}/{$prop['lx_file']}";
        util\cscm_fn::log(__FUNCTION__,0,"Unregister LX = ");
        unlink($lx_file);	

        $lx_locator=self::get_lserver();
        
        //form the full call
        $em=$curr_lx['contact_em'];
        $on=$curr_lx['trx'];
        $end_point=$lx_locator."?mode=unset_ping&or={$on}&em={$em}";
        //util\cscm_fn::log(__FUNCTION__,0,"end point = ".$end_point);
        $response = util\cscm_fn::request_get_url($end_point);
        $content=$response->body;
        util\cscm_fn::log(__FUNCTION__,0,"lserver return value = ".print_r($content,true));


    }

    static function get_lx_display_text($lx_details)
    {
        if ($lx_details['status']=="valid"||$lx_details['status']=="valid_temp")
        {
            $mesg="License is valid and expires on {$lx_details['when']}";
        }
        else
        {
            $mesg="<mark>License expired on {$lx_details['when']}. Please consider <a href='https://www.crawlspider.com?x=xpired' target='_blank'>renewing it <i class='fa fa-external-link'></i></a> </mark>";
        }
        
        if ($lx_details['trx']!='none') 
        {
            $mesg.="<br><mark>{$lx_details['trx']}</mark>";
            $mesg.="<br>{$lx_details['who']}";
            $mesg.="<br>{$lx_details['contact_em']}";
        }    

        return $mesg;
    }

    static function add_init_load_ajax_action()
    {
        add_action( 'wp_ajax_set_lx_status', __NAMESPACE__.'\cscm_lx::set_lx_status' );
        add_action( 'wp_ajax_unset_lx', __NAMESPACE__.'\cscm_lx::unset_lx' );

    }

    static function set_lx_status() 
    {

		//verify nonce
		if (!wp_verify_nonce($_POST['cscm_crawlspider_nonce_security'],'cscm_crawlspider_nonce_security'))
		{
			util\ibi_fn::log(__FUNCTION__,0,"Nonce verification failed. Abort. Nonce value=".$_POST['cscm_crawlspider_nonce_security']);
			wp_die();
		}	
		
        //sanitize
        if (isset($_POST['lx_em']))
        {
            //$lx_em=filter_var($_POST['lx_em'],FILTER_SANITIZE_EMAIL);
			$lx_em=sanitize_email($lx_em);
        }
        else $lx_em="default@crawlspider.com";

        util\cscm_fn::log(__FUNCTION__,0,"POST = ".print_r($_POST,true));
        $lx_trx=preg_replace("/[^a-zA-Z0-9-]+/", "",$_POST['lx_trx']);
        $lx_ur=util\cscm_fn::get_orig_host();
        $lx_id=self::get_install_id();
        $lx_br="na";
        util\cscm_fn::log(__FUNCTION__,0,"form submit lx trx = ".$lx_trx);

        $existing_lx=self::get_lx_status();
        //util\cscm_fn::log(__FUNCTION__,0,"existing LX = ".print_r($existing_lx,true));
         
        $lx_data= self::set_lx_register($lx_trx,$lx_em,$lx_ur,$lx_br,$lx_id);

        echo json_encode($lx_data);
        wp_die();
    }
  
}



?>