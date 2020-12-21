<?php
	class DBMeasures extends DBBase2 
	{
		public function __construct()
		{
			global $db_storage;
			
			parent::__construct($db_storage, 'measures');
		}
		
		public function doesMeasureExist( $sMeasureCode )
		{
			if( empty( $sMeasureCode ) )return false;
			
			$sMeasureCode = addslashes( $sMeasureCode );
			
			$sQuery = "
					SELECT
						*
					FROM measures
					WHERE code = '{$sMeasureCode}'
					LIMIT 1
			";
			
			$aContent = $this->selectOnce( $sQuery );
			
			if( empty( $aContent ) )return false;
			else return true;
		}
		
		public function fixMeasureShortening( $sWhatToFix )
		{
			if( empty( $sWhatToFix ) )
			{
				return $sWhatToFix;
			}
			
			//Съкращението абсолютно отговаря.
			$sQuery = "
					SELECT
						code
					FROM measures
					WHERE code = '{$sWhatToFix}'
					LIMIT 1
			";
			
			$aContent = $this->selectOnce( $sQuery );
			
			if( !empty( $aContent ) )
			{
				return $sWhatToFix;
			}
			
			//Няма я точката, а в базата я има.
			$sQuery = "
					SELECT
						code
					FROM measures
					WHERE code = '{$sWhatToFix}.'
					LIMIT 1
			";
			
			$aContent = $this->selectOnce( $sQuery );
			
			if( !empty( $aContent ) )
			{
				return $sWhatToFix . '.';
			}
			
			//Има я точката, а в базата я няма.
			$sWhatToCheck = substr( $sWhatToFix, 0, strlen( $sWhatToFix ) - 1 );
			$sQuery = "
					SELECT
						code
					FROM measures
					WHERE code = '{$sWhatToCheck}'
					LIMIT 1
			";
			
			$aContent = $this->selectOnce( $sQuery );
			
			if( !empty( $aContent ) )
			{
				return $sWhatToCheck;
			}
			
			//Скрипта няма повече идеи.
			return $sWhatToFix;
		}
		
		public function getMeasures()
		{
			$sQuery = "
				SELECT 
					id,
					code,
					description
				FROM measures
				ORDER BY description
			";
			
			return $this->select( $sQuery );
		}
		
		public function getReport($aParams, DBResponse $oResponse )
		{
				$right_edit = false;
				if (!empty($_SESSION['userdata']['access_right_levels']))
					if (in_array('edit_measures', $_SESSION['userdata']['access_right_levels']))
					{
						$right_edit = true;
					}

				$sQuery = "
						SELECT SQL_CALC_FOUND_ROWS
							id, 
							code,
							description
						FROM measures
						";
				
				$this->getResult($sQuery, 'code', DBAPI_SORT_ASC, $oResponse);
				
				$oResponse->setField("code", 		"Код", 		"Сортирай по код");
				$oResponse->setField("description", "Единица", 	"Сортирай по единица");
				
				if ($right_edit) {
					$oResponse->setField( '', '', '', 'images/cancel.gif', 'deleteMeasure', '');
					$oResponse->setFieldLink("code", "openMeasure");
				}
		}
		
		
	}
?>