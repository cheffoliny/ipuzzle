{literal}

	<script>
		rpc_debug=true;
		
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
		
		function getWindowWidth() {
			var ww = 0;
			if( self.innerWidth )
				ww = self.innerWidth;
			else if( document.documentElement && document.documentElement.clientWidth )
				ww = document.documentElement.clientWidth;
			else if( document.body )
				ww = document.body.clientWidth;
			
			return ww;
		}
		
		function getWindowHeight()
		{
			var wh = 0;
			if( self.innerHeight )
				wh = self.innerHeight;
			else if( document.documentElement && document.documentElement.clientHeight )
				wh = document.documentElement.clientHeight;
			else if( document.body )
				wh = document.body.clientHeight;
			
			return wh;
		}
		
		function resizeHandler()
		{
			var winHeight = getWindowHeight();
			var winWidth = getWindowWidth();
			
			if( document.getElementById( 'content' ) )
			{
				if( winWidth != 0 && winHeight != 0 )
				{
					if( winWidth >= 150 && winHeight >= 150 )
					{
						document.getElementById( 'content' ).style.height = winHeight + "px";
						document.getElementById( 'content' ).style.width = ( winWidth - 50 ) + "px";
					}
				}
			}
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
	<style>
		#result_data {
            overflow: hidden !important;
			max-height: 55% !important;
        }
	</style>

{/literal}

<body onresize="resizeHandler();" onload="resizeHandler();">

<div class="content">
<form action="" method="POST" name="form1" id="form1" onsubmit="return submit_form();">
	
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

	<div class="row nav-tabs nav-intelli py-1">
		<div class="col pt-2 px-5">
			<h6 class="text-white">ПРИЕМО-ПРЕДАВАТЕЛЕН ПРОТОКОЛ</h6>
		</div>
		<div class="col">
			<div class="input-group">
				<span class="input-group-addon">#</span>
				<span class="input-group-addon mr-3">
					<input type="text" name="nID" id="nID"	 size="5" class="form-control" value="{$nID|default:''}" readonly="readonly" />
				</span>
				<span class="input-group-addon text-white mx-3"> / </span>
				<span class="input-group-addon">
					<input type="text" name="nDay"	 id="nDay"	 size="2" class="form-control in" readonly="readonly" />
				</span>
				<span class="input-group-addon">
					<input type="text" name="nMonth" id="nMonth" size="2" class="form-control" readonly="readonly" />
				</span>
				<span class="input-group-addon">
					<input type="text" name="nYear"  id="nYear"  size="4" class="form-control" readonly="readonly" />
				</span>
			</div>
		</div>
	</div>
	<div class="container-fluid bg-light">
		<div class="row pt-2">
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-upload fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<select class="form-control" name="sSendType" id="sSendType" onchange="nullSentType();">
						<option value="">-- Тип Предаващ --</option>
						<option value="object">Обект</option>
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="client">Доставчик</option>
					</select>
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-download fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<select name="sReceiveType" id="sReceiveType" class="form-control" onchange="nullReceivedType();">
						<option value="">-- Тип Получаващ --</option>
						<option value="object">Обект</option>
						<option value="storagehouse">Склад</option>
						<option value="person">Служител</option>
						<option value="client">Доставчик</option>
					</select>
				</div>
			</div>
		</div>
		<div class="row py-1">
			<div class="col">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="fas fa-upload fa-fw" data-fa-transform="right-22 down-10" title="Тип Предаващ...."></span>
					</div>
					<input type="text" name="sSourceName" id="sSourceName" class="form-control suggest" suggest="suggest" queryType="pppSourceName" queryParams="sSendType" onchange="rememberErrorElement(); setTimeout( 'refreshMOL()', 500 );" placeholder="ID или име на предаващия..." />
				</div>
			</div>
			<div class="col">
				<div class="input-group input-group-sm suggest">
					<div class="input-group-prepend">
						<span class="fas fa-download fa-fw" data-fa-transform="right-22 down-10" title="Тип Приемащ..."></span>
					</div>
					<input type="text" name="sDestName" id="sDestName" class="form-control bg-aqua-active" suggest="suggest"  queryType="pppDestName" queryParams="sReceiveType" onchange="rememberErrorElement(); setTimeout( 'refreshMOL()', 500 );" placeholder="ID или име на приемащия..." />
				</div>
			</div>
		</div>

		<div class="row w-100 pt-2 text-primary"><div class="col px-4 py-1">Списък на материални запаси</div></div>


		<div class="row px-0 bg-light" id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height: 380px; overflow: auto;"></div>
{*		<div class="w-100 px-0 py-1" style="height: 350px !important; border: 1px solid yellow; overflow: hidden;">*}
{*			<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height: 350px !important;"></div>*}
{*		</div>*}

		<div class="row py-1">
			<div class="col py-0 ">
				<textarea class="w-100" rows="2" id="sNote" name="sNote" placeholder=" Допълнителна информация..." ></textarea>
			</div>
		</div>

		<div class="row">
			<div class="col"><input type="text" name="sSentBy" id="sSentBy" class="form-control" placeholder="Предаващ..." /></div>
			<div class="col"><input type="text" name="sReceivedBy" id="sReceivedBy" class="form-control" placeholder="Получил..." /></div>
		</div>

		<div class="row py-2" id="search">
			<div class="col">
				<input type="checkbox" name="nClosed" id="nClosed" class="clear" /> Потвърди
				<button class="btn btn-sm btn-danger" id="cancelRecord" name="cancelRecord" onclick="return cancelPPP();" title="Анулиране на ППП"><i class="far fa-times"></i> Анулирай</button>
			</div>
			<div class="col text-right">
				<button class="btn btn-sm btn-success"	onclick="setPPPElement( 0 );" id="addnom" name="addnom" type="button"><i class="far fa-plus"></i> Добави </button>
				<button class="btn btn-sm btn-info" type="submit" id="send"><i class="far fa-save"></i> Запиши </button>
{*				<button class="btn btn-sm btn-danger"	onClick="loadXMLDoc2( 'purgeDatabase', 3 );"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>*}
				<button class="btn btn-sm btn-primary"	onClick="printPDF();"><i class="far fa-file-pdf"></i> Разпечатай </button>
			</div>
		</div>

		<div class="row fixed-bottom bg-light mx-2 mb-2">
			<div class="col px-1">
				<input type="text" name="sCreatedBy" id="sCreatedBy"	class="form-control" readonly="readonly" title="Създал"	placeholder="Създал..." />
			</div>
			<div class="col px-0">
				<input type="text" name="sEditedBy"	id="sEditedBy" class="form-control" readonly="readonly" title="Редактирал"	placeholder="Редактирал..." />
			</div>
			<div class="col px-1">
				<input type="text" name="sConfirmedBy" id="sConfirmedBy" class="form-control" readonly="readonly" title="Потвърдил"	placeholder="Потвърдил..." />
			</div>
		</div>
	</div>
</form>

</div>
</body>

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