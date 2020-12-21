<?php

	require_once( "include/ofc-library/open-flash-chart.php" );
	
	//Params
	$nIDFirm = isset( $_GET['id_firm'] ) ? $_GET['id_firm'] : 0;
	$nInterval = isset( $_GET['interval'] ) ? $_GET['interval'] : 0;
	//End Params
	
	//Initialize
	$oDBStatistics 	= new DBStatistics2();
	$oDBFirms 		= new DBFirms();
	
	$sOutput 		= "";
	$sOutputMoney 	= "";
	$sOutputGlobal 	= "";
	
	$nInterval--;
	$sSDate = date( "Y-m-01" );
	$sEDate = date( "Y-m-01", strtotime( "-{$nInterval} months" ) );
	//End Initialize
	
	if( !empty( $nIDFirm ) )
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
	
	if( !empty( $nIDFirm ) )
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
	
	$aData = $oDBFirms->select( $sQuery );
	
	foreach( $aData as $aElement )
	{
		//Get Needed Data
		$sQuery = "
			SELECT
				CONVERT(
					CONCAT(
						CASE SUBSTR( sta.stat_date, 6, 2 )
							WHEN '01' THEN 'Януари'
							WHEN '02' THEN 'Февруари'
							WHEN '03' THEN 'Март'
							WHEN '04' THEN 'Април'
							WHEN '05' THEN 'Май'
							WHEN '06' THEN 'Юни'
							WHEN '07' THEN 'Юли'
							WHEN '08' THEN 'Август'
							WHEN '09' THEN 'Септември'
							WHEN '10' THEN 'Октомври'
							WHEN '11' THEN 'Ноември'
							WHEN '12' THEN 'Декември'
						END,
						' ',
						SUBSTR( sta.stat_date, 1, 4 ),
						' г.'
					)
					USING utf8
				) AS stat_date,
		";
		
		if( !empty( $nIDFirm ) )
		{
			$sQuery .= "
				sta.objects_count AS objects_count,
				sta.objects_price_sum AS objects_price_sum,
				sta.objects_price_paid AS objects_price_paid
			";
		}
		else
		{
			$sQuery .= "
				SUM( sta.objects_count ) AS objects_count,
				SUM( sta.objects_price_sum ) AS objects_price_sum,
				SUM( sta.objects_price_paid ) AS objects_price_paid
			";
		}
		
		$sQuery .= "
			FROM
				statistics sta
			LEFT JOIN
				offices off ON off.id = sta.id_office
			WHERE
		";
		
		if( !empty( $nIDFirm ) )
		{
			$sQuery .= "
				sta.id_office = {$aElement['id']}
				AND ( sta.stat_date <= '{$sSDate}' AND sta.stat_date >= '{$sEDate}' )
			";
		}
		else
		{
			$sQuery .= "
				off.id_firm = {$aElement['id_firm']}
				AND ( sta.stat_date <= '{$sSDate}' AND sta.stat_date >= '{$sEDate}' )
			GROUP BY
				SUBSTR( sta.stat_date, 1, 7 )
			";
		}
		
		$sQuery .= "
			ORDER BY UNIX_TIMESTAMP( sta.stat_date )
		";
		
		$aAllData = $oDBStatistics->select( $sQuery );
		//End Get Needed Data
		
		$aXLabels = array();
		$aDataCount = array();
		$aDataSum = array();
		$aDataPaid = array();
		
		$nMaxCount = 0;
		$nMinCount = 0;
		$nMinCountSet = false;
		
		$nMaxMoney = 0;
		$nMinMoney = 0;
		$nMinMoneySet = false;
		
		foreach( $aAllData as $aSubElement )
		{
			$nMaxCount = ( $nMaxCount <= $aSubElement['objects_count'] ) ? $aSubElement['objects_count'] : $nMaxCount;
			if( !$nMinCountSet )
			{
				$nMinCount = $aSubElement['objects_count'];
				$nMinCountSet = true;
			}
			else
			{
				$nMinCount = ( $nMinCount >= $aSubElement['objects_count'] ) ? $aSubElement['objects_count'] : $nMinCount;
			}
			
			$nMaxMoney = ( $nMaxMoney <= $aSubElement['objects_price_sum'] ) ? $aSubElement['objects_price_sum'] : $nMaxMoney;
			$nMaxMoney = ( $nMaxMoney <= $aSubElement['objects_price_paid'] ) ? $aSubElement['objects_price_paid'] : $nMaxMoney;
			if( !$nMinMoneySet )
			{
				$nMinMoney = ( $aSubElement['objects_price_sum'] < $aSubElement['objects_price_paid'] ) ? $aSubElement['objects_price_sum'] : $aSubElement['objects_price_paid'];
				$nMinMoneySet = true;
			}
			else
			{
				$nMinMoney = ( $nMinMoney >= $aSubElement['objects_price_sum'] ) ? $aSubElement['objects_price_sum'] : $nMinMoney;
				$nMinMoney = ( $nMinMoney >= $aSubElement['objects_price_paid'] ) ? $aSubElement['objects_price_paid'] : $nMinMoney;
			}
			
			$aXLabels[] = $aSubElement['stat_date'];
			
			$aDataCount[] = $aSubElement['objects_count'];
			$aDataSum[] = $aSubElement['objects_price_sum'];
			$aDataPaid[] = $aSubElement['objects_price_paid'];
		}
		
		//Objects Count
		$g = new graph();
		$g->set_output_type( 'js' );
		
		$g->title( "Диаграми за Фирма " . $aElement['name'], '{font-size: 20px;}' );
		
		$g->set_data( $aDataCount );
		
		$g->line_hollow( 2, 4, '0x9933CC', 'Брой Обекти', 10 );
		
		$g->set_x_labels( $aXLabels );
		$g->set_x_label_style( 9, 0, 0, 1 );
		
		$g->set_y_max( $nMaxCount );
		$g->set_y_min( $nMinCount );
		
		$g->set_width( 700 );
		$g->set_height( 243 );
		
		$sOutput = $g->render();
		//End Objects Count
		
		//Objects Payment
		$g = new graph();
		$g->set_output_type( 'js' );
		
		$g->set_data( $aDataSum );
		$g->set_data( $aDataPaid );
		
		$g->line_hollow( 2, 4, '0x3333CC', 'Сума ( лв. )', 10 );
		$g->line_hollow( 2, 4, '0xCC3333', 'Събираемост ( лв. )', 10 );
		
		$g->set_x_labels( $aXLabels );
		$g->set_x_label_style( 9, 0, 0, 1 );
		
		$g->set_y_max( $nMaxMoney );
		$g->set_y_min( $nMinMoney );
		
		$g->set_width( 700 );
		$g->set_height( 243 );
		
		$sOutputMoney = $g->render();
		//End Objects Payment
		
		$sOutputGlobal .= $sOutput . "<hr />" . $sOutputMoney . "<br /><br />";
	}
	
	$template->assign( "sOutput", $sOutputGlobal );

?>