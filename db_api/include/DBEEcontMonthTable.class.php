<?php
	require_once("db_include.inc.php");
	
	
	if (!defined('PATERN_QUERY_LIMIT'))
		define('PATERN_QUERY_LIMIT', '/(?i)\s+LIMIT\s+\d+(?:\s*,\s*\d+|)/');
	if (!defined('PATERN_QUERY_ORDER'))
		define('PATERN_QUERY_ORDER', '/(?i)\s+ORDER\s+BY\s+\w+\s+(?i:ASC|DESC)?(\s*\,\s*\w+\s+(?i:ASC|DESC)?)*/');
	
	
	/**
	*	DBMonthTable и предвиден да бъде базов (parent) клас на класовете кореспондиращи с месечни таблици.
	*	Целта на класа е да имплементира базовата функционалност за работа с периодични таблици като:
	*	select, insert, update, delete и т.н.
	*
	*	@author dido2kCo. :)
	*	@copyright all rights reserved.
	*	
	*	@name DBMonthTable
	*/
	
	class DBEEcontMonthTable
	{
		
		/**
		*	string съдържа префикса на месечната таблица примерно ('loadings_'). Дефинирани са
		*	префиксите на всички месечни таблици в file://include.inc.php
		*/
		var $_sTablePrefix = NULL;
		
		
		/**
		*	string съдържа името на базата данни в която е месечната таблица.
		*	Дефинирани са променливи в който се съдържат имената на всички 
		*	бази в file://.../config/connect.inc.php
		*/
		var $_sDB = NULL;
		
		
		/**
		*	Конструктор на класа, който трябва да се извика от конструктора на наследения клас.
		*
		*	@name __construct
		*	@param string sDB име на база данни в която се намира периодичната таблица
		*	@param string sTablePrefix префикс на месечната таблица (пример: за таблици loadings_YYYYMM префикса е "laodings_"
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function __construct($sDB, $sTablePrefix)
		{
			assert( !empty( $sDB ) );
			assert( !empty( $sTablePrefix ) );
			
			$this->_sDB = $sDB;
			$this->_sTablePrefix = $sTablePrefix;
		}
		
		
		/**
		*	Функцията прави проверка за същеструване на месечната таблица за текущия месец и ако я няма я създава
		*
		*	@name checkTable
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function checkTable()
		{
			global $db_e_econt;
			
			if( !$db_e_econt->Execute("CALL CHECK_MONTH()") ) 
				return DBAPI_ERR_SQL_QUERY;
				
			return DBAPI_ERR_SUCCESS;
		}
		
		
		
		/**
		*	Функцията връща масив от месечни таблици от време на време
		*	
		*	@name getTables
		*	@param array aTables масив от таблици където се връща резултата
		*	@param timestamp nTimeFrom време "От", ако е нула се игнорира
		*	@param timestamp nTimeTo време "До", ако е нула се игнорира
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function getTables(&$aTables, $nTimeFrom = 0, $nTimeTo = 0) 
		{
			global $db_e_econt;
			
			$sPrefix = str_replace('_', '\_', $this->_sTablePrefix);

			$aTables = $db_e_econt->GetCol("SHOW TABLES FROM {$this->_sDB} LIKE '{$sPrefix}______'");
			
			if( $aTables === false )
				return DBAPI_ERR_SQL_QUERY;
				
			for($i=0;$i<count( $aTables ); )
			{
				if( !ereg("^{$sPrefix}([0-9]){4}(([0][0-9])|([1][0-2]))$", $aTables[ $i ]) )
					array_splice($aTables, $i, 1);
				else
					$i++;
			}
			
			if( empty( $aTables ) )
				return DBAPI_ERR_SUCCESS;
			
			sort( $aTables );
			
			if( $nTimeFrom )
			{	
				$vTemp = getdate( $nTimeFrom );
				
				while( !empty( $aTables ) )
				{	
					$sTable = current( $aTables );
					
					if((int)substr($sTable, -6) >= (int)sprintf(FMT_MONTH_TABLE, $vTemp['year'], $vTemp['mon'] ))
						break;
					
					array_shift( $aTables );
				}
			}
			
			if( $nTimeTo )
			{
				$vTemp = getdate( $nTimeTo );
				
				while( !empty( $aTables ) )
				{	
					$sTable = end( $aTables );
					
					if((int)substr($sTable, -6) <= (int)sprintf(FMT_MONTH_TABLE, $vTemp['year'], $vTemp['mon'] ))
						break;
						
					array_pop( $aTables );
				}
			}
			
			return DBAPI_ERR_SUCCESS;		
		}
		
		
		/**
		*	Функцията генерира заявка тип UNION SELECT от заявката подадена му като параметър.
		*	На мястото на месечната таблица трябва да се изписва стринга "<table>";
		*	
		*	@name makeUnionSelect
		*	@param string sQuery заявката, на базата на която ще се генерира UNION SELECT-а
		*	@param timestamp nTimeFrom време "От", ако е нула се игнорира
		*	@param timestamp nTimeTo време "До", ако е нула се игнорира
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function makeUnionSelect(&$sQuery, $nTimeFrom = 0, $nTimeTo = 0)
		{	
			if( empty( $sQuery ) )
				return DBAPI_ERR_INVALID_PARAM;
				
			$aTables = array();
			
			if( ($nResult = $this->getTables($aTables, $nTimeFrom, $nTimeTo) ) != DBAPI_ERR_SUCCESS )
				return $nResult;
			
			if( empty( $aTables ) )
			{
				$sTable = $this->_sTablePrefix.'origin';
				
				$sQuery = str_ireplace('<table>', $this->_sDB.".".$sTable, $sQuery);
				$sQuery = str_ireplace('<yearmonth>', 'origin', $sQuery);
				
				return DBAPI_ERR_SUCCESS;
			}
			
			$sLimit = "";	
			$sOrder = "";
			
			if( count( $aTables ) > 1 )
			{
				$aMatches = array();
		
				if( preg_match(PATERN_QUERY_ORDER, $sQuery, $aMatches) )
				{
					$sOrder = $aMatches[0];
					$sQuery = preg_replace(PATERN_QUERY_ORDER, '', $sQuery);
				}
			
				if( preg_match(PATERN_QUERY_LIMIT, $sQuery, $aMatches) )
				{
					$sLimit = $aMatches[0];
					$sQuery = preg_replace(PATERN_QUERY_LIMIT, '', $sQuery);
				}
				 
				$sQuery = '('.$sQuery.')';
			}
				
			$aQueries = array();
			
			foreach($aTables as $sTable)
			{
				if( count( $aQueries ) )
					$sQueryTemp = str_ireplace( array('<table>', 'SQL_CALC_FOUND_ROWS'), array($this->_sDB.".".$sTable), $sQuery);
				else
					$sQueryTemp = str_ireplace('<table>', $this->_sDB.".".$sTable, $sQuery);
				
				$sYearMonth = str_ireplace($this->_sTablePrefix, '', $sTable);
				
				$sQueryTemp = str_ireplace('<yearmonth>', $sYearMonth, $sQueryTemp);
				
				$aQueries[] = $sQueryTemp;
			}
			
			$sQuery = implode("\nUNION ALL\n", $aQueries);
			
			if( !empty( $sOrder ) )
				$sQuery .= $sOrder;
	
			if( !empty( $sLimit ) )
				$sQuery .= $sLimit;
			
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията ИЗПЪЛНЯВА заявка тип UNION SELECT от заявката подадена му като параметър.
		*	На мястото на месечната таблица трябва да се изписва стринга "<table>";
		*	
		*	@name select
		*	@param string sQuery заявката, на базата на която ще се генерира UNION SELECT-а
		*	@param array двумерен масив в който ще се запише резултата от заявката
		*	@param timestamp nTimeFrom време "От", ако е нула се игнорира
		*	@param timestamp nTimeTo време "До", ако е нула се игнорира
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		
		function select($sQuery, &$aData, $nTimeFrom = 0, $nTimeTo = 0)
		{
			global $db_e_econt;
			
			$aData = array();
			
			if( ($nResult = $this->makeUnionSelect($sQuery, $nTimeFrom, $nTimeTo)) != DBAPI_ERR_SUCCESS)
				return $nResult;
				
			$rs = $db_e_econt->Execute( $sQuery );
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			if( !$rs->EOF )
				$aData = $rs->GetArray();
				
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията ИЗПЪЛНЯВА заявка тип UNION SELECT от заявката подадена му като параметър.
		*	На мястото на месечната таблица трябва да се изписва стринга "<table>";
		*	Разликата между функциите(методите) select и selectAssoc е че
		*	select връща индексиран масив, докато selectAssoc връща асоциативен с key - id
		*	на записите от таблиците
		
		*	@name selectAssoc
		*	@param string sQuery заявката, на базата на която ще се генерира UNION SELECT-а
		*	@param array двумерен масив в който ще се запише резултата от заявката
		*	@param timestamp nTimeFrom време "От", ако е нула се игнорира
		*	@param timestamp nTimeTo време "До", ако е нула се игнорира
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function selectAssoc($sQuery, &$aData, $nTimeFrom = 0, $nTimeTo = 0)
		{
			global $db_e_econt;
			
			$aData = array();
			
			if( ($nResult = $this->makeUnionSelect($sQuery, $nTimeFrom, $nTimeTo)) != DBAPI_ERR_SUCCESS)
				return $nResult;
				
			$rs = $db_e_econt->Execute( $sQuery );
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
				
			if( !$rs->EOF )
				$aData = $rs->GetAssoc();
				
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията преравя месечните таблици като започва от последната 
		*	и при първи резултат връща първият ред от резултата
		*	
		*	@name selectOnce
		*	@param string sQuery заявката, на базата на която ще се генерира UNION SELECT-а
		*	@param array двумерен масив в който ще се запише резултата от заявката
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function selectOnce($sQuery, &$aData)
		{
			global $db_e_econt;
			
			$aData = array();
			
			if( empty( $sQuery ) )
				return DBAPI_ERR_INVALID_PARAM;
				
			$aTables = array();
			
			if( ($nResult = $this->getTables( $aTables ) ) != DBAPI_ERR_SUCCESS )
				return $nResult;
				
			rsort( $aTables );
			
			foreach($aTables as $sTable)
			{
				$sQueryTemp = str_ireplace('<table>', $this->_sDB.".".$sTable, $sQuery);
				
				$sYearMonth = str_ireplace($this->_sTablePrefix, '', $sTable);
				
				$sQueryTemp = str_ireplace('<yearmonth>', $sYearMonth, $sQueryTemp);
				
				$rs = $db_e_econt->Execute( $sQueryTemp );
				
				if( !$rs )
					return DBAPI_ERR_SQL_QUERY;
				
				if( !$rs->EOF )
				{
					$aData = $rs->FetchRow();
					break;
				}
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията връща ред с посочено ID подаден му като параметър в точната таблица
		*	
		*	@name getRecord
		*	@param int ID ID на записа който се търси
		*	@param array aData асоциативен масив с полетата от базата и техните стойности
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function getRecord($ID, &$aData)
		{
			global $db_e_econt;
			
			$aData = array();
			
			$sTable = MONTH_TABLE($this->_sTablePrefix,(int)substr($ID, 0, 4), (int)substr($ID, 4, 2));
			
			$rs = $db_e_econt->Execute("SELECT * FROM {$this->_sDB}.{$sTable} WHERE id = {$ID} LIMIT 1");
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
			
			if( !$rs->EOF )
				$aData = $rs->fields;
				
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията insert-ва или update-ва запис от месечна таблица в зависимост от id-то на масива
		*	подаден му като параметър
		*	
		*	@name update
		*	@param array aData асоциативен масив с полетата от базата и техните стойности
		*	@param int nUpdatedTime unix timestamp с времето на промяна, ако не е посочено се взима текущото
		*	@param bool bUpdateFlag флаг (по подразбиране - TRUE), посочващ дали ще се актуализараат updated_ полетата
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function update( &$aData, $nUpdatedTime = NULL, $bUpdateFlag = true )
		{
			global $db_e_econt;
			
			if ($bUpdateFlag) {	
				$aData['updated_time'] = is_null( $nUpdatedTime ) ? time() : $nUpdatedTime;
				$aData['updated_user'] = $_SESSION['userdata']['id_person'];
			}
				
			if( empty( $aData['id']) )	//Insert
			{
				$aDate	= getdate();
				$nYear	= (int)$aDate['year'];
				$nMonth = (int)$aDate['mon'];
				
				$sTable	= MONTH_TABLE($this->_sTablePrefix, $nYear, $nMonth);
			
				if( ($nResult = $this->checkTable()) != DBAPI_ERR_SUCCESS )
					return $nResult;
				
				$rs = $db_e_econt->Execute("SELECT * FROM {$this->_sDB}.{$sTable} WHERE id = -1");
				
				if( !$rs )
					return DBAPI_ERR_SQL_QUERY;
				
				if( !$db_e_econt->Execute( $db_e_econt->GetInsertSQL($rs, $aData) ) )
					return DBAPI_ERR_SQL_QUERY;
				
				$aData['id'] = $db_e_econt->Insert_ID();
			}
			else	//Update
			{
				$sTable = MONTH_TABLE($this->_sTablePrefix,(int)substr($aData['id'], 0, 4), (int)substr($aData['id'], 4, 2));
				
				$rs = $db_e_econt->Execute("SELECT * FROM {$this->_sDB}.{$sTable} WHERE id = {$aData['id']}");
				
				if( !$rs )
					return DBAPI_ERR_SQL_QUERY;
				
				$sQuery = $db_e_econt->GetUpdateSQL($rs, $aData);
				
				if( empty( $sQuery ) )
					return DBAPI_ERR_SUCCESS;	
					
				if( !$db_e_econt->Execute( $sQuery ) )
					return DBAPI_ERR_SQL_QUERY;
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията изтрива запис от месечна таблица. Ако таблицата има поле "to_arc", 
		*	то то се udpate-ва в 1 на текущия запис и записа не се трие.
		*	
		*	@name delete
		*	@param int ID ID на записа който ще се изтрива
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function delete( $ID )
		{
			global $db_e_econt;
			
			if( empty( $ID ) )
				return DBAPI_ERR_INVALID_PARAM;
				
			$sTable = MONTH_TABLE($this->_sTablePrefix,(int)substr($ID, 0, 4), (int)substr($ID, 4, 2));
			
			$rs = $db_e_econt->Execute("SHOW FIELDS FROM {$this->_sDB}.{$sTable} LIKE 'to_arc'");
			
			if( !$rs )
				return DBAPI_ERR_SQL_QUERY;
			
			if( $rs->EOF )	//няма поле `to_arc`
			{
				if( !$db_e_econt->Execute("DELETE FROM {$this->_sDB}.{$sTable} WHERE id = '{$ID}' LIMIT 1") )
					return DBAPI_ERR_SQL_QUERY;
			}
			else			//има поле `to_arc`
			{
				$aData['id'] = $ID;
				$aData['to_arc'] = 1;
				
				if( ($nResult = $this->update( $aData )) != DBAPI_ERR_SUCCESS )
					return $nResult;
			}
			
			return DBAPI_ERR_SUCCESS;
		}
	}

?>