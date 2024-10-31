<?php

namespace crawlspider_lite_cm\diff;

require_once  plugin_dir_path( __FILE__ ) .'libs/vendor/phpdiff/FineDiff_x.php';

require_once plugin_dir_path( __FILE__ ) . 'class_util.php';

use GorHill\FineDiff\FineDiff;
use GorHill\FineDiff\FineDiffHTML;


use crawlspider_lite_cm\util    as util;

class cscm_diff
{

    static function get_html_from_word_compare($old_text,$new_text)
    {
        $opcodes=FineDiff::getDiffOpcodes($old_text, $new_text,FineDiff::$wordGranularity);
        
        $html_text= FineDiffHTML::renderDiffToHTMLFromOpcodes($old_text, $opcodes,null,false);

        return addslashes($html_text);
    }

    static function get_diff_changes($tag_name,$old_text,$new_text)
    {
        //get basic html representation of the diff check
        $final_text=self::get_html_from_word_compare($old_text,$new_text);
        
        //begin tag at the end and ending tag at beginning needs to be adjusted
        $line_tokens=explode(util\cscm_fn::CSCM_TOKEN_SPLIT_SEP,$final_text);
        //print_r($line_tokens);
        $token_arry=array();
        
        for ($i=0;$i<count($line_tokens)-1;$i++)
        {
            $line_tokens[$i]=trim($line_tokens[$i]);
            $line_tokens[$i+1]=trim($line_tokens[$i+1]);
            $end_token=mb_substr($line_tokens[$i],-5);
            //$end_token1=mb_substr($line_tokens[$i+1],-5);
            //$begin_token=mb_substr($line_tokens[$i],0,6);
            $begin_token1=mb_substr($line_tokens[$i+1],0,6);		
            if ($end_token=="<ins>"||$end_token=="<del>") 
            {
                $line_tokens[$i]=mb_substr($line_tokens[$i], 0, -5);
                $line_tokens[$i+1]=$end_token.$line_tokens[$i+1];
                
            }
            if ($begin_token1=="</ins>"||$begin_token1=="</del>") 
            {
                $line_tokens[$i+1]=mb_substr($line_tokens[$i+1], 6);
                $line_tokens[$i]=$line_tokens[$i].$begin_token1;
                $end_token=$begin_token1; //assing this to the current token so it reflects the correct state
            }		
            $del_pos=mb_strpos($line_tokens[$i], "<del>", 0);
            $ins_pos=mb_strpos($line_tokens[$i], "<ins>", 0);
            $delx_pos=mb_strpos($line_tokens[$i], "</del>", 0);
            $insx_pos=mb_strpos($line_tokens[$i], "</ins>", 0);
            //echo $line_tokens[$i]."<br>";
            if ($del_pos !== false && $ins_pos === false)
            {
                if ($del_pos==0 && ($delx_pos===false||(mb_strlen($line_tokens[$i])-6==$delx_pos))) $state="Removed";
                else $state="Keywords removed";
            }	
            else if ($del_pos !== false && $ins_pos !== false)
            {
                $state="Keywords replaced";
            }	
            else if ($del_pos === false && $ins_pos !== false)
            {
              //  echo "<hr>".$end_token;
                if ($ins_pos==0 && ($insx_pos===false||(mb_strlen($line_tokens[$i])-6==$insx_pos))) $state="New";
                else $state="Keywords added";
            }		
            else if ($del_pos === false && $ins_pos === false)
            {
                $state="No Change";
            }		
            
            
            if ($state!="No Change")
            {
                $token_arry[]=array("str" => addslashes($line_tokens[$i]),"len" => strlen($line_tokens[$i]),"state" => $state,"del_pos" => $del_pos, "ins_pos" => $ins_pos, "delx_pos" => $delx_pos, "insx_pos" => $insx_pos);
            }
            
        }
        $changes['final_html']=$final_text;
        $changes["token_arry"]=$token_arry;
        return $changes;
        
    }
    
}

?>