{literal}
<script>
	rpc_debug = true;

	function setupMenu(id) {
		dialogMenu(id);
	}

	function deleteMenu(id) {
		if ( confirm('Наистина ли желаете да премахнете менюто?') ) {
			document.getElementById('id').value = id;
			loadXMLDoc('delete', 1);
			document.getElementById('id').value = 0;
		}
	}
</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="id" name="id" value="0" />
	<table class = "page_data">
		<tr>
			<td class="page_name">Администрация - МЕНЮ</td>
			<td class="buttons"> 
				{if $right_edit}<button class="btn btn-sm btn-success" onclick="setupMenu(0);"><i class="far fa-plus"></i> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc('result');
</script>