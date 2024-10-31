<?php

namespace crawlspider_lite_cm\email;

require_once plugin_dir_path( __FILE__ ) . 'commons.php';
require_once plugin_dir_path( __FILE__ ) . 'class_util.php';




use crawlspider_lite_cm\util    as util;


class cscm_em
{

 
    static function get_email_top_body_01()
    {
        $em_template='
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
        <html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title></title>
          <style type="text/css">
            #outlook a {padding:0;}
            body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;} /* force default font sizes */
            .ExternalClass {width:100%;} .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;} /* Hotmail */
            table td {border-collapse: collapse;}
            @media only screen and (min-width: 600px) { .maxW { width:600px !important; } }
          </style>
        </head>
        <body style="margin: 0px; padding: 0px; -webkit-text-size-adjust:none; -ms-text-size-adjust:none;" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" bgcolor="#FFFFFF"><table bgcolor="#CCCCCC" width="100%" border="0" align="center" cellpadding="0" cellspacing="0"><tr><td valign="top">
        <!--[if (gte mso 9)|(IE)]>
        <table cellpadding="0" cellspacing="0" border="0"><tr><td valign="top">
        <![endif]-->
        <table width="100%" style=" margin: auto;" border="0" align="center" cellpadding="0" cellspacing="0"><tr><td valign="top" align="center">
        
        
        <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#FFFFFF">
		';
        return $em_template;

    }

    static function get_email_table_title_row_02($header_string)
    {
        $em_template='
        <tr>
            <td align="left" valign="middle" style="font-family: Verdana, Geneva, Helvetica, Arial, sans-serif; font-size: 24px; color: #353535; padding:3%; padding-top:40px; padding-bottom:40px;">
            '.$header_string.'
            </td>
        </tr>
      
		';
        return $em_template;
    }

    static function get_email_table_header_row_03()
    {
        $em_template='
            <tr>
              <td width="5%" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                <strong>Priority</strong>
              </td>
              <td width="20%" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                <strong>URL</strong>
              </td>
              <td width="75%" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                <strong>Alert Details</strong>
              </td>              
            </tr>
      
		';
        return $em_template;

    }

    static function get_email_table_data_row_04($results)
    {
        $table_rows=" ";
        for ($i=0;$i<count($results);$i++)
        {
            $em_template='
                <tr>
                    <td width="5%"   style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                        '.$results[$i]->priority.'
                    </td>
                    <td width="20%"   style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                        '.$results[$i]->target_url.'
                    </td>
                    <td width="75%"  style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                        '.$results[$i]->test_alert_status.'
                    </td>                    
                </tr>
     
			';
            $table_rows.=$em_template;
        }
        return $table_rows;

    }
    
    static function get_email_table_center_datatable_row($results)
    {
        $data_header=self::get_email_table_header_row_03();
        $data_rows=self::get_email_table_data_row_04($results);
        $em_template='
            <tr>
            <td align="center">
            <table width="94%" border="0" cellpadding="0" cellspacing="0">
                '.$data_header.'
                '.$data_rows.'

            </table>
            </td>
        </tr>
		';
        return $em_template;
    }    

    static function get_email_table_footer($footer_string)
    {
        $em_template='

            <tr>
                <td align="left" valign="middle" style="font-family: Verdana, Arial, sans-serif; font-size: 12px;  padding:10px; padding-right:0;">
                '.$footer_string.'
                </td>
            </tr>
    </table>
    
    
    </td></tr></table>
    <!--[if (gte mso 9)|(IE)]>
    </td></tr></table>
    <![endif]-->
    </td></tr></table></body></html>           
		';
        return $em_template;
    }

}



?>