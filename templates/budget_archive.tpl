{literal}
<script>
	rpc_debug = true;
	
	function openBudget2(id) {
		$('nIDBudget').value = id;

		loadXMLDoc2('openBudget');
	}

	function deleteBudget(id) {
		$('nIDBudget').value = id;

		if ( confirm('Наистина ли желаете да премахнете бюджета?') ) {
			loadXMLDoc2('delete', 1);
		}
	}	
	
	function openBudget(id) {
		var url = 'engine/view_budget.php?id='+encodeURI(id);

		window.open(url, "win", "width=350, height=150"); 
	}	
</script>
{/literal}

<div>
	<form name="form1" id="form1" onsubmit="return false;" >
		<input type="hidden" id="hg" name="hg" value="0" />
		<input type="hidden" id="nIDBudget" name="nIDBudget" value="0" />

		<div class="page_caption" id="capt" name="capt">Списък - БЮДЖЕТИ</div>

		<table border="0" width="100%" class="input">
			<tr>
				<td colspan="6">
					<hr>		
					<div id="result" rpc_excel_panel="off" rpc_paging="on" rpc_resize="on" style="overflow: auto;"></div>
				</td>
			</tr>
		</table>
	</form>
</div>

<script>
	loadXMLDoc2( 'result' );
</script>