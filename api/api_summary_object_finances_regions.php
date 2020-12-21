<?php

	class ApiSummaryObjectFinancesRegions
	{
		public function result( DBResponse $oResponse )
		{
			$aParams = Params::getAll();
			
			$oDBStatistics = new DBStatistics2();
			
			$oDBStatistics->getReport( $aParams, $oResponse );
			
			$oResponse->printResponse();
		}
		
		public function dataIntraToTelenet( DBResponse $oResponse )
		{
			global $db_telepol, $db_sod;
			
			$aData = array();
			$aTelenetOffices = array();
			
			//Get Telenet Offices
			$sQuery = "
				SELECT
					id_region AS id_telepol,
					telenet_id_office AS id_telenet
				FROM
					regions
				WHERE
					telenet_id_office != 0
			";
			
			$oRs = $db_telepol->Execute( $sQuery );
			
			if( !$oRs->EOF )
			{
				$aData = $oRs->getArray();
			}
			else throw new Exception( "Грешка при извличане на данни!", DBAPI_ERR_SQL_DATA );
			
			foreach( $aData as $nKey => $aValue )
			{
				$aTelenetOffices[$aValue['id_telepol']] = $aValue['id_telenet'];
			}
			//End Get Telenet Offices
			
			$aData = array();
			
			//Transfer Records
			$sQuery = "
				SELECT
					st.id,
					UNIX_TIMESTAMP( st.stat_date ) AS stat_date,
					st.obj_count,
					st.obj_price,
					st.obj_price_platili,
					rm.childs
				FROM
					statistic2 st
				LEFT JOIN
					regions_masters rm ON rm.id = st.master_region_id
			";
			
			$oRs = $db_telepol->Execute( $sQuery );
			
			if( !$oRs->EOF )
			{
				$aData = $oRs->getArray();
			}
			else throw new Exception( "Грешка при извличане на данни!", DBAPI_ERR_SQL_DATA );
			
			$db_sod->startTransaction();
			
			foreach( $aData as $aElement )
			{
				$sInsertDate = date( "Y-m-d", strtotime( "-1 months", $aElement['stat_date'] ) );
				$aTelepolOffices = unserialize( $aElement['childs'] );
				
				foreach( $aTelepolOffices as $sTelepolOffice )
				{
					$nTelepolOffice = ( int ) $sTelepolOffice;
					
					if( isset( $aTelenetOffices[$nTelepolOffice] ) )
					{
						$nTelenetOffice = $aTelenetOffices[$nTelepolOffice];
						
						$sInsertQuery = "
							INSERT INTO
								statistics
							VALUES
							(
								NULL,
								'{$sInsertDate}',
								{$nTelenetOffice},
								'{$aElement['obj_count']}',
								'{$aElement['obj_price']}',
								'{$aElement['obj_price_platili']}'
							)
						";
						
						$oRs = $db_sod->Execute( $sInsertQuery );
						if( !$oRs )
						{
							$db_sod->failTransaction();
							throw new Exception( NULL, DBAPI_ERR_FAILED_TRANS );
						}
					}
				}
			}
			
			$db_sod->completeTransaction();
			//End Transfer Records
			
			$oResponse->printResponse();
		}
	}

?>