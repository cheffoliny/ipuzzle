<?php
	$oDocument = New DBBase( $db_personnel, 'person_docs' );

	switch( $aParams['api_action']) {
		case "save" :
			saveDocument( $aParams );
		break;
		default :
			loadDocument( $aParams['id'] );
		break;
	}
	
	function saveDocument( $aParams ) {
		global $oDocument, $oResponse;
		
		if( empty( $aParams['id_person'] ) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Служитела не е вкаран в системата!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		
		if( empty( $aParams['date_in'] ) ) {
			$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете стойност на полето 'издаден на'!", __FILE__, __LINE__ );
			return DBAPI_ERR_INVALID_PARAM;
		}

		if( empty( $aParams['id_document'] ) ) {
			if( $nResult = getDocument( $aParams['document'], $cnt ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при получаване на информацията!", __FILE__, __LINE__ );
				return $nResult;
			}
			
			if ( $cnt > 0 ) {
				$aParams['id_document'] = $cnt;
			} else {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете ВАЛИДЕН тип на документа!", __FILE__, __LINE__ );
				return DBAPI_ERR_INVALID_PARAM;
			}
		}
				
		$aDocument = array();
		$aDocument['doc_num']		= $aParams['doc_num'];
		$aDocument['id'] 			= $aParams['id'];
		$aDocument['id_person'] 	= $aParams['id_person'];
		$aDocument['date_in'] 		= jsDateToTimestamp( $aParams['date_in'] );
		$aDocument['id_document'] 	= $aParams['id_document'];
		$aDocument['valid_from'] 	= jsDateToTimestamp( $aParams['valid_from'] );
		$aDocument['valid_to'] 		= jsDateToTimestamp( $aParams['valid_to'] );
		$aDocument['note'] 			= $aParams['note'];

		if ( !empty($aDocument['valid_to']) ) {
			if ( empty($aDocument['valid_from']) ) {
				$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете валидност на документа!", __FILE__, __LINE__ );
				return DBAPI_ERR_INVALID_PARAM;
			} else {
				if ( $aDocument['valid_from'] > $aDocument['valid_to'] ) {
					$oResponse->setError( DBAPI_ERR_INVALID_PARAM, "Въведете коректни дати за валидност!", __FILE__, __LINE__ );
					return DBAPI_ERR_INVALID_PARAM;
				}
			}
		}
		
		if( $nResult = $oDocument->update( $aDocument ) != DBAPI_ERR_SUCCESS ) {
			$oResponse->setError( $nResult, "Проблем при съхраняване на информацията!", __FILE__, __LINE__ );
			return $nResult;
		}
		
		return DBAPI_ERR_SUCCESS;
	}
	
	function loadDocument( $nID ) {
		global $oDocument, $oResponse;
		
		$id = (int) $nID;
		
		if ( $id > 0 ) {
			// Редакция
			$aData = array();
			
			$qry = "
				t.id_document,
				d.name as document,
				DATE_FORMAT(t.date_in, '%d.%m.%Y') AS date_in,
				IF ( (UNIX_TIMESTAMP(t.valid_from) > 946677600), DATE_FORMAT(t.valid_from,'%d.%m.%Y'), '') AS valid_from,
				IF ( (UNIX_TIMESTAMP(t.valid_to) > 946677600), DATE_FORMAT(t.valid_to,'%d.%m.%Y'), '') AS valid_to,
				t.note,
				t.doc_num as doc_num
			";
			
			$join = "
				LEFT JOIN document_types d ON d.id = t.id_document
			";
			if( $nResult = $oDocument->getData( $qry, $join, $id, 't.id', $aData ) != DBAPI_ERR_SUCCESS ) {
				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
				print( $oResponse->toXML() );
				return $nResult;
			}
			
//			if( $nResult = $oDocument->getRecord( $id, $aData ) != DBAPI_ERR_SUCCESS ) {
//				$oResponse->setError( $nResult, "Проблем при комуникацията!", __FILE__, __LINE__ );
//				return $nResult;
//			}
			
			$oResponse->setFormElement('form1', 'doc_num', array(),		$aData['doc_num']);
			$oResponse->setFormElement('form1', 'date_in', array(),		$aData['date_in']);
			$oResponse->setFormElement('form1', 'document', array(),	$aData['document']);
			$oResponse->setFormElement('form1', 'id_document', array(),	$aData['id_document']);
			$oResponse->setFormElement('form1', 'valid_from', array(),	$aData['valid_from']);
			$oResponse->setFormElement('form1', 'valid_to', array(),	$aData['valid_to']);
			$oResponse->setFormElement('form1', 'note', array(),		$aData['note']);

			//debug($aData);
		}
			
		return DBAPI_ERR_SUCCESS;
	}
	
	function getDocument( $nData, &$cnt ) {
		global $db_personnel, $oResponse;
		
		$cnt = 0;
		$data = array();
		
		if ( !empty($nData) ) {
			
			$qry = "
				SELECT id
				FROM document_types
				WHERE UPPER(name) = UPPER('{$nData}')
				LIMIT 1
			";

			$oRes = $db_personnel->Execute( $qry );
			
			if( !$oRes ) {
				APILog::Log( DBAPI_ERR_SQL_QUERY, $db_personnel->errorMsg(), __FILE__, __LINE__ );
				APILog::Log( DBAPI_ERR_SQL_QUERY, $qry, __FILE__, __LINE__ );
				
				return DBAPI_ERR_SQL_QUERY;
			}
			
			if ( !$oRes->EOF ) {
				$data = $oRes->fields;
			}
				
			//APILog::Log(0, $data);
			$cnt = $data['id'];
		}
		
		return DBAPI_ERR_SUCCESS;
	}
		
	print( $oResponse->toXML() );
?>