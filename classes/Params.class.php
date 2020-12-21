<?php
	
	class Params 
		implements Iterator
	{
		private static $oInstance = NULL;
		
		private $aParams = NULL;
		
		private function __construct( $aArray = NULL )
		{
			if( is_array( $aArray ) )
			{
			   $this->aParams = $aArray;
			}
			else
			{
				$this->aParams = array();
				
				if( !empty( $_GET ) )
					$this->aParams = $_GET;
					
				if( !empty( $_POST ) )
					$this->aParams = array_merge( $this->aParams, $_POST );
					
				if ( ini_get('magic_quotes_gpc') ) {
					include_once("../include/general.inc.php");
					$this->aParams = stripslashes_deep( $this->aParams );
				}
					
				foreach( $this->aParams as $k => $v )
				{
					if( isset( $k[1] ) && $k[1] >= 'A' && $k[1] <= 'Z' )
					{
						switch( $k[0] )
						{
							case 'n':
								if( !is_numeric( $v ) )
									$this->aParams[ $k ] = 0;
								break;
							case 'b':
								$this->aParams[ $k ] = ( bool ) $v;
								break;
						}
					}
				}
			}
		}
		
		public static function getInstance()
		{
			if( self::$oInstance === NULL )
				self::$oInstance = new Params();
				
			return self::$oInstance;
		}
		
		public static function &getAll()
		{
			$oParams = self::getInstance();
			
			return $oParams->aParams;
		}
		
		public function rewind() {
		   reset( $this->aParams );
		}
		
		public function current() {
		   return current( $this->aParams );
		}
		
		public function key() {
			return key( $this->aParams );
		}
		
		public function next() {
			return next( $this->aParams );
		}

		public function valid() {
		   return ( $this->current() !== false );
		}
		
		public static function get( $sName, $mDefault = NULL )
		{
			$oParams = self::getInstance();
			
			if( isset( $oParams->aParams[ $sName ] ) )
				return $oParams->aParams[ $sName ];
				
			return $mDefault;
		}
		
		public static function set( $sName, $mValue )
		{
			$oParams = self::getInstance();
			
			$oParams->aParams[ $sName ] = $mValue;
		}
		
		public function __get( $sName )
		{
			if( isset( $this->aParams[ $sName ] ) )
				return $this->aParams[ $sName ];
				
			return NULL;
		}
		
		public function __set( $sName, $mValue )
		{
			$this->aParams[ $sName ] = $mValue;
		}
		
		public function __clone()
		{
		   trigger_error('Clone is not allowed.', E_USER_ERROR);
		}
		
		public function toArray()
		{
			return $this->aParams;
		}
	}
	
?>