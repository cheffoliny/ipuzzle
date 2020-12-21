<?php

	class DBStatistics2 extends DBBase2
	{
		public function __construct()
		{
			global $db_sod;
			
			parent::__construct( $db_sod, 'statistics' );
		}
		
		public function getReport( $aParams, DBResponse $oResponse )
		{
			//Params
			$nInterval = ( isset( $aParams['nInterval'] ) && !empty( $aParams['nInterval'] ) ) ? $aParams['nInterval'] : 6;
			$nInterval--;
			
			$nIDFirm = 0;
			$bIsByFirm = ( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) );
			if( $bIsByFirm )
			{
				$nIDFirm = $aParams['nIDFirm'];
			}
			//End Params
			
			//DB Objects
			$oDBObjectServices = new DBObjectServices();
			//End DB Objects
			
			//Select Firms and Regions
			if( $bIsByFirm )
			{
				$sQuery = "
					SELECT
						o.id AS id,
						CONCAT( f.name, ' - ', o.name ) AS name,
				";
			}
			else
			{
				$sQuery = "
					SELECT
						f.id AS id,
						f.name AS name,
				";
			}
			
			$sQuery .= "
						f.id AS id_firm
					FROM
						firms f
			";
			
			if( $bIsByFirm )
			{
				$sQuery .= "
					LEFT JOIN
						offices o ON o.id_firm = f.id
					WHERE
						f.id = {$nIDFirm}
				";
			}
			
			$sQuery .= "
					ORDER BY name
			";
			
			$aData = $this->select( $sQuery );
			//End Select Firms and Regions
			
			//Initialize
			$nFinalDataIndex = 0;
			$aFinalData = array();
			$bIsFirst = true;
			
			$nLastFirm = 0;
			$bIsHeaderLight = true;
			
			$sSDate = date( "Y-m-01", strtotime( "-2 months" ) );
			$sEDate = date( "Y-m-01", strtotime( "-{$nInterval} months" ) );
			//End Initialize
			
			//Set Fields
			$oResponse->setField( "stat_date", 					"Месец", 		"" );
			$oResponse->setField( "objects_count", 				"Обекти", 		"" );
			$oResponse->setField( "objects_count_chbars", 		"", 			"" );
			$oResponse->setField( "objects_count_change", 		"Промяна", 		"" );
			$oResponse->setField( "objects_price_sum", 			"Сума", 		"" );
			$oResponse->setField( "objects_price_sum_change", 	"Промяна", 		"" );
			$oResponse->setField( "count_unpaid", 				"Неплатили", 	"" );
			$oResponse->setField( "objects_average", 			"Ср. Наем", 	"" );
			$oResponse->setField( "objects_price_paid", 		"Събираемост", 	"" );
			$oResponse->setField( "objects_overbars", 			"", 			"" );
			$oResponse->setField( "objects_over", 				"Над 90%", 		"" );
			//End Set Fields
			
			foreach( $aData as $aElement )
			{
				//Shift Header Colour
				if( $nLastFirm != $aElement['id_firm'] )
				{
					$bIsHeaderLight = !$bIsHeaderLight;
					$nLastFirm = $aElement['id_firm'];
				}
				//End Shift Header Colour
				
				//Captioning For Every "Firm - Region" Pattern
				if( $bIsFirst )
				{
					$oResponse->setTitle( 1, 1, $aElement['name'], array( "colspan" => "11" ) );
					$bIsFirst = false;
				}
				else
				{
					$aFinalData[$nFinalDataIndex]['stat_date'] = $aElement['name'];
					$oResponse->setDataAttributes( $nFinalDataIndex, 'stat_date', array( "colspan" => "11", "class" => ( $bIsHeaderLight ) ? "bg-primary intelliheader" : "myheader" ) );
					
					$nFinalDataIndex++;
					
					$aFinalData[$nFinalDataIndex]['stat_date'] 					= "Месец";
					$aFinalData[$nFinalDataIndex]['objects_count'] 				= "Обекти";
					$aFinalData[$nFinalDataIndex]['count_unpaid'] 				= "Неплатили";
					$aFinalData[$nFinalDataIndex]['objects_count_chbars'] 		= "";
					$aFinalData[$nFinalDataIndex]['objects_count_change'] 		= "Промяна";
					$aFinalData[$nFinalDataIndex]['objects_price_sum'] 			= "Сума";
					$aFinalData[$nFinalDataIndex]['objects_price_sum_change'] 	= "Промяна";
					$aFinalData[$nFinalDataIndex]['objects_average'] 			= "Ср. Наем";
					$aFinalData[$nFinalDataIndex]['objects_price_paid'] 		= "Събираемост";
					$aFinalData[$nFinalDataIndex]['objects_overbars'] 			= "";
					$aFinalData[$nFinalDataIndex]['objects_over'] 				= "Над 90%";
					
					$oResponse->setDataAttributes( $nFinalDataIndex, 'stat_date', 					array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count', 				array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'count_unpaid', 				array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count_chbars', 		array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count_change', 		array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_sum', 			array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_sum_change', 	array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_average', 			array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_paid', 			array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_overbars', 			array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_over', 				array( "class" => ( $bIsHeaderLight ) ? "bg-info intelliheader" : "bg-info intelliheader" ) );
					
					$nFinalDataIndex++;
				}
				//End Captioning For Every "Firm - Region" Pattern
				
				//Get Previous Months
				$sQuery = "
					SELECT
						sta.id_office AS id_office,
						CONVERT(
							CONCAT(
								CASE MONTH( sta.stat_date )
									WHEN 1 THEN 'Януари'
									WHEN 2 THEN 'Февруари'
									WHEN 3 THEN 'Март'
									WHEN 4 THEN 'Април'
									WHEN 5 THEN 'Май'
									WHEN 6 THEN 'Юни'
									WHEN 7 THEN 'Юли'
									WHEN 8 THEN 'Август'
									WHEN 9 THEN 'Септември'
									WHEN 10 THEN 'Октомври'
									WHEN 11 THEN 'Ноември'
									WHEN 12 THEN 'Декември'
								END,
								' ',
								YEAR( sta.stat_date ),
								' г.'
							)
							USING utf8
						) AS stat_date,
						DATE_FORMAT(sta.stat_date,'%Y-%m') AS stat_date_real,
				";
				
				if( $bIsByFirm )
				{
					$sQuery .= "
						sta.objects_count 			AS objects_count,
						sta.objects_price_sum		/ 1.2 AS objects_price_sum,
						sta.objects_price_paid		/ 1.2 AS objects_price_paid
						
						#sta.objects_price_sum		AS objects_price_sum,
						#sta.objects_price_paid		AS objects_price_paid
					";
				}
				else
				{
					$sQuery .= "
						SUM( sta.objects_count ) 		AS objects_count,
						SUM( sta.objects_price_sum )	/ 1.2 AS objects_price_sum,
						SUM( sta.objects_price_paid )	/ 1.2 AS objects_price_paid
						
						#SUM( sta.objects_price_sum )     AS objects_price_sum,
						#SUM( sta.objects_price_paid )    AS objects_price_paid
					";
				}
				
				$sQuery .= "
					FROM
						statistics sta
					LEFT JOIN
						offices off ON off.id = sta.id_office
					WHERE
				";
				
				if( $bIsByFirm )
				{
					$sQuery .= "
						sta.id_office = {$aElement['id']}
						AND ( DATE_FORMAT(sta.stat_date,'%Y-%m-01') <= '{$sSDate}' AND sta.stat_date >= '{$sEDate}' )
					";
				}
				else
				{
					$sQuery .= "
						off.id_firm = {$aElement['id_firm']}
						AND ( DATE_FORMAT(sta.stat_date,'%Y-%m-01') <= '{$sSDate}' AND sta.stat_date >= '{$sEDate}' )
					GROUP BY
						DATE_FORMAT(sta.stat_date,'%Y-%m')
					";
				}
				
				$sQuery .= "
					ORDER BY UNIX_TIMESTAMP( sta.stat_date )
				";
				APILog::Log(0,$sQuery);
				$aOfficeData = $this->select( $sQuery );
				
				//Define Previous Values
				$nPreviousObjectCount 		= 0;
				$nPreviousObjectPriceSum 	= 0;
				//End Define Previous Values
				
				//Define Percentage Variables
				$nNineteenPercentSum	= 0;
				$nPaidSumPercent		= 0;
				//End Define Percentage Variables
				
				foreach( $aOfficeData as $aOfficeElement )
				{
					$oResponse->setDataAttributes( $nFinalDataIndex, 'stat_date', 					array( "align" => "left" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count', 				array( "align" => "right" ) );
					$oResponse->setDataAttributes
					(
						$nFinalDataIndex,
						'count_unpaid',
						array
						(
							"align" => "right",
							"style" => "cursor: pointer;",
							"onclick" => "openUnpaidObjects( '{$aOfficeElement['stat_date_real']}', {$aElement['id_firm']}, " . ( $bIsByFirm ? $aOfficeElement['id_office'] : "0" ) . ", '1' );"
						)
					);
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count_chbars', 		array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count_change', 		array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_sum', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_sum_change', 	array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_average', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_paid', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_overbars', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_over', 				array( "align" => "right" ) );
					
					//Update Data
					$nNineteenPercentSum = round( ( $aOfficeElement['objects_price_sum'] * 90 ) / 100, 2 );
					$nObjectPriceSum = ( int ) $aOfficeElement['objects_price_sum'];
					if( !empty( $nObjectPriceSum ) )
					{
						$nPaidSumPercent = round( ( $aOfficeElement['objects_price_paid'] / $aOfficeElement['objects_price_sum'] ) * 100, 2 );
					}
					else $nPaidSumPercent = 0;
					
					//--Unpaid Objects Count
					$nIDOffice = $bIsByFirm ? $aElement['id'] : 0;
					$aUnpaid = array();
					$aUnpaid = $oDBObjectServices->getObjectsUnpaidSince( $aOfficeElement['stat_date_real'], $aElement['id_firm'], $nIDOffice );
					//--End Unpaid Objects Count
					
					$aFinalData[$nFinalDataIndex]['stat_date'] 					= $aOfficeElement['stat_date'];
					$aFinalData[$nFinalDataIndex]['objects_count'] 				= $aOfficeElement['objects_count'];
					$aFinalData[$nFinalDataIndex]['count_unpaid']				= isset( $aUnpaid['count_unpaid'] ) ? $aUnpaid['count_unpaid'] : 0;
					$aFinalData[$nFinalDataIndex]['objects_count_chbars'] 		= "";
					$aFinalData[$nFinalDataIndex]['objects_count_change'] 		= empty( $nPreviousObjectCount ) ? 0 : ( $aOfficeElement['objects_count'] - $nPreviousObjectCount );
					$aFinalData[$nFinalDataIndex]['objects_price_sum'] 			= round( $aOfficeElement['objects_price_sum'], 2 ) . " лв.";
					$aFinalData[$nFinalDataIndex]['objects_price_sum_change'] 	= round( empty( $nPreviousObjectPriceSum ) ? 0 : ( $aOfficeElement['objects_price_sum'] - $nPreviousObjectPriceSum ), 2 ) . " лв.";
					$aFinalData[$nFinalDataIndex]['objects_average'] 			= round( !empty( $aFinalData[$nFinalDataIndex]['objects_count_change'] ) ? $aFinalData[$nFinalDataIndex]['objects_price_sum_change'] / $aFinalData[$nFinalDataIndex]['objects_count_change'] : 0, 2 ) . " лв.";
					$aFinalData[$nFinalDataIndex]['objects_price_paid'] 		= round( $aOfficeElement['objects_price_paid'], 2 ) . " лв. ( {$nPaidSumPercent}% )";
					$aFinalData[$nFinalDataIndex]['objects_overbars'] 			= "";
					$aFinalData[$nFinalDataIndex]['objects_over'] 				= round( $aOfficeElement['objects_price_paid'] - $nNineteenPercentSum, 2 ) . " лв.";
					
					$nFinalDataIndex++;
					//End Update Data
					
					$nPreviousObjectCount 		= $aOfficeElement['objects_count'];
					$nPreviousObjectPriceSum 	= $aOfficeElement['objects_price_sum'];
				}
				//End Get Previous Months
				
				//Get Current Month
				$nMonths = 2;
				
				for( $i = 0; $i < $nMonths; $i++ )
				{
					$nRev = $nMonths - ( $i + 1 );
					
					$nDaysInMonth = date( "t", strtotime( "-{$nRev} MONTHS" ) );
					
					$sCDate 	= date( "Y-m-{$nDaysInMonth}", strtotime( "-{$nRev} MONTHS" ) );				//Date to Write
					$sCDateReal = date( "Y-m-01", strtotime( "-{$nRev} MONTHS" ) );								//Date to Write
					
					$sCDateYM = date( "Y-m", strtotime( "-{$nRev} MONTHS" ) );									//Date to Search By
					$sCDateWord = "";
					switch( date( "m", strtotime( "-{$nRev} MONTHS" ) ) )
					{
						case 1: $sCDateWord .= "Януари"; break;
						case 2: $sCDateWord .= "Февруари"; break;
						case 3: $sCDateWord .= "Март"; break;
						case 4: $sCDateWord .= "Април"; break;
						case 5: $sCDateWord .= "Май"; break;
						case 6: $sCDateWord .= "Юни"; break;
						case 7: $sCDateWord .= "Юли"; break;
						case 8: $sCDateWord .= "Август"; break;
						case 9: $sCDateWord .= "Сепрември"; break;
						case 10: $sCDateWord .= "Октомври"; break;
						case 11: $sCDateWord .= "Ноември"; break;
						case 12: $sCDateWord .= "Декември"; break;
					}
					$sCDateWord .= " " . date( "Y" ) . " г.";					//Date to Display
				
					$sCurrentQuery = "
						SELECT
							COUNT( DISTINCT o.id ) AS objects_count,
							
							SUM( s.total_sum ) / 1.2 AS objects_price_sum,
							SUM( s.total_sum ) AS objects_price_sum_origin,
							
							SUM( IF( UNIX_TIMESTAMP( s.real_paid ) >= UNIX_TIMESTAMP( '{$sCDateReal}' ), s.total_sum, 0 ) ) / 1.2 AS objects_price_paid,
							SUM( IF( UNIX_TIMESTAMP( s.real_paid ) >= UNIX_TIMESTAMP( '{$sCDateReal}' ), s.total_sum, 0 ) ) AS objects_price_paid_origin
						FROM
							objects o
						LEFT JOIN
							objects_services s ON s.id_object = o.id AND s.to_arc = 0
						LEFT JOIN
							offices of ON of.id = o.id_office
						LEFT JOIN
							firms f ON f.id = of.id_firm
						LEFT JOIN
							(
								SELECT
									obj_sta.id_obj,
									obj_sta.id_status
								FROM
									object_statuses obj_sta
								WHERE
									obj_sta.to_arc = 0
									AND UNIX_TIMESTAMP( obj_sta.updated_time ) <= UNIX_TIMESTAMP( '{$sCDate}' )
								ORDER BY obj_sta.updated_time DESC
								LIMIT 1
							) AS stat ON stat.id_obj = o.id
						WHERE
							UNIX_TIMESTAMP( o.start ) <= UNIX_TIMESTAMP( '{$sCDate}' )
							AND IF
							(
								ISNULL( stat.id_status ),
								o.id_status IN
								(
									SELECT
										fos.id_status
									FROM
										firms_object_statuses fos
									WHERE
										fos.id_firm = {$aElement['id_firm']}
								),
								stat.id_status IN
								(
									SELECT
										fos.id_status
									FROM
										firms_object_statuses fos
									WHERE
										fos.id_firm = {$aElement['id_firm']}
								)
							)
					";
					
					if( $bIsByFirm )
					{
						$sCurrentQuery .= "
							AND of.id = {$aElement['id']}
						GROUP BY
							of.id
						LIMIT 1
						";
					}
					else
					{
						$sCurrentQuery .= "
							AND f.id = {$aElement['id_firm']}
						GROUP BY
							f.id
						LIMIT 1
						";
					}
					
					$aCurrentData = $this->selectOnce( $sCurrentQuery );
					
					if( empty( $aCurrentData ) )
					{
						$aCurrentData['objects_count'] = $aCurrentData['objects_price_sum'] = $aCurrentData['objects_price_paid'] = 0;
					}

					$oResponse->setDataAttributes( $nFinalDataIndex, 'stat_date', 					array( "align" => "left" ) );
					if($i == ($nMonths-1))
						if($nIDFirm)
							$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count', 			array( "style" => "text-align:right; cursor: pointer;" , "onclick" => "openObject('".mysqlDateToJsDate($sCDate)."', $nIDFirm,{$aElement['id']} ,'1' )") );
						else 
							$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count', 			array( "style" => "text-align:right; cursor: pointer;" , "onclick" => "openObject('".mysqlDateToJsDate($sCDate)."', {$aElement['id']} ,'0' ,'1' )") );
					else
						$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes
					(
						$nFinalDataIndex,
						'count_unpaid',
						array
						(
							"align" => "right",
							"style" => "cursor: pointer;",
							"onclick" => "openUnpaidObjects( '{$sCDateYM}', {$aElement['id_firm']}, " . ( $bIsByFirm ? $aElement['id'] : "0" ) . ", '1' );"
						)
					);
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count_chbars', 		array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_count_change', 		array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_sum', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_sum_change', 	array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_average', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_price_paid', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_overbars', 			array( "align" => "right" ) );
					$oResponse->setDataAttributes( $nFinalDataIndex, 'objects_over', 				array( "align" => "right" ) );
					
					//Update Data
					$nNineteenPercentSum = round( ( $aCurrentData['objects_price_sum'] * 90 ) / 100, 2 );
					$nObjectPriceSum = ( int ) $aCurrentData['objects_price_sum'];
					if( !empty( $nObjectPriceSum ) )
					{
						$nPaidSumPercent = round( ( $aCurrentData['objects_price_paid'] / $aCurrentData['objects_price_sum'] ) * 100, 2 );
					}
					else $nPaidSumPercent = 0;
					
					//--Unpaid Objects Count
					$nIDOffice = $bIsByFirm ? $aElement['id'] : 0;
					$aUnpaid = array();
					$aUnpaid = $oDBObjectServices->getObjectsUnpaidSince( $sCDateYM, $aElement['id_firm'], $nIDOffice );
					//--End Unpaid Objects Count
					
					$aFinalData[$nFinalDataIndex]['stat_date'] 					= $sCDateWord;
					$aFinalData[$nFinalDataIndex]['objects_count'] 				= $aCurrentData['objects_count'];
					$aFinalData[$nFinalDataIndex]['count_unpaid']				= isset( $aUnpaid['count_unpaid'] ) ? $aUnpaid['count_unpaid'] : 0;
					$aFinalData[$nFinalDataIndex]['objects_count_chbars'] 		= "";
					$aFinalData[$nFinalDataIndex]['objects_count_change'] 		= empty( $nPreviousObjectCount ) ? 0 : ( $aCurrentData['objects_count'] - $nPreviousObjectCount );
					$aFinalData[$nFinalDataIndex]['objects_price_sum'] 			= round( $aCurrentData['objects_price_sum'], 2 ) . " лв.";
					$aFinalData[$nFinalDataIndex]['objects_price_sum_change'] 	= round( empty( $nPreviousObjectPriceSum ) ? 0 : ( $aCurrentData['objects_price_sum'] - $nPreviousObjectPriceSum ), 2 ) . " лв.";
					$aFinalData[$nFinalDataIndex]['objects_average'] 			= round( !empty( $aFinalData[$nFinalDataIndex]['objects_count_change'] ) ? $aFinalData[$nFinalDataIndex]['objects_price_sum_change'] / $aFinalData[$nFinalDataIndex]['objects_count_change'] : 0, 2 ) . " лв.";
					$aFinalData[$nFinalDataIndex]['objects_price_paid'] 		= round( $aCurrentData['objects_price_paid'], 2 ) . " лв. ( {$nPaidSumPercent}% )";
					$aFinalData[$nFinalDataIndex]['objects_overbars'] 			= "";
					$aFinalData[$nFinalDataIndex]['objects_over'] 				= round( $aCurrentData['objects_price_paid'] - $nNineteenPercentSum, 2 ) . " лв.";
					
					$nPreviousObjectCount 		= $aFinalData[$nFinalDataIndex]['objects_count'];
					$nPreviousObjectPriceSum 	= $aFinalData[$nFinalDataIndex]['objects_price_sum'];
					
					$nFinalDataIndex++;
					//End Update Data
					
					//Update SQL Data
					$sCheckQuery = "
						SELECT
							id
						FROM
							statistics
						WHERE
							stat_date LIKE '{$sCDateYM}%'
							AND id_office = {$aElement['id']}
						LIMIT 1
					";
					
					$aCheckData = $this->selectOnce( $sCheckQuery );
					
					$aSQLData = array();
					$aSQLData['id'] 				= ( !empty( $aCheckData ) && isset( $aCheckData['id'] ) ) ? $aCheckData['id'] : 0;
					$aSQLData['stat_date'] 			= $sCDate;
					$aSQLData['id_office'] 			= $aElement['id'];
					$aSQLData['objects_count'] 		= $aCurrentData['objects_count'];
					$aSQLData['objects_price_sum'] 	= $aCurrentData['objects_price_sum_origin'];
					$aSQLData['objects_price_paid'] = $aCurrentData['objects_price_paid_origin'];
					
					$this->update( $aSQLData );
					//End Update SQL Data
				}
				//End Get Current Month
				
				//Null Element
				$aFinalData[$nFinalDataIndex]['stat_date'] = "";
				$oResponse->setDataAttributes( $nFinalDataIndex, 'stat_date', array( "colspan" => "11", "class" => "even", "style" => "height: 30px; background: #e9e9e9;" ) );
				
				$nFinalDataIndex++;
				//End Null Element
			}
			
			//Create Bars
			$aGroupCount = array();
			$aGroupOver = array();
			
			foreach( $aFinalData as $nKey => $aValue )
			{
				if( $aValue['stat_date'] == "" )
				{
					//Process Groups
					$aGroupCount = calcReduction( 100, $aGroupCount );
					$aGroupOver = calcReduction( 100, $aGroupOver );
					
					foreach( $aGroupCount as $nKeyInner => $aValueInner )
					{
						$aFinalData[$nKeyInner]['objects_count_chbars'] = "";
						if( $aValueInner > 0 )
						{
							$aValueInner = (int) $aValueInner;
							$aFinalData[$nKeyInner]['objects_count_chbars'] .= "posbaro{$aValueInner}posbarc";
						}
						if( $aValueInner < 0 )
						{
							$aValueInner = (int) -$aValueInner;
							$aFinalData[$nKeyInner]['objects_count_chbars'] .= "negbaro{$aValueInner}negbarc";
						}
					}
					
					foreach( $aGroupOver as $nKeyInner => $aValueInner )
					{
						$aFinalData[$nKeyInner]['objects_overbars'] = "";
						if( $aValueInner > 0 )
						{
							$aValueInner = (int) $aValueInner;
							$aFinalData[$nKeyInner]['objects_overbars'] .= "posbaro{$aValueInner}posbarc";
						}
						if( $aValueInner < 0 )
						{
							$aValueInner = (int) -$aValueInner;
							$aFinalData[$nKeyInner]['objects_overbars'] .= "negbaro{$aValueInner}negbarc";
						}
					}
					//End Process Groups
					
					$aGroupCount = array();
					$aGroupOver = array();
				}
				else
				{
					if( isset( $aValue['objects_count_change'] ) && is_numeric( $aValue['objects_count_change'] ) )
					{
						$aGroupCount[$nKey] = $aValue['objects_count_change'];
					}
					
					if( isset( $aValue['objects_over'] ) && $aValue['objects_over'] != "Над 90%" )
					{
						$aGroupOver[$nKey] = ( float ) $aValue['objects_over'];
					}
				}
			}
			
			$oResponse->setData( $aFinalData );
			//End Create Bars
		}
	}

?>