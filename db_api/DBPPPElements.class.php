<?php

	class DBPPPElements extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct($db_storage, 'ppp_elements');
		}
		
		public function getPPPElement( $nIDPPP, $nIDNomenclature )
		{
			if( empty($nIDPPP) || !is_numeric($nIDPPP) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
				
			if( empty($nIDNomenclature) || !is_numeric($nIDNomenclature) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			$sQuery = "
					SELECT
						*
					FROM ppp_elements
					WHERE 1
						AND to_arc = 0
						AND id_ppp = {$nIDPPP}
						AND id_nomenclature = {$nIDNomenclature}
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getElementsByIDLimitCard($nIDLimitCard) {
			
			$sQuery = "
				SELECT 
					n.id,
					n.name,	
					SUM(pe.count) AS count
				FROM ppp_elements pe
				LEFT JOIN ppp pp ON pp.id = pe.id_ppp
				LEFT JOIN nomenclatures n ON n.id = pe.id_nomenclature
				WHERE 1
					AND pe.to_arc = 0
					AND pp.to_arc = 0
					AND pp.id_limit_card = {$nIDLimitCard}
				GROUP BY pe.id_nomenclature
				HAVING count != 0
			";
			
			return $this->select($sQuery);
		}
		
		public function getClientOwnPPPElement( $nIDPPP, $nIDNomenclature, $nClientOwn, $nNotBeingID = 0 )
		{
			if( empty( $nIDPPP ) || !is_numeric( $nIDPPP ) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			if( empty( $nIDNomenclature ) || !is_numeric( $nIDNomenclature ) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			if( !is_numeric( $nClientOwn ) )
				throw new Exception( NULL, DBAPI_ERR_INVALID_PARAM );
			
			$sQuery = "
					SELECT
						*
					FROM ppp_elements
					WHERE 1
						AND to_arc = 0
						AND id_ppp = {$nIDPPP}
						AND id_nomenclature = {$nIDNomenclature}
						AND client_own = {$nClientOwn}
			";
			
			if( !empty( $nNotBeingID ) )
			{
				$sQuery .= "
						AND id != {$nNotBeingID}
				";
			}
			
			$sQuery .= "
					LIMIT 1
			";
			
			return $this->selectOnce( $sQuery );
		}
		
		public function getNomenclatureFromElement( $nIDElement )
		{
			$aElement = $this->getRecord( $nIDElement );
			$nIDNomenclature = 0;
			$nIDNomenclature = $aElement['id_nomenclature'];
			
			return $nIDNomenclature;
		}
		
		public function refreshNomenclatures( $nIDType, DBResponse $oResponse )
		{
			$nIDType = (int) $nIDType;
			
			//Get Nomenclatures From Type
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc=0 AND id_type={$nIDType}
			";
			
			$oNomenclatures = new DBNomenclatures();
			$aNomenclatures = $oNomenclatures->select( $sQuery );
			
			
			//Set Nomenclature
			$oResponse->setFormElement( 'form1', 'nIDNomenclature' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array("value" => 0), "--- Избери ---" );
			
			foreach( $aNomenclatures as $aNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array('value'=>$aNomenclature['id']), $aNomenclature['name'] );
			}
		}
		
		public function fillFields( $nIDElement, DBResponse $oResponse )
		{
			$oNomenclatures = new DBNomenclatures();
			
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc=0
			";
			$aNomenclatures = $oNomenclatures->select( $sQuery );
			$aElement = $this->getRecord( $nIDElement );
			
			//Set Count
			$nCount = isset( $aElement['count'] ) ? $aElement['count'] : 1;
			$oResponse->setFormElement( 'form1', 'nCount', array( 'value' => $nCount ) );
			$oResponse->setFormElement( 'form1', 'nInitCount', array( 'value' => $nCount ) );
			
			$nIDNomenclature = isset( $aElement['id_nomenclature'] ) ? $aElement['id_nomenclature'] : 0;
			
			//Get Nomenclature Type
			$nIDType = 0;
			foreach( $aNomenclatures as $aNomenclature )
			{
				if( $aNomenclature['id'] == $nIDNomenclature )
				{
					$nIDType = $aNomenclature['id_type'];
					break;
				}
			}
			
			//Set Nomenclature Types
			$oResponse->setFormElement( 'form1', 'nIDNomenclatureType', array( 'value' => $nIDType ) );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclatureType', array( "value" => 0 ), "--- Избери ---" );
			$oNomenclatures->getHierarchy( 0, 0, $nIDType, $oResponse );
			
			//Get Nomenclatures From Type
			$sQuery = "
					SELECT * FROM nomenclatures
					WHERE to_arc = 0 AND id_type = {$nIDType}
			";
			$aNomenclatures = $oNomenclatures->select( $sQuery );
			
			
			//Set Nomenclature
			$oResponse->setFormElement( 'form1', 'nIDNomenclature', array( 'value' => $nIDNomenclature ) );
			
			$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( "value" => 0 ), "--- Избери ---" );
			
			foreach( $aNomenclatures as $aNomenclature )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDNomenclature', array( 'value' => $aNomenclature['id'] ), $aNomenclature['name'] );
			}
			
			//Get Schemes
			$sQuery = "
					SELECT * FROM schemes
					WHERE to_arc = 0
			";
			$aSchemes = $oNomenclatures->select( $sQuery );
			
			$oResponse->setFormElement( 'form1', 'nIDScheme' );
			
			$oResponse->setFormElementChild( 'form1', 'nIDScheme', array( "value" => 0 ), "--- Избери ---" );
			
			foreach( $aSchemes as $aScheme )
			{
				$oResponse->setFormElementChild( 'form1', 'nIDScheme', array( 'value' => $aScheme['id'] ), $aScheme['name'] );
			}
		}
		
		public function calcElementsPrice( $nIDPPP )
		{
			$sQuery = "
					SELECT
						count,
						single_price
					FROM ppp_elements
					WHERE to_arc = 0
						AND id_ppp = {$nIDPPP}
				";
			
			$aElements = $this->select( $sQuery );
			$nTotalPrice = 0;
			if( !empty($aElements) )
			{
				foreach( $aElements as $aElement )
				{
					$nTotalPrice += $aElement['single_price'] * $aElement['count'];
				}
			}
			
			return $nTotalPrice;
		}
		
		public function countNomenclatures($nIDLimitCard,$sNomenclatures) {
			
			$sQuery = "
				SELECT 
					SUM(ppel.count)
				FROM ppp_elements ppel
				RIGHT JOIN ppp pp ON pp.id = ppel.id_ppp
				WHERE 1
					AND pp.id_limit_card = '{$nIDLimitCard}'
					AND ppel.id_nomenclature IN ({$sNomenclatures})
			";
			
			return $this->selectOne($sQuery);
		}
	}

?>