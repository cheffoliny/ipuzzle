<?php
	class DBAssetsSettings extends DBBase2 
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_settings' );
		}
		
		public function getReport( DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'assets_settings_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ts.id,
					ts.asset_earning_coef,
					ts.asset_own_coef,
					IF
					(
						ts.updated_user,
						CONCAT( CONCAT_WS( ' ', up.fname, up.mname, up.lname ), ' [', DATE_FORMAT( ts.updated_time, '%d.%m.%Y %H:%i:%s' ), ']' ),
						'---'
					) AS updated_user
				FROM assets_settings ts
				LEFT JOIN {$db_name_personnel}.personnel as up ON ts.updated_user = up.id
				WHERE 1
					AND ts.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => &$val )
			{
				$val['asset_earning_coef'] 	= $val['asset_earning_coef'] . " %";
				$val['asset_own_coef'] 	= $val['asset_own_coef'] . " %";
								
				$oResponse->setDataAttributes( $key, 'asset_earning_coef', array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'asset_own_coef', array( 'style' => 'text-align: right; width: 200px;' ) );
				$oResponse->setDataAttributes( $key, 'updated_user', array( 'style' => 'text-align: center;' ) );
			}
			
			$oResponse->setField( 'asset_earning_coef',		'коефициент наработка',			'сортирай по коефициент наработка' );
			$oResponse->setField( 'asset_own_coef',			'коефициент актив самоучастие',	'сортирай по коефициент актив самоучастие' );
			$oResponse->setField( 'updated_user',			'последно редактирал', 			'сортирай по последно редактирал' );
			
			if( $right_edit )
			{
				$oResponse->setFieldLink( 'asset_earning_coef',	'editAssetsSettings' );
				$oResponse->setFieldLink( 'asset_own_coef',		'editAssetsSettings' );
			}
		}
		
		public function getActiveSettings()
		{
			$sQuery = "
				SELECT
					ts.id, 
					ts.asset_earning_coef,
					ts.asset_own_coef
				FROM assets_settings ts
				WHERE 1
					AND ts.to_arc = 0
			";
			
			return $this->selectOnce( $sQuery );
		}
	}
	
?>