<?php
	class DBAccessLevel extends DBBase2
	{
		public function __construct()
		{
			global $db_system;
			
			parent::__construct( $db_system, 'access_level' );
		}
		
		public function getAccessLevels()
		{
			$sQuery = "
				SELECT
					name,
					description
				FROM access_level
				ORDER BY description
			";
			
			return $this->select( $sQuery );
		}
		
		public function getAccessRightsReport( $aParams, DBResponse $oResponse )
		{
			global $db_name_personnel, $db_name_sod;
			
			$right_edit = false;
			if( !empty( $_SESSION['userdata']['access_right_levels'] ) )
				if( in_array( 'access_levels_edit', $_SESSION['userdata']['access_right_levels'] ) )
				{
					$right_edit = true;
				}
			
			//Getting Row Data
			$sQuery = "";
			if( isset( $aParams['nByProfile'] ) )
			{
				$oResponse->setFormElement( "form1", "sResultType", array( "value" => "Profile" ) );
				$sQuery = "
						SELECT SQL_CALC_FOUND_ROWS
							ap.id AS id,
							ap.id AS id_profile,
							ap.name AS field_text
						FROM access_profile ap
						ORDER BY field_text
				";
			}
			if( isset( $aParams['nByPerson'] ) )
			{
				$oResponse->setFormElement( "form1", "sResultType", array( "value" => "Person" ) );
				$sQuery = "
						SELECT SQL_CALC_FOUND_ROWS
							aa.id AS id,
							ap.id AS id_profile,
							CONCAT_WS( ' ', p.fname, p.mname, p.lname, '(', aa.username, ')' ) AS field_text,
							IF
							(
								po.id,
								po.name,
								'Не е зададена!'
							) AS position
						FROM access_account aa
							LEFT JOIN {$db_name_personnel}.personnel p ON p.id = aa.id_person
							LEFT JOIN {$db_name_personnel}.positions po ON po.id = p.id_position
							LEFT JOIN {$db_name_sod}.offices of ON of.id = p.id_office
							LEFT JOIN {$db_name_sod}.firms f ON f.id = of.id_firm
							LEFT JOIN access_profile ap ON ap.id = aa.id_profile
						WHERE 1
							AND aa.to_arc = 0
							AND p.to_arc = 0
				";
				
				if( isset( $aParams['nIDFirm'] ) && !empty( $aParams['nIDFirm'] ) )
				{
					$sQuery .= "
							AND f.id = {$aParams['nIDFirm']}
					";
				}
				if( isset( $aParams['nIDOffice'] ) && !empty( $aParams['nIDOffice'] ) )
				{
					$sQuery .= "
							AND of.id = {$aParams['nIDOffice']}
					";
				}
				if( isset( $aParams['nIDPosition'] ) && !empty( $aParams['nIDPosition'] ) )
				{
					$sQuery .= "
							AND po.id = {$aParams['nIDPosition']}
					";
				}
				
				$sQuery .= "
						ORDER BY field_text
				";
			}
			//End Getting Row Data
			
			$aRowData = $this->select( $sQuery );
			
			//Create Columns
			$oResponse->setField( "field_text", "Име", "" );
			if( isset( $aParams['nByPerson'] ) )
			{
				$oResponse->setFieldLink( "field_text", "setupAccount" );
				$oResponse->setField( "position", "Длъжност", "" );
			}
			if( isset( $aParams['nByProfile'] ) )
			{
				$oResponse->setFieldLink( "field_text", "setupProfile" );
			}
			
			foreach( $aParams['search_rights'] as $sColumn )
			{
				$aAccessLevel = $this->selectOnce( "SELECT description FROM access_level WHERE name='{$sColumn}' LIMIT 1" );
				$oResponse->setField( $sColumn, $aAccessLevel['description'], $sColumn, "images/confirm.gif" );
				
				if( $right_edit && isset( $aParams['nByProfile'] ) )
				{
					$oResponse->setFieldData( $sColumn, 'input', array( 'type' => 'checkbox', 'exception' => 'true', 'onclick' => 'changeLevel( this.id );' ) );
				}
			}
			//End Create Columns
			
			//Create Rows
			$aData = array();
			$nRowCount = 0;
			foreach( $aRowData as $nKey => $aRowElement )
			{
				//Get User Office Rights
				if( isset( $aParams['nByPerson'] ) )
				{
					$sQueryOffices = "
								SELECT
									IF
									(
										ao.id_office,
										of.id,
										0
									) AS id,
									f.name AS firm,
									of.name AS office
								FROM account_office ao
									LEFT JOIN {$db_name_sod}.offices of ON of.id = ao.id_office OR ao.id_office = 0
									LEFT JOIN {$db_name_sod}.firms f ON f.id = of.id_firm
									LEFT JOIN access_account aa ON aa.id = ao.id_account
								WHERE 1
									AND of.to_arc = 0
									AND aa.to_arc = 0
									AND aa.id = {$aRowElement['id']}
					";
					
					$aAccessOffices = $this->select( $sQueryOffices );
				}
				//End Get User Office Rights
				
				//Create Columns
				if( isset( $aParams['nByProfile'] ) && $aParams['nAllProfiles'] == 1 )$nSetUser = 1;
				else $nSetUser = 0;
				
				$aDataPrototype = array();
				
				//Form Office Information
				$sInfo = "Достъп до Региони:\n";
				foreach( $aAccessOffices as $aAccessOffice )
				{
					if( $aAccessOffice['id'] != 0 )
					{
						$sInfo .= "  Фирма \"{$aAccessOffice['firm']}\" - Регион \"{$aAccessOffice['office']}\"\n";
					}
					else
					{
						$sInfo .= "Всички";
						break;
					}
				}
				//End Form Office Information
				
				APILog::Log(0,$aParams);
				
				foreach( $aParams['search_rights'] as $sRight )
				{
					$nIsLevelActive = $this->IsRightInProfile( $sRight, $aRowElement['id_profile'] );
					$aDataPrototype[$nKey][$sRight] = $nIsLevelActive;
					
					if( $nIsLevelActive )
					{
						$oResponse->setDataAttributes( $nKey, $sRight, array( "title" => $sInfo, "style" => 'text-align: center;' ) );
						$nSetUser = 1;
					}
					else
					{
						$oResponse->setDataAttributes( $nKey, $sRight, array( "style" => 'text-align: center;' ) );
					}
				}
				if( $nSetUser )
				{
					$aData[$nKey]['id'] = $aRowElement['id'];
					$aData[$nKey]['field_text'] = $aRowElement['field_text'];
					if( isset( $aRowElement['position'] ) )
					{
						$aData[$nKey]['position'] = $aRowElement['position'];
					}
					$aData[$nKey] = array_merge( $aData[$nKey], $aDataPrototype[$nKey] );
					
					$nRowCount++;
				}
				//End Create Columns
			}
			//End Create Rows
			
			$oResponse->addTotal( "field_text", $nRowCount . " намерени" );
			$oResponse->setData( $aData );
		}
		
		public function IsRightInProfile( $sRight, $nIDProfile )
		{
			if( empty( $nIDProfile ) )return 0;
			
			//if($nIDProfile == 7) throw new Exception($sRight);
			
			$sQuery = "
					SELECT
						al.id as id_level,
						al.name AS level
					FROM access_level_profile alp
						RIGHT JOIN access_level al ON al.id = alp.id_level
					WHERE 1
						AND alp.id_profile = {$nIDProfile}
			";
			
			$aContent = $this->select( $sQuery );
			$nFoundElement = 0;
			foreach( $aContent as $aElement )
			{
				if( $sRight == $aElement['level'] )
				{
					$nFoundElement = 1;
					break;
				}
				/*if( $aElement['id_level'] == '0' )
				{
					$nFoundElement = 1;
					break;
				}*/
			}
			if(empty($aContent)) $nFoundElement = 1;
			
			return $nFoundElement;
		}
		
		public function getIDByName( $sName )
		{
			if( empty( $sName ) )return 0;
			
			$sQuery = "
					SELECT
						id
					FROM access_level
						WHERE name = '{$sName}'
					LIMIT 1
			";
			
			$aResult = $this->selectOnce( $sQuery );
			
			if( !empty( $aResult ) )
			{
				return $aResult['id'];
			}
			else return 0;
		}
	}
?>