<?php

class DBCodeLeave extends DBBase2
{
    public function __construct()
    {
        global $db_personnel;

        parent::__construct( $db_personnel, "code_leave" );
    }

    public function geLeaveType() {
        return array(
            'unpaid' => "Неплатен",
            'paid' => "Платен",
        );
    }

    public function getReport(DBResponse $oResponse)
    {
        $sQuery = "
				SELECT
				    SQL_CALC_FOUND_ROWS
					cl.id AS id,
					cl.name AS name,
					cl.clause_paragraph AS clause_paragraph,
					case cl.leave_type 
                       when 'unpaid' then 'Неплатен'
                       when 'paid' then 'Платен'
                    end as leave_type,
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

        $this->getResult($sQuery, 'cl.name', DBAPI_SORT_DESC, $oResponse);

        $oResponse->setField("name", "Наименование", "Сортирай по наименование",NULL,'openCodeLeave');
        $oResponse->setField("clause_paragraph", "Чл. и Ал.", "Сортирай по Чл. и Ал.");
        $oResponse->setField("leave_type", "Вид", "Сортирай по Вид");
        $oResponse->setField('is_due_leave','От год. полагаем отпуск','','images/confirm.gif');
        $oResponse->setField('updated_user','Последна редакция','','');

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

    public function getCodesLeave2()
    {
        $sQuery = "
				SELECT
					id AS id,
					CONCAT( name, ' (', clause_paragraph, ')' ) AS name
				FROM
					code_leave
				WHERE
					to_arc = 0
				ORDER BY
					name
			";

        return $this->selectAssoc( $sQuery );
    }


}

?>