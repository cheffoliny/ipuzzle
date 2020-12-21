<?php
	include_once('pdfc.inc.php');
	
	class ContractPDF extends PDFC {
		
		function ContractPDF($orientation) {
			PDFC::PDFC($orientation);
		}

		function PrintReport( $nID, $nPages = '2' ) {
			
			$oDBContracts = new DBContracts();
			$oDBFaces = new DBFaces();
			$oDBContractsFaces = new DBContractsFaces();
			$oDBContractsServices = new DBContractsServices();
			
			$aContract = $oDBContracts->getInfoForPDF($nID);
			//if (!empty($aContract['id_obj'])) {
			//	$aFaces = $oDBFaces->getFaces($aContract['id_obj']);
			//}
			$aFaces = $oDBContractsFaces->getFaces( $nID );
			
			$aSMSVest = $oDBContractsServices->getSMSVest($nID);
			$aEmailVest	= $oDBContractsServices->getEmailVest($nID);
			$nOnlinePrice = $oDBContractsServices->getOnlinePrice($nID);
			$nMonthAccount = $oDBContractsServices->countMonthAccount($nID);
			$nOtherPrice = $oDBContractsServices->countOthers($nID); 
			
			$this->AddPage('P');
			
			$this->Image( $_SESSION['BASE_DIR'].'/images/title.png','','',200);
			
			$this->SetFont('FreeSans', '', 18);
			$this->SetXY(60,35);
			
			$this->Cell('','','ДОГОВОР ЗА ОХРАНА');
			
			$this->Ln(6);
			
			$this->SetFont('FreeSans', '', 12);
			$this->SetX(70);
			$this->Cell('15','',$aContract['contract_num']);
			$this->Cell('6','','на');
			$this->Cell('6','',$aContract['day']);
			$this->Cell('3','','/');
			$this->Cell('6','',$aContract['month']);
			$this->Cell('3','','/');
			$this->Cell('12','',$aContract['year']);
			
			$this->Ln(2);
			
			$this->moveX(70);
			$this->dottedLine(15);
			$this->moveX(6);
			$this->dottedLine(6);
			$this->moveX(3);
			$this->dottedLine(6);
			$this->moveX(3);
			$this->dottedLine(12);
			$this->moveX(45);
			$this->dottedLine(25);
			
			$this->Ln(2);
			
			$this->SetFont('FreeSans', '', 7);
			$this->SetX(73);
			$this->Cell('18','','номер');
			$this->Cell('8','','ден');
			$this->Cell('11','','месец');
			$this->Cell('10','','година');
			$this->moveX(50);
			$this->Cell('10','','служебен номер');
			
			$this->Line(10,60,10,280);
			$this->Line(10.5,60,10.5,280);
			
			$this->Line(110,60,110,280);
			$this->Line(110.5,60,110.5,280);
			
			
			$this->SetLeftMargin(11);
			
			// ------- ИЗПЪЛНИТЕЛ -------------
			
			$this->SetY(50);
			$this->Ln(10);
			$this->moveX(30);
			$this->SetFont('FreeSans', '', 10);
			$this->Cell(15,'',"ИЗПЪЛНИТЕЛ");
			
			$this->Ln(8);
			
			
			$this->SetFont('FreeSans', 'B', 8);	
			$this->Cell('22','','Фирма:',0,0,'R');
			$this->SetFont('FreeSans', '', 8);	
			$this->Cell('50','','"ИНФРА"');
			$this->Ln(4);
			$this->SetFont('FreeSans', 'B', 8);	
			$this->Cell('22','','Адрес:',0,0,'R');
			$this->SetFont('FreeSans', '', 8);	
			$this->Cell('40','','гр. Шумен ул. "Университетска" 13');
			$this->Ln(4);
			$this->SetFont('FreeSans', 'B', 8);	
			$this->Cell('22','','МОЛ:',0,0,'R');
			$this->SetFont('FreeSans', '', 8);	
			$this->Cell('50','','Любомир Ненчев Гочев');
			$this->Ln(4);
			
			
			$this->SetFont('FreeSans', 'B', 8);	
			$this->Cell('22','','Разпл. сметка:');
			$this->SetFont('FreeSans', '', 8);
			$this->Cell('20','','ОББ Шумен код: 20085415');
			$this->Ln(4);
			$this->moveX(22);
			$this->Cell('20','','IBAN    BG92UBBS854411010333309');

			$this->Ln(6);
			$this->Cell('80','',$aContract['rs_name']);
			$this->Cell('10','',$aContract['rs_code']);
			$this->Ln(2);
			$this->moveX(1);
			$this->dottedLine(90);
			$this->Ln(2);
			$this->SetFont('FreeSans', '', 6);
			$this->moveX(10);	
			$this->Cell('70','','рекламен сътрудник');
			$this->Cell('10','','код');
			
			
			// -------------- ИНФОРМАЦИЯ ЗА ОБЕКТА -------------
			
			$this->Ln(10);
			
			$this->moveX(17);
			$this->SetFont('FreeSans', '', 10);
			$this->Cell(15,'',"ИНФОРМАЦИЯ ЗА ОБЕКТА");	
			
			$this->Ln(7);
			$this->SetFont('FreeSans', '', 8);	
			
			$this->Cell(15,'','Регион:');
			$this->Cell(80,'',$aContract['obj_region']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(4);
			
			$this->Cell(15,'','Обект:');
			$this->Cell(80,'',$aContract['obj_name']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(2);
			$this->SetFont('FreeSans', '', 6);
			$this->moveX(20);	
			$this->Cell(15,'','дом, апартамент, магазин, склад ... име');
			
			$this->SetFont('FreeSans', '', 8);	
			
			$this->Ln(4);
			
			$this->Cell(15,'','Адрес:');
			$this->Cell(80,'',stripslashes($aContract['obj_address']));
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(4);
			
			$this->Cell(25,'','Тел на обекта:');
			$this->Cell(80,'',$aContract['obj_phone']);
			
			$this->Ln(2);
			$this->moveX(21);
			$this->dottedLine(70);	
			
			// ------- МАТЕРИАЛНО ОТГОВОРНИ ЛИЦА ------------------
			
			$this->Ln(7);
			$this->SetFont('FreeSans', 'B', 8);
			$this->moveX(10);	
			$this->Cell(15,'','Материално отговорни лица');
			$this->Ln(4);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(15,'','Име, Фамилия           Длъжност           Телефон');
			$this->SetFont('FreeSans', '', 8);
			$this->Ln(1);
			
			$this->Faces($aFaces,5);

			// -----------------------------------------------------
			
			$this->Ln(4);
			$this->Cell(40,'','Краен срок на изграждане:');
			if ($aContract['last_build_hour'] != '00:00') {
				$this->Cell('10','',$aContract['last_build_hour']);
			} else {
				$this->moveX(10);
			}
			$this->Cell('5','',$aContract['last_build_day']);
			$this->Cell('2','','/');
			$this->Cell('5','',$aContract['last_build_month']);
			$this->Cell('2','','/');
			$this->Cell('12','',$aContract['last_build_year']);
			$this->Ln(2);
			$this->moveX(40);
			$this->dottedLine(9);
			$this->moveX(1);
			$this->dottedLine(5);
			$this->moveX(1);
			$this->dottedLine(5);
			$this->moveX(1);
			$this->dottedLine(7);
			
			$this->ln(1.5);
			$this->moveX(41);
			$this->SetFontSize(6);
			$this->Cell('9','','час');
			$this->Cell('5','','ден');
			$this->Cell('8','','месец');
			$this->Cell('5','','година');
			$this->SetFontSize(8);
			
			$this->Ln(4);
			$this->Cell(40,'','Влиза в сила от:');
			if ($aContract['entered_hour'] != '00:00') {
				$this->Cell('10','',$aContract['entered_hour']);
			} else {
				$this->moveX(10);
			}
			$this->Cell('5','',$aContract['entered_day']);
			$this->Cell('2','','/');
			$this->Cell('5','',$aContract['entered_month']);
			$this->Cell('2','','/');
			$this->Cell('12','',$aContract['entered_year']);
			$this->Ln(2);
			$this->moveX(40);
			$this->dottedLine(9);
			$this->moveX(1);
			$this->dottedLine(5);
			$this->moveX(1);
			$this->dottedLine(5);
			$this->moveX(1);
			$this->dottedLine(7);
			
			$this->ln(1.5);
			$this->moveX(41);
			$this->SetFontSize(6);
			$this->Cell('9','','час');
			$this->Cell('5','','ден');
			$this->Cell('8','','месец');
			$this->Cell('5','','година');
			$this->SetFontSize(8);
			
			$this->Ln(4);
			$this->Cell('30','','Срок на договора:');
			$this->Cell('20','',$aContract['period_in_month']." месеца");
			$this->Ln(2);
			$this->moveX(27);
			$this->dottedLine(64);
			
			$this->Ln(4);
			$this->Cell('30','','Време за реагиране:');
			if(!empty($aContract['reaction_time_normal'])) {
				$this->Cell('3','',$aContract['reaction_time_normal']);
			} else {
				$this->moveX(3);
			}
			$this->Cell('10','','минути');
			$this->Ln(2);
			$this->moveX(29);
			$this->dottedLine(5);
			
			$this->Ln(4);
			$this->Cell('51','','Време за реагиране в зимни условия:');
			if(!empty($aContract['reaction_time_difficult'])) {
				$this->Cell('3','',$aContract['reaction_time_difficult']);
			} else {
				$this->moveX(3);
			}
			$this->Cell('10','','минути');
			$this->Ln(2);
			$this->moveX(51);
			$this->dottedLine(5);
			
			// ---------------- ДОГОВОРНА ОТГОВОРНОСТ ---------------			
			
			$this->Ln(6);
			$this->moveX(10);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(30,'','Договорна отговорност');
			$this->SetFont('FreeSans', '', 8);
			
			$this->Ln(5);
			$this->Cell('20','','Еднократно:');
			if( !empty($aContract['single_liability'])) {
				$this->Cell($this->GetStringWidth($aContract['single_liability'])+2,'',$aContract['single_liability']);
				$this->Cell('10','','лева');
			} else {
				$this->moveX(20);
			}
			$this->Ln(2);
			$this->moveX(19);
			$this->dottedLine(71);
			
			$this->Ln(4);
			$this->Cell('20','','Годишно:');
			if( !empty($aContract['year_liability'])) {
				$this->Cell($this->GetStringWidth($aContract['year_liability'])+2,'',$aContract['year_liability']);
				$this->Cell('10','','лева');
			} else {
				$this->moveX(20);
			}
			$this->Ln(2);
			$this->moveX(19);
			$this->dottedLine(71);
			
			$this->Ln(4);
			if ($aContract['is_invoice']) {
				$this->Cell('20','','- с фактура');
			}
			$this->Ln(4);
			if ($aContract['pay_cash']) {
				$this->Cell('20','','- плащане в брой');
			}
			$this->Ln(4);
			if ($aContract['pay_bank']) {
				$this->Cell('20','','- плащане по банков път');
			}
			
			// --------------- ТЕХНИКА ---------------------------
			
			$this->Ln(5);
			$this->moveX(10);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(30,'','Техника');
			$this->SetFont('FreeSans', '', 8);
			
			$this->Ln(4);
			
			switch ($aContract['technics_type']) {
				case 'rent': $this->Cell('20','','под наем');break;
				case 'buy': $this->Cell('20','','закупена');break;
				case 'owner': $this->Cell('20','','собствена');break;
				default: $this->moveX(20);
			}
		
			//----------------------------------------------------------------
			
			$this->SetY(270);
			
			$this->Cell(20,'','Възложител:');
			$this->Ln(2);
			$this->moveX(21);
			$this->dottedLine(70);
			$this->Ln(4);
			$this->Cell(20,'','Изпълнител:');
			$this->Ln(2);
			$this->moveX(21);
			$this->dottedLine(70);
			
			
			
			// --------------------------------------------------------------
			
			//                ВТОРА КОЛОНА
			
			// --------------------------------------------------------------
			
			// -------- ВЪЗЛОЖИТЕЛ ----------------
			$this->SetLeftMargin(112);
			$this->SetY(50);
			$this->Ln(10);
			
			$this->moveX(35);
			$this->SetFont('FreeSans', '', 10);
			$this->Cell(10,'',"ВЪЗЛОЖИТЕЛ");		
			
			$this->Ln(8);
			$this->SetFont('FreeSans', '', 8);	
			
			$this->Cell(15,'','Име:');
			$this->Cell(80,'',$aContract['client_name']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);		
			
			$this->Ln(4);
			
			$this->Cell(15,'','Адрес:');
			$this->Cell(80,'',stripslashes($aContract['client_address']));
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(4);
			$this->Cell(15,'','ЕИН:');
			$this->Cell(10,'',$aContract['client_dn']);
			$this->moveX(11);	
			$this->Cell(15,'','ЕИН ДДС:');
			$this->Cell(10,'',$aContract['client_bul']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(25);	
			$this->moveX(14);
			$this->dottedLine(40);		
			
			$this->Ln(4);
			
			$this->Cell(15,'','МОЛ:');
			$this->Cell(80,'',$aContract['client_mol']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);
			
			$this->Ln(4);
			
			$this->Cell(15,'','ЕГН:');
			$this->Cell(80,'',$aContract['client_egn']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);
			
			$this->Ln(4);
			
			$this->Cell(35,'','Тел. на възложителя:');
			
			$this->Cell(50,'',$aContract['client_phone']);
			
			$this->Ln(2);
			$this->moveX(35);
			$this->dottedLine(56);
			
			$this->Ln(4);
				
			$this->Cell(15,'','Email:');
			$this->Cell(80,'',$aContract['client_email']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);
			
			//------------- ПРЕДМЕТ НА ДОГОВОРА ------------------------------------
			
			$this->Ln(10);
			
			$this->moveX(10);
			$this->SetFont('FreeSans', '', 10);
			$this->Cell(10,'',"ПРЕДМЕТ НА ДОГОВОРА");
			$this->Ln(5);
			
			$this->Ln(3);
			$this->SetFont('FreeSans', 'B', 8);
			
			switch ($aContract['contract_type']) {
				case 'mdo': $this->Cell('30','','Месечна денонощна охрана');break;
				case 'mon': $this->Cell('30','','Мониторинг на обект');break;
			}
					
			$this->Ln(5);
			$this->Cell('30','','Паник функция');
			$this->SetFont('FreeSans', '', 8);
			$this->Ln(4);
			$this->moveX(5);
			$this->Cell('5','',$aContract['panic_stat_count']);
			$this->Cell('65','','броя стационарни бутона');
			if ( !empty( $aContract['panic_stat_count'] ) ) {
				$this->Cell('10','',$aContract['panic_stat_price']." лв");
			}
			$this->Ln(2);
			$this->moveX(5);
			$this->dottedLine(6);
			$this->moveX(60);
			$this->dottedLine(20);
			$this->Ln(3);
			$this->moveX(5);
			$this->Cell('5','',$aContract['panic_radio_count']);
			$this->Cell('65','','броя радио бутона');
			if ( !empty ( $aContract['panic_radio_count'] ) ) {
				$this->Cell('10','',$aContract['panic_radio_price']." лв");
			}
			$this->Ln(2);
			$this->moveX(5);
			$this->dottedLine(6);
			$this->moveX(60);
			$this->dottedLine(20);
			
			$this->Ln(3);
			$this->moveX(5);
			$this->Cell('70','','от клавиатура');
			if (!empty($aContract['panic_kbd_count'])) {
				$this->Cell('10','',$aContract['panic_kbd_price']." лв");
			} else {
				$this->Cell('10','','не');
			}
			$this->Ln(2);
			$this->moveX(71);
			$this->dottedLine(20);
			
			
			$this->Ln(5);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell('30','','ВЕСТ');
			$this->SetFont('FreeSans', '', 8);
			
			if (!empty($aSMSVest)) {
				$this->ln(4);
				$this->moveX(5);
				$this->Cell('30','','ВЕСТ SMS       Потребители');
				$this->showSMSVest($aSMSVest,$aContract['price_telepol_vest']);
			}
			
			if( $nOnlinePrice ) {
				$this->ln(4);
				$this->moveX(5);
				$this->Cell('70','','ВЕСТ online');
				$this->Cell('10','',$nOnlinePrice." лв");
				$this->Ln(2);
				$this->moveX(71);
				$this->dottedLine(20);
			}
			
			if (!empty($aEmailVest)) {
				$this->ln(4);
				$this->moveX(5);
				$this->Cell('30','','ВЕСТ e-mail (безплатна услуга)');
				$this->showEmails($aEmailVest);
			}
			
			$this->SetY(205);
			
			$this->Ln(10);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(74,'','Месечна такса:');
			$this->SetFont('FreeSans', '', 8);
			if ( !empty( $nMonthAccount ) ) {
				$this->Cell(10,'',$nMonthAccount." лв");
			}
			$this->Ln(2);
			$this->moveX(71);
			$this->dottedLine(20);
			
			$this->Ln(4);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(35,'','Еднократни плащания:');
			$this->SetFont('FreeSans', '', 8);
			$this->Ln(4);
			$this->moveX(5);
			$this->Cell('20','','Вид поръчка:');
			
			switch ($aContract['build_type']) {
				case 'normal': 	$this->Cell('49','','Нормална'); 	$this->Cell('10','','0.00 лв');break;
				case 'fast':	$this->Cell('49','','Бърза');		$this->Cell('10','',$aContract['fast_order_price']." лв");break;
				case 'expres':	$this->Cell('49','','Експресна');	$this->Cell('10','',$aContract['expres_order_price']." лв");break;
				default: $this->moveX(49);
			}
			$this->Ln(2);
			$this->moveX(71);
			$this->dottedLine(20);
			
			
			$this->Ln(4);
			$this->moveX(5);
			$this->Cell(69,'','Други:');
			
			if( !empty($nOtherPrice) ) {
				$this->Cell(10,'',$nOtherPrice." лв");
			}
			$this->Ln(2);
			$this->moveX(71);
			$this->dottedLine(20);
			
			$this->Ln(4);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(73,'','Цена за техника:');
			$this->SetFont('FreeSans', '', 8);
			if ( $aContract['technics_price'] != '0.00' ) {
				$this->Cell('','',$aContract['technics_price']." лв");
			}
			$this->Ln(2);
			$this->moveX(71);
			$this->dottedLine(20);
			
			//----------------------------------------------------------------------
			$this->Ln(4);
			
			$this->SetFont('FreeSans', 'BU', 8);
			$this->Cell('20','','Забележки');
			$this->SetFont('FreeSans', '', 8);
			$this->Ln(5);
			$this->Cell('60','','1. Всички цени упоменати в този договор са');
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell('50','','с включен 20% ДДС');
			$this->SetFont('FreeSans', '', 8);
			$this->Ln(3);
			$this->Cell('50','','2. Правата и задълженията по този договор се уреждат с Общите ');
			$this->Ln(3);
			$this->moveX(3);
			$this->Cell('12','','условия');
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell('50','','за охрана на СОТ');
			$this->SetFont('FreeSans', '', 8);
			$this->Ln(3);
			$this->Cell('50','','3. Фирма ИНФРА ЕООД носи имуществена отговорност за помещенията');
			$this->Ln(3);
			$this->moveX(3);
			$this->Cell('50','','в които има монтирана Сигнално Охранителна Техника. Местото ');
			$this->Ln(3);
			$this->moveX(3);
			$this->Cell('50','','и вида е предварително уточнен с ВЪЗЛОЖИТЕЛЯ');
			$this->Ln(8);
			$this->SetFont('FreeSans', 'B', 10);
			$this->Cell('38','','Дежурни телефони: ');
			$this->SetFont('FreeSans', '', 10);
			$this->Cell('30','','МТел 0888 100011; БТК 0700');
			$this->Ln(4);
			$this->moveX(4);
			$this->Cell('30','','10004 ( на цената на един градски разговор )');
			
			//-----------------------------------------------------------------------------
			
			//					ВТОРА СТРАНИЦА
			
			//-----------------------------------------------------------------------------

			if($nPages == '2') {
			
				$this->AddPage('P');
				$this->SetLeftMargin(11);
				
				
				$this->SetY(20);
				$this->SetFont('FreeSans', 'B', 8);
				$this->Cell('10','','Информация към ПАТРУЛ:');
				$this->SetFont('FreeSans', '', 8);
				$this->Ln(5);
				$this->MultiCell('100','4',stripslashes($aContract['info_operativ']));
				
				$this->SetY(70);
				$this->SetFont('FreeSans', 'B', 8);
				$this->Cell('10','','Информация към ТЕХНИЦИ:');
				$this->SetFont('FreeSans', '', 8);
				$this->Ln(5);
				$this->MultiCell('100','4',stripslashes($aContract['info_tehnics']));
				
				$this->SetY(110);
				$this->Cell('30','','Брой детектори: '.$aContract['count_detectors']);
				
				$this->SetY(120);
				$this->SetFont('FreeSans', 'B', 8);
				$this->Cell('10','','Информация към СЧЕТОВОДСТВО:');
				$this->SetFont('FreeSans', '', 8);
				$this->Ln(5);
				$this->MultiCell('100','4',stripslashes($aContract['info_schet']));
				
				
				$this->SetLeftMargin(100);
				$this->SetY(20);
				$this->Cell('30','','Въвел в системата:');
				$this->Cell('30','',$aContract['entered_user']);
				$this->Ln(4);
				$this->Cell('30','','Посл. редактирал:');
				$this->Cell('30','',$aContract['updated_user']);
			}
		
			$this->SetDisplayMode('real');
			$this->Output();
		}
	}
?>