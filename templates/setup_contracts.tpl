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

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Електронен договор - НАСТРОЙКИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openContract(0);"><img src="images/plus.gif"> Добави </button>
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