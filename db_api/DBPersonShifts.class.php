<?php
	
	class DBPersonShifts
		extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct($db_personnel, 'person_shifts');
		}
		
		public function getReport($aParams, DBResponse $oResponse) {
			global $db_personnel;
			
			$right_edit = false;
			if ( !empty($_SESSION['userdata']['access_right_levels']) ) {
				if ( in_array('person_shifts_edit', $_SESSION['userdata']['access_right_levels']) ) {
					$right_edit = true;
				}
			}
			
			$sQuery = "
				SELECT SQL_CALC_FOUND_ROWS
					ps.id, 
					ps.code,
					TIME_FORMAT(ps.start, '%H:%i') AS start,
					TIME_FORMAT(ps.end, '%H:%i') AS end,
					ps.name,
					ps.description,
					CONCAT(CONCAT_WS(' ', up.fname, up.mname, up.lname), ' [', DATE_FORMAT(ps.updated_time, '%d.%m.%Y %H:%i:%s'), ']') AS updated_user
				FROM person_shifts ps
				LEFT JOIN personnel as up ON ps.updated_user = up.id
				WHERE 1
				AND ps.to_arc = 0
				";
			
			$this->getResult($sQuery, 'code', DBAPI_SORT_ASC, $oResponse);
	
			$oResponse->setField('code'			, 'код'					, 'сортирай по код'		);
			$oResponse->setField('name'			, 'име'					, 'сортирай по име'		);
			$oResponse->setField('start'		, 'от'					, 'сортирай по от'		);
			$oResponse->setField('end'			, 'до'					, 'сортирай по до'		);
			$oResponse->setField('description'	, 'описание'			, 'сортирай по описание');
			$oResponse->setField('updated_user' , '...', 				'Сортиране по последно редактирал', 'images/dots.gif' );
			//$oResponse->setField('updated_user'	, 'последно редактирал'	, 'сортирай по последно редактирал'	);
			if( $right_edit ) {
				$oResponse->setField( '',			'',					'', 'images/cancel.gif', 'delShifts', '');
			}
			
			$oResponse->setFIeldLink('code',	'editShifts' );
			$oResponse->setFIeldLink('name',	'editShifts' );
		}
	}
	
?>