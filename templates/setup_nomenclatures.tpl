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

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<input type="hidden" id="file_name" name="file_name" valur="">
	<input type="hidden" id="file_type" name="file_type" valur="">
	
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Номенклатури</td>
			<td class="buttons">
				{if $right_edit}<button onclick="setupNomenclature( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
				<button onclick="importNomenclature();"><span class="ui-icon ui-icon-file-import" aria-hidden="true"></span> От Файл </button>
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter">
			<tr>
				<td align="right">Тип Номенклатура</td>
				<td>
					<select class="default form-control" name="nIDNomenclatureType" id="nIDNomenclatureType" />
				</td>
				<td align="right">
					<button name="Button" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button>
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
