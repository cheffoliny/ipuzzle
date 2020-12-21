{literal}
	<script>
		rpc_debug = true;
		
		function newFirm( id )
		{
			dialogFirm( id );
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Номенклатури - ФИРМИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="newFirm( 0 );"><img src="images/plus.gif"> Добави </button>
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
		
		function viewFirm( id )
		{
			newFirm( id );
		}
		function deleteFirm( id )
		{
			if( confirm('Наистина ли желаете да премахнете записа?') )
			{
				$('id').value = id;
				loadXMLDoc( 'delete' );
			}
		}
	</script>
{/literal}