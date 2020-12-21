<?php

	class DBAssetsPPPElements extends DBBase2
	{
		public function __construct()
		{
			global $db_storage;
			//$db_storage->debug=true;
			parent::__construct( $db_storage, 'assets_ppp_elements' );
		}

		public function getReport(  DBResponse $oResponse,$nID) {
			
			global $db_name_sod,$db_name_personnel;
			
			$nWebServerTime = time();
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					CONCAT(ass.id,',',ape.id) AS id,
					ass.id AS id_,
					ass.id AS num,
					ass.name,
					CASE ass.status
						WHEN 'entered'	THEN	(ass.amortization_months_left/ass.amortization_months)*ass.price
						WHEN 'attached'	THEN 	
							if( floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							  ((ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000))/ass.amortization_months)*ass.price ,
							0)
						ELSE (ass.amortization_months_left/ass.amortization_months)*ass.price 
					END AS price_left,
					ape.source_type,
					ape.id_source,
					CASE 
						WHEN ape.source_type = 'client' THEN 'Доставчик'
						WHEN ape.source_type = 'asset' THEN ass_source.name
						WHEN ape.source_type = 'person' THEN CONCAT_WS(' ', p_source.fname, p_source.mname, p_source.lname)
						WHEN ape.source_type = 'storagehouse' THEN ass_st_source.name
						ELSE ''
					END AS source,
					CASE 
						WHEN ape.source_type = 'person' THEN CONCAT_WS(' ', p_source.fname, p_source.mname, p_source.lname)
						WHEN ape.source_type = 'storagehouse' THEN CONCAT_WS(' ', p_source_mol.fname, p_source_mol.mname, p_source_mol.lname)
						ELSE ''
					END AS source_person,
					ape.dest_type,
					ape.id_dest,
					CASE 
						WHEN ape.dest_type = 'asset' THEN ass_dest.name
						WHEN ape.dest_type = 'person' THEN CONCAT_WS(' ', p_dest.fname, p_dest.mname, p_dest.lname)
						WHEN ape.dest_type = 'storagehouse' THEN ass_st_dest.name
						ELSE ''
					END AS dest,
					CASE 
						WHEN ape.dest_type = 'person' THEN CONCAT_WS(' ', p_dest.fname, p_dest.mname, p_dest.lname)
						WHEN ape.dest_type = 'storagehouse' THEN CONCAT_WS(' ', p_dest_mol.fname, p_dest_mol.mname, p_dest_mol.lname)
						ELSE ''
					END AS dest_person,
					CASE ass.status
						WHEN 'attached' THEN 
							if(floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000) < ass.amortization_months_left,
							ass.amortization_months_left - floor(({$nWebServerTime} - unix_timestamp(ass.attach_date))/2592000),
							0)
						ELSE ass.amortization_months_left
					END AS amortization_months_left,
					ape.waste_note,
					DATE_FORMAT(ape.add_time,'%d.%m.%Y %H:%i:%s') AS add_time_
				FROM assets_ppp_elements ape
				LEFT JOIN assets ass ON ass.id = ape.id_asset
				#Предаваща страна
				LEFT JOIN assets ass_source ON ass_source.id = ape.id_source AND ape.source_type = 'asset'
				LEFT JOIN assets_storagehouses ass_st_source ON ass_st_source.id = ape.id_source AND ape.source_type = 'storagehouse'
				LEFT JOIN {$db_name_personnel}.personnel p_source ON p_source.id = ape.id_source AND ape.source_type = 'person' 
				LEFT JOIN {$db_name_personnel}.personnel p_source_mol ON p_source_mol.id = ass_st_source.id_mol
				#Приемаща страна
				LEFT JOIN assets ass_dest ON ass_dest.id = ape.id_dest AND ape.dest_type = 'asset'
				LEFT JOIN {$db_name_personnel}.personnel p_dest ON p_dest.id = ape.id_dest AND ape.dest_type = 'person'
				LEFT JOIN assets_storagehouses ass_st_dest ON ass_st_dest.id = ape.id_dest  AND ape.dest_type = 'storagehouse'
				LEFT JOIN {$db_name_personnel}.personnel p_dest_mol ON p_dest_mol.id = ass_st_dest.id_mol
				
				WHERE 1 
					AND ape.to_arc = 0
					AND ape.id_ppp = {$nID}
			";
			
			$this->getResult( $sQuery, 'source, dest', DBAPI_SORT_ASC, $oResponse );

			$oDBAssets = new DBAssets();
			$oDBAssetsPPPs = new DBAssetsPPPs();
			
			$aPPP = $oDBAssetsPPPs->getRecord($nID);
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['num'] = zero_padding($val['num'],6);
				$val['amortization_months_left'] .= ' мес.';	
				
				$oResponse->setDataAttributes($key,'amortization_months_left',array('style' => 'text-align:right;'));
				
/*				global $aAssetsIDs;
				$aAssetsIDs = array();
				$oDBAssets->getSubAssetsIDsRecursive($val['id_']);	
				$sAssetsIDs = implode(',',$aAssetsIDs);
				$nPriceLeft = $oDBAssets->calcPriceLeft($sAssetsIDs);
				$val['price_left'] = $nPriceLeft; */
				
				if($val['source_type'] == 'asset')$val['source_person'] = $oDBAssets->getMOL($val['id_source']);
				if($val['dest_type'] == 'asset')$val['dest_person'] = $oDBAssets->getMOL($val['id_dest']);
			}	
			
			$oResponse->setField( "num", 			"Номер", 					"Сортирай по Номер" );
			$oResponse->setField( "name", 	"Име", 		"Сортирай по Име" );
			$oResponse->setField( "source", 	"Предаваща страна", 		"Сортирай по Предаваща страна");
			if($aPPP['ppp_type'] != 'waste') {
				$oResponse->setField( "dest", 	"Приемаща страна", 		"Сортирай по Приемаща страна");
			}
			if($aPPP['ppp_type'] == 'attach') {
				$oResponse->setField( "dest_person", 	"МОЛ", 		"Сортирай по МОЛ");
			}
			$oResponse->setField( "price_left", 	"Остатъчна стойност", 		"Сортирай по Остатъчна стойност",NULL,NULL,NULL,array('DATA_FORMAT' => DF_CURRENCY, 'DATA_TOTAL' => 1 ) );
			$oResponse->setField( "amortization_months_left", 	"Остатъчен срок", 		"Сортирай по Остатъчен срок" );			
			$oResponse->setField( "add_time_", 	"Време на добавяне", 		"Сортирай по Остатъчен срок" );
			
			if($aPPP['ppp_type'] == "waste") {
				$oResponse->setField("waste_note","бележка","Сортирай по бележка");
			}
			
			if(empty($aPPP['confirm_user'])) {
				$oResponse->setField( '', '', '', 'images/cancel.gif','delAsset','');
			}
			
			if($right_edit)
			{
			  $oResponse->setFieldLink("num", "openAssetInfo");
			}
		}

		
		
		public function getElements($nIDPPP) {
			
			$sQuery = "
				SELECT 
					ape.*,
					ass.name AS asset_name
				FROM assets_ppp_elements ape
				LEFT JOIN assets ass ON ass.id = ape.id_asset
				WHERE 1
					AND ape.to_arc = 0
					AND ape.id_ppp = {$nIDPPP}		
			";
			
			return $this->select($sQuery);
		}
		
		public function getSumByIDPPP ($nIDPPP) {
			$sQuery = "
				SELECT
					ap_e.id,
					ap_e.id_asset
				FROM assets_ppp_elements ap_e
				WHERE 1
					AND ap_e.to_arc = 0
					AND ap_e.id_ppp = {$nIDPPP}
			";
			
			$aAssets=$this->select($sQuery);			
			$oDBAssets = new DBAssets();
			$nSum=0;
			foreach ($aAssets as $key => &$val) {
					$nPrice=$oDBAssets->getPrice($val['id_asset']);
					$nSum+=$nPrice;
			}			
			return $nSum;
		}
		
		
		public function getPPPsByAsset($nId,DBResponse $oResponse)
		{
			global $db_name_personnel;
			
			$sQuery="SELECT
						SQL_CALC_FOUND_ROWS 
						ap_e.id_ppp AS id,
						ap_e.id_ppp AS num,
						ap_e.updated_time AS time,
					CASE ap.ppp_type 
					WHEN 'enter' THEN'придобивaнe'
					WHEN 'attach' THEN'въвеждане'
					WHEN 'waste' THEN 'бракуване'
					END 
						AS type,
					CASE ap_e.source_type
					WHEN 'client'  THEN 'Доставчик'
					WHEN 'person'  THEN CONCAT_WS(' ',per_s.fname,per_s.mname,per_s.lname)
					WHEN 'storagehouse' THEN st_s.name
					WHEN 'asset'   THEN ass_s.name
					END
						AS source,
					CASE ap_e.dest_type
					WHEN 'client' THEN ass_des.client_name
					WHEN 'person' THEN CONCAT_WS(' ',per_d.fname,per_d.mname,per_d.lname)
					WHEN 'storagehouse' THEN st_d.name
					WHEN 'asset' THEN ass_dest.name
					END
						AS destination,
						CONCAT_WS(' ',per_c.fname,per_c.mname,per_c.lname) AS c_user
					FROM assets_ppp_elements ap_e
					LEFT JOIN 
						assets_ppps ap
					ON 
						ap_e.id_ppp = ap.id
				#източник/предаваща страна
					LEFT JOIN 
						assets ass_s
					ON
						 ap_e.id_asset = ass_s.id
					LEFT JOIN 
						{$db_name_personnel}.personnel per_s
					ON 
						ap_e.id_source = per_s.id
					LEFT JOIN 
						assets_storagehouses st_s
					ON
						 ap_e.id_source = st_s.id
					LEFT JOIN
						 assets ass
					ON 
						ap_e.id_source=ass.id
				#приемаща страна
					#client
					LEFT JOIN 
						assets ass_des
					ON 
						ap_e.id_asset=ass_des.id
					#slujitel
					LEFT JOIN 
						{$db_name_personnel}.personnel per_d
					ON 
						ap_e.id_dest=per_d.id
					#sklad
					LEFT JOIN
						 assets_storagehouses st_d
					ON 
						ap_e.id_dest = st_d.id
					#aktiv
					LEFT JOIN 
						assets ass_dest
					ON 
						ap_e.id_dest=ass_dest.id
					LEFT JOIN 
						assets_ppps ap_c
					ON 
						ap_e.id_ppp = ap_c.id
				#confirm_user
					LEFT JOIN 
						{$db_name_personnel}.personnel per_c
					ON 
						ap_c.confirm_user = per_c.id
				WHERE 1
				AND ap_e.id_asset={$nId}
				AND ap_e.to_arc=0 ";
			
			$this->getResult($sQuery,'num', DBAPI_SORT_ASC,$oResponse);
			
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				$val['num'] = zero_padding($val['num'],6);
			}
				

		$right_edit = false;
	    $right_view	= false;
	   
	    if (!empty($_SESSION['userdata']['access_right_levels'] )) {
		   if ( in_array('asset_info_ppp_edit', $_SESSION['userdata']['access_right_levels']) ) {
		        $right_view = true;
			    $right_edit = true;
		       }
            if (in_array('asset_info_ppp_view', $_SESSION['userdata']['access_right_levels'])) 
			  $right_view = true;		
	      }
	      
	      
	      
		
		    $oResponse->setField('num', "Номер", "Сортирай по номер",NULL,NULL,NULL,array());
			$oResponse->setField('time', "Дата","Сортирай по дата",NULL,NULL,NULL,array());
			$oResponse->setField('type', "Тип на протокола","Сортирай по тип на протокола",NULL,NULL,NULL,array());
			$oResponse->setField('source',"Предаваща страна","Сортирай по предаваща страна",NULL,NULL,NULL,array());
			$oResponse->setField('destination', "Приемаща страна","Сортирай по приемаща страна",NULL,NULL,NULL,array());
			$oResponse->setField('c_user',"Служител по протокола","Сортирай по слижител",NULL,NULL,NULL,array());
			
			if($right_edit)	
		      $oResponse->setFieldLink('num','viewPPP');
		 
			
		}

	}

	
?>