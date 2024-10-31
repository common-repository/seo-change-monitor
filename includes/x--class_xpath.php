<?php

namespace crawlspider_lite_cm\xpath;


require_once plugin_dir_path( __FILE__ ) . 'class_util.php';



use crawlspider_lite_cm\util    as util;

class cscm_xpath
{

    static function get_xpath_details($dom,$html_str,$xpath_str)
    {
    
        $xp    = new \DOMXPath($dom);
        $nodes = $xp->query($xpath_str);

        //var_dump($nodes);
        $text_value="";
        $i=0;
        $search_arry=[];
        foreach($nodes as $node) 
        {
            $search_arry[]=trim($node->textContent);
            $i++;
        }
    
    
        
        $return_details=[];
        $return_details["element_count"]=$i;
        $return_details["search_element_list"]=$search_arry;
        $return_details["search_element_body"]=implode(util\cscm_fn::CSCM_TOKEN_SEP,$search_arry);
    
        return $return_details;
        
    }    

    static function get_regex_details($dom,$html_str,$regex_str)
    {
        util\cscm_fn::log(__FUNCTION__,0," regex str = ".$regex_str);
        preg_match_all($regex_str, $html_str, $matches);
        $return_details=[];
        $return_details["element_count"]=count($matches[0]);
        $return_details["search_element_list"]=$matches[0];
        $return_details["search_element_body"]=implode(util\cscm_fn::CSCM_TOKEN_SEP,$matches[0]);
    
        return $return_details;
        
    }

    static function get_string_details($dom,$html_str,$regex_str)
    {
        $search_str="/{$regex_str}/i";
        util\cscm_fn::log(__FUNCTION__,0," regex str = ".$search_str);
        preg_match_all($search_str, $html_str, $matches);
        $return_details=[];
        $return_details["element_count"]=count($matches[0]);
        $return_details["search_element_list"]=$matches[0];
        $return_details["search_element_body"]=implode(util\cscm_fn::CSCM_TOKEN_SEP,$matches[0]);
    
        return $return_details;
        
    }

    static function get_class_details($dom,$html_str,$class_str)
    {
    
        $xp    = new \DOMXPath($dom);
        //   $spaner = $finder->query("//*[contains(@class, '$classname')]");
        $nodes = $xp->query("//*[contains(@class, '{$class_str}')]");

        //var_dump($nodes);
        $text_value="";
        $i=0;
        $search_arry=[];
        foreach($nodes as $node) 
        {
            $search_arry[]=trim($node->textContent);
            $i++;
        }
    
    
        
        $return_details=[];
        $return_details["element_count"]=$i;
        $return_details["search_element_list"]=$search_arry;
        $return_details["search_element_body"]=implode(util\cscm_fn::CSCM_TOKEN_SEP,$search_arry);
    
        return $return_details;
        
    }     

}

?>