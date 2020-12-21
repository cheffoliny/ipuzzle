<?php
 	class DBRegister extends DBBase2 {
		
		public function __construct() {
			global $db_finance;
			
			parent::__construct($db_finance, "new_services_tree");
		}	
		
		
		public function getReport( DBResponse $oResponse, $aParams )
		{	 		
			global $db_name_finance;
			global $db_name_sod;
			
			$nIDService = $aParams['nIDService'];
			$nIDFirm = $aParams['nIDFirm'];
			
			$oResponse->setField( "name", "Наименование", "Сортирай по наименование", NULL, "viewRegister" );
			$oResponse->setField( "firm_name", "Фирма" );
			$oResponse->setField( "office_name", "Регион" );
			$oResponse->setField( "object_name", "Обект", NULL, NULL, "viewObject" );
			$oResponse->setField( "btn_delete",	"", NULL, "images/cancel.gif", "deleteRegister", "Премахни" );
					
			$sQuery = "
				SELECT 
					SQL_CALC_FOUND_ROWS
						CONCAT_WS( ',', t.id, obj.id ) AS id,
						t.name		AS name,
						f.name		AS firm_name,
						o.name		AS office_name,
						obj.name	AS object_name
				FROM
						{$db_name_finance}.new_services_tree	t
							LEFT JOIN {$db_name_sod}.firms	f	ON ( t.id_firm = f.id AND t.id_firm > 0 )
							LEFT JOIN {$db_name_sod}.offices	o	ON ( t.id_office = o.id AND t.id_office > 0 )
							LEFT JOIN {$db_name_sod}.objects	obj	ON ( t.id_object = obj.id AND t.id_object > 0 )
				WHERE
						t.to_arc != 1
					AND
						t.service_type = 'service'
			";
			
			if( isset( $nIDService ) && $nIDService > 0 ) 
			{
				$sQuery .= "
					AND	t.id_service = {$nIDService}
				";
			}
			
//			if( isset( $nIDFirm ) && $nIDFirm > 0 ) 
//			{
//				$sQuery .= "
//					AND	t.id_firm = {$nIDService}
//				";
//			}
						
			
			APILog::Log( 0,$sQuery );
			
			$this->getResult( $sQuery, "id", DBAPI_SORT_ASC, $oResponse );
		
		}
		
	}
	
?>