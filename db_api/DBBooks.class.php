<?php
	class DBBooks
		extends DBBase2 {
			
		public function __construct() {
			global $db_finance;
			//$db_finance->debug=true;
			
			parent::__construct($db_finance, 'books');
		}
		
		public function getRowByNum( $nNum ) {
			global  $db_name_finance;
			
			if ( empty($nNum) || !is_numeric($nNum) ) {
				return array();
			}
			
			$sQuery = "
				SELECT 
					`id`, 
					`num`, 
					`is_use`
				FROM {$db_name_finance}.books
				WHERE num = '{$nNum}'
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}
		
		public function checkNum( $nNum ) {
			global  $db_name_finance;
			
			if ( empty($nNum) || !is_numeric($nNum) ) {
				return 0;
			}
			
			$sQuery = "
				SELECT 
					`id`
				FROM {$db_name_finance}.books
				WHERE num = '{$nNum}'
				LIMIT 1
			";

			return $this->selectOne($sQuery);
		}	
		
		public function getHistoryByID( $nID ) {
			global  $db_name_finance;
			
			if ( empty($nID) || !is_numeric($nID) ) {
				return 0;
			}
			
			$sQuery = "
				SELECT 
					id,
					act,
					from_num,
					to_num,
					note
				FROM {$db_name_finance}.books_history
				WHERE id = '{$nID}'
				LIMIT 1
			";
			
			return $this->selectOnce($sQuery);
		}				
		
		public function getHistory( DBResponse $oResponse ) {
			global  $db_name_personnel, $db_name_finance;
			
			$sQuery = "	
				SELECT SQL_CALC_FOUND_ROWS 
					h.id as _id,
					h.id,
					CONCAT_WS(' ', p.fname, p.mname, p.lname) as person,
					CASE h.act
						WHEN 'add' THEN 'добави'
						WHEN 'delete' THEN 'изтри'
						WHEN 'set' THEN 'промени'
					END as action,
					h.from_num,
					h.to_num,
					h.note,
					UNIX_TIMESTAMP(h.updated_time) as dtime,
					CONCAT_WS(' ', p.fname, p.mname, p.lname, CASE h.act WHEN 'add' THEN 'добави' WHEN 'delete' THEN 'изтри' WHEN 'set' THEN 'промени' END, 'диапазон с номера: ', CONCAT(h.from_num, '-', h.to_num), 'на', DATE_FORMAT(h.updated_time, '%d.%m.%Y %H:%i:%s') ) as blah				
				FROM {$db_name_finance}.books_history h
				LEFT JOIN {$db_name_personnel}.personnel p ON p.id = h.id_person
			";
			
			$this->getResult($sQuery, 'dtime', DBAPI_SORT_DESC, $oResponse);
									
			$oResponse->setField("blah", "Действие", "Сортирай по действие");
			$oResponse->setField("note", "Бележка", "Сортирай по бележка");

			$oResponse->setField( 'id', '', '', 'images/edit.gif', 'books_set', 'Промени');
			$oResponse->setField( '', '', '', 'images/cancel.gif', 'books_del', 'Изтрий');
					
			foreach( $oResponse->oResult->aData as $key => &$val ) {
				//$oResponse->setDataAttributes( $key, 'blah', array('style' => 'text-align: center; width: 530px;') );				
			}
		}
	}
?>