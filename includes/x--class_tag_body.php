<?php

namespace crawlspider_lite_cm\body_tag;


require_once plugin_dir_path( __FILE__ ) . 'class_util.php';



use crawlspider_lite_cm\util    as util;

class cscm_body_tag
{

    static function get_body_tag_details($dom,$html_str)
    {
    
        $xp    = new \DOMXPath($dom);
        $nodes = $xp->query('/html/body//text()[
            not(ancestor::script) and
            not(ancestor::style) and
            not(normalize-space(.) = "")
        ]');
        
        
        $text_value="";
        $text_cnt=0;
        foreach($nodes as $node) 
        {
            $text_value.=util\cscm_fn::clean_node($node->textContent).util\cscm_fn::CSCM_TOKEN_SEP;            
            $text_cnt++;
        }
        
        $return_body_details=[];
        $return_body_details["element_count"]=$text_cnt;
        $return_body_details["text_body"]=$text_value;
    
        return $return_body_details;
        
    }    

    

}

?>