<?php

namespace crawlspider_lite_cm\util;


class cscm_fn
{

    const CSCM_TOKEN_SEP = ' <~br~> ';
    const CSCM_TOKEN_SPLIT_SEP = '<~br~>';
    const CSCM_ATTRIB_SEP = '@@~';
    const CSCM_BATCH_FREQ_MINS = 1440; //24 hours * 60 mins
    const CSCM_BATCH_FORMAT = 'Y-m-d-H-i';

    static function log($orig,$level,$message)
    {

        //if ($level>=5) return;
/*
        $fileinfo = ' ';
        $backtrace = debug_backtrace();
        if (!empty($backtrace[0]) && is_array($backtrace[0])) 
        {
          $fileinfo = $backtrace[0]['file'] . ":" . $backtrace[0]['line'];
        }
        echo "calling file info: $fileinfo\n";
        */
        $dateStamp=date("Y_m_d", time());

        $log_dir=self::get_local_path('log');    
        $logfile="{$log_dir}/log_".$dateStamp.".log";

        $str = "[" . date("Y/m/d h:i:s", time()) . "]   ".$orig." => " . $message;
        
        self::to_file($logfile,$str . "\r\n","a");

        
    }

    static function to_file($file_path,$str,$mode)
    {
        $fd = fopen($file_path, $mode);
        fwrite($fd, $str );
        fclose($fd);
    }


    static function get_sql_result($sql)
    {
        global $wpdb;

        $result = $wpdb->get_results ($sql,ARRAY_N);
        return $result;	
    }

    static function get_sql_result_a($sql)
    {
        global $wpdb;

        $result = $wpdb->get_results ($sql,ARRAY_A);
        return $result;	
    }

    static function run_dml_sql($sql)
    {
        global $wpdb;
        //$wpdb->suppress_errors(false);
        try
        {
           $result= $wpdb->query($sql);
           //util\cscm_fn::log(__FUNCTION__,0," SQL status =".$result);
           if (false === $result) return "fail";
           else return "success";
           /*if($wpdb->last_error !== '') 
           {
                $wpdb->print_error();
           }*/
           //return array("status" => true,"result" => $result);
        }
        catch (\Exception $e)
        {
            //util\cscm_fn::log(__FUNCTION__,0," ERROR: ".$sql);
            return array("status" => false,"result" => "Error");
        }
    }

    static function build_table_structure($table_elem_id,$table_args)
    {
    
        $table_id=$table_elem_id;
        $display_columns=$table_args['display_columns'];
        $display_row_count=100;
    
    
          $result_set =  static::get_sql_result($table_args["sql"]);//cscm_get_post_details_sql();
          $table_row=" ";
          $table_header=static::get_table_header($table_args);
        /*  "	 <thead style='background-color:steelblue;color:white;'>
                      <tr><td>ID</td> <td>Author</td> <td>Date</td> <td>Title</td> <td>Category</td> <td>Tags</td></tr>
              </thead>
          ";
      */
          $table_body="<tbody>";
          $row_class=array('odd','even');
          for ($p=0; $p<count($result_set); $p++)
          {
    
              $row_stripe_class=$row_class[$p % 2];
              
              $table_row.="<tr>";
              for ($dc=0;$dc<count($display_columns); $dc++)
              {
                  $table_cell_value=$result_set[$p][$dc];//cscm_get_cell_value($post_list[$p],$display_columns[$dc]);
                $table_row.="<td>{$table_cell_value}</td>";
              }	  
              $table_row.="</tr>"; 
              $table_body.=$table_row;
              
              $table_row=" ";
          }
          $table_body.="</tbody>";
          //wp_reset_postdata();
          $table_html="<table id='{$table_id}' id_name='{$table_id}' cellpadding='5' cellspacing='0' style='width:100%' class='cscm_datatable display' >"
                      //.cscm_get_table_col_width()
                      .$table_header.$table_body."</table>";

          $return_data["dataset"]=$result_set;
          $return_data["table_html"]=$table_html;
          //return json_encode($return_data);
          return $return_data;
    }

    static function get_table_header($table_args)
    {
        
     // $column_property=cscm_get_column_property_array();
      //util\cscm_fn::log(__FUNCTION__,0," column properties = ".print_r($column_property,true));
      $table_header_style="background-color:white;color:black;";
      $table_header="	 <thead style='{$table_header_style}'>";
      
      $table_header."<tr>";
    
      for ($dc=0; $dc < count($table_args['display_columns']) ; $dc++)
      {
          $display_column_name=$table_args['display_columns'][$dc]; //get the column name
    
          //now use the column name to find the display title from json property
          //$display_title=$column_property[$display_column_name]["title"]; //grab the title index from the multi array 
          $table_header.="<td>".$display_column_name."</td>";//<tr><td>ID</td> <td>Author</td> <td>Date</td> <td>Title</td> <td>Category</td> <td>Tags</td></tr>
      }	  
      $table_header."</tr>";
      
      $table_header.="</thead>";
      return $table_header;
    }
    
    static function request_get_url($url)
    {

        //$response = wp_remote_get( $url );
        //self::log(__FUNCTION__,0," WP_REMOTE_GET URL = ".print_r($response,true)); 
        
        $options = array('user-agent' => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
                        ,'verify' => false
                        ,'verifyname' => false
                        );
        $headers=array();                        
        self::log(__FUNCTION__,0," URL = ".$url);
        return \Requests::get($url,$headers,$options);


    }


    static function clean_node($value)
    {
        return addslashes(trim($value));
        //return htmlspecialchars(trim($value), ENT_QUOTES);
    }

    /**##SAAS## */
    static function get_local_path($subdir)
    {
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_sub_dir = wp_normalize_path($upload_dir . '/crawlspider/'.$subdir);
        if ( ! file_exists( $upload_sub_dir ) )  
        {
            wp_mkdir_p( $upload_sub_dir );
        }
        return $upload_sub_dir;
    }

    static function get_orig_host()
    {
        return $_SERVER['HTTP_HOST'];
    }

	static function sanitize_output($str)
	{
		return sanitize_text_field($str);
	}

	static function display_output($str)
	{
		_e( $str);
	}
}



?>