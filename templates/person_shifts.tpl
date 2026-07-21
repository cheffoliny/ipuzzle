<script>
{literal}
	rpc_debug = true;
	
	function editShifts(id) {
		dialogSetSetupPersonShifts( id )
	}

	function delShifts(id) {
		if ( confirm('Наистина ли желаете да премахнете записа?') ) {
			$('nID').value = id;
			loadXMLDoc2('delete', 1);
		}
	}
{/literal}	
</script>

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-schedule-list ui-person-shifts-list" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Номенклатури - СМЕНИ</td>
			<td class="buttons">
				{if $right_edit}<button type="button" onclick="editShifts( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>


<script>
	loadXMLDoc2('result');
</script>
