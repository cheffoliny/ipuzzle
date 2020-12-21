<?php
	include_once('pdfc.inc.php');
	
	class ContractOldBasePDF extends PDFC {
		
		function ContractOldBasePDF($orientation) {
			PDFC::PDFC($orientation);
		}

		function PrintReport( $nID ) {
			

			$oDBObjects2 = new DBObjects2();
			$oDBFaces2 = new DBFaces2();
			$oDBServises = new DBServices();
			$oDBConfig134 = new DBConfig134();
			
			$aObject = $oDBObjects2->getObjectForContract($nID); 
			$aFaces = $oDBFaces2->getFaces($nID);
			$aTaxes = $oDBServises->getTaxesByIdObject($nID);
			$nNumContract = $oDBConfig134->getNum();
			$oDBConfig134->plusplusNum();
			
			
			foreach ( $aObject as $key => &$value ) {
				$value = trim($value);
				$value = iconv('cp1251','utf-8',$value);
			}
			foreach ( $aFaces as $key => &$value ) {
				$value['name'] = trim($value['name']);
				$value['phone'] = trim($value['phone']);
				$value['name'] = iconv('cp1251','utf-8',$value['name']);
				$value['phone'] = iconv('cp1251','utf-8',$value['phone']);
			}
			
			foreach ( $aTaxes as $key => &$value ) {
				$nSumTaxes += $value['price'];
				$value['name'] = trim($value['name']);
				$value['price'] = trim($value['price']);
				$value['name'] = iconv('cp1251','utf-8',$value['name']);
				$value['price'] = iconv('cp1251','utf-8',$value['price']);
				
				$value['price'] = round($value['price']*1.20,1);
			}
			
			$oldTax = round(($aObject['price'] - $nSumTaxes)*1.20,1);
			$nPrice = round(($aObject['price'])*1.20,1);
			
			$this->AddPage('P');
			
			$this->Image( $_SESSION['BASE_DIR'].'/images/title.png','','',200);
			
			$this->SetFont('FreeSans', '', 18);
			$this->SetXY(60,35);
			
			$this->Cell('','','ДОГОВОР ЗА ОХРАНА');
			
			$this->Ln(6);
			
			$this->SetFont('FreeSans', '', 12);
			$this->SetX(70);
			$this->Cell('15','',$nNumContract);
			$this->Cell('6','','на');
			$this->Cell('6','','01');
			$this->Cell('3','','/');
			$this->Cell('6','','01');
			$this->Cell('3','','/');
			$this->Cell('12','','2008');
			
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
			$this->Cell('50','','"ИНФРА ЕООД"');
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
			$this->Cell(80,'',$aObject['region_name']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(4);
			
			$this->Cell(15,'','Обект:');
			$this->Cell(80,'',$aObject['name']);
			
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
			$this->Cell(80,'',stripslashes($aObject['address']));
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(4);
			
			$this->Cell(25,'','Тел на обекта:');
			$this->Cell(80,'',$aObject['phone']);
			
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

			$this->Cell('10','','00:00');

			$this->Cell('5','','01');
			$this->Cell('2','','/');
			$this->Cell('5','','01');
			$this->Cell('2','','/');
			$this->Cell('12','','2008г.');
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
			$this->Cell('20','',"12 месеца");
			$this->Ln(2);
			$this->moveX(27);
			$this->dottedLine(64);
			
			$this->Ln(4);
			$this->Cell('30','','Време за реагиране:');

			if(!empty($aObject['time_react'])) {
				$this->Cell('10','',$aObject['time_react'].' минути');
			} else {
				$this->Cell('10','','3 минути');
			}
			$this->Ln(2);
			$this->moveX(29);
			$this->dottedLine(5);
			
			$this->Ln(4);
			$this->Cell('51','','Време за реагиране в зимни условия:');

			if(!empty($aObject['time_react'])) {
				$this->Cell('10','',round($aObject['time_react']*1.66).' минути');
			} else {
				$this->Cell('10','','5 минути');
			}
			
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
			if( !empty($aObject['single_otg'])) {
				$this->Cell($this->GetStringWidth($aObject['single_otg'])+2,'',$aObject['single_otg']);
				$this->Cell('10','','лева');
			} else {
				$this->moveX(20);
			}
			$this->Ln(2);
			$this->moveX(19);
			$this->dottedLine(71);
			
			$this->Ln(4);
			$this->Cell('20','','Годишно:');
			if( !empty($aObject['yearly_otg'])) {
				$this->Cell($this->GetStringWidth($aObject['yearly_otg'])+2,'',$aObject['yearly_otg']);
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
			$this->Cell(80,'',$aObject['firm_name']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);		
			
			$this->Ln(4);
			
			$this->Cell(15,'','Адрес:');
			$this->Cell(80,'',stripslashes($aObject['address_reg']));
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);	
			
			$this->Ln(4);
			$this->Cell(15,'','ЕИН:');
			$this->Cell(10,'',$aObject['tax_num']);
			$this->moveX(11);	
			$this->Cell(15,'','ЕИН ДДС:');
			$this->Cell(10,'',$aObject['bulstat']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(25);	
			$this->moveX(14);
			$this->dottedLine(40);		
			
			$this->Ln(4);
			
			$this->Cell(15,'','МОЛ:');
			$this->Cell(80,'',$aObject['face_name']);
			
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
			
			$this->Cell(50,'',$aObject['firm_phone']);
			
			$this->Ln(2);
			$this->moveX(35);
			$this->dottedLine(56);
			
			$this->Ln(4);
				
			$this->Cell(15,'','Email:');
			$this->Cell(80,'',$aObject['mail']);
			
			$this->Ln(2);
			$this->moveX(11);
			$this->dottedLine(80);
			
			//------------- ПРЕДМЕТ НА ДОГОВОРА ------------------------------------
			/*
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
				default: $this->Cell('30','','Такса');break;
			}
					
			$this->Cell(65,'',$oldTax);
			
			$this->SetFont('FreeSans', '', 8);
			$this->showPrihodiSlave( $aTaxes );
			*/
	
			$this->SetY(220);
			
			$this->Ln(10);
			$this->SetFont('FreeSans', 'B', 8);
			$this->Cell(74,'','Месечна такса:');
			$this->SetFont('FreeSans', '', 8);
			if ( !empty( $nPrice ) ) {
				$this->Cell(10,'',$nPrice." лв");
			}
			$this->Ln(2);
			$this->moveX(71);
			$this->dottedLine(20);
	
			
			$this->SetY(243);
			
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
			$this->Cell('50','','3. Фирма Инфра носи имуществена отговорност за помещенията');
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
			
		
			$this->SetDisplayMode('real');
			$this->Output();
		}
	}
?>