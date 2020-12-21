{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			attachEventListener( $('nPersonCode'), "keypress", onKeyPressPersonCode);
			attachEventListener( $('sPersonName'), "keypress", onKeyPressPersonName);
			
			loadXMLDoc2('result');
		}
		
		function deletePerson( id ) {
			var nID = 0;
			
			try {
				var ids = id.split(',');
				nID = parseInt(ids[0]);
				nIDPerson = parseInt(ids[1]);
				
				$('nIDPerson').value = nIDPerson;
			}
			catch(err) {
				//alert(err.description);
			}
			
			if ( nID ) {
				if ( confirm("Желаете ли да премахнете служителя от обекта ?") ) {
					loadXMLDoc2('delete&nIDRelation=' + nID, 1);	
				}
			}
		}
		
		function openPerson( id ) {
			var idPerson = 0;
			
			try {
				var ids = id.split(',');
				idPerson = ids[1];
			}
			catch(err) {
				//alert(err.description);
			}

			dialogPerson( idPerson );
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
			
		function openSchedule() {
			var nID = document.getElementById('nID').value;
			
			window.opener.location.href = 'page.php?page=person_schedule&nIDSelectObject=' + nID;
			window.close();
		}	
		
		function fnStartInit() {
			alert(document.readyState);
			
			if ( document.readyState == "complete" ) {
				// Finish initialization.
			}
		}	
		
		function goSort() {
			//alert($('nID').value);
			loadXMLDoc2('sortNow', 1);
		}	
		
		function nextMonth(act,input_date) {
			
			var oldDate = $(input_date).value;
			var MM = oldDate.substr(0,2);
			var YY = oldDate.substr(3,4);
			
			if(act == 'next') {
				MM++;
				if(MM == '13') {
					MM = '1';
					YY++;
				}
			} else {
				MM--;
				if(MM == '0') {
					MM = '12';
					YY--;
				}
			}
			
			if(MM < 10) MM = "0" + MM;
			$(input_date).value = MM + '.' + YY;			
		}
		
		function techSupport() {
			var id = $('nID').value;
			
			dialogTechSupport(id);
		}		
					
	</script>
{/literal}


<form name="form1" id="form1" onsubmit="return false;">

	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
	<input type="hidden" id="nIDPerson" name="nIDPerson" value="0" />
	
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
			
			<!-- начало на работната част -->
		
			<table class="page_data">
				<tr>
					<td style="text-align: left; padding: 2px;">
					
						<div class="input-group">
							<span class="input-group-addon">
							<i class="far fa-user"></i></span>
							<input type="text" id="nPersonCode" name="nPersonCode" class="inp100" placeholder="Търсене по код..." suggest="suggest" queryType="suggestObjectPerson" onkeypress="formatDigits( event )" onkeyup="onKeyPressPersonCode()" maxlength="12"/>
							<input type="text" id="sPersonName" name="sPersonName" class="inp300" placeholder="Търсене по име..." suggest="suggest" queryType="suggestObjectPerson" onkeyup="onKeyPressPersonName()"/>
						</div>
					
					</td>
					<td width="60">От месец</td>
					<td align="left">
					
					<div class="col-sm-6 col-md-3" style="width:134px;">
					
					<div class="input-group">
							<span class="input-group-addon">
								<img src="images/glyphicons/hand_left.png" onclick="nextMonth('prev','dateFrom');" style="width: 12px; height: 12px; cursor:pointer;">
							</span>					
							<input 
								style="width:50px;" 
								id="dateDDS" 
								name="dateFrom" 
								type="text" 
								  
								maxlength="7" 
								readonly 
								title="ММ.ГГГГ" 
								value={$smarty.now|date_format:'%m.%Y'}
							>
							<span class="input-group-addon">
								<img src="images/glyphicons/hand_right.png" onclick="nextMonth('next','dateFrom');" style="width: 12px; height: 12px; cursor:pointer;">
							</span>					
						</div>
						</div>
					</td>
					<td style="text-align: right; padding-right: 2px;">
						<button id="b100" class="btn btn-xs btn-success" onClick="addPerson()"><i class="fa fa-plus"></i> Добави</button>
					</td>
				</tr>
			</table>

			
			<hr>
			
			<div id="result" rpc_excel_panel="off" rpc_resize="off" rpc_paging="off" style="width:780px; height:360px;overflow: auto;"></div>
			
			</div>
		 	<!-- край на работната част -->
			</td>
		</tr>
	</table>


	<div id="search"  style="padding-top:10px;width:800px;">
		<table class="page_data" >
			<tr valign="top">
				<td valign="top" style="text-align: left; width: 200px; padding: 10px 0 10px 1px;">
					<button id="b100" class="btn btn-xs btn-info" onClick="goSort();" class="search"><img src="images/glyphicons/bullets.png" />Подреди</button>
				</td>
				<td valign="top" style="text-align: right; width: 600px; padding: 10px 1px 10px 0;">
					<button id="b100" class="btn btn-xs btn-primary" onClick="openSchedule();"><img src="images/glyphicons/list.png" > График </button>
					<button id="b100" class="btn btn-xs btn-danger" onClick="window.close();"><img src="images/glyphicons/cancel.png" > Затвори </button>
				</td>
			</tr>
		</table>
	</div>
	<div id="NoDisplay" style="display:none"></div>
</form>

<script>
	onInit();
	
	{if !$edit.object_personnel_schedule_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}	
</script>
	