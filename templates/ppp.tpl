{literal}

	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		InitSuggestForm = function()
		{
			for( var i = 0; i < suggest_elements.length; i++ )
			{
				if( suggest_elements[i]['id'] == 'sSourceName' )
				{
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestSourceName );
				}
				if( suggest_elements[i]['id'] == 'sDestName' )
				{
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestDestName );
				}
			}
		}
		
		function onSuggestSourceName( aParams )
		{
			$('nIDSourceName').value = aParams.KEY;
			
			rememberErrorElement();
			setTimeout( 'refreshMOL()', 500 );
		}
		
		function onSuggestDestName( aParams )
		{
			$('nIDDestName').value = aParams.KEY;
			
			rememberErrorElement();
			setTimeout( 'refreshMOL()', 500 );
		}
		
		function submit_form()
		{
			$('nPseudoSave').value = 0;
			loadXMLDoc2( 'save',3);
			return false;
		}
		
		function rememberErrorElement()
		{
			if( document.activeElement )
			{
				$('sErrorElement').value = document.activeElement.id;
			}
		}
		
		function refreshMOL()
		{
			rpc_on_exit = function( nCode )
			{
				if( !parseInt( nCode ) )
				{
					var neededId = $('sActiveElement').value;
					
					if( neededId )
						document.getElementById( neededId ).focus();
				}
				
				rpc_on_exit = function( nCode ) {}
			}
			
			if( document.activeElement.id )$('sActiveElement').value = document.activeElement.id;
			else $('sActiveElement').value = $('sErrorElement').value;
			loadXMLDoc2( 'refreshMOL' );
		}
		
		function pseudoSave()
		{
			if( $('nID').value == 0 )
			{
				rpc_on_exit = function( nCode )
				{
					if( !parseInt( nCode ) )
					{
						$('nPseudoSave').value = 0;
						if( $('nSetStorage').value != "0" || $("nIDLimitCard").value != "0" )setTimeout( 'refreshMOL()', 200 );
					}
					
					rpc_on_exit = function( nCode ) {}
				}
				
				$('nPseudoSave').value = 1;
				loadXMLDoc2( 'save' );
			}
		}
		
		function setPPPElement( id )
		{
			var nLoadedClosed = $('nLoadedClosed').value;
			var nOnlyValidate = $('nOnlyValidate').value;
			if( nLoadedClosed == 1 || nOnlyValidate == 1 )return false;
			
			if( $('nID').value != 0 )
			{
				
				var params = 'id=' + id;

				params += '&id_ppp=' + $('nID').value;
				params += "&id_storagehouse=" + ( ( $("sSendType").value == "storagehouse" || $("sSendType").value == "object" ) ? $('nIDSourceName').value : "0" );
				params += "&storage_type=" + $("sSendType").value;
				dialogPPPElement( params );
                rpc_on_exit = function( nCode ) {}
			}
			else
			{
				
				rpc_on_exit = function( nCode )
				{
					if( !parseInt( nCode ) )
					{
						var params = 'id=' + id;
						
						params += '&id_ppp=' + $('nID').value;
						
						dialogPPPElement( params );
					}
					
					rpc_on_exit = function( nCode ) {}
				}
				
				loadXMLDoc2( 'save' );
			}
			
		}
		
		function deletePPPElement( id ) {
			var nLoadedClosed = $('nLoadedClosed').value;
			var nOnlyValidate = $('nOnlyValidate').value;
			if( nLoadedClosed == 1 || nOnlyValidate == 1 )return false;
			
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) ) {
				$('nIDElement').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
		function printPDF() {
			var nID = $('nID').value;
			if( !nID ) {
				alert( 'Протокола не е записан!' );
			} else {
				loadDirect( 'export_to_pdf' );
			}
		}
		
		function nullSentType() {
			document.getElementById( 'sSourceName' ).value = '';
			document.getElementById( 'sSentBy' ).value = '';
			document.getElementById( 'nIDSourceName' ).value = 0;
		}
		
		function nullReceivedType() {
			document.getElementById( 'sDestName' ).value = '';
			document.getElementById( 'sReceivedBy' ).value = '';
			document.getElementById( 'nIDDestName' ).value = 0;
		}
		
		function cancelPPP()
		{
			if( confirm( 'Наистина ли желаете да анулирате?' ) )
			{
				loadXMLDoc2( 'cancel', 3 );
			}
		}
		
		function checkAll( bChecked )
		{
			var aCheckboxes = document.getElementsByTagName( 'input' );
			
			for( var i = 0; i < aCheckboxes.length; i++ )
			{
				if( aCheckboxes[i].type.toLowerCase() == 'checkbox' )
				{
					if( aCheckboxes[i].id != 'nClosed' )
					{
						aCheckboxes[i].checked = bChecked;
					}
				}
			}
		}
		
		function just_do_it()
		{
			switch( getById( 'sel' ).value )
			{
				case '1':
					checkAll( true );
					break;
				case '2':
					checkAll( false );
					break;
				case '3':
					var nLoadedClosed = $('nLoadedClosed').value;
					var nOnlyValidate = $('nOnlyValidate').value;
					if( nLoadedClosed == 1 || nOnlyValidate == 1 )break;
					
					if( confirm( 'Наистина ли желаете да премахнете избраните номенклатури?' ) )
					{
						loadXMLDoc2( 'deleteAll', 1 );
					}
					break;
			}
		}
		
	</script>
{/literal}

<main class="ui-ppp-page">
<form action="" method="POST" name="form1" id="form1" class="ui-ppp-editor" onsubmit="return submit_form();">
	
	<input type="hidden" name="sActiceElement" id="sActiveElement" value=""> 				<!-- Активен Елемент -->
	<input type="hidden" name="sErrorElement" id="sErrorElement" value=""> 					<!-- Елемент, върнат при грешка -->
	<input type="hidden" name="nElementsSet" id="nElementsSet" value="0">					<!-- При презареждане на ППП да не се презареждат типа и името на предаващ / приемащ -->
	<input type="hidden" name="nPseudoSave" id="nPseudoSave" value="0">						<!-- Временен указател за извличане на ППП номер -->
	<input type="hidden" name="nCloseCancels" id="nCloseCancels" value="0">					<!-- При затваряне отменят ли се промените -->
	
	<input type="hidden" name="nIDSourceName" id="nIDSourceName" value="0">
	<input type="hidden" name="nIDDestName" id="nIDDestName" value="0">
	<input type="hidden" name="sHour" id="sHour" value="00:00:00">
	
	<input type="hidden" name="nIDObject" id="nIDObject" value="{$nIDObject}">
	<input type="hidden" name="nSetStorage" id="nSetStorage" value="{$nSetStorage}">
	<input type="hidden" name="nIDLimitCard" id="nIDLimitCard" value="{$nIDLimitCard}">
	<input type="hidden" name="nIDElement" id="nIDElement" value="0">
	<input type="hidden" name="nLoadedClosed" id="nLoadedClosed" value="0">
	<input type="hidden" name="nCanceled" id="nCanceled" value="0">
	<input type="hidden" name="nReadOnly" id="nReadOnly" value="0">
	<input type="hidden" name="nOnlyValidate" id="nOnlyValidate" value="0">
	<input type="hidden" name="t" id="t" value="{$sType}" />
	<input type="hidden" name="nIDPerson" id="nIDPerson" value="{$nIDPerson}" />
	<input type="hidden" name="hash" id="hash" value="" />

    <header class="ui-ppp-header">
        <div class="ui-ppp-title"><span class="ui-icon ui-icon-document" aria-hidden="true"></span> Приемо-предавателен протокол</div>
        <div class="ui-ppp-document-meta" aria-label="Данни за протокола">
            <label>№<input type="text" name="nID" id="nID" class="form-control" value="{$nID|default:''}" readonly="readonly" /></label>
            <span class="ui-ppp-date-separator" aria-hidden="true">/</span>
            <label>Ден<input type="text" name="nDay" id="nDay" class="form-control" readonly="readonly" /></label>
            <label>Месец<input type="text" name="nMonth" id="nMonth" class="form-control" readonly="readonly" /></label>
            <label>Година<input type="text" name="nYear" id="nYear" class="form-control" readonly="readonly" /></label>
        </div>
    </header>

    <section class="ui-ppp-transfer" aria-label="Данни за предаване">
        <div class="row g-2">
            <div class="col-12 col-md-6">
                <label class="ui-ppp-field-label" for="sSendType">Предаващ</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-upload" aria-hidden="true"></span></div>
                    <select class="form-control" name="sSendType" id="sSendType" onchange="nullSentType();">
                        <option value="">-- Тип предаващ --</option>
                        <option value="object">Обект</option>
                        <option value="storagehouse">Склад</option>
                        <option value="person">Служител</option>
                        <option value="client">Доставчик</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="ui-ppp-field-label" for="sReceiveType">Получаващ</label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-download" aria-hidden="true"></span></div>
                    <select name="sReceiveType" id="sReceiveType" class="form-control" onchange="nullReceivedType();">
                        <option value="">-- Тип получаващ --</option>
                        <option value="object">Обект</option>
                        <option value="storagehouse">Склад</option>
                        <option value="person">Служител</option>
                        <option value="client">Доставчик</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="ui-ppp-field-label" for="sSourceName">Име на предаващия</label>
                <div class="input-group input-group-sm suggest">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-upload" aria-hidden="true"></span></div>
                    <input type="text" name="sSourceName" id="sSourceName" class="form-control suggest" suggest="suggest" queryType="pppSourceName" queryParams="sSendType" onchange="rememberErrorElement(); setTimeout( 'refreshMOL()', 500 );" placeholder="ID или име на предаващия" />
                </div>
            </div>
            <div class="col-12 col-md-6">
                <label class="ui-ppp-field-label" for="sDestName">Име на получаващия</label>
                <div class="input-group input-group-sm suggest">
                    <div class="input-group-prepend"><span class="ui-icon ui-icon-download" aria-hidden="true"></span></div>
                    <input type="text" name="sDestName" id="sDestName" class="form-control bg-aqua-active" suggest="suggest" queryType="pppDestName" queryParams="sReceiveType" onchange="rememberErrorElement(); setTimeout( 'refreshMOL()', 500 );" placeholder="ID или име на получаващия" />
                </div>
            </div>
        </div>
    </section>

    <section class="ui-ppp-inventory" aria-labelledby="pppInventoryTitle">
        <div class="ui-ppp-section-heading" id="pppInventoryTitle"><span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> Материални запаси</div>
        <div class="ui-ppp-result-frame">
            <div id="result" class="ui-ppp-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" rpc_autonumber="on"></div>
        </div>
    </section>

    <section class="ui-ppp-details" aria-label="Допълнителни данни">
        <div class="row g-2">
            <div class="col-12">
                <label class="ui-ppp-field-label" for="sNote">Допълнителна информация</label>
                <textarea class="form-control" rows="2" id="sNote" name="sNote" placeholder="Бележка към протокола"></textarea>
            </div>
            <div class="col-12 col-md-6">
                <label class="ui-ppp-field-label" for="sSentBy">Предал</label>
                <input type="text" name="sSentBy" id="sSentBy" class="form-control" placeholder="Име на предаващия" />
            </div>
            <div class="col-12 col-md-6">
                <label class="ui-ppp-field-label" for="sReceivedBy">Получил</label>
                <input type="text" name="sReceivedBy" id="sReceivedBy" class="form-control" placeholder="Име на получаващия" />
            </div>
        </div>
    </section>

    <footer class="ui-ppp-footer">
        <div class="ui-ppp-actions" id="search">
            <div class="ui-ppp-status-actions">
                <label class="ui-ppp-confirm"><input type="checkbox" name="nClosed" id="nClosed" class="clear" /> <span>Потвърди</span></label>
                <button class="btn btn-sm btn-danger" id="cancelRecord" name="cancelRecord" type="button" onclick="return cancelPPP();" title="Анулиране на ППП"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Анулирай</button>
            </div>
            <div class="ui-ppp-primary-actions">
                <button class="btn btn-sm btn-success" onclick="setPPPElement( 0 );" id="addnom" name="addnom" type="button"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>
                <button class="btn btn-sm btn-info" type="submit" id="send"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
                <button class="btn btn-sm btn-primary" type="button" onclick="printPDF();"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> Разпечатай</button>
            </div>
        </div>
        <div class="ui-ppp-audit" aria-label="История на протокола">
            <label>Създал<input type="text" name="sCreatedBy" id="sCreatedBy" class="form-control" readonly="readonly" placeholder="Не е посочен" /></label>
            <label>Редактирал<input type="text" name="sEditedBy" id="sEditedBy" class="form-control" readonly="readonly" placeholder="Не е посочен" /></label>
            <label>Потвърдил<input type="text" name="sConfirmedBy" id="sConfirmedBy" class="form-control" readonly="readonly" placeholder="Не е посочен" /></label>
        </div>
    </footer>
</form>
</main>

{literal}
	<script>
	
		if( $('nSetStorage').value == "2" )document.getElementById( "addnom" ).style.display = "none";
		
		rpc_on_exit = function( nCode )
		{
			if( !parseInt( nCode ) )
			{
				if( $('nClosed').checked == true || $('nCanceled').value == "1" || $('nReadOnly').value == "1" )
				{
					//Disable Some Elements:
					document.getElementById( 'sSendType' ).disabled 	= 'disabled';
					document.getElementById( 'sSourceName' ).disabled 	= 'disabled';
					document.getElementById( 'sReceiveType' ).disabled 	= 'disabled';
					document.getElementById( 'sDestName' ).disabled 	= 'disabled';
					
					document.getElementById( 'sNote' ).disabled 		= 'disabled';
					document.getElementById( 'sSentBy' ).disabled 		= 'disabled';
					document.getElementById( 'sReceivedBy' ).disabled 	= 'disabled';
					
					document.getElementById( 'nClosed' ).disabled 		= 'disabled';
					document.getElementById( 'cancelRecord' ).disabled 	= 'disabled';
					document.getElementById( 'send' ).disabled 			= 'disabled';
					document.getElementById( 'addnom' ).disabled 		= 'disabled';
				}
				
				if( $('nOnlyValidate').value == "1" )
				{
					//Disable Some Elements:
					document.getElementById( 'sSendType' ).disabled 	= 'disabled';
					document.getElementById( 'sSourceName' ).disabled 	= 'disabled';
					document.getElementById( 'sReceiveType' ).disabled 	= 'disabled';
					document.getElementById( 'sDestName' ).disabled 	= 'disabled';
					
					document.getElementById( 'sNote' ).disabled 		= 'disabled';
					document.getElementById( 'sSentBy' ).disabled 		= 'disabled';
					document.getElementById( 'sReceivedBy' ).disabled 	= 'disabled';
					document.getElementById( 'cancelRecord' ).disabled 	= 'disabled';
					
					document.getElementById( "addnom" ).style.display 	= "none";
					
					document.getElementById( 'nClosed' ).disabled 		= '';
					document.getElementById( 'send' ).disabled 			= '';
				}
			}
			
			rpc_on_exit = function( nCode ) {}
			
			pseudoSave();
		}
		
		loadXMLDoc2( 'result' );
	
	</script>
{/literal}
