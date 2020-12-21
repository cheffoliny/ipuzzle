<?php
	$oDocument 	= new DBBase( $db_personnel, 'person_docs' );
	
	class MyHandler 
		extends APIHandler {
			
			function setFields($aParams) {
				global $oResponse;
				
			}			

			function Handler( $aParams ) {
				global $oResponse;
				$aData = array();
				//APILog::log(0, $aParams);

				switch( $aParams['api_action']) {
					case 'delete' : 
						isset($aParams['id_document']) ? $nID = (int) $aParams['id_document'] : $nID = 0;

						if( $nResult = $this->delDocument( $nID ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при премахването на записа!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
						$aParams['api_action'] = 'result';
					break;
					default :			
						if ( $nResult = $this->getReport( $aParams ) != DBAPI_ERR_SUCCESS ) {
							$oResponse->setError( $nResult, "Проблем при извличане на данните!", __FILE__, __LINE__ );
							print( $oResponse->toXML() );
							return $nResult;
						}
																		
						$oResponse->setField( 'date_in', 			'дата на издаване', 		'Сортирай по дата'  );
						$oResponse->setField( 'document', 			'тип на документа',			'Сортирай по документ' );
						$oResponse->setField( 'doc_num',			'номер на документа',		'Сортирай по номер на документ');
						$oResponse->setField( 'valid_from', 		'валидност от', 			'Сортирай по ралидност'  );
						$oResponse->setField( 'valid_to', 			'валидност до', 			'Сортирай по ралидност'  );
						$oResponse->setField( 'updated_user', 		'...', 						'Сортиране по последно редактирал', 'images/dots.gif' );
						
						if ( in_array('person_docs_edit', $_SESSION['userdata']['access_right_levels']) ) {
							$oResponse->setField( '', '', 'Изтрий', "images/cancel.gif", "delDocument", '');
							$oResponse->setFIeldLink( 'date_in',		'editDocument' );
							$oResponse->setFIeldLink( 'document',		'editDocument' );
							$oResponse->setFieldLink( 'doc_num',		'editDocument');
							$oResponse->setFIeldLink( 'valid_from',		'editDocument' );
							$oResponse->setFIeldLink( 'valid_to',		'editDocument' );
						}
						
						print( $oResponse->toXML() );
					break;
				}
			}
			
			function getReport( $aParams ) {
				$aWhere = array();
				
				$nID = (int) !empty( $aParams['id'] ) ? $aParams['id'] : 0;
					
				$aWhere[] = sprintf(" t.id_person = %d", $nID);
				$aWhere[] = " t.to_arc=0 ";
					
				if ( empty($aParams['sfield']) ) {
					$aParams['sfield'] = "date_in";
				}
									
				$sQuery = sprintf(" 
					SELECT 
						SQL_CALC_FOUND_ROWS 
						t.id AS _id, 
						t.id,
						DATE_FORMAT(t.date_in,'%%d.%%m.%%Y') AS date_in, 
						d.name AS document,
						t.doc_num,
						IF ( (UNIX_TIMESTAMP(t.valid_from) > 946677600), DATE_FORMAT(t.valid_from,'%%d.%%m.%%Y'), '-') AS valid_from,
						IF ( (UNIX_TIMESTAMP(t.valid_to) > 946677600), DATE_FORMAT(t.valid_to,'%%d.%%m.%%Y'), '-') AS valid_to,
						CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' (', DATE_FORMAT(t.updated_time,'%%d.%%m.%%y %%H:%%i:%%s'), ')') AS updated_user
					FROM 
						%s t 
						LEFT JOIN personnel as up on up.id = t. updated_user
						LEFT JOIN document_types d ON d.id = t.id_document
					", 
					$this->_oBase->_sTableName
				);
				APILog::Log(0,$sQuery);
				
				//echo $sQuery;
				return $this->_oBase->getReport( $aParams, $sQuery, $aWhere );
			}
			
			function delDocument( $nID ) {
				global $db, $oResponse, $oDocument;
				
				$nID = (int) $nID;
				//debug($nID);
				if( $nResult = $oDocument->toARC( $nID ) != DBAPI_ERR_SUCCESS ) {
					$oResponse->setError( $nResult, "Проблем при премахването на записа!", __FILE__, __LINE__ );
					print( $oResponse->toXML() );
				}
								
				return DBAPI_ERR_SUCCESS;
			}
			
		}

	$oHandler = new MyHandler( $oDocument, 'date_in', 'person_docs', 'Документи' );
		
	$oHandler->Handler( $aParams );
?>