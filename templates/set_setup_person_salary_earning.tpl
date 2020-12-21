{literal}
<script>
	rpc_debug=true;
	
	var my_action = '';
	
	function roundNumber( num, dec )
	{
		var result = Math.round( num * Math.pow( 10, dec ) ) / Math.pow( 10, dec );
		return result;
	}
	
	InitSuggestForm = function() {			
		for(var i = 0; i < suggest_elements.length; i++) {
			if( suggest_elements[i]['id'] == 'region_object' ) {
				suggest_elements[i]['suggest'].setSelectionListener( onSuggestObject );
			}
		}
	}
			
	function onSuggestObject ( aParams ) {
		$('id_object').value = aParams.KEY;
	}
	
	function onRegionObjectChange() {
		$('id_object').value = 0;
	}
	
	function sum_one( sum )
	{
		var mult = document.getElementById( 'count' ).value;
		if( mult < 1 ) mult = 1;
		document.getElementById( 'sum_total' ).value = roundNumber( ( sum * mult ), 2 );
	}

	function sum_two( mult )
	{
		var sum = document.getElementById( 'sum' ).value;
		var sum_total = document.getElementById( 'sum_total' ).value;
		if( ( sum > 0 ) && ( mult > 0 ) )document.getElementById( 'sum_total' ).value = roundNumber( ( sum * mult ), 2 );
		else if( ( sum_total > 0 ) && ( mult > 0 ) )document.getElementById( 'sum' ).value = roundNumber( ( sum_total / multi ), 2 );
	}
	
	function sum_three( sum )
	{
		var mult = document.getElementById( 'count' ).value;
		if( mult < 1 )mult = 1;
		document.getElementById( 'sum' ).value = roundNumber( ( sum / mult ), 2 );
	}
	
	function formSubmit() {
		document.getElementById('sAct').value = 'load';
		my_action = 'save';
		loadXMLDoc( 'save',3);
		//loadXMLDoc( 'save', 0 );
	}
	
	function formChange() {
		document.getElementById('sAct').value = 'load';
		loadXMLDoc('load', 0);
	}
	
	function codeChange(obj) {
		var decr = document.getElementById('description');
		var name = obj[obj.selectedIndex].id;
		decr.value = name;
	}
	
	document.onkeydown = function(e) {
		if ( document.all ) {
			keycd = event.keyCode; 
		} else {
			keycd = e.keyCode;
		} 
		if ( keycd == 27 ) return false;
	}
		
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="id" name="id" value="{$id}" />
		<input type="hidden" id="id_object" name="id_object" value="{$id_object}" />
		<input type="hidden" id="id_person" name="id_person" value="{$id_person}" />
		<input type="hidden" id="is_earning" name="is_earning" value="{$type}" />
		<input type="hidden" id="id_code" name="id_code" value="0" />
		<input type="hidden" id="sAct" name="sAct" value="load" />
		<input type="hidden" id="month" name="month" value="{$year}{$month}" />
		
		<input type="hidden" id="refresh" name="refresh" value="{$refresh}" />
		<input type="hidden" id="office" name="office" value="{$office}" />
		<input type="hidden" id="firm" name="firm" value="{$firm}" />
		<input type="hidden" id="codeto" name="codeto" value="{$codeto}" />

		<div class="page_caption">{if $type eq 1}Наработка{else}Удръжка{/if} на служител за {$month}.{$year}</div>
		<fieldset>
			<legend>Обща информация за наработката:</legend><br />
			<table class="input">
			
				<tr class="even">
					<td width="140">Код на наработката:</td>
					<td width="280px">
						<table class="input" cellpadding="0" cellspacing="0">
							<tr>
								<td>
									<select id="code" name="code" style="width: 120px;" onChange="codeChange(this);"></select>
								</td>
								<td align="right">
									<input type="checkbox" id="auto" name="auto" class="clear" />
								</td align="right">
								<td>Автоматична</td>
							</tr>
						</table>
					</td>
				</tr>
				
				<tr class="even">
					<td>Фирма:</td>
					<td>
						<select name="nIDFirm" id="nIDFirm" style="width: 260px;" onChange="formChange();" ></select>
					</td>
				</tr>

				<tr class="even">
					<td>Регион:</td>
					<td>
						<select name="nIDOffice" id="nIDOffice" style="width: 260px;" onChange="formChange();" ></select>
					</td>
				</tr>
				
				<tr class="even">
					<td>Към обект:</td>
					<td><input id="region_object" name="region_object" type="text" class="default" suggest="suggest" queryType="region_object" queryParams="nIDFirm;nIDOffice" onchange="onRegionObjectChange()" onpast="onRegionObjectChange()" style="width: 260px;" /></td>
				</tr>
				
				<tr class="even">
					<td>Кратко описание:</td>
					<td><input id="description" name="description" type="text" class="default" style="width: 260px;" /></td>
				</tr>
				
			</table><br />
		</fieldset>
		
		<fieldset>
			<legend>Стойност на наработката:</legend><br />
			<table class="input">
			
				<tr class="even">
					<td width="140">Ед. стойност:</td>
					<td><input id="sum" name="sum" type="text" class="default" onKeyPress="return formatMoney(event);" onKeyUp="sum_one(this.value);" /></td>
				</tr>
				
				<tr class="even">
					<td>Количество:</td>
					<td><input id="count" name="count" type="text" class="default" onkeypress="return formatNumber(event);" onKeyUp="sum_two(this.value);" value="1" /></td>
				</tr>
				
				<tr class="even">
					<td>Обща стойност:</td>
					<td><input id="sum_total" name="sum_total" type="text" class="default" onKeyPress="return formatMoney( event );" onKeyUp="sum_three( this.value );" /></td>
				</tr>
				
			</table><br />
		</fieldset>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" class="search" onClick="formSubmit();"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
</div>

{literal}
	<script>
		loadXMLDoc('result');

		document.onkeydown = function(e) {
			if ( document.all ) {
				keycd = event.keyCode; 
			} else {
				keycd = e.keyCode;
			} 
			
			if ( keycd == 13 ) formSubmit();
		}

		rpc_on_exit = function( err )
		{
			if( my_action == 'save' && err == 0 )
			{
				if( window.opener && !window.opener.closed )
					window.opener.loadXMLDoc('result');
				
				my_action = '';
			}
		}
		
	</script>
{/literal}
