<?php

namespace crawlspider_lite_cm\robotext;


require_once plugin_dir_path( __FILE__ ) . 'class_util.php';
require_once  plugin_dir_path( __FILE__ ) .'libs/vendor/robottxt/RobotsTxtParser.php';


use crawlspider_lite_cm\util    as util;

class cscm_robotext
{

    static function get_robotext_details($url)
    {
    
        $robotext_def=static::get_robotext_definitions($url);
        //print_r($robotext_def);
        
        if ($robotext_def["robotext_type"]=="none") 
        {
            //echo "<h1>robotext or Index not found</h1>";
            return $robotext_def;
        }
        
        if ($robotext_def["robotext_type"]=="map")
        {
            //echo "<h2>Only robotext exists</h2>".$robotext_def["robotext_url"];
            $robotext_def["robotext_input_url"]=$robotext_def["robotext_url"];
            $robotext_def["robotext_details"]=static::get_parsed_robotextbody($robotext_def["robotext_url"],$robotext_def["robotext_body"]);
        }
    
        return $robotext_def;
        
    }    

    static function get_robotext_definitions($url)
    {
        //start with these urls

        $robotext_url=$url."/robots.txt";	
        $return_status=[];
        
        $response = util\cscm_fn::request_get_url($robotext_url);
        $return_status["robotext_url"]=$response->url;
        if ($response->status_code!=200) //index does not exists
        {
            $robotext_index_exists=false;
            //now check robotext exists
            $response = util\cscm_fn::request_get_url($robotext_url);
            $return_status["robotext_url"]=$response->url;
            if ($response->status_code!=200) 
            {
                $robotext_exists=false;
                $return_status["robotext_type"]="none";
                return $return_status;
            }
            else
            {
                $robotext_exists=true;
                $robotext_html=$response->body;
            }
        }
        else
        {
            $robotext_index_exists=true;
            $robotext_html=$response->body;
        }
     
        
        $return_status["robotext_body"]=$robotext_html;
        $return_status["robotext_type"]="map";
    
        return $return_status;
    }
    

   
    static function get_parsed_robotextbody($robotext_url,$robotext_body)
    {

        $parser = new \RobotsTxtParser\RobotsTxtParser($robotext_body);
	    $parsed_rules=print_r($parser->getRules(),true);

        return $parsed_rules;
    
    }


    

}

?>