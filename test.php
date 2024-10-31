<?php

namespace crawlspider_lite_cm;

require_once __DIR__.'/includes/class_checks_tbl.php';

use crawlspider_lite_cm\checks_tbl as checks_tbl;

class cscm_main
{
    static function display_me() 
    {
        echo "Parent namespace ".__NAMESPACE__."<br>";
        checks_tbl\cscm_checks_tbl::display_me();
    }
}

cscm_main::display_me();

?>