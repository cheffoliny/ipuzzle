<?php
	DEFINE ('VALID_RESULT_OK',0);
	DEFINE ('VALID_RESULT_EMPTY',1);
	DEFINE ('VALID_RESULT_NOTINLIST',2);
	DEFINE ('VALID_RESULT_NOTINTYPE',3);

	DEFINE ('VALID_TYPE_NUM',1);
	DEFINE ('VALID_TYPE_FLOAD',2);
	DEFINE ('VALID_TYPE_DATE',3);

	DEFINE ('INFO_INFO_MSG',0);
	DEFINE ('INFO_ALERT_MSG',1);

	function GetImportedData( $files, &$aData )
	{
		$sFileTmpName  = $files['tmp_name'];
		$sFileType = $files['type'];
		
		$sError = "Непознат формат на файла !";

		switch ($sFileType){
			case 'application/x-zip-compressed' :
				$sError=DataFromZip(false, $sFileTmpName, $aData);
				break;

			default :
				$sError=DataFromXLS(false, $sFileTmpName, $aData);
				break;
		}
		
		return $sError;
	}

	function DataFromZip($sContent, $sFileName, &$aData){
			$import_handle = new SimpleUnzip();
			$import_handle->ReadFile($sFileName);

			if ($import_handle->Count() == 0)
				return "Не са открити файлове в архива!";

			if( $import_handle->GetError(0) != 0 ) 
				return "Грешка: ".$import_handle->GetErrorMsg(0);

			return DataFromXLS( $import_handle->GetData(0), $sFileName, $aData);
	}

	function DataFromXLS($sContent, $sFileName, &$aData){

		$excel = new Spreadsheet_Excel_Reader();

		// Изходен Ecoding
		$excel->setOutputEncoding('UTF-8');
		
		if( $res=$excel->read($sFileName, $sContent) != DBAPI_ERR_SUCCESS )
			return "Грешен формат на файла !";

		$aData = $excel->sheets[0];
		return "";
	}

	function ValidateData( &$Result, $Value, $aValues, $DefaultValue, $nValidateType  )
	{
		$Value = !is_null($Value) ? $Value : '';

		if( empty($Value) ) 
		{
			$Result=$DefaultValue;
			return VALID_RESULT_EMPTY;
		}
		if( (count($aValues)>0) ) 
		{
			if( in_array($Value,$aValues) ) 
			{
				$Result=$Value;
				return VALID_RESULT_OK;
			} else 
			{
				$Result=$DefaultValue;
				return VALID_RESULT_NOTINLIST;
			}
		}

		switch( $nValidateType ) 
		{
			case VALID_TYPE_NUM : 
									if( is_numeric( $Value ) ) 
									{
										$Result=$Value;
										return VALID_RESULT_OK;	
									} else 
									{
										$Result=$DefaultValue;
										return VALID_RESULT_NOTINTYPE;	
									}
									break;
			case VALID_TYPE_DATE : 
									if( preg_match ("/^(0[1-9]|[12][0-9]|3[01])(\/|.)(0[1-9]|1[0-2])(\/|.)([0-9]{4})$/", $Value) > 0 )
									{
										$Result=$Value;
										return VALID_RESULT_OK;	
									} else 
									{
										$Result=$DefaultValue;
										return VALID_RESULT_NOTINTYPE;	
									}
									break;
		}

		$Result=$Value;
		return VALID_RESULT_OK;	
	}
	
	function AddInformationMessage($sMsg, $nRow, $nCol, $nType) 
	{
		$sCell ='';
		$sCell .= !empty( $nRow ) ? ' ред : <b>'.$nRow.'</b>' : ''; 
		$sCell .= !empty( $nCol ) ? ' кол : <b>'.$nCol.'</b>' : ''; 

		$sCell .= !empty( $sCell ) ? ' -> ' : ''; 

		switch( $nType )
		{
			case  INFO_ALERT_MSG :	return "<font style='color:red;'>".$sCell.$sMsg."<br/>";
									break;

			default				 :	return $sMsg."<br/>";
									break;
		}
	}

?>