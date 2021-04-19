<?php

class DBWorkContracts extends DBBase2
{
    public function __construct()
    {
        global $db_personnel;

        parent::__construct( $db_personnel, 'work_contracts' );
    }

    function getWorkContracts( $nIDPerson )
    {
        if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();

        $sQuery = "
					SELECT
						w.id,
						w.num,
						DATE_FORMAT( w.date_contract, '%d.%m.%Y' ) AS date_contract
					FROM work_contracts w
					WHERE 1
						AND w.to_arc = 0
						AND w.id_person = {$nIDPerson}
					ORDER BY w.date_contract
			";

        return $this->select( $sQuery );
    }

    function getWorkContractData( $nID )
    {
        if( empty( $nID ) || !is_numeric( $nID ) )return array();

        $sQuery = "
					SELECT
						id,
						id_person,
						num,
						DATE_FORMAT( date_contract, '%d.%m.%Y' ) AS date_contract,
						DATE_FORMAT( date_today, '%d.%m.%Y' ) AS date_today,
						clause_paragraph,
						head_famaly,
						head_position,
						work_place,
						address,
						position,
						position_code,
						work_time_hours,
						salary,
						DATE_FORMAT( date_from, '%d.%m.%Y' ) AS date_from,
						year_leave_days,
						test_period,
						reward_optional,
						term_optional,
						time_optional,
						is_fulltime_work,
						is_on_night_schedule,
						work_period_type,
						notice_days
					FROM work_contracts
					WHERE 1
						AND to_arc = 0
						AND id = ($nID)
					LIMIT 1
			";

        return $this->selectOnce( $sQuery );
    }

    function getLastWorkContract( $nIDPerson )
    {
        if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();

        $sQuery = "
					SELECT
						id,
						num,
						date_from
					FROM work_contracts
					WHERE 1
						AND date_contract IN
						(
							SELECT
								MAX( date_contract ) AS date_from
							FROM work_contracts
							WHERE 1
								AND to_arc = 0
								AND id_person = {$nIDPerson}
							GROUP BY id_person
						)
                   # ORDER BY date_from DESC
			";

        return $this->selectOnce( $sQuery );
    }

    function getLastWorkContractsForSchedule( $nIDPerson )
    {
        if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) )return array();

        $sQuery = "
					SELECT
						id,
						num,
						date_from
					FROM work_contracts
					WHERE 1
                        AND to_arc = 0
                        AND id_person = {$nIDPerson}

                    ORDER BY date_from DESC
			";

        return $this->selectOnce( $sQuery );
    }

    function getPersonWorkingHoursByLastData( $nIDPerson )
    {
        if( empty( $nIDPerson ) || !is_numeric( $nIDPerson ) ) return 0;

        $sQuery = "
				SELECT
					inn.hours
				FROM
					(
						(
							SELECT
								wc.id,
								wc.id_person,
								wc.date_from AS date,
								'contract' AS type,
								wc.work_time_hours AS hours
							FROM
								work_contracts wc
							WHERE
								wc.id_person = {$nIDPerson}
								AND wc.to_arc = 0
						)
						UNION
						(
							SELECT
								wce.id,
								wce.id_person,
								wce.date_start AS date,
								'extend' AS type,
								wce.work_time_hours AS hours
							FROM
								work_contracts_extend wce
							WHERE
								wce.id_person = {$nIDPerson}
								AND wce.to_arc = 0
						)
					) AS inn
				ORDER BY date DESC, id DESC
				LIMIT 1
			";

        return $this->selectOne( $sQuery );
    }
}

?>