<?php

namespace crawlspider_lite_cm\logging;

class cscm_debug
{

static function log($message)
{
	//if (true === WP_DEBUG) 
	{
		error_log($message);
	}
}

}



?>