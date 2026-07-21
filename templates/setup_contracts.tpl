<script>
{literal}
	rpc_debug = true;
	
	function openContract(id) {
		dialogSetSetupContracts('id=' + id, id);
	}

	function delContract(id) {
		if ( confirm('Наистина ли желаете да премахнете записа?') ) {
			$('nID').value = id;
			loadXMLDoc2('delete', 1);
		}
	}
	
{/literal}
</script>

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-contract-list ui-contract-settings" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class="page_data ui-nomenclature-heading ui-contract-heading">
		<tr>
			<td class="page_name">Електронен договор - НАСТРОЙКИ</td>
			<td class="buttons">
				{if $right_edit}<button type="button" class="search" onclick="openContract(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result" class="ui-contract-result"></div>

</form>


<script>
	loadXMLDoc2('result');
</script>
