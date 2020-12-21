<?php
	class DBSetupPositionsNC extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			//$db_storage->debug=true;
			parent::__construct( $db_personnel, 'positions_nc' );
		}
		
		public function getReport( DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'setup_positions_nc_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ts.id,
					ts.cipher,
					ts.name,
					ts.min_salary,
					IF
					(
						ts.updated_user,
						CONCAT( CONCAT_WS( ' ', up.fname, up.mname, up.lname ), ' [', DATE_FORMAT( ts.updated_time, '%d.%m.%Y %H:%i:%s' ), ']' ),
						'---'
					) AS updated_user
				FROM positions_nc ts
				LEFT JOIN {$db_name_personnel}.personnel as up ON ts.updated_user = up.id
				WHERE 1
					AND ts.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => &$val )
			{
				$val['min_salary'] 	= $val['min_salary'] . " лв.";

				$oResponse->setDataAttributes( $key, 'cipher',		 array( 'style' => 'text-align: center;' ) );
				$oResponse->setDataAttributes( $key, 'name',		 array( 'style' => 'text-align: left; width: 250px;' ) );			
				$oResponse->setDataAttributes( $key, 'min_salary',	 array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'updated_user', array( 'style' => 'text-align: center;' ) );
			}
			
			$oResponse->setField( 'cipher', 	  'шифър', 						'Сортирай по шифър' );
			$oResponse->setField( 'name', 		  'длъжност', 					'Сортирай по длъжност' );
			$oResponse->setField( 'min_salary',	  'миним.осигурителен праг', 	'Сортирай по миним.осигурителен праг' );
			$oResponse->setField( 'updated_user', 'последно редактирал',		'Сортирай по последно редактирал' );
			
			if($right_edit)
			{
				$oResponse->setField( '', 		'', 			'', 'images/cancel.gif', 'deleteSetupPositionsNC', '');
				$oResponse->setFieldLink( 'cipher',  	'editSetupPositionsNC' );
				$oResponse->setFieldLink( 'name',	 	'editSetupPositionsNC' );
				$oResponse->setFieldLink( 'min_salary', 'editSetupPositionsNC' );
			}
		}
		
		public function getActiveSettings()
		{
			$sQuery = "
				SELECT
					ts.id, 
					ts.cipher,
					ts.name,
					ts.min_salary
				FROM positions_nc ts
				WHERE 1
					AND ts.to_arc = 0
			";
			
			return $this->selectOnce( $sQuery );
		}
	}
	
?>