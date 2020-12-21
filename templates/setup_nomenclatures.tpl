{literal}
	<script>
		rpc_debug = true;
		
		function setupNomenclature( id )
		{
			var id_type = $('nIDNomenclatureType').value;
			dialogSetSetupNomenclature( 'id=' + id + '&type=' + id_type );
		}
		
		function deleteNomenclature( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('nID').value = id;
				loadXMLDoc2( 'delete', 1 );
			}
		}
		
		function importNomenclature()
		{
			dialogImportNomenclature();
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" id="file_name" name="file_name" valur="">
	<input type="hidden" id="file_type" name="file_type" valur="">
	
	<table class = "page_data">
		<tr>
			<td class="page_name">Номенклатури</td>
			<td class="buttons">
				{if $right_edit}<button onclick="setupNomenclature( 0 );"><img src="images/plus.gif"> Добави </button>
				{else}&nbsp;
				{/if}
				<button onclick="importNomenclature();"><img src="images/plus.gif"> От Файл </button>
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Тип Номенклатура</td>
				<td>
					<select class="default" name="nIDNomenclatureType" id="nIDNomenclatureType" />
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