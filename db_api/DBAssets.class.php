<?php

	class DBAssets extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets' );
		}
		
		public function searchAssets($aData, DBResponse $oResponse) {
			
			global $db_name_personnel,$db_name_sod;
			
			$nIDFirm	= isset($aData['nIDFirm']) ? $aData['nIDFirm'] : '';
			$nIDOffice	= isset($aData['nIDOffice']) ? $aData['nIDOffice'] : '';
			$nIDPerson	= isset($aData['nIDPerson']) ? $aData['nIDPerson'] : '';
			$sIDGroups	= isset($aData['sIDGroups']) ? $aData['sIDGroups'] : '';
			$nNum		= isset($aData['nNum']) ? $aData['nNum'] : ''; 	
			$nAssetSource = isset($aData['nAssetSource']) ? $aData['nAssetSource'] : ''; 
			$nAssetDest = isset($aData['nAssetDest']) ? $aData['nAssetDest'] : ''; 
			
			$nWebServerTime = time();
			
			$sQuery = "
				
				SELECT SQL_CALC_FOUND_ROWS
					CONCAT_WS(',',ass.id,ass.name,
						IF(ass.status = 'attached',
							IF( floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							  	((ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000))/ass.amortization_months)*ass.price,
								0
							),
							(ass.amortization_months_left/ass.amortization_months)*ass.price 
						)	
					) AS id,
					ass.id AS num,
					ass.name,
					ass.price,
					CONCAT(ass.amortization_months,' мес.') AS amortization_months,
					CASE ass.status
						WHEN 'attached' THEN 
							if(floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000),
							0)
						ELSE ass.amortization_months_left
					END AS amortization_months_left,
					CASE ass.status
						WHEN 'entered'	THEN	(ass.amortization_months_left/ass.amortization_months)*ass.price
						WHEN 'attached'	THEN 	
							if( floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							  ((ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000))/ass.amortization_months)*ass.price,
							0)
						ELSE (ass.amortization_months_left/ass.amortization_months)*ass.price 
					END AS price_left
				FROM assets ass
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = ass.id_storage AND ass.storage_type = 'person'
				LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
				
				LEFT JOIN assets_storagehouses ass_stor ON ass_stor.id = ass.id_storage AND ass.storage_type = 'storagehouse'
				LEFT JOIN {$db_name_personnel}.personnel pp ON pp.id = ass_stor.id_mol
				LEFT JOIN {$db_name_sod}.offices oo ON oo.id = pp.id_office
				WHERE 1 
					AND ass.to_arc = 0
					AND ass.status != 'wasted'
			";
			if($nAssetSource) {
					$sQuery .= " AND ass.id <> {$nAssetSource} \n";
				}
			if($nAssetDest){
					$sQuery .= " AND ass.id <> {$nAssetDest} \n";
			}
				
			if(!empty($nNum)) {
				$sQuery .= " AND ass.id = {$nNum} \n";
			} else {
			
				if(!empty($nIDPerson)) {
					$sQuery .= " AND (p.id = {$nIDPerson} OR pp.id = {$nIDPerson} ) \n";
				}
				
				if(!empty($nIDOffice)) {
					$sQuery .= " AND (p.id_office = {$nIDOffice} OR pp.id_office = {$nIDOffice} ) \n";
				}
				
				if(!empty($nIDFirm)) {
					$sQuery .= " AND (o.id_firm = {$nIDFirm} OR oo.id_firm = {$nIDFirm} ) \n";
				}
				
				if(!empty($sIDGroups)) {
					$sQuery .= " AND ass.id_group IN ({$sIDGroups})\n";
				}
			
			}

			$this->getResult( $sQuery, 'num', DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {

				global $aAssetsIDs;
				$aAssetsIDs = array();
				$this->getSubAssetsIDsRecursive($val['num']);	
				$sAssetsIDs = implode(',',$aAssetsIDs);
				$nPriceLeft = $this->calcPriceLeft($sAssetsIDs);
				$val['price_left'] = $nPriceLeft;
				
				$val['name'] = stripslashes($val['name']);
				
				$val['num'] = zero_padding($val['num'],6);
				if(!empty($val['amortization_months_left']))$val['amortization_months_left'] .= ' мес.';
				
				$oResponse->setDataAttributes($key,'amortization_months',array('style' => 'text-align:right;'));
				$oResponse->setDataAttributes($key,'amortization_months_left',array('style' => 'text-align:right;'));
			}	
			
			$oResponse->setField( 'confirm', '', '', 'images/confirm.gif' ,'transfer','');
			$oResponse->setField( "num", 			"Номер", 					"Сортирай по Номер" );
			$oResponse->setField( "name", 	"Име", 		"Сортирай по Име" );
			$oResponse->setField( "price", 	"Цена на придобиване", 		"Сортирай по Цена на придобиване",NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
			$oResponse->setField( "amortization_months", 	"Време на амортизация", 		"Сортирай по Време на амортизация" );
			$oResponse->setField( "amortization_months_left", 	"Оставащо време", 		"Сортирай по Оставащо време" );
			$oResponse->setField( "price_left", 	"Остатъчна стойност", 		"Сортирай по Остатъчна стойност",NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
			$oResponse->setFieldLink('num','openAssetInfo');
		}

		public function getMOL($nID) {
			
			global $db_name_personnel;
			
			$sQuery = "
				SELECT 
					CASE 
						WHEN ass.storage_type = 'person' THEN CONCAT_WS(' ', p.fname, p.mname, p.lname)
						WHEN ass.storage_type = 'storagehouse' THEN CONCAT_WS(' ', p_st_mol.fname, p_st_mol.mname, p_st_mol.lname)
					END AS mol
				FROM assets ass
				LEFT JOIN {$db_name_personnel}.personnel p ON ass.id_storage = p.id AND ass.storage_type = 'person' 
				LEFT JOIN assets_storagehouses ass_st ON ass.id_storage = ass_st.id AND ass.storage_type = 'storagehouse'
				LEFT JOIN {$db_name_personnel}.personnel p_st_mol ON ass_st.id_mol = p_st_mol.id
				WHERE ass.id = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getIDMOLsAndIDAssets() {
			
			$sQuery = "					
				SELECT
					id_storage AS id_person,
					id AS id_asset
				FROM assets
				WHERE 1
					AND to_arc = 0
					AND status = 'attached'
					AND storage_type = 'person'
				
				UNION
				
				SELECT
					ass_st.id_mol AS id_person,
					ass.id AS id_asset
				FROM assets ass
				LEFT JOIN assets_storagehouses ass_st ON ass_st.id = ass.id_storage
				WHERE 1
					AND ass.to_arc = 0
					AND ass.status = 'entered'
			";
			
			return $this->select($sQuery);
		}
		
		public function calcPriceLeft( $sIDs) {
			
			$nWebServerTime = time();
			
			$sQuery = "
				SELECT 
				id,
				CASE ass.status
					WHEN 'entered'	THEN	(ass.amortization_months_left/ass.amortization_months)*ass.price
					WHEN 'attached'	THEN 	
						if( floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
						  ((ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000))/ass.amortization_months)*ass.price ,
						0)
					ELSE (ass.amortization_months_left/ass.amortization_months)*ass.price 
				END AS price_left
				FROM assets ass
				WHERE ass.id IN  ({$sIDs})
			";
			
			$aPriceLeft = $this->selectAssoc($sQuery);
			
			$nSumPriceLeft = 0;
			foreach ($aPriceLeft as $key => $value ) {
				$nSumPriceLeft += $value;
			}
			
			return $nSumPriceLeft;
		}
		
		public function calcPriceTotal( $sIDs )
		{
			$sQuery = "
				SELECT 
					id,
					price
				FROM assets
				WHERE id IN ({$sIDs})
			";
			
			$aPriceTotal = $this->selectAssoc( $sQuery );
			
			$nSumPriceTotal = 0;
			foreach ($aPriceTotal as $key => $value )
			{
				$nSumPriceTotal += $value;
			}
			
			return $nSumPriceTotal;
		}
		
		public function getPrice($nID) {
			global $aAssetsIDs;
			$aAssetsIDs = array();
			$this->getSubAssetsIDsRecursive($nID);
			$sAssetsIDs = '';
			$sAssetsIDs = implode(',',$aAssetsIDs);
			$nPrice = $this->calcPriceLeft($sAssetsIDs);
			return $nPrice;
			
		}
		
		public function getSinglePrice( $nID )
		{
			$nPrice = $this->calcPriceLeft( $nID );
			return $nPrice;
		}
		
		public function getTotalPrice( $nID )
		{
			global $aAssetsIDs;
			
			$aAssetsIDs = array();
			
			$this->getSubAssetsIDsRecursive( $nID );
			$sAssetsIDs = implode( ',', $aAssetsIDs );
			$nPrice = $this->calcPriceTotal( $sAssetsIDs );
			
			return $nPrice;
		}
		
		public function getSubAssetsIDsRecursive($nID) {
			global $aAssetsIDs;
			
			$aAssetsIDs[] = $nID;
			
			$aSubAssets = $this->getSubAssetsIDs($nID);
			
			foreach ($aSubAssets as $key => $value) {
				$this->getSubAssetsIDsRecursive($value['id']);
			}
			
		}
		
		public function getSubAssetsIDs($nID) {
			
			$sQuery = "
			
				SELECT 
					id
				FROM assets
				WHERE 1
					AND storage_type = 'asset'
					AND id_storage = {$nID}			
			";
			
			return $this->select($sQuery);
			
		}
		
		public function getAssetInfo($nID,&$aData)
		{
			global $db_name_sod, $db_name_personnel,$db_name_storage;
			
			$nWebServerTime = time();
			
			$sQuery = "
				SELECT
				LPAD(	ass.id,6,0 ) AS id,
				ass.name AS name,
				pr.id as id_person,
				ass.invoice AS invoice_num,
				ass.enter_date AS enter_date,
				ass.attach_date AS attach_date,
				CASE ass.status
				WHEN 'entered' THEN 'придобит'
				WHEN 'attached' THEN 'въведен'
				WHEN 'wasted' THEN 'бракуван'
				END AS status,
				ass.storage_type AS storage_type, 
				ass.id_group as id_group,
				ass.id_nomenclature as id_nomenclature,
				CONCAT_WS(' ',ass.amortization_months,'мес.') AS amort_period,
				ass.amortization_months_left,
				CASE ass.status
						WHEN 'attached' THEN 
							CONCAT_WS(' ',if(floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000),
							0), 'мес.')
						ELSE CONCAT_WS(' ',ass.amortization_months_left,'мес.')
					END AS rest_term,
				CONCAT_WS(' ',ass.price,'лв.') AS aquire_price,
				CASE ass.status
						WHEN 'entered'	THEN	(ass.amortization_months_left/ass.amortization_months)*ass.price
						WHEN 'attached'	THEN 	
							if( floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							  ((ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000))/ass.amortization_months)*ass.price ,
							0)
						ELSE (ass.amortization_months_left/ass.amortization_months)*ass.price 
					END AS rest_price,
					ass.id_storage AS id_storage,
					CASE ass.storage_type
					WHEN 'person'		THEN CONCAT_WS(' ', pr.fname,pr.mname,pr.lname)
					WHEN 'storagehouse' THEN CONCAT_WS(' ', per.fname,per.mname,per.lname)
					END AS mol,
					st.mol_id_person as mol_id,
					pr.id_office AS id_region,
					o.name AS region,
					/*f.name AS fname,*/
					f.id  AS  fid,
					IF(UNIX_TIMESTAMP(ass.invoice_date),DATE_FORMAT(ass.invoice_date,'%d.%m.%Y'),'') AS invoice_date
				FROM {$db_name_storage}.assets ass
				
				LEFT JOIN storagehouses st
				ON ass.id_storage = st.id
				LEFT JOIN {$db_name_personnel}.personnel per
				ON  st.mol_id_person=per.id
				LEFT JOIN {$db_name_personnel}.personnel pr
				ON ass.id_storage=pr.id
				LEFT JOIN {$db_name_sod}.offices o
				ON o.id=pr.id_office
				LEFT JOIN {$db_name_sod}.firms f
				ON f.id = o.id_firm
				WHERE ass.id={$nID};";
				
				$aData = $this->oDB->GetArray($sQuery);
				if($aData == false)
				{
					throw new Exception($this->oDB->ErrorMsg());
				}
		}
		
		public function getSubAssetInfo($nID,$aIDS, DBResponse $oResponse)
		{
			$nWebServerTime = time();	
			
			$sQuery="SELECT 
					SQL_CALC_FOUND_ROWS
					id AS id,
					LPAD(id,6,0) AS num, 
					name,
					CONCAT_WS(' ',price,'лв.') AS price,
					CASE status
					WHEN 'entered'	THEN	(amortization_months_left/amortization_months)*price
				    WHEN 'attached'	THEN 	
						if( floor(({$nWebServerTime} - unix_timestamp(attach_date))/2592000) < amortization_months_left,
						((amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(attach_date))/2592000))/amortization_months)*price ,0)
						ELSE (amortization_months_left/amortization_months)*price 
					END AS price_left,											
					CONCAT_WS(' ',amortization_months,'мес.') AS term,
					CONCAT_WS(
						' ',
						CASE status
							WHEN 'entered' THEN amortization_months_left
							WHEN 'attached' THEN
							IF( 
								floor( ( {$nWebServerTime} - unix_timestamp( attach_date ) ) / 2592000 ) < amortization_months_left,
								( ( amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( attach_date ) ) / 2592000 ) ) ),
								0
							)
							ELSE amortization_months_left
						END,
						'мес.'
					) AS term_left	
					FROM assets
					WHERE 1
					AND to_arc=0
					AND storage_type='asset'
					AND id_storage IN({$aIDS}) ";
//			for($i=0; $i < count($aIDS);$i++)
//			{
//				$sQuery.=" OR id_storage={$aIDS[$i]} ";
//			}
			//id_storage IN ({$string});
			$this->getResult($sQuery,"num", DBAPI_SORT_ASC, $oResponse);
			foreach ($oResponse->oResult->aData as $key => &$val)
			{
				$val['price_left'] = round($val['price_left'],2).' лв.';
				
				$oResponse->setDataAttributes($key,'num',array('style'=>'text-align:right'));
				$oResponse->setDataAttributes($key,'name',array('style'=>'text-align:right'));
				$oResponse->setDataAttributes($key,'price',array('style'=>'text-align:right'));
				$oResponse->setDataAttributes($key,'price_left',array('style'=>'text-align:right'));
				$oResponse->setDataAttributes($key,'term',array('style'=>'text-align:right'));
				$oResponse->setDataAttributes($key,'term_left',array('style'=>'text-align:right'));
				
				
			}
		
			
	$right_edit = false;
	$right_view	= false;
	  
	if (!empty($_SESSION['userdata']['access_right_levels'] )) {
	   if ( in_array('asset_info_sub_assets_edit', $_SESSION['userdata']['access_right_levels']) ) {
	        $right_view = true;
		    $right_edit = true;
	   }
       if (in_array('asset_info_sub_assets_view', $_SESSION['userdata']['access_right_levels'])) 
		    $right_view = true;		
	 }
			
	
        $oResponse->setField("num"," Номер","Сортирай по инвентарен номер",null,null,null,array());			
		$oResponse->setField("name","Име","Сортирай по име",null,null,null,array());
		$oResponse->setField("price","Цена","Сортирай по цена",null,null,null,array());
		$oResponse->setField("price_left","Остатъчна цена","Сотрирай по остатъчна цена",null,null,null,array());
		$oResponse->setField("term","Срок на амортизация","Сотрирай по срок на амортизация",null,null,null,array());
		$oResponse->setField("term_left","Остатъчен срок ","Сотрирай по остатъчен срок",null,null,null,array());
			
		if ($right_edit) {
	      $oResponse->setFieldLink("num", 'viewAsset');
		}
       
			
		}
		
		public function getAttributesList( $nIDAsset )
		{
			if( empty( $nIDAsset ) || !is_numeric( $nIDAsset ) )return " ";
			
			$sAttributes = "";
			
			$sQuery = "
					SELECT
						att.name,
						aa.value
					FROM assets_attributes aa
						LEFT JOIN attributes att ON att.id = aa.id_attribute
					WHERE 1
						AND aa.to_arc = 0
						AND att.to_arc = 0
						AND aa.id_asset = {$nIDAsset}
					ORDER BY name
			";
			
			$aAssetAttributes = $this->select( $sQuery );
			
			if( !empty( $aAssetAttributes ) )
			{
				foreach( $aAssetAttributes as $aAssetAttribute )
				{
					$sAttributes .= "{$aAssetAttribute['name']} ({$aAssetAttribute['value']}); ";
				}
			}
			else
			{
				$sAttributes = " ";
			}
			
			return $sAttributes;
		}
		
		public function getAssets( $nStartLevel = 0, $nIDAsset, $aData, $aParams, $oResponse, $bSearchDeep = true )
		{
			global $db_name_personnel, $db_name_sod, $db_storage_backup, $nOrder, $nPriceTotal, $nPriceLeftTotal;
			
			//Initializations
			$nWebServerTime = time();
			if( $nStartLevel == 0 )
			{
				$nPriceTotal = 0;
				$nPriceLeftTotal = 0;
				$nOrder = 1;
			}
			//End Initializations
			
			//Create Conditions
			$sConditions = "";
			$sSubConditions = "";
			$sIndependantConditions = "";
			$sHavingWhat = " 1 ";
			
			//--Params
			$sStatus 				= isset( $aParams['sStatus'] ) 					? $aParams['sStatus'] 				: '';
			
			$nIDGroup 				= isset( $aParams['nIDGroup'] ) 				? $aParams['nIDGroup'] 				: 0;
			$nIDNomenclature 		= isset( $aParams['nIDNomenclature'] ) 			? $aParams['nIDNomenclature'] 		: 0;
			$nIDCustomNomenclature 	= isset( $aParams['nIDCustomNomenclature'] ) 	? $aParams['nIDCustomNomenclature'] : 0;
			
			$nIDPerson 				= isset( $aParams['nIDPerson'] ) 				? $aParams['nIDPerson'] 			: 0;
			$nIDOffice 				= isset( $aParams['nIDOffice'] ) 				? $aParams['nIDOffice'] 			: 0;
			$nIDFirm 				= isset( $aParams['nIDFirm'] ) 					? $aParams['nIDFirm'] 				: 0;
			$nIDStoragehouse 		= isset( $aParams['nIDStoragehouse'] ) 			? $aParams['nIDStoragehouse'] 		: 0;
			//--End Params
			
			//--Basic Conditions
			$sConditions .= "
						a.to_arc = 0
						AND asn.to_arc = 0
			";
			//--End Basic Conditions
			
			//--Dependant Filtering
			if( $nStartLevel == 0 )
			{
				switch( $sStatus )
				{
					case 'attached':
						$sConditions .= "
							AND a.id_storage != 0
							AND a.status = 'attached'
						";
						if( !empty( $nIDPerson ) )
						{
							$sConditions .= "
								AND a.id_storage = {$nIDPerson}
							";
						}
						if( !empty( $nIDOffice ) )
						{
							$sConditions .= "
								AND o.id = {$nIDOffice}
							";
						}
						if( !empty( $nIDFirm ) )
						{
							$sConditions .= "
								AND o.id_firm = {$nIDFirm}
							";
						}
						break;
					
					case 'entered':
						$sConditions .= "
							AND a.id_storage != 0
							AND a.status = 'entered'
							AND a.storage_type = 'storagehouse'
						";
						if( !empty( $nIDStoragehouse ) )
						{
							$sConditions .= "
								AND a.id_storage = {$nIDStoragehouse}
							";
						}
						break;
					
					case 'wasted':
						$sConditions .= "
							AND a.status = 'wasted'
						";
						break;
					
					default:
						break;
				}
			}
			else
			{
				$sConditions .= "
							AND a.id_storage = {$nIDAsset}
							AND a.storage_type = 'asset'
				";
			}
			
			$sSubConditions = $sConditions;
			
			//----Independant Filtering
			if( !empty( $nIDCustomNomenclature ) )
			{
				$sIndependantConditions .= "
						asn.id = {$nIDCustomNomenclature}
				";
			}
			else 
			{
				if( !empty( $nIDGroup ) )
				{
					if( !empty( $nIDNomenclature ) )
					{
						$sIndependantConditions .= "
							asn.id = {$nIDNomenclature} AND
						";
					}
					
					$sIndependantConditions .= "
							asn.id_group = {$nIDGroup}
					";
					
					$sConditions .= " AND " . $sIndependantConditions;
					
					$sIndependantConditions .= " AND ";
				}
			}
			//----End Independant Filtering
			
			if( $sStatus == "out" )
			{
				$sHavingWhat = " amortization_months_left = 0 ";
			}
			
			if( $nStartLevel == 0 && $bSearchDeep )
			{
				$sConditions .= "
							OR
							(
								{$sIndependantConditions}
								a.storage_type = 'asset'
								AND a.id_storage IN
								(
									SELECT
										a.id
									FROM assets a
										LEFT JOIN {$db_name_personnel}.personnel p ON ( p.id = a.id_storage AND a.storage_type = 'person' )
										LEFT JOIN {$db_name_sod}.offices o ON ( o.id = p.id_office AND a.storage_type = 'person' )
										LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
									WHERE
										{$sSubConditions}
									HAVING
										{$sHavingWhat}
								)
							)
				";
			}
			//--End Dependant Filtering
			//End Create Conditions
			
			//Creating Query
			$sQuery = "
					SELECT
						LPAD( a.id, 6, 0 ) AS id,
						IF( a.storage_type = 'asset', 0, 1 ) AS is_parent,
						
						a.name,
						CASE a.storage_type
							WHEN 'person'		THEN CONCAT_WS( ' ', pmol.fname, pmol.mname, pmol.lname )
							WHEN 'storagehouse' THEN CONCAT_WS( ' ', smol.fname, smol.mname, smol.lname )
							WHEN 'asset'		THEN '---'
						END AS mol,
						
						a.price,
						CASE a.status
							WHEN 'entered'	THEN ( a.amortization_months_left / a.amortization_months ) * a.price
							WHEN 'attached'	THEN
								IF
								(
									FLOOR( ( {$nWebServerTime} - UNIX_TIMESTAMP( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
									( ( a.amortization_months_left - FLOOR( ( {$nWebServerTime} - UNIX_TIMESTAMP( a.attach_date ) ) / 2592000 ) ) / a.amortization_months ) * a.price,
									0
								)
							ELSE ( a.amortization_months_left / a.amortization_months ) * a.price
						END AS price_left,
						
						a.amortization_months,
						CASE a.status
							WHEN 'attached' THEN
								IF
								(
									FLOOR( ( {$nWebServerTime} - UNIX_TIMESTAMP( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
									a.amortization_months_left - FLOOR( ( {$nWebServerTime} - UNIX_TIMESTAMP( a.attach_date ) ) / 2592000 ),
									0
								)
							ELSE a.amortization_months_left
						END AS amortization_months_left,
						
						GROUP_CONCAT( CONCAT( att.name, ' (', aa.value, ')' ) ORDER BY att.name SEPARATOR '; ' ) AS attributes,
						
						IF
						(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( a.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM
						assets a
					LEFT JOIN
						storagehouses st ON ( a.id_storage = st.id AND a.storage_type = 'storagehouse' )
					LEFT JOIN
						{$db_name_personnel}.personnel smol ON ( st.mol_id_person = smol.id AND a.storage_type = 'storagehouse' )
					LEFT JOIN
						{$db_name_personnel}.personnel pmol ON ( a.id_storage = pmol.id AND a.storage_type = 'person' )
					LEFT JOIN
						{$db_name_sod}.offices o ON ( o.id = pmol.id_office AND a.storage_type = 'person' )
					
					LEFT JOIN
						assets_nomenclatures asn ON asn.id = a.id_nomenclature
					
					LEFT JOIN
						assets_attributes aa ON ( aa.id_asset = a.id AND aa.to_arc = 0 )
					LEFT JOIN
						attributes att ON att.id = aa.id_attribute
					
					LEFT JOIN
						{$db_name_personnel}.personnel p ON a.updated_user = p.id
					WHERE
						{$sConditions}
					GROUP BY
						a.id
					HAVING
						{$sHavingWhat}
					ORDER BY
						a.name
			";
			
			$aResult = $this->selectFromDB( $db_storage_backup, $sQuery );
			
			if( empty( $aResult ) )return $aData;
			
			foreach( $aResult as $aElement )
			{
				if( $aElement['is_parent'] == "0" 	&&
					$nStartLevel == 0 				&&
					empty( $nIDCustomNomenclature ) &&
					empty( $nIDNomenclature ) 		&&
					empty( $nIDGroup )					)continue;
				
				$nKey = $aElement['id'];
				$aData[$nKey] = array();
				$aData[$nKey]['id'] 						= $aElement['id'];
				$aData[$nKey]['name'] 						= $aElement['name'];
				$aData[$nKey]['mol'] 						= $aElement['mol'];
				$aData[$nKey]['price'] 						= $aElement['price'];
				$aData[$nKey]['price_left'] 				= $aElement['price_left'];
				$aData[$nKey]['amortization_months'] 		= $aElement['amortization_months'];
				$aData[$nKey]['amortization_months_left'] 	= $aElement['amortization_months_left'];
				$aData[$nKey]['attributes'] 				= $aElement['attributes'];
				$aData[$nKey]['updated_user'] 				= $aElement['updated_user'];
				$aData[$nKey]['hierarchy'] 					= $nOrder++;
				
				if( $sStatus != "out" )
				{
					$oResponse->setDataAttributes( $nKey, 'name', array( "style" => "padding-left: " . strval( $nStartLevel * 5 ) . "mm" ) );
					if( $nStartLevel == 0 && $bSearchDeep )
					{
						$oResponse->setDataAttributes( $nKey, 'name', array( "style" => "font-weight: bold;" ) );
					}
				}
				
				if( $bSearchDeep )
				{
					$nPriceTotal += $aData[$nKey]['price'];
					$nPriceLeftTotal += $aData[$nKey]['price_left'];
					
					$aData = $this->getAssets( $nStartLevel + 1, $nKey, $aData, $aParams, $oResponse, $bSearchDeep );
				}
				else
				{
					$nPriceTotal += $this->getTotalPrice( $nKey );
					$nPriceLeftTotal += $this->getPrice( $nKey );
				}
			}
			//End Creating Query
			
			return $aData;
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			global $nPriceTotal, $nPriceLeftTotal;
			$aTotal = array();
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'assets_stock_taking', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			
			$bSearchDeep = ( isset( $aParams['sResultType'] ) && $aParams['sResultType'] == 'detailed' ) ? true : false;
			
			$aFinalData = array();
			$aFinalData = $this->getAssets( 0, 0, $aFinalData, $aParams, $oResponse, $bSearchDeep );
			
			$aTotal['count'] = count( $aFinalData );
			$aTotal['price'] = $nPriceTotal;
			$aTotal['price_left'] = $nPriceLeftTotal;
			
			$oResponse->setField( "id", 						"Инв. Номер", 			"" );
			$oResponse->setField( "hierarchy", 					"Ред",			 		"" );
			$oResponse->setField( "name", 						"Наименование", 		"" );
			$oResponse->setField( "mol", 						"МОЛ", 					"" );
			$oResponse->setField( "price", 						"Цена на прид.", 		"" );
			$oResponse->setField( "price_left", 				"Ост. стойност", 		"" );
			$oResponse->setField( "amortization_months", 		"Срок на Аморт.", 		"" );
			$oResponse->setField( "amortization_months_left", 	"Ост. Срок", 			"" );
			$oResponse->setField( "attributes", 				"Атрибути", 			"" );
			$oResponse->setField( "updated_user", 				"...", 					"", "images/dots.gif" );
			
			$oResponse->addTotal( "price", $aTotal['price'] . " лв." );
			$oResponse->addTotal( "price_left", $aTotal['price_left'] . " лв." );
			$oResponse->addTotal( "name", $aTotal['count'] . " бр." );
			
			if( $right_edit )
			{
				$oResponse->setFieldLink( "id", "openAsset" );
			}
			
			//Sortings
			$oParams = Params::getInstance();
			
			$sSortField = $oParams->get( "sfield", "hierarchy" );
			$nSortType	= $oParams->get( "stype", DBAPI_SORT_ASC );
			
			if( empty( $sSortField ) )$sSortField = "name";
			
			foreach( $aFinalData as $key => $row )
			{
				$id[$key]  = 						$row['id'];
				$name[$key] = 						$row['name'];
				$amortization_months[$key] = 		$row['amortization_months'];
				$amortization_months_left[$key] = 	$row['amortization_months_left'];
				$attributes[$key] = 				$row['attributes'];
				$updated_user[$key] = 				$row['updated_user'];
				$hierarchy[$key] = 					$row['hierarchy'];
				$price[$key] = 						$row['price'];
				$price_left[$key] = 				$row['price_left'];
				$mol[$key] = 						$row['mol'];
			}
			
			if( $nSortType == DBAPI_SORT_ASC )$nSortOrderArray = SORT_ASC;
			if( $nSortType == DBAPI_SORT_DESC )$nSortOrderArray = SORT_DESC;
			
			if( $sSortField == "id" || 
				$sSortField == "amortization_months" ||
				$sSortField == "amortization_months_left" ||
				$sSortField == "hierarchy" ||
				$sSortField == "price" ||
				$sSortField == "price_left" )$nSortTypeArray = SORT_NUMERIC;
			else $nSortTypeArray = SORT_STRING;
			
			array_multisort( $$sSortField, $nSortOrderArray, $nSortTypeArray, $aFinalData );
			
			$oResponse->setSort( $sSortField, $nSortType );
			//End Sortings
			
			//Paging
			$nPage = $oParams->get( "current_page", 1 );
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ( $nPage - 1 ) * $nRowCount;
			$nRowTotal = $aTotal['count'];
			
			$nIndex = 0;
			$aPagedData = array();
			foreach( $aFinalData as $FDKey => $FDValue )
			{
				if( $nIndex >= $nRowOffset && $nIndex < ( $nRowOffset + $nRowCount ) )
				{
					$aPagedData[$FDKey] = $FDValue;
				}
				
				$nIndex++;
			}
			
			$oResponse->setPaging( $nRowCount, $nRowTotal, ceil( $nRowOffset / $nRowCount ) + 1 );
			//End Paging
			
			if ( isset ( $aParams['is_paging_ignored'] ) && $aParams['is_paging_ignored'] == 1 )
			{
				$oResponse->setData( $aFinalData );
			}
			else
			{
				$oResponse->setData( $aPagedData );
			}
		}

		public function getGroupsReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_sod, $nGroupOrder, $nRecordCount;
			
			$nGroupOrder = 1;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'assets_stock_taking', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$nWebServerTime = time();	
				
			$sQuery = "
					SELECT
						LPAD( asg.id, 6, 0 ) AS id,
						asg.name,
						COUNT( a.name ) AS actives_count,
						SUM( a.price ) AS price,
						ROUND( AVG( a.price ), 2 ) AS price_avg,
						SUM(
							ROUND(
								CASE a.status
									WHEN 'entered'	THEN ( a.amortization_months_left / a.amortization_months ) * a.price
									WHEN 'attached'	THEN 	
										IF
										(
											floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
											( ( a.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) ) / a.amortization_months ) * a.price,
											0
										)
									ELSE( a.amortization_months_left / a.amortization_months ) * a.price 
								END,
								2
							)
						) AS price_left,
						SUM(
							CASE a.status
								WHEN 'attached' THEN 
									IF( floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
									a.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ),
									0 )
								ELSE a.amortization_months_left
							END
						) AS amortization_months_left,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( asg.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM assets_groups asg
						LEFT JOIN assets a ON a.id_group = asg.id
						LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
						LEFT JOIN {$db_name_personnel}.personnel p ON asg.updated_user = p.id
						LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
						LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
						LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
					WHERE 1
						AND asg.to_arc = 0
						AND asg.parent_id = 0
						AND
							IF
							(
								a.id != 0,
								(
									a.to_arc = 0
									AND asn.to_arc = 0
			";
			
			//Asset Conditions
			$sSubActivesCriteria = "";
			$sAddition = "";
			
			if( isset( $aParams['nIDGroup'] ) && !empty( $aParams['nIDGroup'] ) )
			{
				if( isset( $aParams['nIDNomenclature'] ) && !empty( $aParams['nIDNomenclature'] ) )
				{
					$sQuery .= "
							AND asn.id = {$aParams['nIDNomenclature']}
					";
					$sSubActivesCriteria .= " AND asn.id = {$aParams['nIDNomenclature']}";
					$sAddition = $sSubActivesCriteria;
				}
				else
				{
					$sQuery .= "
							AND asg.id = {$aParams['nIDGroup']}
					";
					$sSubActivesCriteria .= " AND asg.id = {$aParams['nIDGroup']}";
					$sAddition = $sSubActivesCriteria;
				}
			}
			
			if( !empty( $aParams ) )
			{
				switch( $aParams['sStatus'] )
				{
					case 'attached':
						//$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'attached' AND a.storage_type = 'person'";
						$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'attached'";
						$sQuery .= "
									AND 
									(
										a.id_storage != 0
										AND a.status = 'attached'
										AND a.storage_type = 'person'
						";
						if( !empty( $aParams['nIDFirm'] ) )
						{
							$sSubActivesCriteria .= " AND f.id = {$aParams['nIDFirm']}";
							$sQuery .= "
										AND f.id = {$aParams['nIDFirm']}
							";
						}
						if( !empty( $aParams['nIDOffice'] ) )
						{
							$sSubActivesCriteria .= " AND o.id = {$aParams['nIDOffice']}";
							$sQuery .= "
										AND o.id = {$aParams['nIDOffice']}
							";
						}
						if( !empty( $aParams['nIDPerson'] ) )
						{
							$sSubActivesCriteria .= " AND a.id_storage = {$aParams['nIDPerson']}";
							$sQuery .= "
										AND a.id_storage = {$aParams['nIDPerson']}
							";
						}
						$sQuery .= "
									)
									OR
									(
										a.id_storage != 0
										{$sAddition}
										AND a.storage_type = 'asset'
										AND a.id_storage IN
										(
											SELECT
												a.id
											FROM assets a
												LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
												LEFT JOIN assets_groups asg ON asg.id = a.id_group
												LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
											WHERE 1
												AND a.to_arc = 0
												AND asn.to_arc = 0
												AND asg.to_arc = 0
												{$sSubActivesCriteria}
										)
									)
						";
						break;
					case 'entered':
						//$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'entered' AND a.storage_type = 'storagehouse'";
						$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'entered'";
						$sQuery .= "
									AND
									(
										a.id_storage != 0
										AND a.status = 'entered'
										AND a.storage_type = 'storagehouse'
						";
						if( !empty( $aParams['nIDStoragehouse'] ) )
						{
							$sSubActivesCriteria .= " AND a.id_storage = {$aParams['nIDStoragehouse']}";
							$sQuery .= "
										AND a.id_storage = {$aParams['nIDStoragehouse']}
							";
						}
						$sQuery .= "
									)
									OR
									(
										a.id_storage != 0
										{$sAddition}
										AND a.storage_type = 'asset'
										AND a.id_storage IN
										(
											SELECT
												a.id
											FROM assets a
												LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
												LEFT JOIN assets_groups asg ON asg.id = a.id_group
												LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
											WHERE 1
												AND a.to_arc = 0
												AND asn.to_arc = 0
												AND asg.to_arc = 0
												{$sSubActivesCriteria}
										)
									)
						";
						break;
					case 'wasted':
						$sQuery .= "
									AND a.status = 'wasted'
						";
						break;
					
					default:
						break;
				}
			}
			
			$sQuery .= "
								),
								1
							)
							GROUP BY asg.name
			";
			
			if( $aParams['sStatus'] == "out" )
			{
				$sQuery .= "
						HAVING 
								amortization_months_left = 0
				";
			}
			//End Asset Conditions
			
			$aResult = $this->select( $sQuery );
			
			$aFinalData = array();
			$nRecordCount = 0;
			
			foreach( $aResult as $aResRecord )
			{
				$nIDGroup = $aResRecord['id'];
				$key = $aResRecord['id'];
				
				//Set Data
				$aFinalData[$key]['id'] = 					$aResRecord['id'];
				$aFinalData[$key]['name'] = 				$aResRecord['name'];
				$aFinalData[$key]['actives_count'] = 		$aResRecord['actives_count'];
				$aFinalData[$key]['price'] = 				$aResRecord['price'];
				$aFinalData[$key]['price_avg'] = 			$aResRecord['price_avg'];
				$aFinalData[$key]['price_left'] = 			$aResRecord['price_left'];
				$aFinalData[$key]['updated_user'] = 		$aResRecord['updated_user'];
				$aFinalData[$key]['hierarchy'] = $nGroupOrder++;
				//End Set Data
				
				//NULL Check
				if( $aFinalData[$key]['price'] == NULL )$aFinalData[$key]['price'] = 0;
				if( $aFinalData[$key]['price_avg'] == NULL )$aFinalData[$key]['price_avg'] = 0;
				if( $aFinalData[$key]['price_left'] == NULL )$aFinalData[$key]['price_left'] = 0;
				//End NULL Check
				
				$oResponse->setDataAttributes( $key, 'price', 			array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'price_avg', 		array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'price_left', 		array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'actives_count', 	array( "style" => "text-align: right;" ) );
				
				if( isset( $aParams['sResultType'] ) && $aParams['sResultType'] == 'subgroups' )
				{
					$oResponse->setDataAttributes( $key, 'name', array( "style" => "font-weight: bold;" ) );
				}
				
				//Add Subgroups (If Any)
				$aFinalData = $this->getSubgroupsForReport( $nIDGroup, $nIDGroup, $aFinalData, 1, $oResponse, $aParams );
				//End Add Subgroups (If Any)
				
				//Add Measurements
				$aFinalData[$key]['price'] 		.= " лв.";
				$aFinalData[$key]['price_avg'] 	.= " лв.";
				$aFinalData[$key]['price_left'] .= " лв.";
				//End Add Measurements
				
				$nRecordCount++;
				
//				if( $aParams['sResultType'] == "groups" )
//				{
//					if( empty( $aFinalData[$key]['actives_count'] ) )
//					{
//						unset( $aFinalData[$key] );
//						$nRecordCount--;
//					}
//				}
			}
			
			$aFinalData2 = $aFinalData;
			foreach( $aFinalData2 as $nKey => $aValue )
			{
				if( empty( $aValue['actives_count'] ) )
				{
					unset( $aFinalData[$nKey] );
					$nRecordCount--;
				}
			}
			
			$oResponse->setField( "hierarchy", 			"Ред",			 		"" );
			$oResponse->setField( "name", 				"Група",		 		"" );
			$oResponse->setField( "actives_count", 		"Брой активи", 			"" );
			$oResponse->setField( "price", 				"Обща стойност", 		"" );
			$oResponse->setField( "price_avg", 			"Средна стойност", 		"" );
			$oResponse->setField( "price_left", 		"Ост. стойност", 		"" );
			$oResponse->setField( "updated_user", 		"...", 					"", "images/dots.gif" );
			
			if( $right_edit )
			{
				$oResponse->setFieldLink( "name", "gotoNomenclatures" );
			}
			
			//Sortings
			$oParams = Params::getInstance();
			
			$sSortField = $oParams->get( "sfield", "hierarchy" );
			$nSortType	= $oParams->get( "stype", DBAPI_SORT_ASC );
			
			if( empty( $sSortField ) )$sSortField = "hierarchy";
			
			foreach( $aFinalData as $key => $row )
			{
				$id[$key]  = 			$row['id'];
				$name[$key] = 			$row['name'];
				$actives_count[$key] = 	$row['actives_count'];
				$updated_user[$key] = 	$row['updated_user'];
				$hierarchy[$key] = 		$row['hierarchy'];
				$price[$key] = 			$row['price'];
				$price_avg[$key] = 		$row['price_avg'];
				$price_left[$key] = 	$row['price_left'];
			}
			
			if( $nSortType == DBAPI_SORT_ASC )$nSortOrderArray = SORT_ASC;
			if( $nSortType == DBAPI_SORT_DESC )$nSortOrderArray = SORT_DESC;
			
			if( $sSortField == "actives_count" || 
				$sSortField == "hierarchy" ||
				$sSortField == "price" ||
				$sSortField == "price_avg" ||
				$sSortField == "price_left" )$nSortTypeArray = SORT_NUMERIC;
			else $nSortTypeArray = SORT_STRING;
			
			array_multisort( $$sSortField, $nSortOrderArray, $nSortTypeArray, $aFinalData );
			
			$oResponse->setSort( $sSortField, $nSortType );
			//End Sortings
			
			//Paging
			$nPage = $oParams->get( "current_page", 1 );
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ( $nPage - 1 ) * $nRowCount;
			$nRowTotal = $nRecordCount;
			
			$nIndex = 0;
			$aPagedData = array();
			foreach( $aFinalData as $FDKey => $FDValue )
			{
				if( $nIndex >= $nRowOffset && $nIndex < ( $nRowOffset + $nRowCount ) )
				{
					$aPagedData[$FDKey] = $FDValue;
				}
				
				$nIndex++;
			}
			
			$oResponse->setPaging( $nRowCount, $nRowTotal, ceil( $nRowOffset / $nRowCount ) + 1 );
			//End Paging
			
			if ( isset ( $aParams['is_paging_ignored'] ) && $aParams['is_paging_ignored'] == 1 )
			{
				$oResponse->setData( $aFinalData );
			}
			else
			{
				$oResponse->setData( $aPagedData );
			}
		}

		function getSubgroupsForReport( $nIDGroup, $nIDRootGroup, $aFinalData, $nLevel, $oResponse, $aParams )
		{
			global $db_name_personnel, $db_name_sod, $nGroupOrder, $nRecordCount;
			
			//$nIDGroup = (int) $nIDGroup;
			
			$nWebServerTime = time();
			
			$sQuery = "
					SELECT
						LPAD( asg.id, 6, 0 ) AS id,
						asg.name,
						COUNT( a.name ) AS actives_count,
						SUM( a.price ) AS price,
						ROUND( AVG( a.price ), 2 ) AS price_avg,
						SUM(
							ROUND(
								CASE a.status
									WHEN 'entered'	THEN ( a.amortization_months_left / a.amortization_months ) * a.price
									WHEN 'attached'	THEN 	
										IF
										(
											floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
											( ( a.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) ) / a.amortization_months ) * a.price,
											0
										)
									ELSE( a.amortization_months_left / a.amortization_months ) * a.price 
								END,
								2
							)
						) AS price_left,
						SUM(
							CASE a.status
								WHEN 'attached' THEN 
									IF( floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
									a.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ),
									0 )
								ELSE a.amortization_months_left
							END
						) AS amortization_months_left,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( asg.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM assets_groups asg
						LEFT JOIN assets a ON a.id_group = asg.id
						LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
						LEFT JOIN {$db_name_personnel}.personnel p ON asg.updated_user = p.id
						LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
						LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
						LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
					WHERE 1
						AND asg.to_arc = 0
						AND asg.parent_id = {$nIDGroup}
						AND
							IF
							(
								a.id != 0,
								(
									a.to_arc = 0
									AND asn.to_arc = 0
			";
			
			//Asset Conditions
			$sSubActivesCriteria = "";
			$sAddition = "";
			
			if( isset( $aParams['nIDGroup'] ) && !empty( $aParams['nIDGroup'] ) )
			{
				if( isset( $aParams['nIDNomenclature'] ) && !empty( $aParams['nIDNomenclature'] ) )
				{
					$sQuery .= "
							AND asn.id = {$aParams['nIDNomenclature']}
					";
					$sSubActivesCriteria .= " AND asn.id = {$aParams['nIDNomenclature']}";
					$sAddition = $sSubActivesCriteria;
				}
				else
				{
					$sQuery .= "
							AND asg.id = {$aParams['nIDGroup']}
					";
					$sSubActivesCriteria .= " AND asg.id = {$aParams['nIDGroup']}";
					$sAddition = $sSubActivesCriteria;
				}
			}
			
			if( !empty( $aParams ) )
			{
				switch( $aParams['sStatus'] )
				{
					case 'attached':
						//$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'attached' AND a.storage_type = 'person'";
						$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'attached'";
						$sQuery .= "
									AND 
									(
										a.id_storage != 0
										AND a.status = 'attached'
										AND a.storage_type = 'person'
						";
						if( !empty( $aParams['nIDFirm'] ) )
						{
							$sSubActivesCriteria .= " AND f.id = {$aParams['nIDFirm']}";
							$sQuery .= "
										AND f.id = {$aParams['nIDFirm']}
							";
						}
						if( !empty( $aParams['nIDOffice'] ) )
						{
							$sSubActivesCriteria .= " AND o.id = {$aParams['nIDOffice']}";
							$sQuery .= "
										AND o.id = {$aParams['nIDOffice']}
							";
						}
						if( !empty( $aParams['nIDPerson'] ) )
						{
							$sSubActivesCriteria .= " AND a.id_storage = {$aParams['nIDPerson']}";
							$sQuery .= "
										AND a.id_storage = {$aParams['nIDPerson']}
							";
						}
						$sQuery .= "
									)
									OR
									(
										a.id_storage != 0
										{$sAddition}
										AND a.storage_type = 'asset'
										AND a.id_storage IN
										(
											SELECT
												a.id
											FROM assets a
												LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
												LEFT JOIN assets_groups asg ON asg.id = a.id_group
												LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
											WHERE 1
												AND a.to_arc = 0
												AND asn.to_arc = 0
												AND asg.to_arc = 0
												{$sSubActivesCriteria}
										)
									)
						";
						break;
					case 'entered':
						//$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'entered' AND a.storage_type = 'storagehouse'";
						$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'entered'";
						$sQuery .= "
									AND
									(
										a.id_storage != 0
										AND a.status = 'entered'
										AND a.storage_type = 'storagehouse'
						";
						if( !empty( $aParams['nIDStoragehouse'] ) )
						{
							$sSubActivesCriteria .= " AND a.id_storage = {$aParams['nIDStoragehouse']}";
							$sQuery .= "
										AND a.id_storage = {$aParams['nIDStoragehouse']}
							";
						}
						$sQuery .= "
									)
									OR
									(
										a.id_storage != 0
										{$sAddition}
										AND a.storage_type = 'asset'
										AND a.id_storage IN
										(
											SELECT
												a.id
											FROM assets a
												LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
												LEFT JOIN assets_groups asg ON asg.id = a.id_group
												LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
												LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
											WHERE 1
												AND a.to_arc = 0
												AND asn.to_arc = 0
												AND asg.to_arc = 0
												{$sSubActivesCriteria}
										)
									)
						";
						break;
					case 'wasted':
						$sQuery .= "
									AND a.status = 'wasted'
						";
						break;
					
					default:
						break;
				}
			}
			
			$sQuery .= "
								),
								1
							)
							GROUP BY asg.name
			";
			
			if( $aParams['sStatus'] == "out" )
			{
				$sQuery .= "
						HAVING 
								amortization_months_left = 0
							)
				";
			}
			//End Asset Conditions
			
			$aSubgroups = $this->select( $sQuery );
			
			if( empty( $aSubgroups ) )return $aFinalData;
			
			foreach( $aSubgroups as $aResRecord )
			{
				$nIDGroup2 = $aResRecord['id'];
				$key = $aResRecord['id'];
				
				if( $aParams['sResultType'] == "subgroups" )
				{
					//Set Data
					$aFinalData[$key]['id'] = 					$aResRecord['id'];
					$aFinalData[$key]['name'] = 				$aResRecord['name'];
					$aFinalData[$key]['actives_count'] = 		$aResRecord['actives_count'];
					$aFinalData[$key]['price'] = 				$aResRecord['price'];
					$aFinalData[$key]['price_avg'] = 			$aResRecord['price_avg'];
					$aFinalData[$key]['price_left'] = 			$aResRecord['price_left'];
					$aFinalData[$key]['updated_user'] = 		$aResRecord['updated_user'];
					$aFinalData[$key]['hierarchy'] = $nGroupOrder++;
					//End Set Data
					
					$oResponse->setDataAttributes( $key, 'price', 			array( "style" => "text-align: right;" ) );
					$oResponse->setDataAttributes( $key, 'price_avg', 		array( "style" => "text-align: right;" ) );
					$oResponse->setDataAttributes( $key, 'price_left', 		array( "style" => "text-align: right;" ) );
					$oResponse->setDataAttributes( $key, 'actives_count', 	array( "style" => "text-align: right;" ) );
					
					if( isset( $aParams['sStatus'] ) && $aParams['sStatus'] != "out" )$oResponse->setDataAttributes( $key, 'name', array( "style" => "padding-left: " . strval( $nLevel * 5 ) . "mm" ) );
					
					$nRecordCount++;
//					
//					if( empty( $aFinalData[$key]['actives_count'] ) )
//					{
//						unset( $aFinalData[$key] );
//						$nRecordCount--;
//					}
				}
				else
				{
					//Set Data
					if( isset( $aFinalData[$nIDRootGroup]['actives_count'] ) )	$aFinalData[$nIDRootGroup]['actives_count'] += 	$aResRecord['actives_count'];
					if( isset( $aFinalData[$nIDRootGroup]['price'] ) )			$aFinalData[$nIDRootGroup]['price'] += 			$aResRecord['price'];
					if( isset( $aFinalData[$nIDRootGroup]['price_avg'] ) )		$aFinalData[$nIDRootGroup]['price_avg'] += 		( $aResRecord['price_avg'] / 2 );
					if( isset( $aFinalData[$nIDRootGroup]['price_left'] ) )		$aFinalData[$nIDRootGroup]['price_left'] += 	$aResRecord['price_left'];
					//End Set Data
				}
				
				//Add Subassets (If Any)
				$aFinalData = $this->getSubgroupsForReport( $nIDGroup2, $nIDGroup, $aFinalData, $nLevel + 1, $oResponse, $aParams );
				//End Add Subassets (If Any)
				
				if( $aParams['sResultType'] == "subgroups" )
				{
					//Add Measurements
					$aFinalData[$key]['price'] 		.= " лв.";
					$aFinalData[$key]['price_avg'] 	.= " лв.";
					$aFinalData[$key]['price_left'] .= " лв.";
					//End Add Measurements
				}
			}
			
			return $aFinalData;
		}
		
		public function getNomenclaturesReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_sod;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'assets_stock_taking', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			$nWebServerTime = time();	
				
			$sQuery = "
					SELECT
						LPAD( asn.id, 6, 0 ) AS id,
						asn.name,
						COUNT( a.name ) AS actives_count,
						SUM( a.price ) AS price,
						ROUND( AVG( a.price ), 2 ) AS price_avg,
						SUM(
							ROUND(
								CASE a.status
									WHEN 'entered'	THEN ( a.amortization_months_left / a.amortization_months ) * a.price
									WHEN 'attached'	THEN 	
										IF
										(
											floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
											( ( a.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) ) / a.amortization_months ) * a.price,
											0
										)
									ELSE( a.amortization_months_left / a.amortization_months ) * a.price 
								END,
								2
							)
						) AS price_left,
						SUM(
							CASE a.status
								WHEN 'attached' THEN 
									IF( floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ) < a.amortization_months_left,
									a.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( a.attach_date ) ) / 2592000 ),
									0 )
								ELSE a.amortization_months_left
							END
						) AS amortization_months_left,
						IF(
							p.id,
							CONCAT(
								CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
								' (',
								DATE_FORMAT( asn.updated_time, '%d.%m.%Y %H:%i:%s' ),
								')'
							),
							''
						) AS updated_user
					FROM assets a
						LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
						LEFT JOIN assets_groups asg ON asg.id = a.id_group
						LEFT JOIN {$db_name_personnel}.personnel p ON asn.updated_user = p.id
						LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
						LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
						LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
					WHERE 1
						AND a.to_arc = 0
						AND asn.to_arc = 0
						AND asg.to_arc = 0
			";
			
			//Asset Conditions
			$sSubActivesCriteria = "";
			$sAddition = "";
			
			if( isset( $aParams['nIDCustomGroup'] ) && !empty( $aParams['nIDCustomGroup'] ) )
			{
				$sQuery .= "
						AND asn.id_group = {$aParams['nIDCustomGroup']}
				";
				$sSubActivesCriteria .= " AND asn.id_group = {$aParams['nIDCustomGroup']}";
				$sAddition .= " AND asn.id_group = {$aParams['nIDCustomGroup']}";
			}
			
			if( isset( $aParams['nIDGroup'] ) && !empty( $aParams['nIDGroup'] ) )
			{
				if( isset( $aParams['nIDNomenclature'] ) && !empty( $aParams['nIDNomenclature'] ) )
				{
					$sQuery .= "
							AND asn.id = {$aParams['nIDNomenclature']}
					";
					$sSubActivesCriteria .= " AND asn.id = {$aParams['nIDNomenclature']}";
					$sAddition .= " AND asn.id = {$aParams['nIDNomenclature']}";
				}
				else
				{
					$sQuery .= "
							AND asg.id = {$aParams['nIDGroup']}
					";
					$sSubActivesCriteria .= " AND asg.id = {$aParams['nIDGroup']}";
					$sAddition .= " AND asg.id = {$aParams['nIDGroup']}";
				}
			}
			
			if( !empty( $aParams ) )
			{
				switch( $aParams['sStatus'] )
				{
					case 'attached':
						//$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'attached' AND a.storage_type = 'person'";
						$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'attached'";
						$sQuery .= "
							AND
							(
								a.id_storage != 0
								AND a.status = 'attached'
								#AND a.storage_type = 'person'
						";
						if( !empty( $aParams['nIDFirm'] ) )
						{
							$sSubActivesCriteria .= " AND f.id = {$aParams['nIDFirm']}";
							$sQuery .= "
								AND f.id = {$aParams['nIDFirm']}
							";
						}
						if( !empty( $aParams['nIDOffice'] ) )
						{
							$sSubActivesCriteria .= " AND o.id = {$aParams['nIDOffice']}";
							$sQuery .= "
								AND o.id = {$aParams['nIDOffice']}
							";
						}
						if( !empty( $aParams['nIDPerson'] ) )
						{
							$sSubActivesCriteria .= " AND a.id_storage = {$aParams['nIDPerson']}";
							$sQuery .= "
								AND a.id_storage = {$aParams['nIDPerson']}
							";
						}
						$sQuery .= "
							)
							OR
							(
								a.id_storage != 0
								{$sAddition}
								AND a.storage_type = 'asset'
								AND a.id_storage IN
								(
									SELECT
										a.id
									FROM assets a
										LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
										LEFT JOIN assets_groups asg ON asg.id = a.id_group
										LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
										LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
										LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
									WHERE 1
										AND a.to_arc = 0
										AND asn.to_arc = 0
										AND asg.to_arc = 0
										{$sSubActivesCriteria}
								)
							)
						";
						break;
					case 'entered':
						//$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'entered' AND a.storage_type = 'storagehouse'";
						$sSubActivesCriteria .= " AND a.id_storage != 0 AND a.status = 'entered'";
						$sQuery .= "
							AND
							(
								a.id_storage != 0
								AND a.status = 'entered'
								AND a.storage_type = 'storagehouse'
						";
						if( !empty( $aParams['nIDStoragehouse'] ) )
						{
							$sSubActivesCriteria .= " AND a.id_storage = {$aParams['nIDStoragehouse']}";
							$sQuery .= "
								AND a.id_storage = {$aParams['nIDStoragehouse']}
							";
						}
						$sQuery .= "
							)
							OR
							(
								a.id_storage != 0
								{$sAddition}
								AND a.storage_type = 'asset'
								AND a.id_storage IN
								(
									SELECT
										a.id
									FROM assets a
										LEFT JOIN assets_nomenclatures asn ON asn.id = a.id_nomenclature
										LEFT JOIN assets_groups asg ON asg.id = a.id_group
										LEFT JOIN {$db_name_personnel}.personnel pe ON ( a.id_storage = pe.id AND a.storage_type = 'person' )
										LEFT JOIN {$db_name_sod}.offices o ON ( o.id = pe.id_office AND a.storage_type = 'person' )
										LEFT JOIN {$db_name_sod}.firms f ON ( f.id = o.id_firm AND a.storage_type = 'person' )
									WHERE 1
										AND a.to_arc = 0
										AND asn.to_arc = 0
										AND asg.to_arc = 0
										{$sSubActivesCriteria}
								)
							)
						";
						break;
					case 'wasted':
						$sQuery .= "
							AND a.status = 'wasted'
						";
						break;
					
					default:
						break;
				}
			}
			
			$sQuery .= " GROUP BY asn.name ";
			
			if( $aParams['sStatus'] == "out" )
			{
				$sQuery .= "
						HAVING amortization_months_left = 0
				";
			}
			
			$aResult = $this->select( $sQuery );
			
			$aFinalData = array();
			$nRecordCount = 0;
			
			foreach( $aResult as $aResRecord )
			{
				$key = $aResRecord['id'];
				
				//Set Data
				$aFinalData[$key]['id'] = 					$aResRecord['id'];
				$aFinalData[$key]['name'] = 				$aResRecord['name'];
				$aFinalData[$key]['actives_count'] = 		$aResRecord['actives_count'];
				$aFinalData[$key]['price'] = 				$aResRecord['price'] . " лв.";
				$aFinalData[$key]['price_avg'] = 			$aResRecord['price_avg'] . " лв.";
				$aFinalData[$key]['price_left'] = 			$aResRecord['price_left'] . " лв.";
				$aFinalData[$key]['updated_user'] = 		$aResRecord['updated_user'];
				//End Set Data
				
				$oResponse->setDataAttributes( $key, 'price', 			array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'price_avg', 		array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'price_left', 		array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'actives_count', 	array( "style" => "text-align: right;" ) );
				
				$nRecordCount++;
			}
			
			$oResponse->setField( "name", 				"Номенклатура",	 		"" );
			$oResponse->setField( "actives_count", 		"Брой активи", 			"" );
			$oResponse->setField( "price", 				"Обща стойност", 		"" );
			$oResponse->setField( "price_avg", 			"Средна стойност", 		"" );
			$oResponse->setField( "price_left", 		"Ост. стойност", 		"" );
			$oResponse->setField( "updated_user", 		"...", 					"", "images/dots.gif" );
			
			if( $right_edit )
			{
				$oResponse->setFieldLink( "name", "gotoAssets" );
			}
			
			//Sortings
			$oParams = Params::getInstance();
			
			$sSortField = $oParams->get( "sfield", "name" );
			$nSortType	= $oParams->get( "stype", DBAPI_SORT_ASC );
			
			if( empty( $sSortField ) )$sSortField = "name";
			
			foreach( $aFinalData as $key => $row )
			{
				$id[$key]  = 			$row['id'];
				$name[$key] = 			$row['name'];
				$actives_count[$key] = 	$row['actives_count'];
				$updated_user[$key] = 	$row['updated_user'];
				$price[$key] = 			$row['price'];
				$price_avg[$key] = 		$row['price_avg'];
				$price_left[$key] = 	$row['price_left'];
			}
			
			if( $nSortType == DBAPI_SORT_ASC )$nSortOrderArray = SORT_ASC;
			if( $nSortType == DBAPI_SORT_DESC )$nSortOrderArray = SORT_DESC;
			
			if( $sSortField == "actives_count" || 
				$sSortField == "hierarchy" ||
				$sSortField == "price" ||
				$sSortField == "price_avg" ||
				$sSortField == "price_left" )$nSortTypeArray = SORT_NUMERIC;
			else $nSortTypeArray = SORT_STRING;
			
			array_multisort( $$sSortField, $nSortOrderArray, $nSortTypeArray, $aFinalData );
			
			$oResponse->setSort( $sSortField, $nSortType );
			//End Sortings
			
			//Paging
			$nPage = $oParams->get( "current_page", 1 );
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ( $nPage - 1 ) * $nRowCount;
			$nRowTotal = $nRecordCount;
			
			$nIndex = 0;
			$aPagedData = array();
			foreach( $aFinalData as $FDKey => $FDValue )
			{
				if( $nIndex >= $nRowOffset && $nIndex < ( $nRowOffset + $nRowCount ) )
				{
					$aPagedData[$FDKey] = $FDValue;
				}
				
				$nIndex++;
			}
			
			$oResponse->setPaging( $nRowCount, $nRowTotal, ceil( $nRowOffset / $nRowCount ) + 1 );
			//End Paging
			
			if ( isset ( $aParams['is_paging_ignored'] ) && $aParams['is_paging_ignored'] == 1 )
			{
				$oResponse->setData( $aFinalData );
			}
			else
			{
				$oResponse->setData( $aPagedData );
			}
		}
		
		public function getActivesByID( DBResponse $oResponse, $nID, $sMyType )
		{
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'person_actives_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$personnel_view = true;
					$personnel_edit = true;
				}
				
				if( in_array( 'person_actives_view', $_SESSION['userdata']['access_right_levels'] ) )
					$personnel_view = true;
			}
			
			$nWebServerTime = time();
			
			$oParams = Params::getInstance();
			if( $sMyType == "full" )
			{
				$sQuery = "
						SELECT
							ass.id,
							LPAD( ass.id, 6, 0 ) AS num,
							ass.name,
							CONCAT_WS( ' ', ass.price, 'лв.' ) AS price,
							CONCAT( ass.amortization_months, ' мес.' ) AS amortization_months,
							CASE ass.status
								WHEN 'entered'	THEN ( ass.amortization_months_left / ass.amortization_months ) * price
					  			WHEN 'attached'	THEN
									IF
									(
										floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
										( ( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) ) / ass.amortization_months ) * ass.price,
										0
									)
								ELSE( ass.amortization_months_left / ass.amortization_months ) * ass.price
							END AS price_left,
							( CASE ass.status
								WHEN 'entered'	THEN ( ass.amortization_months_left / ass.amortization_months ) * price
					  			WHEN 'attached'	THEN
									IF
									(
										floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
										( ( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) ) / ass.amortization_months ) * ass.price,
										0
									)
								ELSE( ass.amortization_months_left / ass.amortization_months ) * ass.price
							END ) * ( aset.asset_own_coef / 100 ) AS sum_own_coef,
							CASE ass.status
								WHEN 'attached'	THEN
									IF
									(
										floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
										( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) / ass.amortization_months ),
										0
									)
								ELSE ass.amortization_months_left
							END AS term_left
							FROM assets ass
								LEFT JOIN assets_storagehouses ast
									ON ast.id = ass.id_storage AND ( ( ass.status = 'entered' ) && ( ass.storage_type = 'storagehouse' ) && ( ast.id_mol = {$nID} ) )
								LEFT JOIN assets_settings aset ON aset.to_arc = 0
							WHERE ( ( ass.status = 'attached' ) && ( ass.storage_type = 'person' ) && ( ass.id_storage = {$nID} ) ) OR ( ( ass.status = 'entered' ) && ( ass.storage_type = 'storagehouse' ) && ( ast.id_mol = {$nID} ) )
				";
				
				$sTotalQuery = "
							SELECT
								COUNT( * ) AS count_num,
								SUM(
									CASE ass.status
										WHEN 'entered'	THEN ( ass.amortization_months_left / ass.amortization_months ) * price
									WHEN 'attached'	THEN
											IF
											(
												floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
												( ( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) ) / ass.amortization_months ) * ass.price,
												0
											)
									ELSE( ass.amortization_months_left / ass.amortization_months ) * ass.price
									END
								) AS price_left,
								SUM(
									( CASE ass.status
										WHEN 'entered'	THEN ( ass.amortization_months_left / ass.amortization_months ) * price
							  			WHEN 'attached'	THEN
											IF
											(
												floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
												( ( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) ) / ass.amortization_months ) * ass.price,
												0
											)
										ELSE( ass.amortization_months_left / ass.amortization_months ) * ass.price
									END ) * ( aset.asset_own_coef / 100 )
								) AS sum_own_coef
							FROM assets ass
								LEFT JOIN assets_storagehouses ast
									ON ast.id = ass.id_storage  AND( ( ass.status = 'entered' ) && ( ass.storage_type = 'storagehouse' ) && ( ast.id_mol = {$nID} ) )
								LEFT JOIN assets_settings aset ON aset.to_arc = 0
							WHERE( ( ass.status = 'attached' ) && ( ass.storage_type = 'person' ) && ( ass.id_storage = {$nID} ) ) OR ( ( ass.status = 'entered' ) && ( ass.storage_type = 'storagehouse' ) && ( ast.id_mol = {$nID} ) )
							GROUP BY
								CASE ass.status
									WHEN 'entered' 	THEN ast.id_mol
									WHEN 'attached' THEN ass.id_storage
								END
							LIMIT 1
				";
				
				if( $oParams->get( "sfield" ) == "actives_count" )$oParams->set( "sfield", "num" );
				APILog::Log(0, $sQuery);
				$this->getResult( $sQuery, "num", DBAPI_SORT_ASC, $oResponse );
				$aTotal = $this->selectOnce( $sTotalQuery );
				
				$oResponse->addTotal( 'name', 			$aTotal['count_num'] 	. " бр." );
				$oResponse->addTotal( 'price_left', 	$aTotal['price_left'] 	. " лв." );
				$oResponse->addTotal( 'sum_own_coef', 	$aTotal['sum_own_coef'] . " лв." );
				
				foreach( $oResponse->oResult->aData as $key => &$val )
				{
					$val['term_left'] = round( $val['term_left'] );
					$val['term_left'] .= ' мес.';
				}
				
				$oResponse->setField( "num",					"Инвентарен номер",		"Сортирай по инвентарен номер",		null, null, null, array() );
				$oResponse->setField( "name",					"Име",					"Сортирай по име",					null, null, null, array() );
				$oResponse->setField( "price",					"Цена",					"Сортирай по цена",					null, null, null, array() );
				$oResponse->setField( "price_left",				"Остатъчна стойност",	"Сотрирай по остатъчна стойност",	null, null, null, array( 'DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
				$oResponse->setField( "sum_own_coef",			"Сума самоучастие",		"Сотрирай по сума самоучастие",		null, null, null, array( 'DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
				$oResponse->setField( "amortization_months",	"Срок на амортизация",	"Сотрирай по срок на амортизация",	null, null, null, array() );
				$oResponse->setField( "term_left",				"Остатъчен срок ",		"Сотрирай по остатъчен срок",		null, null, null, array() );
				
				if( $personnel_edit )
				{
					$oResponse->setFieldLink( "num", "asset_info" );
				}
			}
			if( $sMyType == "group" )
			{
				$sQuery = "
						SELECT
							asg.id,
							asg.name,
							COUNT( ass.name ) AS actives_count,
							CONCAT_WS( ' ', SUM( ass.price ), 'лв.' ) AS price,
							CONCAT( SUM( ass.amortization_months ), ' мес.' ) AS amortization_months,
							SUM(
								CASE ass.status	
								WHEN 'entered'	THEN ( ass.amortization_months_left / ass.amortization_months ) * price
						  	WHEN 'attached'	THEN
									IF
									(
										floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
										( ( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) ) / ass.amortization_months ) * ass.price,
										0
									)
								ELSE( ass.amortization_months_left / ass.amortization_months ) * ass.price
								END
							) AS price_left,
							SUM(
								CASE ass.status
								WHEN 'attached'	THEN
									IF
									(
										floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) / 2592000 ) < ass.amortization_months_left,
										( ass.amortization_months_left - floor( ( {$nWebServerTime} - unix_timestamp( ass.attach_date ) ) /2592000 ) / ass.amortization_months ),
										0
									)
								ELSE ass.amortization_months_left
								END
							) AS term_left
						FROM assets ass
							LEFT JOIN assets_storagehouses ast
								ON ast.id = ass.id_storage AND( ( ass.status = 'entered' ) && ( ass.storage_type = 'storagehouse' ) && ( ast.id_mol = {$nID} ) )
							LEFT JOIN assets_groups asg ON asg.id = ass.id_group
						WHERE ( ( ass.status = 'attached' ) && ( ass.storage_type = 'person' ) && ( ass.id_storage = {$nID} ) ) OR ( ( ass.status = 'entered' ) && ( ass.storage_type = 'storagehouse' ) && ( ast.id_mol = {$nID} ) )
							AND asg.id != 0
							AND asg.to_arc = 0
						GROUP BY asg.name
				";
				
				if( $oParams->get( "sfield" ) == "num" ||
					$oParams->get( "sfield" ) == "sum_own_coef" )
				{
					$oParams->set( "sfield", "name" );
				}
				
				$this->getResult( $sQuery, "name", DBAPI_SORT_ASC, $oResponse );
				
				foreach( $oResponse->oResult->aData as $key => &$val )
				{
					$val['term_left'] = round( $val['term_left'] );
					$val['term_left'] .= ' мес.';
				}
				
				$oResponse->setField( "name", 					"Име", 					"Сортирай по име", 					null, null, null, array() );
				$oResponse->setField( "actives_count",			"Бр. активи",			"Сортирай по бр. активи",			null, null, null, array() );
				$oResponse->setField( "price",					"Цена",					"Сортирай по цена",					null, null, null, array() );
				$oResponse->setField( "price_left",				"Остатъчна стойност",	"Сотрирай по остатъчна стойност",	null, null, null, array( 'DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
				$oResponse->setField( "amortization_months",	"Срок на амортизация",	"Сотрирай по срок на амортизация",	null, null, null, array() );
				$oResponse->setField( "term_left",				"Остатъчен срок ",		"Сотрирай по остатъчен срок",		null, null, null, array() );
				
				if( $personnel_edit )
				{
					$oResponse->setFieldLink( "name", "modifyGroup" );
				}
			}
		}
		
		public function getAverageStats( $aParams, DBResponse $oResponse )
		{
			$sQuery = "
					SELECT SQL_CALC_FOUND_ROWS
						n.name,
						ROUND( AVG( a.price ), 2 ) AS avg_price,
						ROUND( MIN( a.price ), 2 ) AS min_price,
						ROUND( MAX( a.price ), 2 ) AS max_price,
						ROUND( AVG( a.amortization_months ), 0 ) AS avg_months,
						ROUND( MIN( a.amortization_months ), 0 ) AS min_months,
						ROUND( MAX( a.amortization_months ), 0 ) AS max_months
					FROM assets a
						LEFT JOIN assets_nomenclatures n ON n.id = a.id_nomenclature
					WHERE 1
						AND a.to_arc = 0
						AND n.to_arc = 0
			";
			
			if( isset( $aParams['nGroup'] ) && !empty( $aParams['nGroup'] ) )
			{
				$sQuery .= "
						AND a.id_group = {$aParams['nGroup']}
				";
			}
			
			$sQuery .= "
					GROUP BY name
			";
			
			$this->getResult( $sQuery, "name", DBAPI_SORT_ASC, $oResponse );
			
			foreach( $oResponse->oResult->aData as $key => &$value )
			{
				$oResponse->setDataAttributes( $key, 'avg_price', 	array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'min_price', 	array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'max_price', 	array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'avg_months', 	array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'min_months', 	array( "style" => "text-align: right;" ) );
				$oResponse->setDataAttributes( $key, 'max_months', 	array( "style" => "text-align: right;" ) );
				
				$value['avg_price'] .= " лв.";
				$value['min_price'] .= " лв.";
				$value['max_price'] .= " лв.";
				$value['avg_months'] .= " мес.";
				$value['min_months'] .= " мес.";
				$value['max_months'] .= " мес.";
			}
			
			$oResponse->setField( "name", 			"Име на номенклатура",				"Сортирай по номенклатура" );
			$oResponse->setField( "avg_price", 		"Ср. цена на придобиване", 			"Сортирай по средна цена" );
			$oResponse->setField( "min_price", 		"Мин. стойност на придобиване", 	"Сортирай по минимална стойност" );
			$oResponse->setField( "max_price", 		"Макс. стойност на придобиване", 	"Сортирай по максимална стойност" );
			$oResponse->setField( "avg_months", 	"Ср. срок на амортизиране", 		"Сортирай по среден срок на амортизиране" );
			$oResponse->setField( "min_months", 	"Мин. срок на амортизиране", 		"Сортирай по минимален срок на амортизиране" );
			$oResponse->setField( "max_months", 	"Макс. срок на амортизиране", 		"Сортирай по максимален срок на амортизиране" );
		}
		
		public function getMasterInfo($nID)
		{
			$sQuery = "SELECT
						id,
						name,
						id_storage,
						storage_type,
						name
						FROM assets
						WHERE
						id ={$nID} ";
			return  $this->select($sQuery);
			
		}
		public function getMasterAsset($nID, &$aData)
		{
			global $db_name_personnel, $db_name_storage;
			$aResult = $this->getMasterInfo($nID);
			while ($aResult[0]['storage_type']=='asset') {
				$aResult = $this->getMasterInfo($aResult[0]['id_storage']);
			}
			
			//$aData = $aResult;
			switch($aResult[0]['storage_type'])
			{
				case 'person' : 
				$sQuery = "
					SELECT
					CONCAT_WS(' ',fname, mname, lname)
					FROM {$db_name_personnel}.personnel
					WHERE id = {$aResult[0]['id_storage']}
				";
			
				$aData = $this->oDB->GetOne($sQuery);
				break;
				case 'storagehouse':
					$sQuery="
						SELECT
						CONCAT_WS(' ',p.fname, p.mname, p.lname)
						FROM {$db_name_personnel}.personnel p
						LEFT JOIN {$db_name_storage}.storagehouses s
						ON s.mol_id_person = p.id
						WHERE s.id = {$aResult[0]['id_storage']}
						
						";
					APILog::Log(0,$sQuery);	
					$aData = $this->oDB->GetOne($sQuery);
					//if($aData == false)throw new Exception($this->oDB->ErrorMsg());
					break;
			}
			
		}
		
		public function getCountAssetsByIDStoragehouse ($nID) {
			$sQuery = "
				SELECT
					count(*) as count
				FROM assets
				WHERE 1
					AND to_arc = 0
					AND id_storage	 = {$nID}
					AND storage_type = 'storagehouse'
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getCountAssetsByIDNomenclatures ($nID) {
			$sQuery = "
				SELECT
					count(*) as count
				FROM assets a
				WHERE 1
					AND a.to_arc = 0
					AND a.id_nomenclature = {$nID}
			";
			
			return $this->selectOne($sQuery);
		}
		
		public function getSingleAssets() {
			$aAssets = array();
			
			$sQuery = "
				SELECT 
					id, 
					storage_type,
					status,
					id_storage
				FROM assets
				WHERE to_arc = 0
					AND NOT ( status = 'attached' AND storage_type = 'asset' )
			";
			
			$aAssets = $this->selectAssoc( $sQuery );
						
			return $aAssets;
		}

		public function getOfficeByPerson( $nID ) {
			global $db_personnel;
			
			$aOffice = 0;
			$nID = !empty($nID) && is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					id_office
				FROM personnel
				WHERE id = '{$nID}'
			";
			
			$aOffice = $db_personnel->getArray( $sQuery );
						
			return $aOffice;
		}

		public function getOfficeByStoragehouse( $nID ) {
			global $db_storage;
			
			$aOffice = 0;
			$nID = !empty($nID) && is_numeric($nID) ? $nID : 0;
			
			$sQuery = "
				SELECT 
					p.id_office
				FROM assets_storagehouses s
				LEFT JOIN personnel.personnel p ON p.id = s.id_mol
				WHERE s.id = '{$nID}'
			";
			
			$aOffice = $db_storage->getArray( $sQuery );
						
			return $aOffice;
		}

		public function checkAssets( $nData ) {
			global $db_storage;
			
			$nIDOffice = isset($nData['id_office']) ? $nData['id_office'] : 0;
			$date_in = isset($nData['date_in']) ? date("Y-m", $nData['date_in']) : date("Y-m");
			$check = array();
			
			$sQuery = "
				SELECT 
					id
				FROM assets_totals
				WHERE id_office = '{$nIDOffice}'
					AND DATE_FORMAT(date_in, '%Y-%m')  = '{$date_in}'
			";

			$check = $db_storage->getArray( $sQuery );
					
			if ( isset($check[0]['id']) ) {
				return $check[0]['id'];
			} else {						
				return false;
			}
		}
		
		public function getAssetTotals( $nData ) {
			$aAssets = array();
			$firm = isset($nData['nIDFirm']) ? $nData['nIDFirm'] : 0;
			$period = isset($nData['period']) ? $nData['period'] : 0;	
			$months = array();
			$dates = "";
			$where = "";
			$nIDOffices = "";
			
			if ( $period > 0 ) {
				for ( $i = 0; $i < $period; $i++ ) {
					$data = mktime( 0, 0, 0, date("m") - $i, date("d"), date("Y") );
					$months[date("m-Y", $data)] = date("m-Y", $data);
				}	
				
				$dates = implode("','", $months);
				//debug("'".$dates."'");
				$where .= " AND date_format(a.date_in, '%m-%Y') IN ('{$dates}') ";
			}		
			
			if ( $firm > 0 ) {
				$offices = array();
				$oOffices = new DBOffices();
				$offices = $oOffices->getOfficesByFirm( $firm );
				
				foreach ( $offices as $val ) {
					$nIDOffices .= !empty($nIDOffices) ? ",".$val['id'] : $val['id'];	
				}
				
				if ( !empty($nIDOffices) ) {
					$where .= " AND a.id_office IN ({$nIDOffices}) ";
				}
			}
			
			
			$sQuery = "
				SELECT 
					a.id, 
					a.id_office, 
					o.id_firm,
					f.name as firm,
					date_format(a.date_in, '%m-%Y') as date_in, 
					sum(a.sum_wasted) as sum_wasted, 
					sum(a.sum_entered) as sum_entered,
					sum(a.sum_attached) as sum_attached
				FROM assets_totals a
				LEFT JOIN sod.offices o ON o.id = a.id_office
				LEFT JOIN sod.firms f ON f.id = o.id_firm
				WHERE 1
					{$where}
				GROUP BY date_format(a.date_in, '%m-%Y'), o.id_firm	
				ORDER BY a.date_in, o.id_firm		
			";
			//debug($sQuery);
			$aAssets = $this->selectAssoc( $sQuery );
						
			return $aAssets;
		}
		
		public function getMOLForAsset ( $nID )
		{
			$aData = array ();
			$this->getMasterAsset ( $nID, $aData );
			return $aData;
		}
		
	}
?>