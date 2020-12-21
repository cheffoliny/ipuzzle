<?php

	class DBSalaryUnstored extends DBBase2
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, "salary_unstored" );
		}
		
		/**
		 * Почистване на данните от таблицата с незапазени записи по Работна Заплата
		 * $nIDPerson е ID-то на служителя, който прави промените.
		 *
		 * @param int $nIDPerson
		 * @param int $nType ( 0 - Отпуск; 1 - Болничен; 2 - Обезщетение )
		 * @return DBAPI_ERR_ ...
		 */
		public function clearData( $nIDPerson, $nType )
		{
			global $db_personnel;
			
			//Validation
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			if( !is_numeric( $nType ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			//End Validation
			
			$sQuery = "
				DELETE FROM salary_unstored
				WHERE
					type = {$nType}
			";
			
			$oRes = $db_personnel->Execute( $sQuery );
			
			if( !$oRes )return DBAPI_ERR_SQL_QUERY;
			
			return DBAPI_ERR_SUCCESS;
		}
		
		/**
		 * Добавяне / редактиране на сума от Работна заплата към незапазените данни.
		 * $nIDPerson е ID-то на служителя, който прави промените.
		 *
		 * @param int $nIDPerson
		 * @param int $nIDSalaryRow
		 * @param float $nTotalSum
		 * @param int $nType ( 0 - Отпуск; 1 - Болничен; 2 - Обезщетение )
		 * @return DBAPI_ERR_ ...
		 */
		public function saveSum( $nIDPerson, $nIDSalaryRow, $nTotalSum, $nType )
		{
			//Validation
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			if( empty( $nIDSalaryRow ) || !is_numeric( $nIDSalaryRow ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			if( !is_numeric( $nType ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			//End Validation
			
			$sGetQuery = "
				SELECT
					*
				FROM
					salary_unstored
				WHERE
					id_person = {$nIDPerson}
					AND type = {$nType}
					AND id_salary_row = {$nIDSalaryRow}
				LIMIT 1
			";
			
			$aData = $this->selectOnce( $sGetQuery );
			
			$aData['id'] = isset( $aData['id'] ) ? $aData['id'] : 0;
			$aData['id_person'] = $nIDPerson;
			$aData['id_salary_row'] = $nIDSalaryRow;
			$aData['total_sum'] = $nTotalSum;
			$aData['type'] = $nType;
			
			$this->update( $aData );
			
			return DBAPI_ERR_SUCCESS;
		}
		
		/**
		 * Функция за прехвърляне на незаписаните данни в Работна Заплата
		 * $nIDPerson е ID-то на служителя, който прави промените.
		 *
		 * @param int $nIDPerson
		 * @param int $nType ( 0 - Отпуск; 1 - Болничен; 2 - Обезщетение )
		 * @return DBAPI_ERR_ ...
		 */
		public function flushData( $nIDPerson, $nType )
		{
			$oDBSalary = new DBSalary();
			
			//Validation
			if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			if( !is_numeric( $nType ) )
			{
				return DBAPI_ERR_INVALID_PARAM;
			}
			//End Validation
			
			$sQuery = "
				SELECT
					*
				FROM
					salary_unstored
				WHERE
					type = {$nType}
			";
			
			$aData = $this->select( $sQuery );
			
			foreach( $aData as $nKey => $aValue )
			{
				if( !empty( $aValue['id_salary_row'] ) && !empty( $aValue['total_sum'] ) )
				{
					$aData = array();
					$aData['id'] = $aValue['id_salary_row'];
					$aData['total_sum'] = $aValue['total_sum'];
					
					$nResult = $oDBSalary->update( $aData );
					if( $nResult != DBAPI_ERR_SUCCESS )
					{
						return DBAPI_ERR_SQL_QUERY;
					}
				}
				else return DBAPI_ERR_INVALID_PARAM;
			}
			
			return $this->clearData( $nIDPerson, $nType );
		}
	}

?>