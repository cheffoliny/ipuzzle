<?php

	class DBActivitiesOperations extends DBBase2
	{
		function __construct()
		{	
			global $db_finance;
			parent::__construct( $db_finance, 'new_activity_operation' );
		}
		
		
		/**
		 * Функцията проверява дали името съществува вече.
		 * Връща 1 ако съществува
		 * @author  Станислав Велчев
		 * @param unknown_type $sName
		 * @param unknown_type $id
		 * @return unknown
		 */
		public function checkName ( $sName, $id )
		{
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						COUNT( a.name ) as exist
				FROM 	
					{$db_name_finance}.new_activity_operation a
				WHERE 
						a.item_type = 'activity'
					AND
						a.to_arc = 0
					AND
						a.id != {$id}
					AND 
						LOWER( a.name ) = LOWER( '{$sName}' )
			";
			
			return $this->selectOne( $sQuery ); //return 1 if exist
		}
		
		public function getActivies( DBResponse $oResponse, $aParams )
		{
	 		
			global $db_name_finance;
			
			$oResponse->setField( "name", "Наименование", "Сортирай по наименование", NULL, "viewActivity" );
			$oResponse->setField( "description", "Описание" );
			$oResponse->setField( "btn_delete",	"", NULL, "images/cancel.gif", "deleteActivity", "Премахни" );
			
					
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						a.id,
						a.name,
						a.description
				FROM 	
					{$db_name_finance}.new_activity_operation a
									
				WHERE 
						a.item_type = 'activity'
					AND
						a.to_arc = 0
			";
			
			if( isset( $aParams['sName'] ) && !empty( $aParams['sName'] ) )
			{
				$sQuery .= "
					AND 	a.name LIKE '%{$aParams['sName']}%' 
				";
			}

			if( isset( $aParams['sDesc'] ) && !empty( $aParams['sDesc'] ) )
			{
				$sQuery .= "
					AND 	a.description LIKE '%{$aParams['sDesc']}%' 
				";
			}					

					
			$this->getResult( $sQuery, "id", DBAPI_SORT_ASC, $oResponse );
		
		}
		
		
		public function getOperations( DBResponse $oResponse, $aParams )
		{
			global $db_name_finance;
			
			$oResponse->setField( "name", "Наименование", "Сортирай по наименование", NULL, "viewOperation" );
			$oResponse->setField( "description", "Описание" );
			$oResponse->setField( "btn_delete",	"", NULL, "images/cancel.gif", "deleteOperation", "Премахни" );
			
					
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						a.id,
						a.name,
						a.description
				FROM 	
					{$db_name_finance}.new_activity_operation a
									
				WHERE 
						a.item_type = 'operation'
					AND
						a.to_arc = 0
			";
			
			if( isset( $aParams['sName'] ) && !empty( $aParams['sName'] ) )
			{
				$sQuery .= "
					AND 	a.name LIKE '%{$aParams['sName']}%' 
				";
			}

			if( isset( $aParams['sDesc'] ) && !empty( $aParams['sDesc'] ) )
			{
				$sQuery .= "
					AND 	a.description LIKE '%{$aParams['sDesc']}%' 
				";
			}					
							
			$this->getResult( $sQuery, "id", DBAPI_SORT_ASC, $oResponse );
		}
		

		/**
		 * Функцията връща писък с дейностите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 * 
		 * @author Павел Петров
		 * @name getActiviesForReceipts()
		 * 
		 * @return (array) - списък с дейностите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 */	
		public function getActiviesForReceipts() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM {$db_name_finance}.new_activity_operation
				WHERE item_type = 'activity'
					AND to_arc = 0
				ORDER BY name
			";
			
			return $this->select($sQuery);
		}
		
		
		/**
		 * Функцията връща писък с операциите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 * 
		 * @author Павел Петров
		 * @name getOperationsForReceipts()
		 * 
		 * @return (array) - списък с операциите (id, name), които ще  
		 * участват в рецептурника, подредени по азбучен ред;
		 */	
		public function getOperationsForReceipts() {
			global $db_name_finance;
			
			$sQuery = "
				SELECT 
					id,
					name
				FROM {$db_name_finance}.new_activity_operation
				WHERE item_type = 'operation'
					AND to_arc = 0
				ORDER BY name
			";
			
			return $this->select($sQuery);
		}		
		
		
	}

?>