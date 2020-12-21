<?php
	class DBTechSettings extends DBBase2 
	{
		public function __construct()
		{
			global $db_sod;
			//$db_sod->debug=true;
			parent::__construct( $db_sod, 'tech_settings' );
		}
		
		public function getReport( DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'tech_settings', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ts.id,
					ts.tech_price_destroy,
					ts.tech_price_arrange,
					ts.tech_price_holdup,
					ts.tech_price_km,
					IF
					(
						ts.updated_user,
						CONCAT( CONCAT_WS( ' ', up.fname, up.mname, up.lname ), ' [', DATE_FORMAT( ts.updated_time, '%d.%m.%Y %H:%i:%s' ), ']' ),
						'---'
					) AS updated_user
				FROM tech_settings ts
				LEFT JOIN {$db_name_personnel}.personnel as up ON ts.updated_user = up.id
				WHERE 1
					AND ts.to_arc = 0
			";
			
			$this->getResult( $sQuery, 'id', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => &$val )
			{
				$val['tech_price_destroy'] 	= $val['tech_price_destroy'] . " лв.";
				$val['tech_price_arrange'] 	= $val['tech_price_arrange'] . " лв.";
				$val['tech_price_holdup'] 	= $val['tech_price_holdup'] . " лв.";
				$val['tech_price_km']	   	= $val['tech_price_km'] . " лв.";
				
				$oResponse->setDataAttributes( $key, 'tech_price_destroy', array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'tech_price_arrange', array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'tech_price_holdup', array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'tech_price_km', array( 'style' => 'text-align: right; width: 150px;' ) );
				$oResponse->setDataAttributes( $key, 'updated_user', array( 'style' => 'text-align: center;' ) );
			}
			
			$oResponse->setField( 'tech_price_destroy',		'цена за сваляне',		'сортирай по цена' );
			$oResponse->setField( 'tech_price_arrange',		'цена за аранжировка',	'сортирай по цена' );
			$oResponse->setField( 'tech_price_holdup',		'цена за профилактика',	'сортирай по цена' );
			$oResponse->setField( 'tech_price_km',			'цена за км./преход',	'сортирай по цена' );
			$oResponse->setField( 'updated_user',			'последно редактирал', 	'сортирай по последно редактирал' );
//			$oResponse->setField( 'updated_user', '...', 	'Сортиране по последно редактирал', 'images/dots.gif' );
//			if( $right_edit ) {
//				$oResponse->setField( '',			'',			'', 'images/cancel.gif', 'deleteTechSupport', 'Изтрий');
//			}
			
			if( $right_edit )
			{
				$oResponse->setFieldLink( 'tech_price_destroy',	'editTechSupport' );
				$oResponse->setFieldLink( 'tech_price_arrange',	'editTechSupport' );
				$oResponse->setFieldLink( 'tech_price_holdup',	'editTechSupport' );
				$oResponse->setFieldLink( 'tech_price_km',		'editTechSupport' );
			}
		}
		
		public function getActiveSettings()
		{
			$sQuery = "
				SELECT
					ts.id, 
					ts.tech_price_destroy,
					ts.tech_price_arrange,
					ts.tech_price_holdup,
					ts.tech_price_km
				FROM tech_settings ts
				WHERE 1
					AND ts.to_arc = 0
			";
			
			return $this->selectOnce( $sQuery );
		}
	}
	
?>