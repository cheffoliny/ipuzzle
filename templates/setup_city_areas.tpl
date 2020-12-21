{literal}
	<script>
		rpc_debug = true;
		
		function setupArea( id )
		{
			var id_city = document.getElementById( 'nIDCity' ).value;
			dialogSetSetupCityArea( id, id_city );
		}
		
		function deleteArea( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	
	<table class = "page_data">
		<tr>
			<td class="page_name">Квартали</td>
			<td class="buttons">
				{if $right_edit}<button onclick="setupArea( 0 );"><img src="images/plus.gif"> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Населено място:</td>
				<td>
					<select class="default" name="nIDCity" id="nIDCity" />
				</td>
				<td align="right">
					<button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif">Търси</button>
				</td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( 'result' );
</script>