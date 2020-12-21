<?php

	class DBCodeLeave extends DBBase2 
	{
		public function __construct()
		{
			global $db_personnel;
			
			parent::__construct( $db_personnel, "code_leave" );
		}
		
		public function getResultData()
		{
			$sQuery = "
				SELECT
					cl.id AS id,
					cl.name AS name,
					cl.clause_paragraph AS clause_paragraph,
					cl.leave_type AS leave_type,
					cl.is_due_leave AS is_due_leave,
					IF
					(
						cl.updated_user != 0,
						CONCAT(
							CONCAT_WS( ' ', p.fname, p.mname, p.lname ),
							' [',
							DATE_FORMAT( cl.updated_time, '%d.%m.%Y %H:%i:%s' ),
							'] '
						),
						''
					) AS updated_user,
					cl.to_arc AS to_arc
				FROM
					code_leave cl
				LEFT JOIN
					personnel p ON p.id = cl.updated_user
				WHERE
					cl.to_arc = 0
			";
			
			return $this->select( $sQuery );
		}
		
		/**
		 * Връща подходящи данни за Flex Combobox.
		 *
		 * @return array
		 */
		public function getCodesLeave()
		{
			$sQuery = "
				SELECT
					id AS id,
					CONCAT( name, ' (', clause_paragraph, ')' ) AS label
				FROM
					code_leave
				WHERE
					to_arc = 0
				ORDER BY
					name
			";
			
			return $this->select( $sQuery );
		}
	}

?>