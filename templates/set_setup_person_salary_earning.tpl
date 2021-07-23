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

	<div class="modal-content pb-3">
		<div class="modal-header">
			{if $type eq 1}Наработка{else}Удръжка{/if} на служител за {$month}.{$year}
			<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>

		<div class="modal-body">
			<div class="input-group input-group-sm mb-1 text-white bg-info p-2"> Обща информация за наработката </div>
			<div class="row">
				<div class="col-7">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="far fa-money-bill-wave"  data-fa-transform="right-22 down-10" title="Код на наработка"></i>
						</div>
						<select class="form-control" id="code" name="code" onChange="codeChange(this);"></select>
					</div>
				</div>
				<div class="col-5">
					<div class="col-6">
						<div class="custom-control custom-checkbox ">
							<input class="custom-control-input" type="checkbox" id="auto" name="auto"/>
							<label class="custom-control-label" for="auto">Автоматична</label>
						</div>
					</div>
				</div>
			</div>

			<div class="row py-1">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fas fa-tag"  data-fa-transform="right-22 down-10" title="Фирма"></i>
						</div>
						<select class="form-control" name="nIDFirm" id="nIDFirm" onChange="formChange();" ></select>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fas fa-tags"  data-fa-transform="right-22 down-10" title="Регион"></i>
						</div>
						<select class="form-control" name="nIDOffice" id="nIDOffice" onChange="formChange();" ></select>
					</div>
				</div>
			</div>

			<div class="row py-1">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fas fa-home"  data-fa-transform="right-22 down-10" title="Обект"></i>
						</div>
						<input class="form-control" id="region_object" name="region_object" type="text" suggest="suggest" queryType="region_object" queryParams="nIDFirm;nIDOffice" onchange="onRegionObjectChange()" onpast="onRegionObjectChange()"/>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fas fa-signature"  data-fa-transform="right-22 down-10" title="Обект"></i>
						</div>
						<input class="form-control bg-faded" id="description" name="description" type="text" class="default" placeholder="Кратко описание..."/>
					</div>
				</div>
			</div>

			<div class="input-group input-group-sm mb-1 text-white bg-info p-2"> Стойност на наработката </div>
			<div class="row">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fas fa-money-bill"  data-fa-transform="right-22 down-10" title="Ед. стойност..."></i>
						</div>
						<input class="form-control text-right pr-5" id="sum" name="sum" type="text" class="default" onKeyPress="return formatMoney(event);" onKeyUp="sum_one(this.value);" placeholder="Ед. стойност" />
					</div>
				</div>
			</div>
			<div class="row py-1">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="far fa-digital-tachograph"  data-fa-transform="right-22 down-10" title="Количество..."></i>
						</div>
						<input class="form-control text-right pr-5" id="count" name="count" type="text" class="default" onkeypress="return formatNumber(event);" onKeyUp="sum_two(this.value);" value="1" placeholder="Количество"/>
						<div class="input-group-append">бр.</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<i class="fas fa-money-bill" data-fa-transform="right-22 down-10" title="Обща стойност..."></i>
						</div>
						<input class="form-control text-right pr-5" id="sum_total" name="sum_total" type="text" class="default" onKeyPress="return formatMoney( event );" onKeyUp="sum_three( this.value );" placeholder="Обща стойност" />
						<div class="input-group-append">лв.</div>
					</div>
				</div>
			</div>

			<nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
				<div class="col">
					<div class="input-group input-group-sm text-right">
						<button class="btn btn-block btn-sm btn-primary" onClick="formSubmit();"><i class="fa fa-plus"></i> Запиши</button>
					</div>
				</div>
			</nav>
		</div>

	</div>
</form>

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
