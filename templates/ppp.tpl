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
		
		function deletePPPElement( id )
		{
			var nLoadedClosed = $('nLoadedClosed').value;
			var nOnlyValidate = $('nOnlyValidate').value;
			if( nLoadedClosed == 1 || nOnlyValidate == 1 )return false;
			
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('nIDElement').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
		function printPDF()
		{
			var nID = $('nID').value;
			if( !nID )
			{
				alert( 'Протокола не е записан!' );
			}
			else
			{
				loadDirect( 'export_to_pdf' );
			}
		}
		
		function nullSentType()
		{
			document.getElementById( 'sSourceName' ).value = '';
			document.getElementById( 'sSentBy' ).value = '';
			document.getElementById( 'nIDSourceName' ).value = 0;
		}
		
		function nullReceivedType()
		{
			document.getElementById( 'sDestName' ).value = '';
			document.getElementById( 'sReceivedBy' ).value = '';
			document.getElementById( 'nIDDestName' ).value = 0;
		}
		
		function getWindowWidth()
		{
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
	

{/literal}

<body onresize="resizeHandler();" onload="resizeHandler();">
<div class="content" style="overflow-y: auto; overflow-x: auto;">
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
			
	<table class="search" style="width:100%;">
	<tr>
		<td colspan="2" class="header_buttons" style="height: 33px; text-align: center; vertical-align: top;">
			<span id="head_window" style="color: #3060aa; vertical-align: bottom;" >ПРИЕМО-ПРЕДАВАТЕЛЕН ПРОТОКОЛ - 
				&nbsp;&num; <input type="text" name="nID" id="nID" class="clear" value="{$nID|default:''}" readonly="readonly" style="width: 70px; line-height: 28px; font-size: 14px; font-weight: bold; text-shadow: -1px 1px #ffffff; color: #aa0000; text-align: right; border-bottom: 1px solid #cccccc;" />
				&nbsp;/&nbsp;
				<input type="text" name="nDay"	 id="nDay"	 class="clear" readonly="readonly" style="width: 20px; line-height: 28px; font-size: 14px; font-weight: bold; text-shadow: -1px 1px #ffffff; color: #4060aa;" /> .
				<input type="text" name="nMonth" id="nMonth" class="clear" readonly="readonly" style="width: 20px;line-height: 28px; font-size: 14px; font-weight: bold; text-shadow: -1px 1px #ffffff; color: #4060aa;" /> .
				<input type="text" name="nYear"  id="nYear"  class="clear" readonly="readonly" style="width: 40px;line-height: 28px; font-size: 14px; font-weight: bold; text-shadow: -1px 1px #ffffff; color: #4060aa;" />
			</span>
		</td>
	</tr>
	<tr><td style="height: 5px;"></td></tr>
	<tr style="background: rgba(255,255,255,0.3)">
		<td style="padding: 7px 2px 7px 2px;">

			 &nbsp;Предаващ<br />
			<div class="input-group" style="margin: 3px;">
			<span class="input-group-addon"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
			<select name="sSendType" id="sSendType" class="select300" onchange="nullSentType();">
				<option value="object">Обект</option>
				<option value="storagehouse">Склад</option>
				<option value="person">Служител</option>
				<option value="client">Доставчик</option>
			</select>
			</div>
	
			<div class="input-group" style="margin: 3px;">
			<span class="input-group-addon"><img src="images/glyphicons/inbox.out.png" style="width: 12px; height: 12px;"></span>
			<input type="text" name="sSourceName" id="sSourceName" class="inp300" suggest="suggest" queryType="pppSourceName" queryParams="sSendType" onchange="rememberErrorElement(); setTimeout( 'refreshMOL()', 500 );" placeholder="ID или име на предаващия..." />
			</div>

		</td>
		<td style="float: right; text-align: right;">
			
			 &nbsp;Приемащ&nbsp;<br />
			<div class="input-group" style="border: 0px solid gray; float: right; margin: 3px; width: 300px;">
			<span class="input-group-addon"><img src="images/glyphicons/info.png" style="float: right; width: 12px; height: 12px;"></span>
			<select name="sReceiveType" id="sReceiveType" class="select300" onchange="nullReceivedType();">
					<option value="object">Обект</option>
					<option value="storagehouse">Склад</option>
					<option value="person">Служител</option>
					<option value="client">Доставчик</option>
				</select>
			</div>
	
			<div class="input-group" style="float: right; margin: 3px; width: 300px;">
			<span class="input-group-addon"><img src="images/glyphicons/inbox.in.png" style="width: 12px; height: 12px;"></span>
			<input type="text" name="sDestName" id="sDestName" class="inp300" suggest="suggest" queryType="pppDestName" queryParams="sReceiveType" onchange="rememberErrorElement(); setTimeout( 'refreshMOL()', 500 );" placeholder="ID или име на приемащия..." />
			</div>
			
		</td>
	</tr>
			
	<tr><td style="height: 5px;"></td></tr>
	<tr style="background: rgba(255,255,255,0.3)">
		<td colspan="2" style="padding: 0px 2px 7px 2px;">
			&nbsp;Списък на материални запаси
			<div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height: 360px; overflow: auto;"></div>
		</td>
	</tr>
	<tr>
		<td colspan="2" style="padding: 0px 2px 7px 2px;">
			&nbsp;Забележка<br />
			<textarea rows="3" id="sNote" name="sNote" style="width: 777px; background: rgba(255,255,255,0.8)" placeholder=" Допълнителна информация..." ></textarea>
		</td>
	</tr>
	<tr>
		<td>&nbsp;<input type="text" name="sSentBy" id="sSentBy" class="inp300" placeholder="Предаващ..." style="background: rgba(255,255,255,0.7)" /></td>
		<td style="text-align: right;"><input type="text" name="sReceivedBy" id="sReceivedBy" class="inp300" placeholder="Получил..." style="background: rgba(255,255,255,0.7)" />&nbsp;</td>
	</tr>
</table>
	
<div id="search"  style="padding-top:10px; width:100%;">
	<table class="page_data" >
		<tr>
			<td style="text-align: left; padding: 10px 0 10px 1px;">
				
				<input type="checkbox" name="nClosed" id="nClosed" class="clear" /> Потвърди
				<button class="btn btn-xs btn-danger" id="cancelRecord" name="cancelRecord" class="search" style="background: #F09E93;" onclick="return cancelPPP();" title="Анулиране на ППП"><img src="images/cancel.gif"/>Анулирай</button>
			</td>
			<td style="text-align: right; width: 500px; padding: 10px 1px 10px 0;">
				<button class="btn btn-xs btn-success"	onclick="setPPPElement( 0 );" id="addnom" name="addnom" type="button"><i class="fa fa-plus"></i> Добави </button>
				<button class="btn btn-xs btn-info" type="submit" id="send"><img src="images/glyphicons/save.png" style="width: 14px; height: 14px;"> Запиши </button>
				<button class="btn btn-xs btn-danger"	onClick="loadXMLDoc2( 'purgeDatabase', 3 );"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>
				<button class="btn btn-xs btn-primary"	onClick="printPDF();"><i class="fa fa-file-pdf-o"></i> Разпечатай </button>
			</td>
		</tr>
	</table>
</div>

<table class="search" style="width: 100%;">
	<tr>
		<td align="left"><input type="text" name="sCreatedBy"	id="sCreatedBy"		class="clear" size="40" readonly="readonly" title="Създал"		style="text-shadow: 0px 1px #ffffff; color: #3060aa;" placeholder="Създал..." /></td>
		<td align="left"><input type="text" name="sEditedBy"	id="sEditedBy"		class="clear" size="40" readonly="readonly" title="Редактирал"	style="text-shadow: 0px 1px #ffffff; color: #3060aa;" placeholder="Редактирал..." /></td>
		<td align="left"><input type="text" name="sConfirmedBy" id="sConfirmedBy"	class="clear" size="40" readonly="readonly" title="Потвърдил"	style="text-shadow: 0px 1px #ffffff; color: #3060aa;" placeholder="Потвърдил..." /></td>
	</tr>
</table>

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