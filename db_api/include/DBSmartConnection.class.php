<?php
	class DBSmartConnection
	{
		private $m_sVarName;
		
		private $m_sHost;
		private $m_sUser;
		private $m_sPass;
		private $m_sName;
		private $oDB = NULL;

		/**
		 * @param $sVarName
		 * @param $sHost
		 * @param $sUser
		 * @param $sPass
		 * @param $sName
		 */
		public function __construct($sVarName, $sHost, $sUser, $sPass, $sName)
		{
			$this->m_sVarName = $sVarName;
			
			$this->m_sHost = $sHost;
			$this->m_sUser = $sUser;
			$this->m_sPass = $sPass;
			$this->m_sName = $sName;
		}
		
		private function factory()
		{
			if( empty( $this->oDB ) )
			{
				$this->oDB = ADONewConnection('mysqli');
				$this->oDB->SetFetchMode(ADODB_FETCH_ASSOC);
                $this->oDB->clientFlags |= MYSQLI_CLIENT_COMPRESS;
                $this->oDB->clientFlags |= CLIENT_MULTI_STATEMENTS;
				$this->oDB->NConnect($this->m_sHost, $this->m_sUser, $this->m_sPass, $this->m_sName);
				$this->oDB->Execute('SET NAMES utf8');
				$this->oDB->Execute('SET SESSION group_concat_max_len = 986000');
				$this->oDB->Execute('SET SESSION innodb_table_locks = FALSE');
				$this->oDB->Execute('SET SESSION sql_mode = FALSE');
			}
		}
		
		public function __get( $sVarName ) 
		{
			$this->factory();
			
			return $this->oDB->$sVarName;
		}
		
		public function __set($sVarName, $mValue)
		{
			$this->factory();
			
			return $this->oDB->$sVarName = $mValue;
		}


		public function __call($sMethodName, $aArguments)
		{
			$this->factory();

			$db_class = $this->oDB;

			//return call_user_func_array( array(&$this->oDB, $sMethodName), $aArguments);
			return call_user_func_array( array($db_class, $sMethodName), $aArguments );
		}
		
		public function foundRows()
		{
			$oRs = $this->oDB->Execute("SELECT FOUND_ROWS()");
	
			if( $oRs->EOF )
				throw new Exception(NULL, DBAPI_ERR_SQL_DATA);
				
			return current( $oRs->fields );
		}
	}

	
?>
