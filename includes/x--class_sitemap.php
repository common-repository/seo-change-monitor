<?php

namespace crawlspider_lite_cm\sitemap;


require_once plugin_dir_path( __FILE__ ) . 'class_util.php';

use crawlspider_lite_cm\util    as util;

class cscm_sitemap
{

    static function get_sitemap_details($url)
    {
    
        $sitemap_def=static::get_sitemap_definitions($url);
        //print_r($sitemap_def);
        
        if ($sitemap_def["sitemap_type"]=="none") 
        {
            //echo "<h1>Sitemap or Index not found</h1>";
            return $sitemap_def;
        }
        
        if ($sitemap_def["sitemap_type"]=="index")
        {
            $sitemap_arry=static::get_sitemap_list_from_index($sitemap_def["sitemap_body"]);

            $sitemap_def["sitemap_input_url_or_list"]=$sitemap_arry;
            $sitemap_def["sitemap_details"]=static::get_urls_from_list_of_sitemaps($sitemap_arry); //get_sitemap_urls_from_list($sitemap_arry); //get_urls_from_list_of_sitemaps
            
        }
        else if ($sitemap_def["sitemap_type"]=="map")
        {
            //echo "<h2>Only Sitemap exists</h2>".$sitemap_def["sitemap_url"];
            $sitemap_def["sitemap_input_url_or_list"]=$sitemap_def["sitemap_url"];
            $sitemap_def["sitemap_details"]=static::get_urls_from_sitemapbody($sitemap_def["sitemap_url"],$sitemap_def["sitemap_body"]);
        }
    
        return $sitemap_def;
        
    }    

    static function get_sitemap_definitions($url)
    {
        //start with these urls
        $sitemap_index_url=$url."/sitemap_index.xml";
        $sitemap_url=$url."/sitemap.xml";	
        $return_status=[];
        util\cscm_fn::log(__FUNCTION__,0," sitemap urls Index={$sitemap_index_url} , sitemap={$sitemap_url}");
        $response = util\cscm_fn::request_get_url($sitemap_index_url); 
        $return_status["sitemap_url"]=$response->url;
        if ($response->status_code!=200) //index does not exists
        {
            $sitemap_index_exists=false;
            //now check sitemap exists
            $response = util\cscm_fn::request_get_url($sitemap_url);
            $return_status["sitemap_url"]=$response->url;
            if ($response->status_code!=200) 
            {
                $sitemap_exists=false;
                $return_status["sitemap_type"]="none";
                return $return_status;
            }
            else
            {
                $sitemap_exists=true;
                $sitemap_html=$response->body;
            }
        }
        else
        {
            $sitemap_index_exists=true;
            $sitemap_html=$response->body;
        }
    
        //scan the body to determine if it is a sitemapindex or sitemap
        //util\cscm_fn::log(__FUNCTION__,0," sitemap body=".$sitemap_html);
        libxml_use_internal_errors(true);
        $dom = new \DomDocument();
        $dom->loadXML($sitemap_html, LIBXML_PARSEHUGE);
    
        if ( !$dom) 
        {
            $errors = libxml_get_errors();
            util\cscm_fn::log(__FUNCTION__,0," sitemap error=".print_r($errors,true));
            $return_status["sitemap_type"]="none";
            return $return_status;
        }

        $sitemap_index = $dom->getElementsByTagName('sitemapindex');
        
        
        $return_status["sitemap_body"]=$sitemap_html;
        if($sitemap_index->length == 0)
        {
            //If you are here this means the content does not have sitemapindex. 
            //now verify if it has any sitemap tags
            $urlset_index = $dom->getElementsByTagName('urlset');
            if($urlset_index->length == 0)
            {
                $return_status["sitemap_type"]="none";
            }
            else
            {
                $return_status["sitemap_type"]="map";
            }    
        }
        else
        {
            $return_status["sitemap_type"]="index";
        }
    
        return $return_status;
    }
    
    static function get_sitemap_list_from_index($sitemap_body)
    {
    
        $dom = new \DomDocument();
        $dom->loadXML($sitemap_body, LIBXML_PARSEHUGE);
    
        $sitemap_index = $dom->getElementsByTagName('sitemapindex');
        if($sitemap_index->length == 0)
        {
           // echo "<h2>Sitemap Index does not exist</h2><br>";
            return false; //no entries in the sitemap index
        }
        else
        {
            //echo "<h2>Sitemap Index does exist</h2><br>";
            //get_all_sitemaps($sitemap_index->item(0))	;
            $smi_search=$sitemap_index->item(0);
            //get each sitemap
            $sitemaps= $smi_search->getElementsByTagName('sitemap');
            $sitemap_arry=[];
            // loop through each item and display information
            foreach($sitemaps as $sitemap) 
            {
                $this_sitemap=$sitemap->getElementsByTagName('loc')->item(0)->nodeValue;
                $sitemap_arry[]=$this_sitemap;
               // print '<h3>Sitemap = </h3>'.$this_sitemap.'<br />';
            }
            return $sitemap_arry;
        }
    
    }
    
    static function get_urls_from_list_of_sitemaps($sitemap_arry)
    {
        $url_arry=[];
        //print_r($sitemap_arry);
        
        for ($i=0;$i<count($sitemap_arry);$i++)
        {
            //echo "<h2>Processing Sitemap </h2>".$sitemap_arry[$i];
            $response = util\cscm_fn::request_get_url($sitemap_arry[$i]);
            $xml_str=$response->body;
            if ($response->status_code!=200) continue;
            $list_of_urls=static::get_urls_from_sitemapbody($sitemap_arry[$i],$xml_str);
            //array_push($url_arry,$list_of_urls);
            $url_arry["sitemap_urls"][$sitemap_arry[$i]]=$list_of_urls["sitemap_urls"];
            $url_arry["sitemap_summary"][]=$list_of_urls["sitemap_summary"];
        }
        return $url_arry;
    }
    
    static function get_urls_from_sitemapbody($sitemap_url,$xml_str)
    {
        $url_arry=[];
        $dom = new \DomDocument();
        $dom->loadXML($xml_str, LIBXML_PARSEHUGE);
        $urlset_index = $dom->getElementsByTagName('urlset');
        if($urlset_index->length == 0)
        {
           // echo "<h2>Sitemap Index does not exist</h2><br>";
            return false; //no entries in the sitemap index
        }        
        $url_parent=$urlset_index->item(0);
        $urls= $url_parent->getElementsByTagName('url');
        
        foreach($urls as $url) 
        {
            $this_siteurl=$url->getElementsByTagName('loc')->item(0)->nodeValue;
            $url_arry[]=$this_siteurl;
           // print '<h4>URL = </h4>'.$this_siteurl.'<br />';
                
        }

        $return_url_arry=[];
        $return_url_arry["sitemap_urls"]=$url_arry;
        $return_url_arry["sitemap_summary"]=$sitemap_url." count=".count($url_arry);
        return $return_url_arry;
    
    }

    static function array_subcount_string($sitemap_def)
    {
        if ($sitemap_def["sitemap_type"] == "index")
        {
            // util\cscm_fn::log(__FUNCTION__,0," ".__LINE__.count(array_keys($multi_arry)));
            $subcount_arry=[];
            $sitemap_urls=$sitemap_def["sitemap_all_urls"];
           // util\cscm_fn::log(__FUNCTION__,0," ".print_r(array_keys($sitemap_urls),true));
            /*for ($i=0;$i<count($sitemap_urls);$i++)
            {
                $sitemap_index=$sitemap_urls[$i];
                $subcount_arry[]=
            }*/
        }
    }
    

}

?>