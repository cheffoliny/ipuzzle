{literal}
	<script>
		rpc_debug = true;

		function openLeave(id) {
			var person = document.getElementById('id').value;
			dialogLeave(id, person);
		}

		function openPersonLeave( id )
		{
			var id_person = document.getElementById( 'id' ).value;

			dialogSetupPersonLeave( id, id_person );
		}

		function delLeave(id) {
			if( confirm('Наистина ли желаете да премахнете записа?') ) {
				document.getElementById('idc').value = id;
				loadXMLDoc('delete', 1);
				document.getElementById('idc').value = 0;
			}
		}

		function openApplication(id) {
			var person = document.getElementById('id').value;
			dialogApplication( id, person );
		}

		function openHospital(id) {
			var person = document.getElementById('id').value;
			dialogHospital( id, person );
		}

		function openQuittance( id )
		{
			var person = document.getElementById('id').value;
			dialogQuittance( id, person );
		}

		function rpcEnd( oCallerHandle )
		{
			rpc_on_exit = function()
			{
				if( oCallerHandle )oCallerHandle.focus();

				rpc_on_exit = function() {}
			}

			loadXMLDoc( "result" );
		}
	</script>
{/literal}

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="id" name="id" value="{$id|default:0}" />
		<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
		<input type="hidden" id="idc" name="idc" value="0" />

		{include file='person_tabs.tpl'}

		<div class="row mb-1">
			<div class="col ml-3">
				<div class="input-group input-group-sm">
					<span class="input-group-addon">
						<input type="checkbox"
							   id="nIsSubstituteNeeded"
							   name="nIsSubstituteNeeded"
							   class="clear"
							   title="Иска ли се посочване на заместник."
							   onclick="loadXMLDoc( 'save' );">
						<span class="input-group-addon">Посочва се заместник</span>
					</span>
				</div>
			</div>
			<div class="col">
				<button class="btn btn-sm btn-success" id="new_leave" name="new_leave" onclick="return openPersonLeave( 0 );"><i class="far fa-plus"></i>&nbsp;Нова Молба</button>
				<!-- <button class="search" onclick="return openLeave(0);"><img src="images/plus.gif"/>Отпуск</button> -->
				<button class="btn btn-sm btn-success" id="e_leave" name="e_leave"  onclick="return openApplication(0);">Молби за Отпуск</button>
				<button class="btn btn-sm btn-success" id="e_hospital" name="e_hospital" onclick="return openHospital(0);"><i class="far fa-plus"></i>&nbsp;Болничен</button>
				<button class="btn btn-sm btn-success" id="e_quittance" name="e_quittance" onclick="return openQuittance(0);"><i class="far fa-plus"></i>&nbsp;Обезщетение</button>
			</div>
		</div>
		<div id="result"  rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height: 370px; overflow: auto;"></div>
		<nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1" id="search">
			<div class="col align-right">
				<div class="input-group input-group-sm">
					<button class="btn btn-sm btn-danger float-right" onClick="window.close();"><i class="far fa-times"></i>&nbsp;Затвори</button>
				</div>
			</div>
		</nav>

	</form>
</div>

<script>
	{if !$personnel_edit}
	{literal}
	if ( form=document.getElementById('form1') ) {
		for (i=0; i<form.elements.length-1; i++) {
			{/literal}
			{if $new_leave}
			{literal}
			if ( (form.elements[i].id == 'new_leave') || (form.elements[i].id == 'e_leave') ) {
				// nothing
			} else {
				form.elements[i].setAttribute('disabled', 'disabled');
			}
			{/literal}
			{else}
			form.elements[i].setAttribute('disabled', 'disabled');
			{/if}
			{literal}
		}
	}
	{/literal}
	{/if}
	loadXMLDoc('result');
</script>