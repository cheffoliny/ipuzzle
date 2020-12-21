{literal}
	<script>
		rpc_debug = true;
		
		function editActives( id ) {
			dialogActives( id );
		}
		
		function deleteActives( id ) {
			if( confirm('Наистина ли желаете да премахнете записа?') ) {
				$('id').value = id;
				loadXMLDoc( 'delete' );
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Номенклатури - АКТИВИ</td>
			<td class="buttons">
			{if $right_edit}<button onclick="editActives( 0 );"><img src="images/plus.gif"> Добави </button>
			{else}&nbsp;
			{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
<script>
		loadXMLDoc('result');
</script>
{/literal}