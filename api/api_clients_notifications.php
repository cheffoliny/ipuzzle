<?php

    class ApiClientsNotifications {
        public function result(DBResponse $oResponse) {

            $oDBNotification = new DBNotifications();

            $oDBNotification->getReport2($oResponse);

        }
    }

?>