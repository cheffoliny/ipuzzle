<?php
 
	class DBExportDocuments
		extends DBBase2 {
		public function __construct() {
			global $db_finances;
			
			parent::__construct($db_finances, "sales_docs_origin");
		}	
		
		public function ExportSaleDocs( $aData, $oResponse ) {
			global $db_finance, $db_name_personnel, $db_name_finance, $db_name_sod;
			
			$nIDOffice 	= isset($aData['nIDOffice']) && is_numeric($aData['nIDOffice']) ? $aData['nIDOffice'] 	: 0;
			$nIDFirm 	= isset($aData['nIDFirm'])	 && is_numeric($aData['nIDFirm']) 	? $aData['nIDFirm'] 	: 0;
			$from 		= isset($aData['from']) 										? $aData['from'] 		: 0;
			$to 		= isset($aData['to']) 											? $aData['to'] 			: 0;
			$where 		= "";
			
			$oSalesRow	= new DBSalesDocsRows();

			//if ( !empty($nIDFirm) {
				
				//
			//}
			
			if ( !empty($nIDFirm) ) {
			
				$eFirm = new DBFirms();
				$eFirmEIN = $eFirm->getEIN( $nIDFirm );
				$eFirmEIN = !empty($eFirmEIN) ? $eFirmEIN : -1;
				
            	if( $eFirmEIN != -1 ) {
					$where .= " AND mp.deliverer_ein = '{$eFirmEIN}' ";
				}
			
			
				if ( !empty($nIDOffice) ) {
					$where .= " AND o.id = {$nIDOffice} ";
				} else {
					$oFirm = new DBOffices();
					$aFirm = $oFirm->getOfficesIDByFirm( $nIDFirm );
					$aFirm = !empty($aFirm) ? $aFirm : -1;

					if( $aFirm !=  -1 ) {
						$where .= " AND o.id IN ({$aFirm}) ";
					}
				}
			
			}
			
			if ( !empty($from) ) {
				$where .= " AND mp.doc_date >= '{$from}' ";
			}
			
			if ( !empty($to) ) {
				$where .= " AND mp.doc_date <= '{$to}' ";
			}			

			//throw new Exception($from, DBAPI_ERR_INVALID_PARAM);
			$tables2 = array();
			$tables = SQL_get_tables($db_finance, 'sales_docs_', '______');

			foreach ( $tables as $val ) {
				if ( substr($val, -6) >= date("Ym", strtotime($from)) && substr($val, -6) <= date("Ym", strtotime($to)) ) {
					$tables2[] = $val;
				}
				
			}
			
			//APILog::Log(0, $tables2);
			$sQuery = "";
			
			if ( count($tables2) > 1 ) {
				for ( $i = 0; $i < count($tables2) - 1; $i++ ) {
					$sQuery .= "
						(
							SELECT 
								mp.id,
								mp.doc_num,
								mp.paid_type,
								DATE_FORMAT(mp.doc_date, '%d.%m.%Y') AS doc_date,
								MONTH(mp.doc_date) as mnth,
								mp.note,
								mp.total_sum as sum,
								mp.client_ein,
								mp.client_mol,
								mp.client_ein_dds,
								mp.client_name,
								1 as br,
								o.name as office,
								f.name as firm,
								IF ( f.id = 3, 'Физическа охрана', IF (	f.id = 2, 'Охрана обект', '' ) ) as ohrana,
								o.work_flow_acc as work_flow
							FROM {$db_name_finance}.{$tables2[$i]} mp
							LEFT JOIN {$db_name_personnel}.personnel p ON p.id = mp.created_user
							LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
							LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
							WHERE mp.doc_type = 'faktura'
								#AND mp.exported = 0
								{$where}
						) 
						
						UNION
					";
				}
				
				$sQuery .= "
					(
						SELECT 
							mp.id,
							mp.doc_num,
							mp.paid_type,
							DATE_FORMAT(mp.doc_date, '%d.%m.%Y') AS doc_date,
							MONTH(mp.doc_date) as mnth,
							mp.note,
							mp.total_sum as sum,
							mp.client_ein,
							mp.client_mol,
							mp.client_ein_dds,
							mp.client_name,
							1 as br,
							o.name as office,
							f.name as firm,
							IF ( f.id = 3, 'Физическа охрана', IF (	f.id = 2, 'Охрана обект', '' ) ) as ohrana,
							o.work_flow_acc as work_flow
						FROM {$db_name_finance}.{$tables2[count($tables2)-1]} mp
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = mp.created_user
						LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
						LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm						
						WHERE mp.doc_type = 'faktura'
							#AND mp.exported = 0
							{$where}
					)
				";
				
			} else {
				$sQuery .= "
					SELECT 
						mp.id,
						o.id  AS offid,
						mp.doc_num,
						mp.paid_type,
						DATE_FORMAT(mp.doc_date, '%d.%m.%Y') AS doc_date,
						MONTH(mp.doc_date) as mnth,
						mp.note,
						mp.total_sum as sum,
						mp.client_ein,
						mp.client_mol,
						mp.client_ein_dds,
						mp.client_name,
						1 as br,
						o.name as office,
						f.name as firm,
						IF ( f.id = 3, 'Физическа охрана', IF (	f.id = 2, 'Охрана обект', '' ) ) as ohrana,
						o.work_flow_acc as work_flow
					FROM {$db_name_finance}.{$tables2[0]} mp
					LEFT JOIN {$db_name_personnel}.personnel p ON p.id = mp.created_user
					LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
					LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
					WHERE (mp.doc_type = 'faktura')
						#debitno izvestie
						#kreditno izvestie
						#AND mp.exported = 0
						{$where}
				";
			}

            APILog::Log(0, $sQuery);
			$aData = $db_finance->getArray( $sQuery );
			$content = "";
			$count = count($aData);
			
			//*
			if ($count == 0){
				$oResponse->setAlert( "Не бяха намерени документи за експорт.");
				return 0;
			}
			//*/
			
			foreach ( $aData as $value ) {
				$strSpace 	= str_repeat(" ", 200);
				
				$nID 		= isset($value['id']) ? $value['id'] : 0;
				
				if ( !empty($nID) && (strlen($nID) == 13) ) {
					$table 						= PREFIX_SALES_DOCS.substr($nID, 0, 6);
					
					$sQueryUpdate = "UPDATE {$db_name_finance}.{$table} SET exported = 1 WHERE id = {$nID}";
					$db_finance->Execute($sQueryUpdate);
				}
				
				$num 		= isset($value['doc_num']) ? sprintf("%.0f",  $value['doc_num'] ) : sprintf("%.0f",  0 );
				$type 		= isset($value['paid_type']) && $value['paid_type'] == "bank" ? "02" : "01";
				$date 		= isset($value['doc_date']) ? $value['doc_date'] : 0;
				$short 		= isset($value['mnth']) ? $value['mnth'] : 1;
				$note 		= isset($value['note']) ? $value['note'] : 0;
				$sum 		= isset($value['sum']) ? $value['sum'] : 0;
                $no_dds   	= substr($strSpace.sprintf("%.2f", $sum/1.2), -15);
				$new_dds  	= substr($strSpace.($sum - $no_dds), -15);
				$client_ein = isset($value['client_ein']) ? $value['client_ein'] : 0;
				$client_mol = isset($value['client_mol']) ? $value['client_mol'] : 0;
				$client_dds = isset($value['client_ein_dds']) ? $value['client_ein_dds'] : 0;
				$client_name = isset($value['client_name']) ? $value['client_name'] : 0;
				$br 		= isset($value['br']) ? $value['br'] : 0;
                $product_price = sprintf("%.2f",$no_dds / $br);
                $office		= isset($value['office']) ? $value['office'] : "";
                $firm		= isset($value['firm']) ? $value['firm'] : "";
                $ohrana 	= isset($value['ohrana']) ? $value['ohrana'] : "";
                $smetka		= isset($value['work_flow']) ? $value['work_flow'] : "";
                
                $aDocRows = $oSalesRow->getByIDSaleDocOffice($nID);
                
                $sFirstRowFirm = "";
                $sFirstRowOffice = "";
                if (count($aDocRows) > 0) {

                    $firstOfficeID = $aDocRows[0]['id_office'];

                    $oOffices = new DBOffices();
                	$aInfo = $oOffices->getFirmNameOfficeNameByIDOffice($firstOfficeID);

                	$sFirstRowFirm = $aInfo[0]['fname'];
                	$sFirstRowOffice = $aInfo[0]['oname'];
//                	APILog::Log(0, $aInfo[0]['fname'].' - '.$aInfo[0]['oname']);
                }
                $firm	= isset($sFirstRowFirm)		? $sFirstRowFirm	: $firm;
                $office = isset($sFirstRowOffice)	? $sFirstRowOffice	: $office;

                $sum           = substr( $strSpace.$sum, -15 );
                $firstOfficeID = substr( $firstOfficeID.$strSpace, 0, 4 );
                $client_name   = mb_substr( $client_name.$strSpace, 0, 40, "UTF-8" );

                if( $client_dds <> 0 || $client_dds <> '' ) {
                    $client_dds = substr( $client_dds.$strSpace, 0, 45 );
                    $client_ein = substr( $strSpace, 0, 10 ); //substr( $client_ein.$strSpace, 0, 10 );
                } else {
                    $client_dds = substr( $client_ein.$strSpace, 0, 45 ); // Ако не е SET-нат ДДС номер
                    $client_ein = substr( $strSpace, 0, 10 ); //substr( $client_ein.$strSpace, 0, 10 );
                }


				foreach ( $aDocRows as $docRow ) {

                    $service_name  = mb_substr( $docRow['service_name'].$strSpace, 0, 20, "UTF-8" );

                    $sum_row       = substr( $strSpace.sprintf("%.2f", ($docRow['total_sum']*1.2))  , -15 );
                    $no_dds_row    = substr( $strSpace.sprintf("%.2f", $docRow['total_sum'])        , -15 );
                    $new_dds_row   = substr( $strSpace.sprintf("%.2f", ($sum_row - $no_dds_row))    , -15 );

                    $smetkaID      = substr( $strSpace.$docRow['work_flow_acc'], -26);
                    if( $type == '01' ) { // Ако е в брой SET-ваме каса
                        $kasa      = substr( $strSpace.$docRow['work_flow_acc_paydesk'], -25);
                    } else {
                        $kasa = '';
                    }

                    if ($docRow['is_dds'] != 1){
                        $content .= $firstOfficeID ."".$date.$num.$date."          20.00".$new_dds_row."".$no_dds_row."".$sum_row."".$client_name.$client_ein.$client_dds."".$type."0101".$service_name."   411".$smetkaID.$kasa."    \r\n";
                    }

				}
			}
			
			$content = iconv("UTF-8", "CP1251", $content);

			$dir_name = "../export_docs/";

			if ( !file_exists($dir_name) ) {
				if ( @mkdir($dir_name, 0777) === FALSE) {
					throw new Exception("Грешка при създаване на директория!", DBAPI_ERR_INVALID_PARAM);
				}
			}
			
			$filename = "sale_".date("YmdHi", strtotime($from))."_".date("YmdHi", strtotime($to)).".txt";

		    if ( $handle = @fopen($dir_name.$filename, 'w') ) {
			   if ( @fwrite($handle, $content) === FALSE) {
					throw new Exception("Грешка при опит за запис във файл!", DBAPI_ERR_INVALID_PARAM);
			   }
		    }	
		    
		    @fclose($handle);
			
		    $oResponse->setAlert( "Бяха създадени ".$count." записа във ".$filename );
		    //print("<script> alert('Бяха създадени {$count} записа във {$filename}); </script>");

			return 0;
		}
		
		public function DeleteFile( $file ) {
			$dir_name = "../export_docs/";
			
			if ( @unlink($dir_name.$file) === FALSE) {
				throw new Exception("Грешка при опит изтриване на файл!", DBAPI_ERR_INVALID_PARAM);
			}
		}
		
		public function ViewFile( $file ) {
			$dir_name = "../export_docs/";
			$content = "";


			if ( !file_exists($dir_name.$file) ) {
				throw new Exception("Грешка при отваряне на файл!", DBAPI_ERR_INVALID_PARAM);
			}
			
			
		    if ( $handle = @fopen($dir_name.$file, 'r') ) {
				while (!feof($handle)) {
					$content .= @fread($handle, 8192);
				}			   
		    }	
		    
		    @fclose($handle);
		    
		    header("Cache-Control: public, must-revalidate");
			header("Pragma: hack"); 
			//header("Content-Type: text/plain");
			header("Content-Length: ".(string)(filesize($dir_name.$file)) );
			header('Content-Disposition: attachment; filename="'.$dir_name.$file.'"');
			header("Content-Transfer-Encoding: binary\n");
			readfile($dir_name.$file);
		    
		   // APILog::Log(0, $content);
		}		
		
		public function getReport( $buy = 0, DBResponse $oResponse )	{

			
			$dir_name = "../export_docs/";
			$aData = array();
			
			if ( $handle = @opendir($dir_name) ) {

			   while ( false !== ($file = @readdir($handle)) ) {
			       $aData[]['file'] = $file;
			   }
			
			   @closedir($handle);
			}
			
			rsort($aData);
			reset($aData);
			
			foreach ( $aData as $key => &$val ) {
				if ( filetype($dir_name.$val['file']) == "file" ) {
					if ( ($buy == 1) && (substr($val['file'], 0, 3) == "buy") ) {
						$val['id'] 	 = $dir_name.$val['file'];
						$val['size'] = filesize($dir_name.$val['file']);
						$val['date'] = date( "d.m.Y H:i:s", filemtime($dir_name.$val['file']) );
					} elseif ( ($buy == 0) && (substr($val['file'], 0, 4) == "sale") ) {
						$val['id'] 	 = $dir_name.$val['file'];
						$val['size'] = filesize($dir_name.$val['file']);
						$val['date'] = date( "d.m.Y H:i:s", filemtime($dir_name.$val['file']) );						
					} else unset($aData[$key]);
				} else {
					unset($aData[$key]);
				}				
			}
			
			//APILog::Log(0, $aData);
			$nRowTotal = count($aData);
			$oParams = Params::getInstance();
			
			$nPage = $oParams->get("current_page", 1);
			
			$nRowCount  = $_SESSION['userdata']['row_limit'];
			$nRowOffset = ($nPage-1) * $nRowCount;
			
			$newArray = array_splice($aData, $nRowOffset, $nRowCount);
			
			$oResponse->setData( $newArray );	
			
			$bLimited = !empty( $nPage );
			
			if ( $bLimited ) {
				$oResponse->setPaging($nRowCount, $nRowTotal, ceil($nRowOffset / $nRowCount) + 1);
			}			
			
			foreach ( $oResponse->oResult->aData as $key => &$aRow ) {
				if ( $aRow['size'] == 0 ) {
					$oResponse->setDataAttributes( $key, 'date', array("style" => "width: 120px; text-align: center; background: #FFE8E8;", "nowrap" => "nowrap") );
					$oResponse->setDataAttributes( $key, 'size', array("style" => "width: 70px; text-align: right; background: #FFE8E8;") );
					$oResponse->setDataAttributes( $key, 'file', array("style" => "width: 80%; text-align: left; background: #FFE8E8;") );
				} else {
					$oResponse->setDataAttributes( $key, 'date', array("style" => "width: 120px; text-align: center;", "nowrap" => "nowrap") );
					$oResponse->setDataAttributes( $key, 'size', array("style" => "width: 70px; text-align: right;") );					
					$oResponse->setDataAttributes( $key, 'file', array("style" => "width: 80%; text-align: left;") );
				}
			}			
			
			$oResponse->setField("date", "Дата", "");
			$oResponse->setField("size", "Големина", "");
			$oResponse->setField("file", "Файл", "");
			
			$oResponse->setFieldLink("date", "viewFile");
			$oResponse->setFieldLink("size", "viewFile");
			$oResponse->setFieldLink("file", "viewFile");
			
			//if ($right_edit) {
				$oResponse->setField( "id", "", "", "images/glyphicons/row.delete.png", "delFile", "");
			//}
		}		

		
		
		
		public function ExportBuyDocs( $aData, $oResponse ) {
			global $db_finance, $db_name_personnel, $db_name_finance, $db_name_sod;
			
			$nIDOffice = isset($aData['nIDOffice']) && is_numeric($aData['nIDOffice']) ? $aData['nIDOffice'] : 0;
			$nIDFirm = isset($aData['nIDFirm']) && is_numeric($aData['nIDFirm']) ? $aData['nIDFirm'] : 0;
			$from = isset($aData['from']) ? $aData['from'] : 0;
			$to = isset($aData['to']) ? $aData['to'] : 0;
			$where = "";
			
			if ( !empty($nIDOffice) ) {
				$where .= " AND o.id = {$nIDOffice} ";
			} elseif ( !empty($nIDFirm) ) {
				$oFirm = new DBOffices();
				$aFirm = $oFirm->getOfficesIDByFirm( $nIDFirm );
				$where .= " AND o.id IN ({$aFirm}) ";
			}
			
			if ( !empty($from) ) {
				$where .= " AND mp.doc_date >= '{$from}' ";
			}
			
			if ( !empty($to) ) {
				$where .= " AND mp.doc_date <= '{$to}' ";
			}			

			$tables2 = array();
			$tables = SQL_get_tables($db_finance, 'buy_docs_', '______');
			
			foreach ( $tables as $val ) {
				if ( substr($val, -6) >= date("Ym", strtotime($from)) && substr($val, -6) <= date("Ym", strtotime($to)) ) {
					$tables2[] = $val;
				}
				
			}
			
			//APILog::Log(0, $tables2);
			$sQuery = "";
			
			if ( count($tables2) > 1 ) {
				for ( $i = 0; $i < count($tables2) - 1; $i++ ) {
					$sQuery .= "
						(
							SELECT 
								mp.id,
								mp.doc_num,
								mp.paid_type,
								DATE_FORMAT(mp.doc_date, '%d.%m.%y') AS doc_date, 
								MONTH(mp.doc_date) as mnth,
								mp.note,
								mp.total_sum as sum,
								mp.client_ein,
								mp.client_mol,
								mp.client_ein_dds,
								mp.client_name,
								1 as br,
								o.name as office,
								f.name as firm,
								IF ( f.id = 3, 'Физическа охрана', IF (	f.id = 2, 'Охрана обект', '' ) ) as ohrana,
								o.work_flow_acc as work_flow
							FROM {$db_name_finance}.{$tables2[$i]} mp
							LEFT JOIN {$db_name_personnel}.personnel p ON p.id = mp.created_user
							LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
							LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm
							WHERE mp.doc_type = 'faktura'
								{$where}
						) 
						
						UNION
					";
				}
				
				$sQuery .= "
					(
						SELECT 
							mp.id,
							mp.doc_num,
							mp.paid_type,
							DATE_FORMAT(mp.doc_date, '%d.%m.%y') AS doc_date, 
							MONTH(mp.doc_date) as mnth,
							mp.note,
							mp.total_sum as sum,
							mp.client_ein,
							mp.client_mol,
							mp.client_ein_dds,
							mp.client_name,
							1 as br,
							o.name as office,
							f.name as firm,
							IF ( f.id = 3, 'Физическа охрана', IF (	f.id = 2, 'Охрана обект', '' ) ) as ohrana,
							o.work_flow_acc as work_flow
						FROM {$db_name_finance}.{$tables2[count($tables2)-1]} mp
						LEFT JOIN {$db_name_personnel}.personnel p ON p.id = mp.created_user
						LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
						LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm						
						WHERE mp.doc_type = 'faktura'
							{$where}
					)
				";
				
			} else {
				$sQuery .= "
					SELECT 
						mp.id,
						mp.doc_num,
						mp.paid_type,
						DATE_FORMAT(mp.doc_date, '%d.%m.%y') AS doc_date, 
						MONTH(mp.doc_date) as mnth,
						mp.note,
						mp.total_sum as sum,
						mp.client_ein,
						mp.client_mol,
						mp.client_ein_dds,
						mp.client_name,
						1 as br,
						o.name as office,
						f.name as firm,
						IF ( f.id = 3, 'Физическа охрана', IF (	f.id = 2, 'Охрана обект', '' ) ) as ohrana,
						o.work_flow_acc as work_flow
					FROM {$db_name_finance}.{$tables2[0]} mp
					LEFT JOIN {$db_name_personnel}.personnel p ON p.id = mp.created_user
					LEFT JOIN {$db_name_sod}.offices o ON o.id = p.id_office
					LEFT JOIN {$db_name_sod}.firms f ON f.id = o.id_firm						
					WHERE mp.doc_type = 'faktura'
						{$where}
				";
			}
			
			//APILog::Log(0, $sQuery);
			$aData = $db_finance->getArray( $sQuery );
			$content = "";
			$count = count($aData);
			
			foreach ( $aData as $value ) {
				$strSpace 	= str_repeat(" ", 200);
				
				$num 		= isset($value['doc_num']) ? sprintf("%010d",  $value['doc_num'] ) : sprintf("%010d",  0 );
				$type 		= isset($value['paid_type']) && $value['paid_type'] == "bank" ? 1 : 0;
				$date 		= isset($value['doc_date']) ? $value['doc_date'] : 0;
				$short 		= isset($value['mnth']) ? $value['mnth'] : 1;
				$note 		= isset($value['note']) ? $value['note'] : 0;
				$sum 		= isset($value['sum']) ? $value['sum'] : 0;
				$no_dds   	= substr(sprintf("%.2f", $sum/1.2).$strSpace, 0, 10);
				$new_dds  	= substr($sum - $no_dds.$strSpace, 0, 10);
				$client_ein = isset($value['client_ein']) ? $value['client_ein'] : 0;
				$client_mol = isset($value['client_mol']) ? $value['client_mol'] : 0;
				$client_dds = isset($value['client_ein_dds']) ? $value['client_ein_dds'] : 0;
				$client_name = isset($value['client_name']) ? $value['client_name'] : 0;
				$br 		= isset($value['br']) ? $value['br'] : 0;
                $new_type 	= "   U   ";
                $new_sell 	= "   P   ";	
                $product_price = sprintf("%.2f",$no_dds / $br);		
                $office		= isset($value['office']) ? $value['office'] : "";
                $firm		= isset($value['firm']) ? $value['firm'] : "";
                $ohrana 	= isset($value['ohrana']) ? $value['ohrana'] : "";
                $smetka		= isset($value['work_flow']) ? $value['work_flow'] : "";
                
				if ( $type == 1 ) {
					$content .= $num." @@ D @@ ".$num." @@ ".$date." @@ разход  @@ ".$no_dds." @@ 20 @@ ".$new_dds." @@ ".$sum." @@ C @@ ".$ohrana." @@ ".$type." @@ ".$firm." @@ ".$office." @@ Покупки-".$office." @@ L @@ ".$short." @@ 4 @@ ".$firm." @@ ".$client_ein." @@ ".$client_dds." @@ ".$client_name." @@ ".$num."\r\n";
					$content .= $num." @@ R @@ U @@ ".$note." @@ бр @@ ".$br." @@ ".$product_price." @@ ".$no_dds."\r\n";
					$content .= $num." @@ S @@ 411 @@ ".$smetka." @@ ".$no_dds."\r\n";
					$content .= $num." @@ S @@ 411 @@ 4532 @@ ".$new_dds."\r\n";
					$content .= $num." @@ S @@ 501-".$office." @@ 411 @@ ".$sum."\r\n";
				} else {
					$content .= $num." @@ D @@ ".$num." @@ ".$date." @@ разход  @@ ".$no_dds." @@ 20 @@ ".$new_dds." @@ ".$sum." @@ C @@ ".$ohrana." @@ ".$type." @@ ".$firm." @@ ".$office." @@ Покупки-".$office." @@ L @@ ".$short." @@ 4 @@ ".$firm." @@ ".$client_ein." @@ ".$client_dds." @@ ".$client_name." @@ ".$num."\r\n";
					$content .= $num." @@ R @@ U @@ ".$note." @@ бр @@ ".$br." @@ ".$product_price." @@ ".$no_dds."\r\n";
					$content .= $num." @@ S @@ 411 @@ ".$smetka." @@ ".$no_dds."\r\n";
					$content .= $num." @@ S @@ 411 @@ 4532 @@ ".$new_dds."\r\n";
					$content .= $num." @@ S @@ 501-".$office." @@ 411 @@ ".$sum."\r\n";
				}
			}
			
			$content = iconv("UTF-8", "CP1251", $content);

			$dir_name = "../export_docs/";

			if ( !file_exists($dir_name) ) {
				if ( @mkdir($dir_name, 0777) === FALSE) {
					throw new Exception("Грешка при създаване на директория!", DBAPI_ERR_INVALID_PARAM);
				}
			}
			
			$filename = "buy_".date("YmdHi", strtotime($from))."_".date("YmdHi", strtotime($to)).".txt";

		    if ( $handle = @fopen($dir_name.$filename, 'w') ) {
			   if ( @fwrite($handle, $content) === FALSE) {
					throw new Exception("Грешка при опит за запис във файл!", DBAPI_ERR_INVALID_PARAM);
			   }
		    }	
		    
		    @fclose($handle);

			$oResponse->setAlert( "Бяха създадени ".$count." записа във ".$filename );
			
			return 0;
		}			
		
	}
	
?>