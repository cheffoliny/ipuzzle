
function dialogWinHelper(page, params, w, h, scroll, winname, resize) 
{
	var filter = page;
	
	winname = page + winname;
	
	if( params && params.length )
	{
		if( params.charAt(0) != '&' )
			filter += '&';
			
		filter += params;
	}

	dialog_win(filter, w, h, scroll, winname, resize);
}

function dialogNomenclatureEarning(id) {
	dialog_win('set_nomenclature_earning&id='+id,400,150,1,'set_nomenclature_earning');
}

function dialogNomenclatureExpense(id) {
	dialog_win('set_nomenclature_expense&id='+id,400,230,1,'set_nomenclature_expense');
}

function dialogNomenclatureService(id) {
	dialog_win('set_nomenclature_service&id='+id,400,265,1,'set_nomenclature_service');
}

function dialogNomenclaturesServicesFirms(id) {
	dialog_win('set_nomenclatures_services_firms&id='+id,800,305,1,'set_nomenclatures_services_firms');
}

function dialogNomenclaturesEarexpFirms(id) {
	dialog_win('set_nomenclatures_earexp_firms&id='+id,800,520,1,'set_nomenclatures_earexp_firm');
}

function dialogMoneyNomenclaturesOverview(firm, office, from, to) {
	dialog_win('view_money_nomenclatures_overview&nIDFirm='+firm+'&nIDOffice='+office+'&dFrom='+from+'&dTo='+to,1050,620,1);
}

function dialogMoneyNomenclaturesDetails(sParams) {
	dialog_win('view_money_nomenclatures_detail&' + sParams, 1050, 620, 1);
}

function dialogPdfDoc(id) {
	var _w = 1024;
	var _h = 600;
	
	var xMax = 800;
	var yMax = 600;

	if (document.all){ 
		xMax = screen.width;
		yMax = screen.height;
	} else {
		xMax = window.outerWidth;
		yMax = window.outerHeight;
	}

	var xOffset = (xMax - _w)/2;
	var yOffset = (yMax - _h)/2;
	if (yOffset > 30) yOffset = yOffset - 30; else yOffset = 0;

	resolveit = 'width=' + _w + ', height=' + _h + ', directories=0, hotkeys=0, location=0, menubar=0, resizable=0, screenX='+ xOffset +', screenY=' + yOffset +', scrollbars=1, status=0, toolbar=0, left=' + xOffset +', top=' + yOffset;

	//"location=1,status=1,scrollbars=1, width=" + _w + ",height=" + _h
	var nWin = window.open ("api/api_general.php?action_script=api/api_sale.php&api_action=gen_pdf2&rpc_version=2&nID="+id, "mywindow",resolveit);
	nWin.focus();
}

function dialogExcelDoc(id_firm, id_office, id_filter, month_from, month_to) {
	var _w = 100;
	var _h = 50;
	
	var xMax = 800;
	var yMax = 600;

	if (document.all){ 
		xMax = screen.width;
		yMax = screen.height;
	} else {
		xMax = window.outerWidth;
		yMax = window.outerHeight;
	}

	var xOffset = (xMax - _w)/2;
	var yOffset = (yMax - _h)/2;
	if (yOffset > 30) yOffset = yOffset - 30; else yOffset = 0;

	resolveit = 'width=' + _w + ', height=' + _h + ', directories=0, hotkeys=0, location=0, menubar=0, resizable=0, screenX='+ xOffset +', screenY=' + yOffset +', scrollbars=1, status=0, toolbar=0, left=' + xOffset +', top=' + yOffset;

	//"location=1,status=1,scrollbars=1, width=" + _w + ",height=" + _h
	var nWin = window.open ("api/api_general.php?action_script=api/api_incomings.php&api_action=gen_excel&rpc_version=2&id_firm="+id_firm+
		"&id_office="+id_office+"&id_filter="+id_filter+"&month_from="+month_from+"&month_to="+month_to,
		"mywindow",resolveit);
	nWin.focus();
}

function dialogExcelDocCollections(id_firm, id_office, id_filter, month_from, month_to) {
	var _w = 100;
	var _h = 50;
	
	var xMax = 800;
	var yMax = 600;

	if (document.all){ 
		xMax = screen.width;
		yMax = screen.height;
	} else {
		xMax = window.outerWidth;
		yMax = window.outerHeight;
	}

	var xOffset = (xMax - _w)/2;
	var yOffset = (yMax - _h)/2;
	if (yOffset > 30) yOffset = yOffset - 30; else yOffset = 0;

	resolveit = 'width=' + _w + ', height=' + _h + ', directories=0, hotkeys=0, location=0, menubar=0, resizable=0, screenX='+ xOffset +', screenY=' + yOffset +', scrollbars=1, status=0, toolbar=0, left=' + xOffset +', top=' + yOffset;

	//"location=1,status=1,scrollbars=1, width=" + _w + ",height=" + _h
	var nWin = window.open ("api/api_general.php?action_script=api/api_collections.php&api_action=gen_excel&rpc_version=2&id_firm="+id_firm+
		"&id_office="+id_office+"&id_filter="+id_filter+"&month_from="+month_from+"&month_to="+month_to,
		"mywindow",resolveit);
	nWin.focus();
}

function dialogExcelDocBudget(id_firm, id_office, id_filter, month_from, month_to) {
	var _w = 100;
	var _h = 50;
	
	var xMax = 800;
	var yMax = 600;

	if (document.all){ 
		xMax = screen.width;
		yMax = screen.height;
	} else {
		xMax = window.outerWidth;
		yMax = window.outerHeight;
	}

	var xOffset = (xMax - _w)/2;
	var yOffset = (yMax - _h)/2;
	if (yOffset > 30) yOffset = yOffset - 30; else yOffset = 0;

	resolveit = 'width=' + _w + ', height=' + _h + ', directories=0, hotkeys=0, location=0, menubar=0, resizable=0, screenX='+ xOffset +', screenY=' + yOffset +', scrollbars=1, status=0, toolbar=0, left=' + xOffset +', top=' + yOffset;

	//"location=1,status=1,scrollbars=1, width=" + _w + ",height=" + _h
	var nWin = window.open ("api/api_general.php?action_script=api/api_budget.php&api_action=gen_excel&rpc_version=2&id_firm="+id_firm+
		"&id_office="+id_office+"&id_filter="+id_filter+"&month_from="+month_from+"&month_to="+month_to,
		"mywindow",resolveit);
	nWin.focus();
}

function dialogBudgetArchive() {
	dialog_win('budget_archive',900,690,1,'budget_archive');
}

function dialogSalesDocsFilter(id) {
	dialog_win('sales_docs_filter&id='+id,900,690,1,'sales_docs_filter');
}

function dialogSalesPayOrders(id) {
	var str = '';
	
	if ( arguments.length == 2 ) {
		str = '&simplify=' +arguments[1];
	}
	
	dialog_win('sales_pay_orders&id='+id+str,400,180,1,'sales_pay_orders');
}

function dialogBuyDocsFilter(id) {
	dialog_win('buy_docs_filter&id='+id,800,585,1,'buy_docs_filter');
}

function dialogBuy() {
//	dialog_win('buy',1000,600,'buy');
//	vPopUp({ url: `page.php?page=buy_new&id=${id}`, name: `buy_new${id}`, width: 960, height: 660 })
	vPopUp({ url: `page.php?page=buy_new`, name: `buy_new`, width: 960, height: 660 })
}

function dialogBuy2(id) {
	//dialog_win('buy&id='+id,800,550,'buy');
	vPopUp({ url: `page.php?page=buy_new&id=${id}`, name: `buy_new${id}`, width: 960, height: 660 })
}
function dialogSale() {
//	dialog_win('sale',800,550,'sale');
	vPopUp({ url: `page.php?page=sale_new`, name: `sale_new`, width: 960, height: 660 })
}
function dialogSale2(id) {
	//dialog_win('sale&id='+id,800,550,'sale');
	vPopUp({ url: `page.php?page=sale_new&id=${id}`, name: `sale_new${id}`, width: 960, height: 660 })
}

function dialogSaleForObject(id) {
	//dialog_win('sale&id_object='+id,800,550,'sale');
	vPopUp({ url: `page.php?page=sale_new&id_object=${id}`, name: `sale_new_object${id}`, width: 960, height: 660 })
}

function dialogSaleFromBook() {
	//dialog_win('sale&from_book=true',800,550,'sale');
	vPopUp({ url: `page.php?page=sale_new&is_book=1`, name: 'sale_new_from_book', width: 960, height: 660 })
}

function dialogReceipt(id) {
	dialog_win('admin_services&receipt_id='+id,680,490,'admin_services');
}

function dialogBuyDocRow(id,id_buy_doc) {
	return dialog_win('buy_doc_row&id='+id+'&id_buy_doc='+id_buy_doc,470,330,'buy_doc_row');
}

function dialogSales() {
	//dialog_win('sales',1000,550,1,'sales');
	vPopUp({ url: `page.php?page=sale_new`, name: `sale_new`, width: 980, height: 660 })
}

function dialogSaleDocInfo(id) {
	dialog_win('sale_doc_info&id='+id,800,600,1,'sale_doc_info');
}

function dialogSaleDocInfo2(id) {
	var str = '';
	
	if ( arguments.length == 3 ) {
		str = '&make=' +arguments[1] + '&client=' + arguments[2];
	} else if ( arguments.length == 2 ) {
		str = '&make=' +arguments[1];
	} 
	
	dialog_win('sale_doc_info2&id='+id+str,800,600,1,'sale_doc_info2');
}

function dialogBuyDocInfo(id) {
	dialog_win('buy_doc_info&id='+id,800,400,1,'buy_doc_info');
}

function dialogWasteNote( id,id_ppp ) {
	dialog_win('waste_note&id='+id+'&id_ppp='+id_ppp,370,270,1,'waste_note');
}

function dialogStatesFilter(id) {
	dialog_win('states_filter&id='+id,400,400,'states_filter');
}

function dialogSetSalaryTicketImport() {
	dialog_win('set_salary_ticket_import',500,200,1,'set_salary_import_ticket');
}

function dialogAssetInfo( id ) {
	dialog_win('asset_info&id='+id,1000,380,1,'asset_info');
}

function dialogFirm( id ) {
	dialog_win('set_setup_firms&id='+id,460,475,1,'set_setup_firms');
}

function dialogPerson( id ) {
	dialog_win('personInfo&id='+id,1000,520,1,'personInfo');
}

function dialogPersonLeave( id ) {
	dialog_win('person_leave&id='+id,1000,520,1,'person_leave');
}

function dialogPersonSalary( id,sMonth,sYear ) {
	dialog_win('person_salary&id='+id+'&sMonth='+sMonth+'&sYear='+sYear,1000,520,1,'person_salary');
}

function dialogSalaryEarning( id ) {
	dialog_win('set_setup_salary_earning&id='+id,430,275,1,'set_setup_salary_earning');
}

function dialogSalaryExpense( id ) {
	dialog_win('set_setup_salary_expense&id='+id,380,205,1,'set_setup_salary_expense');
}

function dialogDocType( id ) {
	dialog_win('set_setup_document_types&id='+id,400,140,1,'set_setup_document_types');
}

function dialogRegion( id, id_f ) {
	dialog_win('set_setup_regions&id='+id+'&id_f='+id_f,650,650,1,'set_setup_regions');
}

function dialogObject( id, id_f, id_r ) {
	dialog_win('set_setup_objects&id='+id+'&id_f='+id_f+'&id_r='+id_r,380,180,1,'set_setup_objects');
}

function dialogObjectArchiv(id) {
	dialog_win('object_archiv&nID='+id,800,540,1,'object_archiv');
}

function dialogObjectMessages(id) {
    dialog_win('object_messages&nID='+id,800,540,1,'object_messages');
}


function dialogObjectStore( params, winname)
{
	dialogWinHelper('object_store', params, 800, 540, 1, winname);
}


function dialogUpload( id ) {
	dialog_win('upload_image&id='+id,460,130,1,'upload_image');
}

function dialogImportSlary( ) {
	dialog_win('import_salary',400,130,1,'import_salary');
}

function dialogNewSalary( id, id_person, month, year, type ) {
	var argv = dialogNewSalary.arguments;
	var argc = argv.length;
	
	var refresh = argc > 5 ? argv[5] : 0;
	var firm 	= argc > 6 ? argv[6] : 0;
	var office 	= argc > 7 ? argv[7] : 0;
	var code 	= argc > 8 ? argv[8] : '';
	
	return dialog_win('set_setup_person_salary_earning&id='+id+'&id_person='+id_person+'&month='+month+'&year='+year+'&type='+type+'&refresh='+refresh+'&firm='+firm+'&office='+office+'&code='+code,460,410,1,'set_setup_person_salary_earning');
}

function dialogImportSlaryGSM( id ) {
	dialog_win('import_salary_gsm&id='+id,400,160,1,'set_setup_person_salary_earning');
}

function dialogPosition( id ) {
	dialog_win('set_setup_positions&id='+id,380,165,1,'set_setup_positions');
}

function dialogPositionNC( id ) {
	dialog_win('set_setup_positions_nc&id='+id,380,175,1,'set_setup_positions_nc');
}

function dialogAssetsSettings( id ) {
	dialog_win('set_assets_settings&id='+id,380,150,1,'set_assets_settings');
}

function dialogLeave( id, person ) {
	dialog_win('set_setup_leave&id='+id+'&person='+person,300,185,1,'set_setup_leave');
}

function dialogSetupPersonLeave( id, person ) {
	dialog_win('setup_person_leave&id='+id+'&id_person='+person,500,300,1,'setup_person_leave');
}

function dialogApplication( id, person ) {
	dialog_win('set_setup_application_list&id='+id+'&id_person='+person,700,450,1,'set_setup_application_list');
}

function dialogSetApplication( id, person ) {
	dialog_win('set_setup_application&id='+id+'&id_person='+person,460,330,1,'set_setup_application');
}

function dialogAttestation( id, person ) {
	dialog_win('set_setup_attestation&id='+id+'&person='+person,500,300,1,'set_setup_attestation');
}

function dialogHospital( id, person ) {
	dialog_win('set_setup_hospital_list&id='+id+'&id_person='+person,700,450,1,'set_setup_hospital_list');
}

function dialogQuittance( id, person ) {
	dialog_win('set_setup_quittance_list&id='+id+'&id_person='+person,700,450,1,'set_setup_quittance_list');
}

function dialogSetHospital( id, person ) {
	dialog_win('set_setup_hospital&id='+id+'&id_person='+person,460,315,1,'set_setup_hospital');
}

function dialogSetQuittance( id, person ) {
	dialog_win('set_setup_quittance&id='+id+'&id_person='+person,460,330,1,'set_setup_quittance');
}

function dialogUploadCharacter( id ) {
	dialog_win('upload_character&id='+id,460,130,1,'upload_character');
}

function dialogNewDocument( id, person ) {
	dialog_win('set_setup_document&id='+id+'&person='+person,415,265,1,'set_setup_document');
}

function dialogActives( id ) {
	dialog_win('set_setup_actives&id='+id,400,145,1,'set_setup_actives');
}

function dialogPersonActives( id, person ) {
	dialog_win('set_setup_person_actives&id='+id+'&id_person='+person,700,450,1,'set_setup_person_actives');
}

function dialogPPP( id, person ) {
	dialog_win('set_setup_ppp&id='+id+'&id_person='+person,460,330,1,'set_setup_ppp');
}

function dialogMenu( id ) {
	dialog_win('admin_set_setup_menu&id='+id,380,240,1,'admin_set_setup_menu');
}

function dialogPassword( id ) {
	dialog_win('admin_access_account_pass&id='+id,340,205,1,'admin_access_account_pass');
}

function dialogSetupAccount( id ) {
	dialog_win('admin_set_setup_access_account&id='+id,485,400,1,'admin_set_setup_access_account');
}

function dialogSetupAccount2( params ) {
	dialog_win('admin_set_setup_access_account&'+params,485,400,1,'admin_set_setup_access_account');
}

function dialogTechSupportRequest(params, winname) {
	dialogWinHelper('tech_support_request', params, 500, 400, 1, winname);
}

function dialogLimitCard(id) {
	dialog_win('limit_card_info&nID='+id, 700, 520, 1, 'limit_card_info');
}

function dialogSetSetupAlarmReasons(params, winname) {
	dialogWinHelper('set_setup_alarm_reasons', params, 350, 130, 1, winname);
}

function dialogSetSetupPersonShifts(id) {
	dialog_win('set_setup_person_shifts&id='+id, 350, 280, 1, 'set_setup_person_shifts');
}

function dialogObjectInfo(params, winname) {
	dialogWinHelper('object_info', params,  800, 540, 1, winname);
}

function dialogObjectTaxes( params, winname)
{
	dialogWinHelper('object_taxes', params, 820, 480, 1, winname);
}

function dialogObjectContract( params, winname)
{
	dialogWinHelper('object_contract', params, 800, 540, 1, winname);
}

function dialogObjectShifts(params, winname) {
	dialogWinHelper('object_shifts', params,  800, 540, 1, winname);
}

function dialogObjectDuty(params, winname) {
	dialogWinHelper('object_duty', params,  800, 540, 1, winname);
}

function dialogObjectPersonnel(params, winname) {
	dialogWinHelper('object_personnel', params,  800, 540, 1, winname);
}

function dialogObjectPersonnelSchedule(params, winname) {
	dialogWinHelper('object_personnel_schedule', params,  800, 540, 1, winname);
}

function dialogSetObjectPerson(params, winname) {
	dialogWinHelper('set_object_personnel', params,  400, 300, 1, winname);
}

function dialogSetSetupObjectShifts(id, obj) {
	dialog_win('set_setup_object_shifts&id='+id+'&obj='+obj, 390, 380, 1, 'set_setup_object_shifts');
}

function dialogPatrulParking(id) {
	dialog_win('set_setup_patrul_parking&id='+id, 350, 275, 1, 'set_setup_patrul_parking');
}

function dialogSetupPatruls(id) {
	dialog_win('set_setup_patruls&id='+id, 350, 255, 1, 'set_setup_patruls');
}

function dialogSetSetupNomenclatureType(params, winname) {
	dialogWinHelper('set_setup_nomenclature_type', params, 400, 170, 1, winname);
}

function dialogSetSetupNomenclature(params, winname) {
	dialogWinHelper('set_setup_nomenclatures', params, 400, 205, 1, winname);
}

function dialogScheme(id)
{
	dialog_win('set_scheme&id='+id,712,360,1,'set_scheme');
}

function dialogPPP2( params ) {
	dialog_win('ppp&'+params, 800, 680, 1, 'ppp');
}

function dialogPPPElement( params ) {
	dialog_win('set_ppp_element&'+params, 480, 435, 1, 'set_ppp_element');
}

function dialogPatrol(id, idc) {
	dialog_win('set_setup_patrol&nID='+id+'&nIDCard='+idc, 700, 430, 1, 'set_setup_patrol');
}

function dialogStopRoadList(id) {
	dialog_win('set_stop_road_list&id='+id, 300, 165, 1, 'set_stop_road_list');
}

function dialgOpenFuelList(id) {
	dialog_win('set_open_fuel_list&id='+id, 300, 215, 1, 'set_open_fuel_list');
}

function dialogTechRequest(id) {
	dialog_win('set_setup_tech_request&id='+id, 390, 530, 1, 'set_setup_tech_request');
}

function dialogWCMoveAdd(id, nIDCard) {
	dialog_win('working_card_movement_add&id='+id+'&nIDCard='+nIDCard, 390, 240, 1, 'working_card_movement_add');
}

function dialogLimitCardPersons(id,lc) {
	dialog_win('set_limit_card_persons&nID='+id+'&nIDCard='+lc, 390, 220, 1, 'set_limit_card_persons');
}

function dialogChangeAmortization(id) {
	dialog_win('set_asset_info&nID='+id, 300, 200, 1, 'set_asset_info');
}

function dialogRequest( params ) {
	dialog_win('set_setup_request&'+params, 600, 550, 1, 'set_setup_request');
}

function dialogRequestNomenclature( params ) {
	dialog_win('setup_request_nomenclature&'+params, 370, 220, 1, 'setup_request_nomenclature');
}
function dialogSetupAutoMark(id) {
	dialog_win('set_auto_mark&id='+id, 300, 150, 1, 'set_auto_mark');
}
function dialogSetupAutoModel(id) {
	dialog_win('set_auto_model&id='+id, 250, 150, 1, 'set_auto_model');
}
function dialogSetupAuto(id) {
	dialog_win('set_auto&id='+id, 500, 320, 1, 'set_auto');
}
function dialogStoragehouse(id) {
	dialog_win('set_storagehouses&id='+id, 310, 410, 1, 'set_storagehouses');
}

function dialogTechSupport(id) {
	dialog_win('set_tech_settings&id='+id,310,205,1,'set_tech_settings');
}
function dialogSetupFace(id,id_obj) {
	dialog_win('setup_face&id='+id+'&id_obj='+id_obj,400,240,'setup_face');
}

function dialogSetSetupMeasure(params, winname) {
	dialogWinHelper('set_setup_measure', params, 350, 155, 1, winname);
}

function dialogObjectToContract(id) {
	dialog_win('object_to_contract&id='+id,360,170,'object_to_contract');
}

function dialogObjectToLimitCard(id,id_limit_card) {
		dialog_win('object_to_limit_card&id='+id+'&id_limit_card='+id_limit_card,360,200,'object_to_limit_card');
}

function dialogSetSetupMonthCharge(params, winname) {
	dialogWinHelper('set_setup_contract_month_charge', params, 450, 490, 1, winname);
}

function dialogSetSetupContracts(params, winname) {
	dialogWinHelper('set_setup_contracts', params, 450, 300, 1, winname);
}

function dialogSetSetupCompensation(params, winname) {
	dialogWinHelper('set_setup_compensation', params, 370, 230, 1, winname);
}

function dialogSetSetupTechPrice(params, winname) {
	dialogWinHelper('set_setup_tech_price', params, 450, 180, 1, winname);
}

function dialogSetSetupAssistant(params, winname) {
	dialogWinHelper('set_setup_assistant', params, 540, 440, 1, winname);
}

function dialogSetSetupCities(params, winname) {
	dialogWinHelper('set_setup_cities', params, 350, 160, 1, winname);
}

function dialogFixSalary( ) {
	dialog_win('fix_salary',400,130,1,'fix_salary');
}

function dialogMonitor(nCode) {
	dialog_win('object_monitor&id='+nCode, 800, 500, 1, 'object_monitor');
}

function dialogImportNomenclature( ) {
	dialog_win( 'import_nomenclatures', 400, 130, 1, 'import_nomenclatures' );
}

function dialogSetSetupSignalMessage(id, obj) {
	dialog_win('set_setup_signalMessage&nID='+id+'&nIDObj='+obj, 400, 420, 1, 'set_setup_signalMessage');
}

function dialogSetSetupSignalZone(id, obj) {
    dialog_win('set_setup_object_zone&nID='+id+'&nIDObject='+obj, 400, 200, 1, 'set_setup_object_zone');
}

function dialogSetSetupSignalSector(id, obj) {
    dialog_win('set_setup_object_sector&nID='+id+'&nIDObject='+obj, 400, 200, 1, 'set_setup_object_sector');
}

function dialogSetSetupObjectUsers(id, obj) {
    dialog_win('set_setup_object_user&nID='+id+'&nIDObject='+obj, 400, 200, 1, 'set_setup_object_user');
}

function dialogSalaryFirms(sMonth,sYear,nIDFirmFrom,nIDFirmTo) {
	dialog_win('salary_firms&sMonth='+sMonth+'&sYear='+sYear+'&nIDFirmFrom='+nIDFirmFrom+'&nIDFirmTo='+nIDFirmTo,1200,800,1,'salary_firms');
}

function dialogAdminSalaryTotalFilter(id) {
	dialog_win('admin_salary_total_filter&nID='+id,800,400,1,'admin_salary_total_filter');
}

function dialogVisibleTabs( id ) {
	dialog_win('setup_visible_tabs&id='+id,500,375,1,'setup_visible_tabs');
}

function dialogSetTechTiming( id ) {
	dialog_win('set_tech_timing&id='+id,420,150,1,'set_tech_timing');
}

function dialogSetObjectType( id ) {
	dialog_win('set_object_type&id='+id,320,125,1,'set_object_type');
}

function dialogSetObjectFunction( id ) {
	dialog_win('set_object_function&id='+id,320,155,1,'set_object_function');
}

function dialogSetSetupCityArea( id, id_city ) {
	dialog_win('set_setup_city_area&id='+id+'&id_city='+id_city,320,150,1,'set_setup_city_area');
}

function dialogSetSetupCityStreet( id, id_city ) {
	dialog_win('set_setup_city_street&id='+id+'&id_city='+id_city,320,150,1,'set_setup_city_street');
}

function dialogSetObjectStatus( id ) {
	dialog_win('set_object_status&id='+id,320,155,1,'set_object_status');
}

function dialogSetSetupTrouble(id, obj) {
	dialog_win('set_setup_trouble&nID='+id+'&nIDObj='+obj, 410, 380, 1, 'set_setup_trouble');
}
//
// function dialogObjectOldBase(id) {
// 	dialog_win('set_object_old_base&id='+id,340,450,1,'set_object_old_base');
// }
//
// function dialogSync( id, num ) {
// 	dialog_win('set_setup_sync&nID='+id+'&nNum='+num,700,450,1,'set_setup_sync');
// }

// function dialogTechNosignalSchemes( id ) {
// 	dialog_win('setup_tech_nosignal_schemes&id='+id,800,480,1,'setup_tech_nosignal_schemes');
// }
//
// function dialogRepeatSignalSchemes( id ) {
// 	dialog_win('setup_tech_repeat_signal_schemes&id='+id,800,480,1,'setup_tech_repeat_signal_schemes');
// }

function dialogSetHoldupReason( id ) {
	dialog_win('set_holdup_reason&id='+id,350,135,1,'set_holdup_reason');
}

function dialogSetLimitCardOperation( id ) {
	dialog_win('set_limit_card_operation&id='+id,300,150,1,'set_limit_card_operation');
}

function dialogSetTechOperation(id)
{
	dialog_win('set_tech_operation&id='+id,712,365,1,'set_tech_operation');
}

function dialogTechInstantSchemes( id ) {
	dialog_win('setup_tech_instant_schemes&id='+id,800,480,1,'setup_tech_instant_schemes');
}

function dialogObjectInfo2(params, winname) {
	dialogWinHelper('object_info', params,  800, 540, 1, winname);
}

function dialogPersonalCard( id ) {
	dialog_win('personal_card&nID='+id,800,600,1,'personal_card');
}

function dialogStopMovement( id ) {
	dialog_win('stop_movement&id='+id,300,240,1,'stop_movement');
}

function dialogMovementScheme( id ) {
	dialog_win('movement_scheme&id='+id,250,360,1,'movement_scheme');
}

function dialogAssetsPPP ( id, type ) {
	dialog_win('assets_ppp&id='+id+'&type='+type,1000,500,1,'assets_ppp');
}

function dialogAssetSearch (params) {
	dialog_win('asset_search&'+params,1000,500,1,'asset_search');
}

function dialogPrintContract ( type, id ) {
	dialog_win('person_contract_print&type='+type+'&id='+id, 640, 700, 'person_contract_print');
}

function dialogAssetStoragehouses ( id ) {
	dialog_win('set_assets_storagehouses&id='+id,300,240,1,'set_assets_storagehouses');
}

function dialogSetAttribute (id) {
	dialog_win('set_attribute&id='+id,320,300,'set_attribute');
}
function dialogSetGroup(id)
{
	dialog_win('set_asset_group&id='+id,400,160,'set_asset_group');
}
function dialogAssetsNomenclatures ( id ) {
	dialog_win('set_assets_nomenclatures&id='+id,700,380,1,'set_assets_nomenclatures');
}

// function dialogSyncObject(id, state) {
// 	dialog_win('manual_sync_object&nID='+id+'&stat='+state, 800, 600, 1, 'manual_sync_object');
// }

function dialogObjectSupport( params, winname ) {
	dialogWinHelper('object_support', params,  800, 400, 1, winname);
}

function dialogShiftHistory(id) {
	dialog_win('shiftHistory&id='+id, 650, 355, 1, 'shiftHistory');
}

function dialogTechSupportRequestsFilter( id ) {
	dialog_win('tech_support_requests_filter&id='+id,250,285,1,'tech_support_requests_filter');
}

function dialogTechRequest2(id) {
	dialog_win('set_setup_tech_request&id=0&idOldObj='+id, 390, 530, 1, 'set_setup_tech_request');
}

function dialogTechUnknownSignalSchemes( id ) {
	dialog_win('setup_tech_unknown_signal_schemes&id='+id,400,140,1,'setup_tech_unknown_signal_schemes');
}

function dialogSetSetupBankAccount(params, winname) {
	dialogWinHelper('set_setup_bank_account', params, 465, 405, 1, winname);
}

function dialogSetSetupCashier(params, winname) {
	dialogWinHelper('set_setup_cashier', params, 680, 630, 1, winname);
}

function dialogSetSetupPayDesk(params, winname) {
	dialogWinHelper('set_setup_pay_desk', params, 650, 335, 1, winname);
}

function dialogObjectArchiv2(params, winname) {
	dialogWinHelper('object_archiv', params,  800, 540, 1, winname);
}

function dialogClientObject( id ) {
	dialog_win('set_object_client&nID='+id,400,220,1,'set_object_client');
}

function dialogSetSetupObjectTaxes(id, obj) {
	dialog_win('set_setup_object_taxes&id='+id+'&obj='+obj, 320, 390, 1, 'set_setup_object_taxes');
}

function dialogSetSetupObjectSingles(id, obj) {
	dialog_win('set_setup_object_singles&id='+id+'&obj='+obj, 320, 390, 1, 'set_setup_object_singles');
}

function dialogClientsFilter( id ) {
	dialog_win('setup_clients_filter&nID='+id,480,400,1,'setup_clients_filter');
}

function dialogClientInfo( id ) {
	dialog_win('client_info&id='+id,640,480,1,'client_info');
}

function dialogClientPayments( id ) {
	dialog_win('client_payments&id='+id,800,590,1,'client_payments');
}

function dialogViewFiles(id) {
	dialog_win('view_export_doc&id='+id, 350, 220, 1, 'view_export_doc');
}

function dialogSetInvoiceMailScheme() {
	dialog_win('set_invoice_mail_scheme', 700, 600, 1, 'set_invoice_mail_scheme');
}

function dialogSetPayDeskReport() {
	dialog_win('set_pay_desk_report', 400, 200, 1, 'set_pay_desk_report');
}

function dialogOrder (params) {
	dialog_win('order_info&'+params,650,500,1,'order_info');
}

function dialogOrderInventory (params) {
	dialog_win('order_inventory&'+params,650,500,1,'order_inventory');
}

function dialogTransfer (params) {
	dialog_win('set_setup_transfer&'+params,620,460,1,'set_setup_transfer');
}

function dialogScheduleSettings(id) {
	dialog_win('set_object_schedule_settings&id='+id,310,195,1,'set_object_schedule_settings');
}

function dialogHours( firm, region, object ) {
	dialog_win('schedule_hours&nIDFirm=' + firm + '&nIDOffice=' + region + '&nIDObject=' + object, 900, 500, 1, 'schedule_hours');
}

function dialogObjectsFilter( id ) {
	dialog_win('setup_objects_filter&nID='+id,520,380,1,'setup_objects_filter');
}

function dialogOldObjectArchiv(id) {
	dialog_win('object_archiv&oldOD='+id,800,540,1,'object_archiv');
}

function dialogTechSupport(id) {
	dialog_win('object_troubles&nID='+id,800,540,1,'object_troubles');
}

function dialogScheduleMonthNorm(id) {
	dialog_win('set_schedule_month_norm&id='+id,310,220,1,'set_schedule_month_norm');
}

function dialogGroupSalesPayOrders(id, bank) {
//	var str = '';
//	
//	if ( arguments.length == 2 ) {
//		str = '&simplify=' +arguments[1];
//	}
	
	dialog_win('group_sales_pay_orders&id='+id+'&bank='+bank,400,180,1,'group_sales_pay_orders');
}

function dialogGroupBuyesPayOrders(id, bank) {
	dialog_win('group_buyes_pay_orders&id='+id+'&bank='+bank,400,180,1,'group_buyes_pay_orders');
}

function dialogSummaryFinancesRegionsStat (params) {
	dialog_win('summary_object_finances_regions_stat&'+params,720,500,1,'summary_object_finances_regions_stat');
}

function dialogConcession( id ) {
	dialog_win('set_setup_concession&nID='+id,365,205,1,'set_setup_concession');
}

function dialogSetSetupDirection(params, winname) {
	dialogWinHelper('set_setup_direction', params, 370, 130, 1, winname);
}

// function dialogBuy(params, winname) {
// 	dialogWinHelper('buy', params, 720, 560, 1, winname);
// }

function dialogDidoDemo( id ) {
	dialog_win('dido_demo&nID='+id, 720, 560, 1, 'dido_demo');
}

function dialogPersonSchedule( id_object, date ) {
	dialog_win('person_schedule&nIDSelectObject='+id_object+"&nCustomDate="+date,1100,800,1,'person_schedule');
}

function dialogShiftsCountFilter( id ){
	dialog_win('shifts_count_filter&id='+id,490,280,1,'shifts_count_filter');
}

function dialogSetupAgent( params )
{
	dialog_win( 'setup_agent&' + params, 390, 205, 0, 'setup_agent' );
}

function dialogSetupAgentGroups( id )
{
	dialog_win( 'setup_agent_groups&id=' + id, 300, 125, 1, 'setup_agent_groups' );
}

function dialogSetupCallCenterQueue( id )
{
	dialog_win( 'setup_call_center_queue&id=' + id, 790, 520, 1, 'setup_call_center_queue' );
}

function dialogSetupCallCenterQueueRule( params )
{
	dialog_win( 'setup_call_center_queue_rule&' + params, 400, 280, 1, 'setup_call_center_queue_rule' );
}

function dialogSetPhoneInternalNumber( id )
{
	dialog_win( 'set_phone_internal_number&id=' + id, 410, 280, 1, 'set_phone_internal_number' );
}

function dialogBooksAdd( id ) {
	dialog_win('set_setup_books_add&nID=' + id, 300, 245, 1, 'set_setup_books_add');
}

function dialogBooksDel( id ) {
	dialog_win('set_setup_books_del&nID=' + id, 300, 225, 1, 'set_setup_books_del');
}

function dialogBooksSet( id ) {
	dialog_win('set_setup_books_set&nID=' + id, 300, 245, 1, 'set_setup_books_set');
}

function dialogExportToEmail(id) {
	dialog_win('set_export_to_email&id=' + id, 300, 115, 1, 'set_export_to_email');
}

function dialogSetRegionManagerReport( params, winname )
{
	dialogWinHelper( 'set_region_manager_report', params, 900, 700, 1, winname );
}

function dialogViewPersonLeavesFilter( id )
{
	dialog_win( 'view_person_leaves_filter&id=' + id, 500, 330, 'view_person_leaves_filter' );
}

// function dialogViewMoneyNomenclaturesDetailFilter(id) {
//     dialog_win('view_money_nomenclatures_detail_filter&id='+id,480,315,1,'view_money_nomenclatures_detail_filter');
// }

function dialogSetupLeaveEarningsFilter( id )
{
	dialog_win( 'setup_leave_earnings_filter&id=' + id, 500, 432, 'setup_leave_earnings_filter' );
}

function dialogAdminVouchersFilter( id )
{
	dialog_win( 'admin_vouchers_filter&id=' + id, 450, 285, 1, 'admin_vouchers_filter' );
}

function dialogActivity( id )
{
	dialog_win( 'activity_dialog&nID=' + id, 400, 220, 'Activity');
}

function dialogOperation( id )
{
	dialog_win( 'operation_dialog&nID=' + id, 400, 220, 'Operation');
}

function dialogServicesRegister( id )
{
	var arr = new Array();
	arr = id.split(',');
	dialog_win( 'register&nIDService=' + id[0] + '&nIDFirm=' + id[1], 900, 500, 'Register');
}

function dialogTechAnalyticsObjects( idOffice )
{
	dialog_win( 'tech_analytics_objects', 750, 600, 'Objects');
}
function dialogObjectOverview( params ) 
{
	dialog_win('setup_objects&' + params,1050,620,1);
}
function dialogMoneyNomenclaturesView( from, to, id) {
	
	dialog_win('view_money_nomenclatures_detail&sFromDate='+from+'&sToDate='+to+'&nIDBankAccount='+id,1050,620,1);
}