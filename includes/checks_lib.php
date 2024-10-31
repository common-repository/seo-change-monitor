<?php

//error_log(plugin_dir_path( __FILE__ ) . 'includes/class_debug.php');

require_once plugin_dir_path( __FILE__ ) . 'class_util.php';
require_once plugin_dir_path( __FILE__ ) . 'class_lic.php';

use crawlspider_lite_cm\util    as util;
use crawlspider_lite_cm\lix as lix;

//define( 'CSCM_TOKEN_SEP', '<~br~>' );

//util\cscm_fn::log(__FUNCTION__,0,util\cscm_fn::CSCM_TOKEN_SEP);

function cscm_get_cert_details($url_details,$url_parts,$batch_id,$check_record)
{
    try
    {
        $time_start = microtime(true);
        $parsed_url = $url_parts["host"];
        $status["test_name"]="ssl_cert";        
        $stream_context = stream_context_create(array("ssl" => array("capture_peer_cert" => TRUE)));
        $stream_client = stream_socket_client("ssl://".$parsed_url.":443", $errno, $errstr, 	30, STREAM_CLIENT_CONNECT, $stream_context);
		if (!$stream_client) //means it did not return a resource but a false flag so some error
		{
			throw new Exception('Error fetching SSL certificate details');
		}
        $cert = stream_context_get_params($stream_client);
        $certinfo = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);

        $expire_date=date("Y-m-d", $certinfo["validTo_time_t"]);
        $certinfo["expire_date"]=$expire_date;
        $json_cert=cscm_clean_node(json_encode($certinfo));



        /////////////

            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="success"; 
            $status["text_body"]=$json_cert;
            $status["expire_date"]=$expire_date;
            $time_end = microtime(true);
            $status["execution_time"] = ($time_end - $time_start);
            cscm_save_state($url_details,$batch_id,$status,$check_record);

        
    }
    catch (Exception $e)    
    {
        /////////////

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting certificate details";
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record);  

    }
}

function cscm_get_dns_details($url_details,$url_parts,$batch_id,$check_record)
{
    //keep this and see if it produces same result in linux hosts
    //possibly remove ttl values
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]="ip";        
        $result = dns_get_record($url_parts["host"],DNS_ALL);
        $target_arry=array();

        for ($i=0;$i<count($result);$i++)
        {
            $result[$i]["ttl"]='ns'; //this keeps changing so suppress it
            if (!isset($result[$i]['target'])) $result[$i]['target']="na";
        }

        //sort the mx details as they come random
        usort($result, function ($item1, $item2) 
        {
            if ($item1['type'] == $item2['type']) 
            {
                if ($item1['target'] == $item2['target']) return 0;
                return $item1['target'] < $item2['target'] ? -1 : 1;
            }
            else
            {
                return $item1['type'] < $item2['type'] ? -1 : 1;
            }
        });
        
        $result_json=cscm_clean_node(json_encode($result));

        /////////////

        $status["return_status"]="success"; //this should be return html code
        $status["test_status"]="success"; 
        $status["text_body"]=$result_json;
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting DNS details";
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}

function cscm_get_www_test($url_details,$url_parts,$batch_id,$check_record)
{
    try
    {
        $time_start = microtime(true);
        $status["test_name"]="www_test";      
        if (empty($url_parts['scheme'])) $scheme="https";
        else $scheme=$url_parts['scheme'];
        $front_part=substr($url_parts['host'],0,4);
        if ($front_part=="www.") $www_present=true;
        else $www_present=false;
        
        if ($www_present)
        {
            $host = explode('.',$url_parts['host'],2); 
            $base_host=$host[1];

        }
        else
        {
            $base_host=$url_parts['host'];

        }

        //test with www 
        $test_www_url=$scheme."://"."www.".$base_host;
        
        
        $return_www_url=cscm_redirect_test($test_www_url);
        


        
        $test_non_www_url=$scheme."://".$base_host;
        
        $return_non_www_url=cscm_redirect_test($test_non_www_url);
        if ($return_www_url == $return_non_www_url)
        {
            //non www and www URL point redirect to the same
            
      
            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="success";
            $status["text_body"]="URL with www => {$test_www_url} and without www => {$test_non_www_url} point to Final URL = {$return_www_url}";
            $status["expire_date"]="";
            $time_end = microtime(true);
            $status["execution_time"] = ($time_end - $time_start);
            cscm_save_state($url_details,$batch_id,$status,$check_record);


        }
        else
        {

            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="fail";
            $status["text_body"]="URL with www = {$test_www_url} points to URL = {$return_www_url} and without www = {$test_non_www_url} point to URL = {$return_non_www_url}";
            $status["expire_date"]="";
            $time_end = microtime(true);
            $status["execution_time"] = ($time_end - $time_start);
            cscm_save_state($url_details,$batch_id,$status,$check_record);
        }
    }
    catch (Exception $e)    
    {
        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail";
        $status["text_body"]="Exception Error";
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record);         

    }

}


function cscm_get_https_test($url_details,$url_parts,$batch_id,$check_record)
{
    try
    {
        $time_start = microtime(true);
        $status["test_name"]="https_test";
        if (empty($url_parts['scheme'])) $scheme="https";
        else $scheme=$url_parts['scheme'];
        $front_part=substr($url_parts['host'],0,4);
        if ($front_part=="www.") $www_present=true;
        else $www_present=false;
        
        $base_host=$url_parts['host']; 
        
  
        $test_http_url="http"."://".$base_host;
        
        
        $return_www_url=cscm_redirect_test($test_http_url);
        

        $return_parts=parse_url($return_www_url);
        if (empty($return_parts['scheme'])) $return_scheme="http";
        else $return_scheme=$return_parts['scheme'];
        
        if ($return_scheme=="https")
        {

            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="success";
            $status["text_body"]="URL with HTTP = {$test_http_url} redirects to HTTPS URL = {$return_www_url}";
            $status["expire_date"]="";
            $time_end = microtime(true);
            $status["execution_time"] = ($time_end - $time_start);
            cscm_save_state($url_details,$batch_id,$status,$check_record);
 
        }
        else
        {

            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="fail";
            $status["text_body"]="URL with HTTP = {$test_http_url} does not redirect to HTTPS URL = {$return_www_url}";
            $status["expire_date"]="";
            $time_end = microtime(true);
            $status["execution_time"] = ($time_end - $time_start);
            cscm_save_state($url_details,$batch_id,$status,$check_record);            
        }

 
    }
    catch (Exception $e)    
    {
        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail";
        $status["text_body"]="Exception Error";
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record);         

    }

}



function cscm_get_sitemap($url_details,$url_parts,$batch_id,$check_record)
{
    //keep this and see if it produces same result in linux hosts
    //possibly remove ttl values
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]="sitemap";        
        //$result = dns_get_record($url_parts["host"],DNS_ALL);
        util\cscm_fn::log(__FUNCTION__,0," Begin Sitemap scanning ");
        require_once plugin_dir_path( __FILE__ ) . 'class_sitemap.php';
        
        $sitemap_def=crawlspider_lite_cm\sitemap\cscm_sitemap::get_sitemap_details($url_details->target_url);
      //util\cscm_fn::log(__FUNCTION__,0," sitemap details = ".print_r($sitemap_def,true));
        /*
        util\cscm_fn::log(__FUNCTION__,0,"==========================sitemap summary============================");
        util\cscm_fn::log(__FUNCTION__,0,print_r($sitemap_def["sitemap_details"]["sitemap_summary"],true));
        util\cscm_fn::log(__FUNCTION__,0,"======================================================");
      */
        if ($sitemap_def["sitemap_type"]=="none")
        {
            $status["return_status"]="fail"; //this should be return html code
            $status["test_status"]="fail"; 
            $status["text_body"]="Missing sitemap. Sitemap.xml or sitemap_index.xml definitions could not be found";
        }
        else
        {
            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="success";
            $status["text_body"]=implode("<br>",$sitemap_def["sitemap_details"]["sitemap_summary"]);
            if (is_array($sitemap_def["sitemap_details"]["sitemap_summary"]))
            {
                $status["text_body"]=implode("\r\n<br>",$sitemap_def["sitemap_details"]["sitemap_summary"]);
            }
            else
            {
                $status["text_body"]=$sitemap_def["sitemap_details"]["sitemap_summary"];
            }
        }
        /////////////

 
        
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting Sitemap details".$e->getMessage();
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}

function cscm_get_robot_txt($url_details,$url_parts,$batch_id,$check_record)
{
    //keep this and see if it produces same result in linux hosts
    //possibly remove ttl values
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]="robot_txt";        
        //$result = dns_get_record($url_parts["host"],DNS_ALL);
        util\cscm_fn::log(__FUNCTION__,0," Begin robot_txt scanning ");
        require_once plugin_dir_path( __FILE__ ) . 'class_robot_txt.php';
        
        $robot_txt_def=crawlspider_lite_cm\robotext\cscm_robotext::get_robotext_details($url_details->target_url);

        if ($robot_txt_def["robotext_type"]=="none")
        {
            $status["return_status"]="fail"; //this should be return html code
            $status["test_status"]="fail"; 
            $status["text_body"]="Missing robot_txt. robot_txt.xml or robot_txt_index.xml definitions could be found";
        }
        else
        {
            $status["return_status"]="success"; //this should be return html code
            $status["test_status"]="success";
            $status["text_body"]=cscm_clean_node($robot_txt_def["robotext_body"]);
        }
        /////////////

 
        
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting robot_txt details".$e->getMessage();
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}



function cscm_redirect_test($test_url)
{
		
    $response = util\cscm_fn::request_get_url($test_url);

	if (substr($response->url,-1)=="/") $response_url=substr($response->url,0,-1); 
	

	
	return $response_url;

}	

function cscm_get_html_content($url_details,$url_parts,$batch_id)
{
    try

    {
        //clearstatcache - test this if cached serverd  
        //$response = \Requests::get($url_details->target_url);
        $response = util\cscm_fn::request_get_url($url_details->target_url);
        

        $html_str=$response->body;
        
       /// util\cscm_fn::log(__FUNCTION__,0," HTML Source ".$html_str);

        if (!$html_str && !($response->status_code>=400)) //empty content and not error code
        {
            cscm_update_url_status($url_details,$response->status_code,$batch_id,"Empty content");
            return false;
        }
        else if ($response->status_code>=400)
        {
            cscm_update_url_status($url_details,$response->status_code,$batch_id,"Error");
            return false;
        }
        else
        {
            cscm_update_url_status($url_details,$response->status_code,$batch_id,null);
        }
        
        return $html_str;
    }
    catch (Exception $e)
    {

        cscm_update_url_status($url_details,0,$batch_id,"Error:Fetching Site Content - ".addslashes($e->getMessage()));
        return false;
    }
}

function cscm_get_dom_doc($html_str)
{
    $dom = new DomDocument();
	@ $dom->loadHTML($html_str);
    return $dom;

}

function cscm_get_list_of_html_checks()
{
    $html_checks=array(
        "title"=>"tag_content"
        ,"meta"=>"attr_content"
        ,"link"=>"attr_content"
        ,"script"=>"attr_content"
        ,"img"=>"attr_content"
        ,"iframe"=>"attr_content"
        ,"style"=>"attr_content"
        ,"ssl_cert"=>"cert_details"
        ,"ip"=>"dns_details"
        ,"www_test"=>"www_test"
        ,"https_test"=>"https_test"
                );
    return $html_checks;                
}

function cscm_get_list_of_root_checks()
{
    //$root_checks=array("ssl_cert"=>"cert_details");
    $root_checks=array("ssl_cert"=>"cert_details"
                        ,"ip"=>"dns_details","www_test"=>"www_test"
                    ,"https_test"=>"https_test"
                    ,"robot_txt"=>"robot_txt","sitemap"=>"sitemap");
    return $root_checks;                
}



function cscm_collect_content($url_details,$url_parts,$batch_id,$dom,$html_str)
{
    

    $tag_list=array("title","h1","h2");
    
    
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
    
  
    for ($t=0;$t<count($tag_list);$t++)
	{
        $content_str="";
		//$content_str.= "Tag = ".$tag_list[$t]."";
		$all_tags = $dom->getElementsByTagName($tag_list[$t]);
        $status["test_name"]=$tag_list[$t];
		foreach( $all_tags as $tag ) 
        {
		  $tag_content=htmlspecialchars($tag->textContent, ENT_QUOTES);
			$content_str.= "{$tag_list[$t]}=>{$tag_content} \n";
		 
		}
        $status["text_body"]=$content_str;
        cscm_save_state($url_details,$batch_id,$status,$check_record);

	}
    return $content_str;
}

function cscm_get_batch_cycles()
{
    $batch_cycles["names"]=array("s1_snapshot","s2_diff","s3_alert","scan_complete");
    $batch_cycles["seq"]=array("s1_snapshot" => 0,"s2_diff" => 1,"s3_alert" => 2,"scan_complete" => 3);
    return $batch_cycles;
}

function cscm_start_scan($url_details,$url_parts,$batch_id,$dom,$html_str)
{
    $lx_status=lix\cscm_lx::get_lx_status();
   // util\cscm_fn::log(__FUNCTION__,0,print_r($lx_status,true));
    if (!($lx_status["status"]=="valid" || $lx_status["status"]=="valid_temp"))
    {
        util\cscm_fn::log(__FUNCTION__,0,"Check validation failed");
        return;
    }

    util\cscm_fn::log(__FUNCTION__,0," Scanning URL  = ".$url_details->target_url);
    $list_of_checks=cscm_get_active_checks($url_details->list_url_id);
    util\cscm_fn::log(__FUNCTION__,5,"list of checks =".print_r($list_of_checks,true));
    $list_of_url_checkmaps=cscm_get_list_of_html_checks();
    //$list_of_root_checkmaps=cscm_get_list_of_root_checks();
    //get already processed checks if the batch was not complete

    if ($url_details->batch_status=="inprogress")
    {
        $processed_checks=cscm_get_processed_checks($url_details);
    }
    else
    {
        //create empty array 
        $processed_checks=array();
    }    
    //check if the url was in progress and already completed few checks
    //if so then foreach loop should skip the completed checks

    for ($i=0;$i<count($list_of_checks);$i++)
    {
        //sleep(5);
        $url_check_name=$list_of_checks[$i]->check_name;
        $url_check_type=$list_of_checks[$i]->check_type;
        $url_check_category=$list_of_checks[$i]->check_category;
        //util\cscm_fn::log(__FUNCTION__,0,"Start Name : {$url_check_name} Type:{$url_check_type} Category : {$url_check_category} ");
        //check if the check is already done, if so skip it
        if ( isset($processed_checks[$list_of_checks[$i]->url_check_id]) ) 
        {
            util\cscm_fn::log(__FUNCTION__,0,"Already processed - skipping {$url_check_name}");
            continue;
        }    
        if ( $url_details->url_type=="url" && $url_check_category!="url") 
        {
            util\cscm_fn::log(__FUNCTION__,0,"Check is Root type on non-root URL - skipping {$url_check_name} for {$url_details->target_url}");
            continue;
        }
        
            if (!isset($list_of_url_checkmaps[$url_check_name]) && !isset($list_of_url_checkmaps[$url_check_type])) 
            {                
                util\cscm_fn::log(__FUNCTION__,0," ".__LINE__." No mapping function for check name = {$url_check_name} or check type = {$url_check_type} ");
                continue; //there is no such mapping or function to run the check
            }
            if (isset($list_of_url_checkmaps[$url_check_name]))
            {
                $check_routine=$list_of_url_checkmaps[$url_check_name];
            }
            else if  (isset($list_of_url_checkmaps[$url_check_type]))
            {
                $check_routine=$list_of_url_checkmaps[$url_check_type];
            }
            else continue;
            
            $check_fn=cscm_get_check_fn_name($check_routine);
        
        util\cscm_fn::log(__FUNCTION__,0," Check details Name  = {$url_check_name} , routine={$check_fn}");
        if (function_exists($check_fn)) 
        {
            if ($url_check_type=="root")
            {
                 $check_fn($url_details,$url_parts,$batch_id,$list_of_checks[$i]);
            }
            else
            {
                 $check_fn($url_details,$url_parts,$batch_id,$dom,$html_str,$url_check_name,$list_of_checks[$i]);
            }    
    
        }
        else 
        {
            util\cscm_fn::log(__FUNCTION__,0,"Function does not exists  = {$url_check_name} , routine={$check_fn}");
            continue;
        }
    }

    //status code is already updated so we pass it as null so as not update that field
    cscm_update_url_status($url_details,null,$batch_id,"complete");

}

function cscm_get_check_fn_name($check_routine)
{
    return "cscm_get_".$check_routine;
}

function cscm_get_processed_checks($url_details)
{
    $checks=$url_details->batch_processed_checks;
    util\cscm_fn::log(__FUNCTION__,0," processed checks ".$checks);
    //$check_arry=explode(",",$checks);
    $check_json_str='['.$checks.']';
    //util\cscm_fn::log(__FUNCTION__,0," processed checks JSON Str".$check_json_str);
    $check_arry=json_decode($check_json_str,true);
    //util\cscm_fn::log(__FUNCTION__,0," processed checks ".print_r($check_arry,true));
    $check_arry_keys = array_fill_keys($check_arry, 1);
    util\cscm_fn::log(__FUNCTION__,5," processed checks keys ".print_r($check_arry_keys,true));
    return $check_arry_keys;

}

function cscm_get_tag_content($url_details,$url_parts,$batch_id,$dom,$html_str,$tag_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
	{
        $content_str="";
		//$content_str.= "Tag = ".$tag_name."\n";
		$all_tags = $dom->getElementsByTagName($tag_name);
        $status["test_name"]=$tag_name;
        $tag_item_seq=0;
        $time_start = microtime(true);
		foreach( $all_tags as $tag ) 
        {
		    $tag_content=cscm_clean_node($tag->textContent);
			$content_str.= $tag_content.util\cscm_fn::CSCM_TOKEN_SEP;
            $tag_item_seq++;
		 
		}
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$content_str;
        $status["element_count"]=$tag_item_seq;
        cscm_save_state($url_details,$batch_id,$status,$check_record);

	}
    return $content_str;
}

function cscm_get_link_content($url_details,$url_parts,$batch_id,$dom,$html_str,$tag_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
	{
        $content_str="";
		//$content_str.= "Tag = ".$tag_name."\n";
		$all_tags = $dom->getElementsByTagName($tag_name);
        $status["test_name"]=$tag_name;
        $tag_item_seq=0;
        $time_start = microtime(true);
		foreach( $all_tags as $tag ) 
        {

            ///

            if ($tag->hasAttributes()) 
            {
                $href= $tag->getAttribute('href');
                
                $attrib_str=" ";
                foreach ($tag->attributes as $attr) 
                {
                    $name = cscm_clean_node($attr->nodeName);
                    
                    if ($name=="href")
                    {
                       $value=cscm_clean_node(cscm_get_absolute_url($url_parts,$attr->nodeValue));      
                    }
                    else $value = cscm_clean_node($attr->nodeValue);

                    $attrib_str.= " $name=$value ".util\cscm_fn::CSCM_ATTRIB_SEP;;
                }
                $anchor_text=cscm_clean_node($tag->textContent);
                if (empty($anchor_text))
                {
                    //check if there is an img link
                    $anchor_children = $tag->getElementsByTagName('img');
                    if ($anchor_children->length > 0)
                    {
                        $anchor_child=$anchor_children->item(0);
                        $anchor_child_attr=cscm_get_tag_flat_value($anchor_child);
                        $anchor_text="IMG:".cscm_clean_node($anchor_child_attr);
                    }
                    
                }
                //$tag_content=cscm_clean_node($tag->textContent);
                $content_str.= "{$anchor_text} >> {$attrib_str}".util\cscm_fn::CSCM_TOKEN_SEP;
                $tag_item_seq++;
            }    
		 
		}
        $status["text_body"]=$content_str;
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["element_count"]=$tag_item_seq;
        cscm_save_state($url_details,$batch_id,$status,$check_record);

	}
    return $content_str;
}


function cscm_get_body_content($url_details,$url_parts,$batch_id,$dom,$html_str,$tag_name,$check_record)
{
    require_once plugin_dir_path( __FILE__ ) . 'class_tag_body.php';
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
	{
        
        $status["test_name"]=$tag_name;
        $tag_item_seq=0;
        $time_start = microtime(true);
        
      
            $body_details=crawlspider_lite_cm\body_tag\cscm_body_tag::get_body_tag_details($dom,$html_str);
		    $body_tag_text=cscm_clean_node($body_details["text_body"]);
		 

        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$body_tag_text;
        $status["element_count"]=$body_details["element_count"];
        cscm_save_state($url_details,$batch_id,$status,$check_record);

	}
    return $body_tag_text;
}


function cscm_get_attr_content($url_details,$url_parts,$batch_id,$dom,$html_str,$tag_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
	{
        $content_str="";
		//$content_str.= "Tag = ".$tag_name."\n";
		$all_tags = $dom->getElementsByTagName($tag_name);
        $status["test_name"]=$tag_name;
        $tag_item_seq=0;
        $time_start = microtime(true);
		foreach( $all_tags as $tag ) 
        {

            if ($tag->hasAttributes()) 
            {
                
                
                $attrib_str=" ";
                foreach ($tag->attributes as $attr) 
                {
                    $name = cscm_clean_node($attr->nodeName);
                    $value = cscm_clean_node($attr->nodeValue);
                    $attrib_str.= " $name=$value ".util\cscm_fn::CSCM_ATTRIB_SEP;
                }
                
                $tag_content=cscm_clean_node($tag->textContent);
                $content_str.= "{$tag_content} >> {$attrib_str}".util\cscm_fn::CSCM_TOKEN_SEP;
                $tag_item_seq++;
            }    
		}
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$content_str;
        $status["element_count"]=$tag_item_seq;
        cscm_save_state($url_details,$batch_id,$status,$check_record);
 
	}
    return $content_str;
}

function cscm_get_custom_tag_content($url_details,$url_parts,$batch_id,$dom,$html_str,$tag_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
	{
        $content_str="";
		//$content_str.= "Tag = ".$tag_name."\n";
		$all_tags = $dom->getElementsByTagName($tag_name);
        $status["test_name"]=$tag_name;
        $tag_item_seq=0;
        $time_start = microtime(true);
		foreach( $all_tags as $tag ) 
        {
		    $tag_content=cscm_clean_node($tag->textContent);
			$content_str.= "{$tag_item_seq}=>{$tag_content}".util\cscm_fn::CSCM_TOKEN_SEP;
            $tag_item_seq++;
		 
		}
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$content_str;
        $status["element_count"]=$tag_item_seq;
        cscm_save_state($url_details,$batch_id,$status,$check_record);

	}
    return $content_str;
}

function cscm_get_custom_class_content($url_details,$url_parts,$batch_id,$dom,$html_str,$check_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    
    $status["expire_date"]="";
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]=$check_name;        
        $status["test_type"]="xpath"; 
        //$result = dns_get_record($url_parts["host"],DNS_ALL);
        util\cscm_fn::log(__FUNCTION__,0," Begin ".$check_name);
        require_once plugin_dir_path( __FILE__ ) . 'class_xpath.php';
        
        $search_details=crawlspider_lite_cm\xpath\cscm_xpath::get_class_details($dom,$html_str,$check_name);
       
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$search_details["search_element_body"];
        $status["element_count"]=$search_details["element_count"];
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting xpath details".$e->getMessage();
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}

function cscm_get_search_xpath_content($url_details,$url_parts,$batch_id,$dom,$html_str,$check_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    
    $status["expire_date"]="";
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]=$check_name;        
        $status["test_type"]="xpath"; 
        //$result = dns_get_record($url_parts["host"],DNS_ALL);
        util\cscm_fn::log(__FUNCTION__,0," Begin ".$check_name);
        require_once plugin_dir_path( __FILE__ ) . 'class_xpath.php';
        
        $search_details=crawlspider_lite_cm\xpath\cscm_xpath::get_xpath_details($dom,$html_str,$check_name);
       
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$search_details["search_element_body"];
        $status["element_count"]=$search_details["element_count"];
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting xpath details".$e->getMessage();
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}

function cscm_get_search_regex_content($url_details,$url_parts,$batch_id,$dom,$html_str,$check_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]=$check_name;   
		$status["test_type"]="regex"; 		
        //$result = dns_get_record($url_parts["host"],DNS_ALL);
        util\cscm_fn::log(__FUNCTION__,0," Begin ".$check_name);
        require_once plugin_dir_path( __FILE__ ) . 'class_xpath.php';
        
        $search_details=crawlspider_lite_cm\xpath\cscm_xpath::get_regex_details($dom,$html_str,$check_name);
       
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$search_details["search_element_body"];
        $status["element_count"]=$search_details["element_count"];
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting xpath details".$e->getMessage();
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}

function cscm_get_search_string_content($url_details,$url_parts,$batch_id,$dom,$html_str,$check_name,$check_record)
{
    $status["return_status"]="success"; //this should be return html code
    $status["test_status"]="success"; 
    $status["expire_date"]="";
    try
    {    
        $time_start = microtime(true);
        $status["test_name"]=$check_name;   
		$status["test_type"]="string"; 		
        //$result = dns_get_record($url_parts["host"],DNS_ALL);
        util\cscm_fn::log(__FUNCTION__,0," Begin ".$check_name);
        require_once plugin_dir_path( __FILE__ ) . 'class_xpath.php';
        
        $search_details=crawlspider_lite_cm\xpath\cscm_xpath::get_string_details($dom,$html_str,$check_name);
       
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        $status["text_body"]=$search_details["search_element_body"];
        $status["element_count"]=$search_details["element_count"];
        cscm_save_state($url_details,$batch_id,$status,$check_record);        
    }
    catch (Exception $e)
    {

        $status["return_status"]="fail"; //this should be return html code
        $status["test_status"]="fail"; 
        $status["text_body"]="Exception Error while getting xpath details".$e->getMessage();
        $status["expire_date"]="";
        $time_end = microtime(true);
        $status["execution_time"] = ($time_end - $time_start);
        cscm_save_state($url_details,$batch_id,$status,$check_record); 
    }
}

function cscm_get_tag_flat_value($tag)
{
	if ($tag->hasAttributes()) 
	{
		$attrib_str=" ";
	  foreach ($tag->attributes as $attr) 
	  {
            $name = cscm_clean_node($attr->nodeName);
            $value = cscm_clean_node($attr->nodeValue);
			$attrib_str.= " '$name' = '$value' ";
	  }
		
	  return $attrib_str;			  
	}
	else return "";
}

function cscm_clean_node($value)
{
    return addslashes(trim($value));
    //return htmlspecialchars(trim($value), ENT_QUOTES);
}

function cscm_get_absolute_url($base_url_parts,$url)
{
    
    $url_parts = parse_url($url);
    // Is it a relative link (URI)?
    
    if ( !isset($url_parts['host']) || ($url_parts['host'] == '') ) 
    {
       return $base_url_parts["scheme"]."://".$base_url_parts["host"]."/".$url; 
    }
    else return $url;
}

function cscm_scan_and_save($url_details,$url_parts,$batch_id)
{

/*
        
        $status["test_name"]="ip";
        $status["return_status"]="success"; //this should be return html code
        $status["test_status"]="success";
        $status["text_body"]=$result_json;
        $status["expire_date"]="";
*/    

}

/**
 * called from each checks to save the state of the check on the target url.
 *
 * @author     Nilesh Jethwa <contact@infocaptor.com>
 */
function cscm_save_state($url_details,$batch_id,$status,$check_record)
{
    try
    {
        global $wpdb;
        if (!isset($status["expire_date"]) || empty($status["expire_date"]))
        {
            $expire_date_str="null";
        }
        else
        {
            $expire_date_str="STR_TO_DATE('{$status["expire_date"]}', '%Y-%m-%d')";

        }

        if (!isset($status["element_count"]))
        {
            $status["element_count"]=1;
        }
        
        $status["test_name"]=cscm_clean_node($status["test_name"]);
        if (!isset($status["test_type"])) $status["test_type"]=$status["test_name"];

        //clear any whitespace near the edges
        $status["text_body"]=trim($status["text_body"]);

        $url_sql="insert into {$wpdb->prefix}xcm_url_summary (list_url_id,target_url,test_type,test_name,
        return_status,test_status,action_status,error_try_count,text_body,batch_id,creation_date,test_execution_time,expire_date,element_count) 
        values ('{$url_details->list_url_id}','{$url_details->target_url}','{$status["test_type"]}','{$status["test_name"]}','{$status["return_status"]}','{$status["test_status"]}',
        'pending',0,'{$status["text_body"]}','{$batch_id}',now(),{$status["execution_time"]}, {$expire_date_str},{$status["element_count"]} )
        ON DUPLICATE KEY UPDATE text_body='{$status["text_body"]}' , expire_date={$expire_date_str}
        ,creation_date=now(), test_execution_time={$status["execution_time"]}
        ";
        //util\cscm_fn::log(__FUNCTION__,0,$url_sql);
        $results=$wpdb->get_results($url_sql); 

        //now mark the URL with the batch and status 
        $update_sql="
        update  {$wpdb->prefix}xcm_list_of_urls
        set batch_id='{$batch_id}'
        , batch_status='inprogress'
        , batch_processed_checks=concat(COALESCE(batch_processed_checks,'\"\"'),',','\"{$check_record->url_check_id}\"')
        where list_url_id='{$url_details->list_url_id}'
		";
       // util\cscm_fn::log(__FUNCTION__,0,$update_sql);
        $results=$wpdb->get_results($update_sql); 

    }
    catch (Exception $e) 
    {
        util\cscm_fn::log(__FUNCTION__,0,"error insert");
    }    
}


function cscm_get_active_checks($list_url_id)
{
	try
	{
		global $wpdb;
        util\cscm_fn::log(__FUNCTION__,0," URL = ".$list_url_id);
		$checks_sql="
        select * from
            (
            SELECT  xcd.*
            FROM {$wpdb->prefix}xcm_check_details xcd
            where list_url_id=-1
            and xcd.check_active='Y'
            and check_name in ('ssl_cert','https_test','www_test','img','ip','link','meta','noscript','script','style','title')
            ) as x
            order by check_category desc
";
        //util\cscm_fn::log(__FUNCTION__,0,$checks_sql);
		$resultset = $wpdb->get_results ($checks_sql,OBJECT);
        return $resultset;
	//now mark the URL with the batch and status 


	}
	catch (Exception $e) 
	{
		
	}
}

function cscm_update_batch($batch_id,$batch_status)
{
	try
	{
		global $wpdb;
		$url_sql="update {$wpdb->prefix}xcm_batch_runs 
		set batch_status ='{$batch_status}'
		where batch_id='{$batch_id}'
		";
		
		$results=$wpdb->get_results($url_sql); 

	//now mark the URL with the batch and status 


	}
	catch (Exception $e) 
	{
		
	} 	
}

function cscm_update_url_status($url_details,$status_code,$batch_id,$batch_status)
{
	try
	{
		global $wpdb;

        if (isset($status_code)) $update_fields[]="last_status_code='{$status_code}'";
        if (isset($batch_id)) $update_fields[]="batch_id='{$batch_id}'";
        if (isset($batch_status)) $update_fields[]="batch_status='{$batch_status}'";

        $update_field_sql=implode(",",$update_fields);
        $update_sql="update  {$wpdb->prefix}xcm_list_of_urls
		set {$update_field_sql}
		where list_url_id='{$url_details->list_url_id}'
		";
		util\cscm_fn::log(__FUNCTION__,0,$update_sql);
		$results=$wpdb->get_results($update_sql); 

	//now mark the URL with the batch and status 


	}
	catch (Exception $e) 
	{
		
	} 	
}

function cscm_reset_url_batch_status($batch_id)
{
	try
	{
		global $wpdb;
		$update_sql="update  {$wpdb->prefix}xcm_list_of_urls
		set  batch_status='not_started'		
        , batch_processed_checks=null
		where batch_id='{$batch_id}'
        and batch_status<>'complete'
		";
		
		$results=$wpdb->get_results($update_sql); 

	//now mark the URL with the batch and status 


	}
	catch (Exception $e) 
	{
		
	} 	
}

function cscm_get_urls_for_processing($batch_id)
{
    global $wpdb;
    $url_sql="
    SELECT * FROM {$wpdb->prefix}xcm_list_of_urls
    where active='Y'
    and list_url_id not in 
    (
        select list_url_id from {$wpdb->prefix}xcm_list_of_urls
        where batch_id='{$batch_id}'
        and batch_status='complete'
    )";
    $results=$wpdb->get_results($url_sql);
    return $results;

}

