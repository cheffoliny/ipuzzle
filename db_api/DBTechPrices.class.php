<?php
	
	class DBTechPrices extends DBBase2
	{
		public function __construct()
		{
			global $db_finance;
			
			parent::__construct( $db_finance, 'tech_prices' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'setup_tech_prices', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$sQuery = "
					SELECT
						t.id,
						t.base_price,
						t.factor,
						DATE_FORMAT( t.price_list_date, '%d.%m.%Y' ) AS price_list_date,
						IF( 
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( t.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM tech_prices t
					LEFT JOIN {$db_name_personnel}.personnel p ON t.updated_user = p.id
					WHERE t.to_arc = 0
			";
			
			$aNeededData = $this->select( $sQuery );
			
			$oResponse->setField("detector_count", 	"Брой Детектори",					"Сортирай по Брой Детектори");
			$oResponse->setField("price", 			"Цена", 							"Сортирай по Цена");
			
			$aData = array();
			for( $i = 1; $i <= 10; $i++ )
			{
				$aData[$i]['detector_count'] = $i;
				$aData[$i]['price'] = ( $aNeededData[0]['base_price'] + ( $aNeededData[0]['factor'] * ( $i - 1 ) ) ) . " лв.";
				
				$oResponse->setDataAttributes( $i, "detector_count", 	array( 'style' => 'text-align: center;' ) );
			}
			
			$oResponse->setFormElement( 'form1', 'sListDate', array(), $aNeededData[0]['price_list_date'] );
			$oResponse->setFormElement( 'form1', 'nBasePrice', array(), $aNeededData[0]['base_price'] );
			$oResponse->setFormElement( 'form1', 'nFactor', array(), $aNeededData[0]['factor'] );
			$oResponse->setFormElement( 'form1', 'sUpdatedUser', array(), $aNeededData[0]['updated_user'] );
			
//			if ($right_edit)
//			{
//				$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteTechPrice', '');
//				$oResponse->setFieldLink("base_price", "openTechPrice");
//			}
			$oResponse->setData( $aData );
		}
	}

?>