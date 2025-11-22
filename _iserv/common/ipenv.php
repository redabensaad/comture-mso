<?php
	function PMA_getIp() {
		global $REMOTE_ADDR;
		global $HTTP_X_FORWARDED_FOR, $HTTP_X_FORWARDED, $HTTP_FORWARDED_FOR, $HTTP_FORWARDED;
		global $HTTP_VIA, $HTTP_X_COMING_FROM, $HTTP_COMING_FROM;
		if( empty( $REMOTE_ADDR ) && PMA_getenv( 'REMOTE_ADDR' ) )
			$REMOTE_ADDR = PMA_getenv(  'REMOTE_ADDR'  );
		if ( empty( $HTTP_X_FORWARDED_FOR ) && PMA_getenv( 'HTTP_X_FORWARDED_FOR' ) )
			$HTTP_X_FORWARDED_FOR = PMA_getenv( 'HTTP_X_FORWARDED_FOR' );
		if( empty( $HTTP_X_FORWARDED ) && PMA_getenv( 'HTTP_X_FORWARDED' ) )
			$HTTP_X_FORWARDED = PMA_getenv( 'HTTP_X_FORWARDED' );
		if ( empty( $HTTP_FORWARDED_FOR ) && PMA_getenv( 'HTTP_FORWARDED_FOR' ) )
			$HTTP_FORWARDED_FOR = PMA_getenv( 'HTTP_FORWARDED_FOR' );
		if ( empty( $HTTP_FORWARDED ) && PMA_getenv( 'HTTP_FORWARDED' ) )
			$HTTP_FORWARDED = PMA_getenv( 'HTTP_FORWARDED' );
		if ( empty( $HTTP_VIA ) && PMA_getenv( 'HTTP_VIA' ) )
			$HTTP_VIA = PMA_getenv( 'HTTP_VIA' );
		if ( empty( $HTTP_X_COMING_FROM ) && PMA_getenv( 'HTTP_X_COMING_FROM' ) )
			$HTTP_X_COMING_FROM = PMA_getenv( 'HTTP_X_COMING_FROM' );
		if ( empty( $HTTP_COMING_FROM ) && PMA_getenv( 'HTTP_COMING_FROM' ) )
			$HTTP_COMING_FROM = PMA_getenv( 'HTTP_COMING_FROM' );
		if ( !empty( $REMOTE_ADDR ) )
			$direct_ip = $REMOTE_ADDR;
		$proxy_ip = '';
		if ( !empty( $HTTP_X_FORWARDED_FOR ) )
			$proxy_ip = $HTTP_X_FORWARDED_FOR;
		elseif ( !empty( $HTTP_X_FORWARDED ) )
			$proxy_ip = $HTTP_X_FORWARDED;
		elseif ( !empty( $HTTP_FORWARDED_FOR ) )
			$proxy_ip = $HTTP_FORWARDED_FOR;
		elseif ( !empty( $HTTP_FORWARDED ) )
			$proxy_ip = $HTTP_FORWARDED;
		elseif ( !empty( $HTTP_VIA ) )
			$proxy_ip = $HTTP_VIA;
		elseif ( !empty( $HTTP_X_COMING_FROM ) )
			$proxy_ip = $HTTP_X_COMING_FROM;
		elseif ( !empty( $HTTP_COMING_FROM ) )
			$proxy_ip = $HTTP_COMING_FROM;
		if ( empty( $proxy_ip ) )
			return $direct_ip; 
		else {
			$is_ip = preg_match( '|^( [0-9]{1,3}\. ){3,3}[0-9]{1,3}|', $proxy_ip, $regs );
			if ( $is_ip && ( count( $regs ) > 0 ) )
				return $regs[0]; 
			else {
				if( !empty( $direct_ip ) )
				  return $direct_ip;
				if( !empty( $proxy_ip ) )
				  return $proxy_ip;
				return FALSE;
		    }
		} 
	} 
	function PMA_getenv( $var_name ) {
		if ( isset( $_SERVER[$var_name] ) )
			return $_SERVER[$var_name];
		elseif ( isset( $_ENV[$var_name] ) )
			return $_ENV[$var_name];
		elseif ( getenv( $var_name ) )
			return getenv( $var_name );
		elseif ( function_exists( 'apache_getenv' ) && apache_getenv( $var_name, true ) )
			return apache_getenv( $var_name, true );
		return '';
	}
?>
