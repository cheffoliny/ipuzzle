<?php
	DEFINE( 'GRID_LEN', 50 );
	
	$aColotGradient = array( '#00FF00', '#2AFF00', '#55FF00', '#7FFF00', '#AAFF00', '#D4FF00', '#FFFF00', '#FFD400', '#FFAA00', '#FF7F00', '#FF5500', '#FF2A00', '#FF0000' );
	// google maps
	$aGoogleKey['telepol.net']		= "ABQIAAAAvogtsTYeCCn9bb52RPbh-xRv7QWxkr-3IKJEXfTogZixrXvNrxTFUqzQfNBIIEf8T4kg00Wf1-SGFw";
	$aGoogleKey['telenet1.telepol.com']	= "ABQIAAAAvogtsTYeCCn9bb52RPbh-xQgXI407SWkrCxD7M_O9RWeWzWn6RSNTfQvxkRbeE7SAJbtK4I5-WfXOQ";
	$aGoogleKey['telenet2.telepol.com']	= "ABQIAAAAdouWIVeqAFAeslBKto6N4BShAnn75XYqscKnPf6_hzGF3TrSxhRTvQJKhEDsGJpITp3aF0FMhCQF5g";
	$aGoogleKey['test']			= "ABQIAAAAdouWIVeqAFAeslBKto6N4BRbZHnKkPiOgFOiUuTNWhFQ49yrExRhejihQkQuG_BNLFEVRHwgqxuYfw";
	

	$aParams = array_merge( $_GET, $_POST );
	
	// Региони (офиси)
	$oOffices = new DBBase( $db_sod, 'offices');
	$aOffices = array();
	
	$aWhere[] = "to_arc=0";
	$aWhere[] = "id_firm=2";

	$nResult = $oOffices->getResult( $aOffices, NULL, $aWhere );
	if( $nResult != DBAPI_ERR_SUCCESS )
	{ 	
		printf( "%s<br/>%s", $db_sod->ErrorMsg());
		return;
	}

	$bViewOjects 	= !empty($aParams['bObjects']) 	? $aParams['bObjects'] 	: FALSE;
	$bZoom 	= !empty($aParams['bZoom']) 	? $aParams['bZoom'] 		: FALSE;
	
	$template -> assign("nSelectedOffice"	, !empty($aParams['nOffice']) ? $aParams['nOffice'] : 0		);
	$template -> assign("aOffices"			, $aOffices 							);
	$template -> assign("bObjects"			, $bViewOjects 	? 'checked' : ''				);
	$template -> assign("bZoom"				, $bZoom 		? 'checked' : ''				);
	$template -> assign("nType"				, !empty($aParams['nType']) ? $aParams['nType'] : 0		);
	

	
	switch( $_SERVER["HTTP_HOST"] )
	{
		case '213.91.252.135' : 
			$template -> assign("GoogleKey"			, $aGoogleKey['telenet1.telepol.com']		);
			break;
		case '213.91.252.162' : 
			$template -> assign("GoogleKey"			, $aGoogleKey['telenet2.telepol.com']		);
			break;
		case '213.91.252.129' :
			$template -> assign("GoogleKey"			, $aGoogleKey['test']				);
			break;			
		default:
			$template -> assign("GoogleKey"			, $aGoogleKey['telepol.net']			);
			
	}
	
	if( empty( $aParams['nType'] ) )
		return;
		
	$sQuery = " 
		SELECT 
			o.id as _id , 
			o.id_oldobj , 
			o.num , 
			o.name , 
			o.address , 
			o.geo_lan , 
			o.geo_lat , 
			group_concat( concat( '[' ,o.num, '] ', o.name ) separator '<br/>' ) AS grp_num, 
			COUNT( o.id ) AS br_obj, 
			1 as value 
		FROM sod.objects o 
		LEFT JOIN statuses s ON s.id=o.id_status

		
	";
		
	$aWhere 	= array();
	$aWhere[] 	= "s.is_sod 	 =1";
	$aWhere[] 	= "o.geo_lan	!=0";
	$aWhere[] 	= "o.geo_lat	!=0";

	if( $aParams['nType'] == 'passable_time')
		$aWhere[] 	= "o.id_oldobj 	> 0";
	
	if( $bZoom )	
	{
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 20 )
		{
			// Шумен
			$aWhere[] 	= "o.geo_lat	> 43.25070436607026";
			$aWhere[] 	= "o.geo_lat	< 43.28520334369384";
			
			$aWhere[] 	= "o.geo_lan	> 26.89830780029297";
			$aWhere[] 	= "o.geo_lan	< 26.97040557861328";
	
//			$aWhere[] 	= "o.geo_lat	!= 43.270603700000002";
//			$aWhere[] 	= "o.geo_lan	!= 26.923377200000001";		
		}
		
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 62 )
		{
			// Д Град
			$aWhere[] 	= "o.geo_lat	> 42.03577933956859";
			$aWhere[] 	= "o.geo_lat	< 42.07987816698549";
			
			$aWhere[] 	= "o.geo_lan	> 25.508880615234375";
			$aWhere[] 	= "o.geo_lan	< 25.657882690429687";
	
//			$aWhere[] 	= "o.geo_lat	!= 42.060271499999999";
//			$aWhere[] 	= "o.geo_lan	!= 25.593334400000000";		
		}
	
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 76 )
		{
			// В Търново
			$aWhere[] 	= "o.geo_lat	> 43.05358653605547";
			$aWhere[] 	= "o.geo_lat	< 43.11125978280668";
			
			$aWhere[] 	= "o.geo_lan	> 25.572566986083984";
			$aWhere[] 	= "o.geo_lan	< 25.67676544189453";
	
//			$aWhere[] 	= "o.geo_lat	!= 43.078556200000001";
//			$aWhere[] 	= "o.geo_lan	!= 25.627157400000002";		
		}
	
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 61 )
		{
			// Габрово
			$aWhere[] 	= "o.geo_lat	> 42.85520388672971";
			$aWhere[] 	= "o.geo_lat	< 42.905771104764796";
			
			$aWhere[] 	= "o.geo_lan	> 25.293102264404297";
			$aWhere[] 	= "o.geo_lan	< 25.341510772705078";
	
//			$aWhere[] 	= "o.geo_lat	!= 42.874224200000000";
//			$aWhere[] 	= "o.geo_lan	!= 25.318937500000001";		
		}
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 79 )
		{
			// Варна
			$aWhere[] 	= "o.geo_lat	> 43.19291232085178";
			$aWhere[] 	= "o.geo_lat	< 43.25795577396601";
			
			$aWhere[] 	= "o.geo_lan	> 27.83283233642578";
			$aWhere[] 	= "o.geo_lan	< 28.020286560058594";
	
//			$aWhere[] 	= "o.geo_lat	!= 43.216645200000002";
//			$aWhere[] 	= "o.geo_lan	!= 27.911805800000000";		
		}
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 70 )
		{
			// Русе
			$aWhere[] 	= "o.geo_lat	> 43.782744761157325";
			$aWhere[] 	= "o.geo_lat	< 43.8855215890078";
			
			$aWhere[] 	= "o.geo_lan	> 25.891342163085938";
			$aWhere[] 	= "o.geo_lan	< 26.0540771484375";
	
//			$aWhere[] 	= "o.geo_lat	!= 43.849378199999997";
//			$aWhere[] 	= "o.geo_lan	!= 25.954253399999999";		
		}
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 60 )
		{
			// Ст. Загора
			$aWhere[] 	= "o.geo_lat	> 42.39138895532425";
			$aWhere[] 	= "o.geo_lat	< 42.45082138532402";
			
			$aWhere[] 	= "o.geo_lan	> 25.57565689086914";
			$aWhere[] 	= "o.geo_lan	< 25.676250457763672";
	
//			$aWhere[] 	= "o.geo_lat	!= 42.874224200000000";
//			$aWhere[] 	= "o.geo_lan	!= 25.318937500000001";		
		}
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 21 )
		{
			// Попово
			$aWhere[] 	= "o.geo_lat	> 43.33391856198401";
			$aWhere[] 	= "o.geo_lat	< 43.36225533960651";
			
			$aWhere[] 	= "o.geo_lan	> 26.199817657470703";
			$aWhere[] 	= "o.geo_lan	< 26.250457763671875";
	
//			$aWhere[] 	= "o.geo_lat	!= 42.874224200000000";
//			$aWhere[] 	= "o.geo_lan	!= 25.318937500000001";	
	
		}
		if( !empty($aParams['nOffice']) && $aParams['nOffice'] == 59 )
		{
			// Н Пазар
			$aWhere[] 	= "o.geo_lat	> 43.33229535044218";
			$aWhere[] 	= "o.geo_lat	< 43.36225533960651";
			
			$aWhere[] 	= "o.geo_lan	> 27.174768447875977";
			$aWhere[] 	= "o.geo_lan	< 27.212190628051758";
	
//			$aWhere[] 	= "o.geo_lat	!= 42.874224200000000";
//			$aWhere[] 	= "o.geo_lan	!= 25.318937500000001";		
		}
	}
	
	if( !empty($aParams['nOffice']) )
		$aWhere[] 	= "o.id_reaction_office = {$aParams['nOffice']}";
	
	 
	
	$oBase = new DBBase( $db_sod, 'objects');
	$aData = array();
	 
	$nResult = $oBase->getResult( $aData, $sQuery, $aWhere, NULL, NULL, ' o.geo_lan, o.geo_lat' );
	if( $nResult != DBAPI_ERR_SUCCESS )
	{ 	
		printf( "%s<br/>%s", $db_sod->ErrorMsg(), $sQuery );
		return;
	}
	
	switch( $aParams['nType'] )
	{
		case 'objects' :
				$sOperation = 'SUM';
			break;
			
		case 'passable_time' :
				$aObjectsIDs = array();
				foreach( $aData as $aObject )
					$aObjectsIDs[ $aObject['id_oldobj'] ] = $aObject['id_oldobj'];
					
				$sQuery = sprintf(" 
						SELECT 
							m.id_obj	as _id,
							m.id_obj	as id_obj,
							AVG(a.pass) as pass 
							
						FROM 
							messages m 
							LEFT JOIN %s a ON a.id_msg=m.id_msg
							
						WHERE
							a.pass != 0 AND
							a.MsgTime > '%s' AND 
							m.id_obj in (%s)
							
						
						GROUP BY m.id_obj
						
					",
					date("Y_m"),
					date("Y-m-d", mktime(0,0,0,date('m'),date('d')-2,date('y'))),
					implode(',', $aObjectsIDs)
				);
				
				$aArchive = array();
				$oArchive = new DBBase( $db_telepol, 'objects');
				$nResult = $oArchive->getResult( $aArchive, $sQuery );
				if( $nResult != DBAPI_ERR_SUCCESS )
				{ 	
					printf( "%s<br/>%s", $db_sod->ErrorMsg(), $sQuery );
					return;
				}
				
				foreach( $aData as &$aObject )
					if( !empty( $aArchive[ $aObject['id_oldobj'] ] ) )
						$aObject['value'] = $aArchive[ $aObject['id_oldobj'] ]['pass']; 
						
				$sOperation = 'AVG';
						
			break;
			
		default:
			return;
			
	}
	
	
	
	DrawMap( $aData, $sOperation, $bViewOjects );
	
	
	
	
	
	// ---------------------------------------------------------------------------------

	
	function DrawMap( $aData, $sOperation, $bViewOjects )
	{
		global $template, $aColotGradient;
		
		// init kmz file
		$kml= new kmz; 
		
		//init kmz styles
		foreach( $aColotGradient as $sColor )
		{
			$style = $kml->addStyle( $sColor, array( "PolyStyle" => array( "color" => $sColor ) ) );
		}
	 
		$pointStyle = $kml->addStyle( "#points", array( "IconStyle" => array( "href" => 'http://telenet1.telepol.com/telenet/images/marker1.png'  ) ) );
//		$pointStyle = $kml->addStyle( "#points", array( "IconStyle" => array( "href" => 'http://extremeuniverse.eu/marker2.png'  ) ) );
		
		$nLanMin 	= NULL;
		$nLanMax 	= NULL;
		$nLatMin 	= NULL;
		$nLatMax 	= NULL;
		
		$nCenterLan = 0.00;
		$nCenterLat = 0.00;
		
		// Min, Max
		foreach ( $aData as $aPoint )
		{
			if( $aPoint['geo_lan'] < $nLanMin || $nLanMin == NULL )
				$nLanMin = $aPoint['geo_lan'];
				
			if( $aPoint['geo_lat'] < $nLatMin || $nLatMin == NULL )
				$nLatMin = $aPoint['geo_lat'];
				
			if( $aPoint['geo_lan'] > $nLanMax || $nLanMax == NULL )
				$nLanMax = $aPoint['geo_lan'];
				
			if( $aPoint['geo_lat'] > $nLatMax || $nLatMax == NULL )
				$nLatMax = $aPoint['geo_lat'];
		}
		
		
		$nDeltaLan 	= $nLanMax - $nLanMin;
		$nDeltaLat 	= $nLatMax - $nLatMin;
		
		if( $nDeltaLan == 0 )
			$nDeltaLan = 0.01;

		if( $nDeltaLat == 0 )
			$nDeltaLat = 0.01;

		$nCenterLan	= $nLanMin + $nDeltaLan / 2;
		$nCenterLat = $nLatMin + $nDeltaLat / 2;
		
		$nGridDeltaLan =  $nDeltaLan / GRID_LEN;
		$nGridDeltaLat =  $nGridDeltaLan;
		
		
		
		
		// Правене на мрежа
		$nMaxValue = 0.00;
		
		
		foreach( $aData as $aPoint )
		{
			$nLan = (intval(($aPoint['geo_lan'] - $nLanMin) / $nGridDeltaLan) ) * $nGridDeltaLan + $nLanMin;
			$nLat = (intval(($aPoint['geo_lat'] - $nLatMin) / $nGridDeltaLat) ) * $nGridDeltaLat + $nLatMin;
			
			$sKey = "$nLan@$nLat";
			
			if( empty($aGrid[$sKey]) )
			{
				$aGrid[$sKey] = array();
				$aGrid[$sKey]['lan_min'] = $nLan;
				$aGrid[$sKey]['lan_max'] = $nLan+$nGridDeltaLan;
				$aGrid[$sKey]['lat_min'] = $nLat;
				$aGrid[$sKey]['lat_max'] = $nLat+$nGridDeltaLat;
				
				$aGrid[$sKey]['value'] = 0;
				$aGrid[$sKey]['count'] = 0;
			}
			
			$aGrid[$sKey]['value'] 		+= $aPoint['value'];
			$aGrid[$sKey]['bgcolor'] 	= $aColotGradient[0];
			$aGrid[$sKey]['count'] 	++;
			
			$aGrid[$sKey]['pints'][] = array( 'num' => $aPoint['num'], 'value' => $aPoint['value'], 'lan' => $aPoint['geo_lan'], 'lat' => $aPoint['geo_lat'] );
			
			if( $nMaxValue < $aGrid[$sKey]['value'] )
				$nMaxValue = $aGrid[$sKey]['value'];
			
		}
		
		if( $sOperation == 'AVG' )
		{
			$nMaxValue = 0.00;
			foreach( $aGrid as &$aCell )
			{
				$aCell['value'] = $aCell['value'] / $aCell['count'];
				
				if( $nMaxValue < $aCell['value'] )
					$nMaxValue = $aCell['value'];
			}
		}
		
		// Установяване на стоности - цветове на кутийките от мрежата
		foreach( $aGrid as &$aElement )
		{
			$nGradient = intval( ($aElement['value'] / $nMaxValue) * count($aColotGradient) ) - 1;
			$nGradient = $nGradient < 0 ? 0 : $nGradient;
			
			$aElement['bgcolor'] = $aColotGradient[$nGradient];
		}
		
		$aLegend = array();
		
		foreach( $aColotGradient as $nKey => $nColor )
			$aLegend[] = array( 'color'=>$nColor, 'to'=>number_format( ( $nMaxValue / count($aColotGradient) ) * ($nKey+1), 2, '.', '') );
		
		//debug( "nLanMin=>$nLanMin; nLanMax=>$nLanMax; nLatMin=>$nLatMin; nLanMax=>$nLatMax"  );
		//debug( "nGridDeltaLan=>$nGridDeltaLan; nGridDeltaLat=>$nGridDeltaLat"  );
		//debug( $nMaxValue );
		//reset( $aGrid ); 
		//debug( $nMaxValue );
		//debug( $aGrid );
		//debug( $sOperation );
		//debug( $aData );
		//debug( $aLegend );
	
		$template -> assign("aGrid"			, $aGrid 			);
		$template -> assign("aData"			, $aData 			);
		
		$template -> assign("nLanMin"		, $nLanMin 			);
		$template -> assign("nLanMax"		, $nLanMax 			);
		$template -> assign("nLatMin"		, $nLatMin 			);
		$template -> assign("nLatMax"		, $nLatMax 			);
		
		$template -> assign("nCenterLat"		, $nCenterLat 			);
		$template -> assign("nCenterLan"		, $nCenterLan 			);
		$template -> assign("nZoom"			, 13 				);
		$template -> assign("bViewMap"		, 1				);
		$template -> assign("bViewObjects"		, $bViewOjects			);
		$template -> assign("aLegend"		, $aLegend			);
			
	  
		if( $bViewOjects )
		{
			
		//*добавяне на обектите към kml файла
			$oPointsFolder = $kml->addFolder( 'Обекти' );
			
			foreach( $aData as $aPoint ) {
				
				if( $aPoint['br_obj'] > 1 )
				{
					$sDesc = $aPoint['br_obj'];
					$sDesc .= " обекта се намират на тези координати";
					
					$sDesc .= "<p>";
					$sDesc .= $aPoint['grp_num'];
					$sDesc .= "</p>";
					
				} 
				else 
				{
					$sDesc = $aPoint['name'];
					$sDesc .= "<br/><span>№" .$aPoint['num']. "</span>";
					$sDesc .= "<p>" . $aPoint['address'] . "</p>";
				
				}
				$oMarker = $kml->addMarker( $oPointsFolder, NULL, $sDesc, array( "lat" => $aPoint['geo_lan'], "lon" => $aPoint['geo_lat']) );
				$kml->setStyle( $oMarker, $pointStyle);
			}
		}
		
		
		//*генерира kml документ с мрежата
		$oFolder = $kml->addFolder( 'Мрежа' );
		
		foreach( $aGrid as $aPoint) {
		$polygon = $kml->addPolygon( $oFolder, NULL,
			array(
				array(	'lon'=>$aPoint['lan_max'],
					'lat'=>$aPoint['lat_min'],
					'alt'=>0),
					
				array(	'lon'=>$aPoint['lan_max'],
					'lat'=>$aPoint['lat_max'],
					'alt'=>0),
					
				array(	'lon'=>$aPoint['lan_min'],
					'lat'=>$aPoint['lat_max'],
					'alt'=>0),
					
				array(	'lon'=>$aPoint['lan_min'],
					'lat'=>$aPoint['lat_min'],
					'alt'=>0)
				)
			);
			
		$polyStyle = $kml->setStyle( $polygon, $aPoint['bgcolor'] );
		}
	
		//проверява за файлове *.kmz, които не са обновени в последните  30 мин и ги изтрива
		$clean = cleanOldFiles( './storage/', '.kmz', 30 );
		
		$sFileName = $_SESSION['login_img_num'] . rand(1,1000);
		
//		$sFileName = 'doc33';
		
		$kml->saveKmz( './storage/'.$sFileName.'.kmz' );
		
		$template->assign( 'sKmzFile', 'http://'.$_SERVER["HTTP_HOST"].'/telenet/storage/'.$sFileName.'.kmz' );	
//		$template->assign( 'sKmzFile', 'http://extremeuniverse.eu/'.$sFileName.'.kmz' );
		
		
	}

/*
	Проходимост за конкретен приемник:
	1. Време От, До
	2. Брой сигнали;

	Отколконие от средната проходимост
	
*/	

	
		function cleanOldFiles( $sDir, $sFileExt, $sPeriod )
		{
			$sPeriodSec = $sPeriod * 60;
			$sTime = time() - $sPeriodSec;
			
			$clearCache = clearstatcache();
			
			$dir_handle = @opendir( $sDir ) or die( "Unable to open" . $sDir );
			 
			while ( $sFileName = readdir( $dir_handle ) ) 
			{
				 if( $sFileName != "." && $sFileName != ".." && substr( $sFileName, -4 ) == $sFileExt && filemtime( $sDir.$sFileName ) < $sTime ) 
				 {
			 		  unlink( $sDir.$sFileName );
				 }
			}
		
			closedir( $dir_handle );
		}


?>
