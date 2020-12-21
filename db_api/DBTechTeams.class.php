<?php

class DBTechTeams extends DBBase2 {


    public function __construct()
    {
        global $db_sod;
        parent::__construct( $db_sod, 'tech_teams' );
    }

    public function getTeamByIDPerson($nIDPerson) {
        $sQuery = "

            SELECT
                id,
                id_person,
                access_services,
                team_num
            FROM tech_teams tt
            WHERE tt.id_person = {$nIDPerson}

        ";

        return $this->selectOnce($sQuery);
    }

    public function getMembersByTeamNum($teamNum , $nAssoc = true ) {
        $sQuery = "
            SELECT
                id,
                id_person,
                team_num,
                percent,
                team_num_entered,
                access_services
            FROM tech_teams tt
            WHERE tt.team_num = {$teamNum}
        ";

        if($nAssoc)
            return $this->selectAssoc($sQuery);
        else
            return $this->select($sQuery);
    }

    public function getTeamMate($nIDPerson) {

        global $db_name_personnel;

        $teamID = $this->getTeamByIDPerson($nIDPerson);

        if(empty($teamID)) {
            return array();
        }

        $sQuery = "
            SELECT
                tt.id,
                tt.id_person,
                tt.team_num,
                tt.access_services,
                tt.team_num_entered,
                CONCAT_WS(' ',p.fname,p.lname) as name
            FROM tech_teams tt
            LEFT JOIN {$db_name_personnel}.personnel p ON p.id = tt.id_person
            WHERE
            tt.team_num = {$teamID['team_num']}
            #AND tt.id_person != {$nIDPerson}
        ";

//        APILog::Log($sQuery);

        return $this->selectAssoc($sQuery);
    }

    public function deleteTeamByNum($teamNum) {
        $sQuery = "DELETE FROM tech_teams WHERE team_num = {$teamNum}";
        $this->select($sQuery);
    }

    public function getLastNum() {
        $sQuery = "SELECT max(team_num) as last_num FROM tech_teams tt";
        return $this->selectOnce($sQuery);

    }


    public function checkNumExsist($nTeamNum) {
        $sQuery = "SELECT * FROM tech_teams WHERE team_num_entered = {$nTeamNum}";
        $res = $this->selectAssoc($sQuery);
        return !empty($res) ? true : false;
    }

    public function getTeamByIDLimitCard($nIDLimitCard) {
        $sQuery = "
            SELECT
                tt.id_person as _id,
                tt.id_person,
                tt.access_services,
                tt.percent,
                tt.team_num_entered,
                tt.team_num
            FROM tech_teams tt
            WHERE tt.team_num =  (SELECT team_num FROM tech_teams WHERE id_person = (SELECT	id_person FROM limit_card_persons WHERE id_limit_card = {$nIDLimitCard} LIMIT 1))
        ";

        return $this->selectAssoc($sQuery);

    }

    public function updateEnteredNum($nTeamNumEntered , $nIDTeam) {

        $sQuery = "UPDATE tech_teams SET team_num_entered= {$nTeamNumEntered}  WHERE team_num = {$nIDTeam}";

        $this->select($sQuery);
    }


}