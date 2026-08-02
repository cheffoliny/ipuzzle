{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			attachEventListener( $('nPersonCode'), "keypress", onKeyPressPersonCode);
			attachEventListener( $('sPersonName'), "keypress", onKeyPressPersonName);
			
			loadXMLDoc2('result');
		}
		
		function deletePerson( id )
		{
			var nID = parseInt( id );
			
			if( nID )
			{
				if( confirm("Желаете ли да премахнете служителя от обекта ?") )
					loadXMLDoc2('delete&nIDPerson=' + id, 1);	
			}
		}
		
		function openPerson( id )
		{
			dialogPerson( id );
		}
		
		function addPerson()
		{
			var fnBackRpcOnExit = rpc_on_exit;
			
			rpc_on_exit = function( nCode )
			{
				if( !parseInt( nCode ) )
				{
					loadXMLDoc2('result');
					
					$('nPersonCode').value = "";
					$('sPersonName').value = "";
				}
					
				rpc_on_exit = fnBackRpcOnExit;
			}
			
			loadXMLDoc2('addPerson');
		}
		
		function onKeyPressPersonCode()
		{
			$('sPersonName').value = "";
		}
		
		function onKeyPressPersonName()
		{
			$('nPersonCode').value = "";
		}
		
		InitSuggestForm = function()
		{
			for(var i=0; i<suggest_elements.length; i++) 
			{
				switch( suggest_elements[i]['id'] )
				{
					case 'nPersonCode':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
						break;
					case 'sPersonName':
						suggest_elements[i]['suggest'].setSelectionListener( onSuggestPerson );
						break;
				}
			}
		}
			
		function onSuggestPerson( aParams ) 
		{
			var aParts = aParams.KEY.split(';');
			
			$('nPersonCode').value = aParts[0];
			$('sPersonName').value = aParts[1];
		}
		
		function techSupport() {
			var id = $('nID').value;
			
			dialogTechSupport(id);
		}		
	</script>
{/literal}


<form name="form1" id="form1" class="ui-object-core ui-object-personnel" onsubmit="return false;">

	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	
	<table class="search ui-object-personnel-shell">
		<tr>
			<td class="header_buttons">
			<span id="head_window">Служители в обект {$object}</span> 
				<button type="button" class="btn btn-xs btn-primary ui-object-personnel-service" onClick="techSupport();"><span class="ui-icon ui-icon-wrench" aria-hidden="true"></span> Oбслужване</button>
				{include file="object_tabs.tpl"}
			</td>
		</tr>
		
		<tr class="odd">
			<td id="filter_result">
				
			<table class="page_data ui-object-personnel-toolbar">
			<!-- начало на работната част -->
			
				<tr>
					<td style="text-align: left; padding: 2px;">
						<div class="input-group">
							<span class="input-group-addon">
							<span class="ui-icon ui-icon-user" aria-hidden="true"></span></span>
							<input type="text" id="nPersonCode" name="nPersonCode" class="ui-object-personnel-code" suggest="suggest" queryType="suggestFreeObjectPerson" onkeypress="formatDigits( event )" maxlength="12" />
							<input type="text" id="sPersonName" name="sPersonName" class="ui-object-personnel-name" suggest="suggest" queryType="suggestFreeObjectPerson" />
							
						</div>		
					</td>
					<td style="text-align: right; padding-right: 2px;">
						<button type="button" class="btn btn-xs btn-success" onClick="addPerson()"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>
					</td>
				</tr>
		  </table>
		
		
		<hr>
	
		<div id="result" class="ui-object-result ui-object-personnel-main-result" rpc_excel_panel="off" rpc_resize="off"></div>
	 	<!-- край на работната част -->
		</td>
	</tr>
</table>

	<div id="search" class="fixed-bottom ui-object-actions ui-object-personnel-actions">
		<table class="page_data ui-nomenclature-actions" >
			<tr valign="top">
				<td valign="top" style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
					&nbsp;
				</td>
				<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
					<button type="button" class="btn btn-xs btn-danger" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
				</td>
			</tr>
		</table>
	</div>
	<div id="NoDisplay" style="display:none"></div>
</form>

<script>
	onInit();
	
	{if !$edit.object_personnel_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}
</script>
