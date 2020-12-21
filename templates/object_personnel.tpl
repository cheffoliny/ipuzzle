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


<form name="form1" id="form1" onsubmit="return false;">

	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	
	<table class="search" style="width:100%;">
		<tr>
			<td class="header_buttons">
			<span id="head_window">Служители в обект {$object}</span> 
				<button class="btn btn-xs btn-primary" style="float:right; margin-right: 3px;" onClick="techSupport();"><img src="images/glyphicons/tech.png" style="width: 14px; height: 14px;"> Oбслужване</button>
				{include file=object_tabs.tpl}
			</td>
		</tr>
		
		<tr class="odd">
			<td id="filter_result">
				
			{if $mobile}
				{if $cnt>6}
					<div id="search" style="padding-top: 10px; width: 800px; height: 275px; overflow-y: auto">
				{else}
					<div id="search" style="padding-top: 10px; width: 800px; height: 290px; overflow-y: auto">
				{/if}
			{/if}
			
			
			<table class="page_data">
			<!-- начало на работната част -->
			
				<tr>
					<td style="text-align: left; padding: 2px;">
						<div class="input-group">
							<span class="input-group-addon">
							<i class="far fa-user"></i></span>
							<input type="text" id="nPersonCode" name="nPersonCode" style="width: 100px; text-align: right;" suggest="suggest" queryType="suggestFreeObjectPerson" onkeypress="formatDigits( event )" maxlength="12" />
							<input type="text" id="sPersonName" name="sPersonName" style="width: 200px" suggest="suggest" queryType="suggestFreeObjectPerson" />
							
						</div>		
					</td>
					<td style="text-align: right; padding-right: 2px;">
						<button id="b100" class="btn btn-xs btn-success" onClick="addPerson()"><i class="fa fa-plus"></i> Добави</button>
					</td>
				</tr>
		  </table>
		
		
		<hr>
	
		<div id="result" rpc_excel_panel="off" rpc_resize="off" style="width: 780px; height: 360px;overflow: auto;"></div>
	
		</div>
	 	<!-- край на работната част -->
		</td>
	</tr>
</table>

	{if $mobile}
		{if $cnt>6}
			<div id="search" style="padding-top: 10px; width: 800px; height: 220px; overflow-y: auto">
		{else}
			<div id="search" style="padding-top: 10px; width: 800px; height: 245px; overflow-y: auto">
		{/if}
	{/if}
		
	<div id="search"  style="padding-top:10px;width:800px;">
		<table class="page_data" >
			<tr valign="top">
				<td valign="top" style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
					&nbsp;
				</td>
				<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
					<button id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори</button>
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
	