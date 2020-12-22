<?php

	require_once("db_include.inc.php");
	
	
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
	
	class DBMonthTable
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
		
		
		var $_oDB = NULL;
		
		/**
		*	Конструктор на класа, който трябва да се извика от конструктора на наследения клас.
		*
		*	@name __construct
		*	@param string sDB име на база данни в която се намира периодичната таблица
		*	@param string sTablePrefix префикс на месечната таблица (пример: за таблици loadings_YYYYMM префикса е "laodings_"
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function __construct($sDB, $sTablePrefix,$oDB)
		{
			assert( !empty( $sDB ) );
			assert( !empty( $sTablePrefix ) );
			assert( is_object( $oDB ));
			
			$this->_sDB = $sDB;
			$this->_sTablePrefix = $sTablePrefix;
			$this->_oDB = $oDB;
		}
		
		
		/**
		*	Функцията прави проверка за същеструване на месечната таблица за текущия месец и ако я няма я създава
		*
		*	@name checkTable
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function checkTable()
		{
			global $db_system;
			
			if( !$db_system->Execute("CALL CHECK_MONTH()") )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията прави проверка за същеструване на тригери на месечни таблици за текущия месец и ако няма ги създава
		*
		*	@name checkTriggers
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function checkTriggers()
		{
			global $db_system, $db_name_system;
			
			if( !$db_system->Execute("UPDATE {$db_name_system}.system s SET s.last_date_check_triggers = NOW()") )
			{
				$db_system->FailTrans();
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
			
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
			$sPrefix = str_replace('_', '\_', $this->_sTablePrefix);

			$aTables = $this->_oDB->GetCol("SHOW TABLES FROM {$this->_sDB} LIKE '{$sPrefix}______'");
			APILog::Log("DbMonth", "SHOW TABLES FROM {$this->_sDB} LIKE '{$sPrefix}______'");
			if( $aTables === false )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
				
			for($i=0;$i<count( $aTables ); )
			{
				//if( !ereg("^{$sPrefix}([0-9]){4}(([0][0-9])|([1][0-2]))$", $aTables[ $i ]) )
				if( !preg_match("/^{$sPrefix}([0-9]){4}(([0][0-9])|([1][0-2]))$/", $aTables[ $i ]) )
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
			{
				APILog::Log($nResult, NULL, __FILE__, __LINE__);
				return $nResult;
			}
			
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
			global $db_system;
			
			return $this->selectFromDB($db_system, $sQuery, $aData, $nTimeFrom, $nTimeTo);
		}
		
		
		/**
		*	Функцията ИЗПЪЛНЯВА заявка тип UNION SELECT от заявката подадена му като параметър.
		*	На мястото на месечната таблица трябва да се изписва стринга "<table>";
		*	
		*	@name select
		* 	@param object oDB обект от тип ADODBConnection
		*	@param string sQuery заявката, на базата на която ще се генерира UNION SELECT-а
		*	@param array двумерен масив в който ще се запише резултата от заявката
		*	@param timestamp nTimeFrom време "От", ако е нула се игнорира
		*	@param timestamp nTimeTo време "До", ако е нула се игнорира
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function selectFromDB($oDB, $sQuery, &$aData, $nTimeFrom = 0, $nTimeTo = 0)
		{
			$aData = array();			
			
			if( ($nResult = $this->makeUnionSelect($sQuery, $nTimeFrom, $nTimeTo)) != DBAPI_ERR_SUCCESS)
			{
				APILog::Log($nResult, NULL, __FILE__, __LINE__);
				return $nResult;
			}
			$this->checkTable();
			$oRs = $oDB->Execute( $sQuery );
			
			
			if( !$oRs )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, $oDB->ErrorMsg(), __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
				
			if( !$oRs->EOF )
				$aData = $oRs->GetArray();
				
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
			global $db_system;
			
			return $this->selectAssocFromDB($db_system, $sQuery, $aData, $nTimeFrom, $nTimeTo);
		}
		
		
		/**
		*	Функцията ИЗПЪЛНЯВА заявка тип UNION SELECT от заявката подадена му като параметър.
		*	На мястото на месечната таблица трябва да се изписва стринга "<table>";
		*	Разликата между функциите(методите) select и selectAssoc е че
		*	select връща индексиран масив, докато selectAssoc връща асоциативен с key - id
		*	на записите от таблиците
		
		*	@name selectAssoc
		*  	@param object oDB обект от тип ADODBConnection
		*	@param string sQuery заявката, на базата на която ще се генерира UNION SELECT-а
		*	@param array двумерен масив в който ще се запише резултата от заявката
		*	@param timestamp nTimeFrom време "От", ако е нула се игнорира
		*	@param timestamp nTimeTo време "До", ако е нула се игнорира
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function selectAssocFromDB($oDB, $sQuery, &$aData, $nTimeFrom = 0, $nTimeTo = 0)
		{
			$aData = array();
			
			if( ($nResult = $this->makeUnionSelect($sQuery, $nTimeFrom, $nTimeTo)) != DBAPI_ERR_SUCCESS)
			{
				APILog::Log($nResult, NULL, __FILE__, __LINE__);
				return $nResult;
			}
			$this->checkTable();
			$oRs = $oDB->Execute( $sQuery );
			
			if( !$oRs )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
				
			if( !$oRs->EOF )
				$aData = $oRs->GetAssoc();
				
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
			$aData = array();
			
			if( empty( $sQuery ) )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}
				
			$aTables = array();
			
			if( ($nResult = $this->getTables( $aTables ) ) != DBAPI_ERR_SUCCESS )
			{
				APILog::Log($nResult, NULL, __FILE__, __LINE__);
				return $nResult;
			}
				
			rsort( $aTables );
			$this->checkTable();
			foreach($aTables as $sTable)
			{
				$sQueryTemp = str_ireplace('<table>', $this->_sDB.".".$sTable, $sQuery);
				
				$sYearMonth = str_ireplace($this->_sTablePrefix, '', $sTable);
				
				$sQueryTemp = str_ireplace('<yearmonth>', $sYearMonth, $sQueryTemp);
				$oRs = $this->_oDB->Execute( $sQueryTemp );
				
				if( !$oRs )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
					return DBAPI_ERR_SQL_QUERY;
				}
				
				if( !$oRs->EOF )
				{
					$aData = $oRs->FetchRow();
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
			global $db_system;
			
			if( !$this->isValidID( $ID ) )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			$aData = array();
			
			$sTable = $this->tableNameFromID( $ID );
			$this->checkTable();
			$oRs = $db_system->Execute("SELECT * FROM {$this->_sDB}.{$sTable} WHERE id = {$ID} LIMIT 1");
			
			if( !$oRs )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if( !$oRs->EOF )
				$aData = $oRs->fields;
				
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		 * Функцията връща име на таблица, която текущо БИ ТРЯБВАЛО да се използва при добавяне на нови записи
		 *
		 * @name currentTableName()
		 * @return string име на таблица
		 */
		
		function currentTableName()
		{
			$aDate	= getdate();
			$nYear	= (int)$aDate['year'];
			$nMonth = (int)$aDate['mon'];
			
			return MONTH_TABLE($this->_sTablePrefix, $nYear, $nMonth);
		}
		
		
		/**
		*	Функцията insert-ва или update-ва запис от месечна таблица в зависимост от id-то на масива
		*	подаден му като параметър
		*	
		*	@name update
		*	@param array aData асоциативен масив с полетата от базата и техните стойности
		*	@param int nUpdatedTime unix timestamp с времето на промяна, ако не е посочено се взима текущото
		*	@param bool bUpdateFlag флаг (по подразбиране - TRUE), посочващ дали ще се актуализараат updated_ полетата
		* 	@param numeric $nParentID id на водителя за да знае в коя месечна таблица да insert-ва
		*	@return int статус на операцията (пример: DBAPI_ERR_...)
		*/
		
		function update( &$aData, $nUpdatedTime = NULL, $bUpdateFlag = true, $nParentID = NULL )
		{
						
			if( $nParentID !== NULL && !$this->isValidID($nParentID) )
			{
				APILog::Log( DBAPI_ERR_INVALID_PARAM, '', __FILE__, __LINE__ );
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			if( $bUpdateFlag ) 
			{	
				$aData['updated_time'] = is_null( $nUpdatedTime ) ? time() : $nUpdatedTime;
				$aData['updated_user'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;
			}
			
			if( empty( $aData['id'] ) )	//Insert
			{
                $aData['created_time'] = time();
                $aData['created_user'] = !empty( $_SESSION['userdata']['id_person'] )? $_SESSION['userdata']['id_person'] : 0;

				if( ($nResult = $this->checkTable()) != DBAPI_ERR_SUCCESS )
				{
					APILog::Log($nResult, NULL, __FILE__, __LINE__);
					return $nResult;
				}
					
				if( ($nResult = $this->checkTriggers()) != DBAPI_ERR_SUCCESS )
				{
					APILog::Log($nResult, NULL, __FILE__, __LINE__);
					return $nResult;
				}
				
				if( $nParentID === NULL )
					$sTable	= $this->currentTableName();
				else 
					$sTable = $this->tableNameFromID( $nParentID );
				
				$oRs = $this->_oDB->Execute("SELECT * FROM {$this->_sDB}.{$sTable} WHERE 0");
				
				if( !$oRs )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
					return DBAPI_ERR_SQL_QUERY;
				}
				
				$sQuery = $this->_oDB->GetInsertSQL($oRs, $aData);
				
				if( empty( $sQuery ) )
					return DBAPI_ERR_SUCCESS;	
				
				if( !$this->_oDB->Execute( $sQuery ) )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, sprintf("%s/n%s", $this->_oDB->ErrorMsg(), $sQuery), __FILE__, __LINE__);
					return DBAPI_ERR_SQL_QUERY;
				}
				
				$aData['id'] = $this->_oDB->Insert_ID();

			}
			else	//Update
			{
				$sTable = $this->tableNameFromID( $aData['id'] );
				
				$oRs = $this->_oDB->Execute("SELECT * FROM {$this->_sDB}.{$sTable} WHERE id = {$aData['id']}");
				
				if( !$oRs )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
					return DBAPI_ERR_SQL_QUERY;
				}
				
				$sQuery = $this->_oDB->GetUpdateSQL($oRs, $aData);
				
				if( empty( $sQuery ) )
					return DBAPI_ERR_SUCCESS;	
					
				if( !$this->_oDB->Execute( $sQuery ) )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, $this->_oDB->ErrorMsg(), __FILE__, __LINE__);
					APILog::Log(DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__);
					return DBAPI_ERR_SQL_QUERY;
				}
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
			global $db_system;
			
			if( !$this->isValidID( $ID ) )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}
				
			$sTable = MONTH_TABLE($this->_sTablePrefix,(int)substr($ID, 0, 4), (int)substr($ID, 4, 2));
			$this->checkTable();
			$oRs = $db_system->Execute("SHOW FIELDS FROM {$this->_sDB}.{$sTable} LIKE 'to_arc'");
			
			if( !$oRs )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if( $oRs->EOF )	//няма поле `to_arc`
			{
				if( !$db_system->Execute("DELETE FROM {$this->_sDB}.{$sTable} WHERE id = '{$ID}' LIMIT 1") )
				{
					APILog::Log(DBAPI_ERR_SQL_QUERY, NULL, __FILE__, __LINE__);
					return DBAPI_ERR_SQL_QUERY;
				}
			}
			else			//има поле `to_arc`
			{
				$aData['id'] = $ID;
				$aData['to_arc'] = 1;
				
				if( ($nResult = $this->update( $aData )) != DBAPI_ERR_SUCCESS )
				{
					APILog::Log($nResult, NULL, __FILE__, __LINE__);
					return $nResult;
				}
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		
		/**
		*	Функцията проверява за валидност ID от месечна таблица (YYYYMM + 7) = 13
		*	
		*	@name isValidID
		*	@param int ID ID на записа който ще се валидира
		*	@return bool резултат от валидацията
		*/
		
		function isValidID( $nID )
		{
			return preg_match("/^\d{13}$/", $nID);
		}
		
		
		/**
		*	Метода връща име на таблицата от дадено ID
		*	
		*	@name isValidID
		*	@param int ID ID на записа който ще се валидира
		*	@return bool резултат от валидацията
		*/
		
		function tableNameFromID( $nID )
		{
			/*
			if( APISettings::isDebug() )
				assert( $this->isValidID( $nID ) );
			*/	
				
			return MONTH_TABLE($this->_sTablePrefix, $this->yearFromID( $nID ), $this->monthFromID( $nID ));
		}
		
		function monthFromID( $nID )
		{
			/*
			if( APISettings::isDebug() )
				assert( $this->isValidID( $nID ) );
			*/	
				
			return (int) substr($nID, 4, 2);
		}
		
		function yearFromID( $nID )
		{
			/*
			if( APISettings::isDebug() )
				assert( $this->isValidID( $nID ) );
			*/	
				
			return (int)substr($nID, 0, 4);
		}
		
		
		/**
		 *  Метода връща SQL заявка за извличане на записи на база ID-та на записи подадени като параметър
		 * 
		 * @name getRecordsByIDsQuery
		 * @param array aIDs масив с ID-та
		 * @param string sQuery заявката която ще се върне като резултат
		 * @return int статус на операцията (пример: DBAPI_ERR_...)
		 */
		
		function getRecordsByIDsQuery($aIDs, &$sQuery)
		{
			$sQuery = "";
			
			if( !is_array( $aIDs ) )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}
			
			if( empty( $aIDs ) )
				return DBAPI_ERR_SUCCESS;
				
			//групиране на ID-тата по таблици
			$aMonthIDs = array();
			
			foreach( $aIDs as $nKey => $nID )
			{
				if( empty( $nID ) )
				{
					unset( $aIDs[ $nKey ] );
					continue;
				}
				
				if( !$this->isValidID( $nID ) )
				{
					APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
					return DBAPI_ERR_INVALID_PARAM;
				}
				
				$aMonthIDs[ $this->tableNameFromID( $nID ) ][] = $nID;
			}
			$this->checkTable();
			$sQueryTemplate = "SELECT t.id AS _id, t.* FROM {$this->_sDB}.%s t WHERE t.id IN( %s ) LIMIT %u";
			
			if( count( $aMonthIDs ) > 1 )
			{
				$sQueryTemplate = '('.$sQueryTemplate.')';
				
				$aQueries = array();
				
				foreach( $aMonthIDs as $sTable => $aTableIDs )
					$aQueries[] = sprintf($sQueryTemplate, $sTable, implode(", ", $aTableIDs), count( $aTableIDs ));
					
				$sQuery  = implode("\nUNION\n", $aQueries);
				$sQuery .= sprintf("\nLIMIT %u", count( $aIDs ));
			}
			else
			{
				$sQuery = sprintf($sQueryTemplate, key( $aMonthIDs ), implode(", ", $aIDs), count( $aIDs ));
			}
			
			return DBAPI_ERR_SUCCESS;
		}
		
		/**
		 *  Метода връща записи на база ID-та на записи подадени като параметър
		 * 
		 * @name getRecordsByIDs
		 * @param array aIDs масив с ID-та
		 * @param array aResult резултат от заявката
		 * @return int статус на операцията (пример: DBAPI_ERR_...)
		 */
		
		function getRecordsByIDs($aIDs, &$aResult)
		{
			global $db_system;
			
			$aResult = array();
			
			if( !is_array( $aIDs ) )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}

			if( empty( $aIDs ) )
				return DBAPI_ERR_SUCCESS;

			$sQuery = "";
			
			if( ($nResult = $this->getRecordsByIDsQuery($aIDs, $sQuery)) != DBAPI_ERR_SUCCESS )
			{
				APILog::Log(DBAPI_ERR_INVALID_PARAM, NULL, __FILE__, __LINE__);
				return DBAPI_ERR_INVALID_PARAM;
			}

			$oRs = $db_system->Execute( $sQuery );
			
			if( !$oRs )
			{
				APILog::Log(DBAPI_ERR_SQL_QUERY, $sQuery, __FILE__, __LINE__);
				return DBAPI_ERR_SQL_QUERY;
			}
				
			if( !$oRs->EOF )
				$aResult = $oRs->GetAssoc();
			
			return DBAPI_ERR_SUCCESS;
		}
		
		public function getResult($sQuery, $sDefaultSortField, $nDefaultSortType, DBResponse $oResponse)
		{
			if( empty( $sQuery ) || !is_string( $sQuery ) )
				throw new Exception(NULL, DBAPI_ERR_INVALID_PARAM);
				
			$oParams = Params::getInstance();
			$aParams = Params::getAll();
			
			$nPage = NULL;
			
			switch( $oParams->get("api_action", "") )
			{
				case 'export_to_xls' :
				case 'export_to_pdf' :
					$nPage = 0;
					break;
				default:
					$nPage = $oParams->get("current_page", 1);
			}
			
			$sSortField = $oParams->get("sfield", $sDefaultSortField);
			$nSortType	= $oParams->get("stype", $nDefaultSortType);
			if(empty($sSortField)) {
				$sSortField = $sDefaultSortField;
			}
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$sSortType = ($nSortType == DBAPI_SORT_DESC) ? "DESC" : "ASC";
			
						
			$bLimited = !empty( $nPage ) && !preg_match( PATERN_QUERY_LIMIT, $sQuery );
			
			$sQuery .= sprintf("ORDER BY %s %s\n", trim($sSortField,"_"), $sSortType);
			
			$oResponse->setSort($sSortField, $nSortType);
				
			if( $bLimited )
				$sQuery .= sprintf("LIMIT %d, %d\n", $nRowOffset, $nRowCount);
			
			$this->checkTable();		
			$rs = $this->_oDB->Execute( $sQuery );
			
			$aData = $rs->getArray();
			
			//$aData = addslashes_deep($aData);
			
			$oResponse->setData( $aData );
			
			$nRowTotal = $this->_oDB->foundRows();
			
//			try {
//				$nRowTotal = $this->oDB->foundRows();
//				$nRowTotal = $this->_oDB->Execute("SELECT FOUND_ROWS()");
//			} catch( exception $e ) {
//				var_dump($e); 
//				$nRowTotal = 20;
//			}
			
						
			if( $bLimited )
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
		}
		
		public function select2( $sQuery )
		{
			$this->checkTable();
			$oRs = $this->_oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->getArray() : array();
		}
		
		public function selectAssoc2( $sQuery )
		{
			$this->checkTable();
			$oRs = $this->_oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->getAssoc() : array();
		}
		
		public function selectOnce2( $sQuery )
		{
			$this->checkTable();
			$oRs = $this->_oDB->Execute( $sQuery );
			
			return !$oRs->EOF ? $oRs->fields : array();
		}
		
		public function selectOne2( $sQuery )
		{
			$this->checkTable();
			$aData = $this->selectOnce2( $sQuery );
			
			return !empty( $aData )? current( $aData ) : NULL;
		}

        public function selectOne( $sQuery )
        {
            $oRs = $this->_oDB->Execute( $sQuery );

            return $oRs && !$oRs->EOF ? $oRs->fields : array();
        }
		
	}

?>