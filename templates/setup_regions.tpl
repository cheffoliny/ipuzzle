{literal}
	<script>
		rpc_debug = true;
		
		function newRegion( id )
		{
			var id_f = document.getElementById( 'id_firm' ).value;
			
			dialogRegion( id, id_f );
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Номенклатури - РЕГИОНИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="newRegion( 0 );"><img src="images/plus.gif"> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default" name="id_firm" id="id_firm" />
				</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc( 'result' );"><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		loadXMLDoc( 'generate', 1 );
		loadXMLDoc( 'result' );
		
		function viewRegion( id )
		{
			newRegion( id );
		}

		function deleteRegion( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('id').value = id;
				loadXMLDoc( 'delete' );
			}
		}
	</script>
{/literal}