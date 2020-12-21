<?php

	class ApiAdvertSquares
	{
		public function result( DBResponse $oResponse )
		{
			//Rights Level
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
			{
				if( in_array( 'advert_squares', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			}
			//End Rights Level
			
			//Set Fields
			$oResponse->setField( "path", 			"Път", 				"", 							NULL, 					NULL, NULL, array( "type" => "hidden", "style" => "display: none;" ) );
			$oResponse->setField( "file", 			"Име на файл", 		"", 							NULL, 					NULL, NULL, array( "type" => "hidden", "style" => "display: none;" ) );
			$oResponse->setField( "upload_date", 	"Дата на Качване", 	"Сортирай по Дата на Качване", 	NULL, 					NULL, NULL, array( "DATA_FORMAT" => DF_DATETIME ) );
			$oResponse->setField( "is_default", 	"Избрано", 			"Сортирай по Избрано", 			"images/confirm.gif", 	NULL, NULL, array( "style" => "width: 100px;" ) );
			$oResponse->setField( "view", 			"Изглед", 			NULL, 							"images/dots.gif", 		NULL, NULL, array( "style" => "width: 100px;" ) );
			
			if( $right_edit )
			{
				$oResponse->setField( "def", "", "Избрано", "images/confirm.gif", "defaultSquare", "" );
				$oResponse->setField( "del", "", "Изтриване", "images/cancel.gif", "deleteSquare", "" );
			}
			//End Set Fields
			
			$nData = 1;
			$aData = array();
			
			$oMyDir = opendir( $_SESSION['BASE_DIR'] . "/images_adverts" );
			$sMyDir = $_SESSION['BASE_DIR'] . "/images_adverts/";
			
			while( false !== ( $sFile = readdir( $oMyDir ) ) )
			{
				if( $sFile != "." && $sFile != ".." )
				{
					if( !$this->isAdvertFilenameValid( $sFile ) )continue;
					
					$nMyDate = filemtime( $sMyDir . $sFile );
					$sMyDate = date( "Y-m-d H:i:s", $nMyDate );
					$nIsDefault = ( substr( $sFile, 0, 7 ) == "adverts" ) ? 1 : 0;
					
					$aData[$nData]['id'] = $nData;
					$aData[$nData]['path'] = $sMyDir;
					$aData[$nData]['file'] = $sFile;
					$aData[$nData]['upload_date'] = $sMyDate;
					$aData[$nData]['is_default'] = $nIsDefault;
					$aData[$nData]['view'] = 1;
					
					$nData++;
				}
			}
			
			//Sortings
			$oParams = Params::getInstance();
			
			$sSortField = $oParams->get( "sfield", "upload_date" );
			$nSortType	= $oParams->get( "stype", DBAPI_SORT_DESC );
			
			if( empty( $sSortField ) )$sSortField = "upload_date";
			
			foreach( $aData as $key => $row )
			{
				$id[$key]  = 			$row['id'];
				$upload_date[$key] = 	$row['upload_date'];
				$is_default[$key] = 	$row['is_default'];
			}
			
			if( $nSortType == DBAPI_SORT_ASC )$nSortOrderArray = SORT_ASC;
			if( $nSortType == DBAPI_SORT_DESC )$nSortOrderArray = SORT_DESC;
			
			if( $sSortField == "id" || 
				$sSortField == "is_default" )$nSortTypeArray = SORT_NUMERIC;
			else $nSortTypeArray = SORT_STRING;
			
			array_multisort( $$sSortField, $nSortOrderArray, $nSortTypeArray, $aData );
			
			$oResponse->setSort( $sSortField, $nSortType );
			//End Sortings
			
			foreach( $aData as $nKey => $aValue )
			{
				$sFile = $aValue['file'];
				$oResponse->setDataAttributes( $nKey, "view", array( "style" => "text-align: center; background: rgb( 240, 240, 230 );", "onmousemove" => "setTooltipLocation();", "onmouseover" => "setTooltip( true, './images_adverts/{$sFile}' );", "onmouseout" => "setTooltip( false, '' );" ) );
			}
			
			$oResponse->setData( $aData );
			
			$oResponse->printResponse( "Рекламно Каре", "advert_squares" );
		}
		
		public function current( DBResponse $oResponse )
		{
			$nID 	= Params::get( "nID", 0 );
			$aFiles = Params::get( "file", array() );
			$aPaths = Params::get( "path", array() );
			
			if( !empty( $nID ) )
			{
				//Get Extension
				$aFileOrig = explode( ".", $aFiles[$nID] );
				$sFileExt = "." . ( !empty( $aFileOrig ) ? end( $aFileOrig ) : "jpg" );
				//End Get Extension
				
				rename( $aPaths[$nID] . "adverts" . $sFileExt, $aPaths[$nID] . date( "YmdHis" ) . $sFileExt );
				if( !rename( $aPaths[$nID] . $aFiles[$nID], $aPaths[$nID] . "adverts" . $sFileExt ) )
				{
					throw new Exception( "Грешка при изпълнение на операцията", DBAPI_ERR_UNKNOWN );
				}
			}
			
			$this->result( $oResponse );
		}
		
		public function delete( DBResponse $oResponse )
		{
			$nID 	= Params::get( "nID", 0 );
			$aFiles = Params::get( "file", array() );
			$aPaths = Params::get( "path", array() );
			
			if( !empty( $nID ) )
			{
				if( !unlink( $aPaths[$nID] . $aFiles[$nID] ) )
				{
					throw new Exception( "Грешка при изпълнение на операцията", DBAPI_ERR_UNKNOWN );
				}
			}
			
			$this->result( $oResponse );
		}
		
		private function isAdvertFilenameValid( $sFilename )
		{
			$aFilename = explode( ".", $sFilename );
			
			if( empty( $aFilename ) || count( $aFilename ) > 2 )return false;
			if( strlen( $aFilename[0] ) != 14 || !is_numeric( $aFilename[0] ) )
			{
				if( $aFilename[0] != "adverts" )return false;
			}
			
			if( $aFilename[0] != "adverts" )
			{
				if( ( int ) substr( $aFilename[0], 4, 2 ) > 12 || ( int ) substr( $aFilename[0], 4, 2 ) < 1 )return false;
				if( ( int ) substr( $aFilename[0], 6, 2 ) > 31 || ( int ) substr( $aFilename[0], 6, 2 ) < 1 )return false;
				if( ( int ) substr( $aFilename[0], 8, 2 ) > 23 || ( int ) substr( $aFilename[0], 8, 2 ) < 1 )return false;
				if( ( int ) substr( $aFilename[0], 10, 2 ) > 59 || ( int ) substr( $aFilename[0], 10, 2 ) < 1 )return false;
				if( ( int ) substr( $aFilename[0], 12, 2 ) > 59 || ( int ) substr( $aFilename[0], 12, 2 ) < 1 )return false;
			}
			
			return true;
		}
	}

?>