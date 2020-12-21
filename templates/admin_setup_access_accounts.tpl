{literal}
<script>
	rpc_debug=true;

	function setupAccount(id) {
		dialogSetupAccount(id);
	}

	function changePassword(id) {
		dialogPassword(id);
	}

	function deleteAccount(id) {
		if( confirm('Наистина ли желаете да премахнeте потребителя?') ){
			document.getElementById('id').value = id;
			loadXMLDoc('delete');
			document.getElementById('id').value = 0;
		}
	}
</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id_profile" id="id_profile" value="{$id_profile|default:0}">
	<input type="hidden" name="id" id="id" value="0">
	<table class="page_data">
		<tr>
			<td class="page_name">Администриране - ПОТРЕБИТЕЛИ</td>
			<td class="buttons"> 
				{if $right_edit}<button onclick="setupAccount(0);"><img src="images/plus.gif"> Добави </button>
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
