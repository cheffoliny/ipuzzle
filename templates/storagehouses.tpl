{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2('load');
		}
		
		function onRegionChange()
		{
			$('nIDRegion').value = 0;
		}
		
		function editStoragehouse(id)
		{
			dialogStoragehouse(id);
		}
		
		function Offices()
		{
			loadXMLDoc2('loadOffices');
		}
		
		function formSubmit()
		{
			loadXMLDoc2('result');
		}
		
		function delStoragehouse(id)
		{
			if ( confirm('Наистина ли желаете да премахнете зaписа?') )
			{
				$('nID').value = id;
				loadXMLDoc2('delete', 1);
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list ui-assets-list ui-storagehouses-list" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	<input type="hidden" id="nIDRegion" name="nIDRegion" value="0" />
	<input type="hidden" id="id_firm" name="id_firm" value="1" />
	
	<table class="page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Склад - СКЛАДОВЕ</td>
			<td class="buttons"> 
				{if $right_edit}<button type="button" class="search" onclick="editStoragehouse(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter ui-storagehouses-filter">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default form-control" name="nIDFirm" id="nIDFirm" onchange="Offices();" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион</td>
				<td>
					<select class="default form-control" name="nIDOffice" id="nIDOffice" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Тип Склад</td>
				<td>
					<select name="sType" id="sType" class="select150 form-control">
						<option value="">-- Всички --</option>
						<option value="new">Нова Техника</option>
						<option value="virtual">Виртуален</option>
						<option value="recik">Рециклирана Техника</option>
						<option value="removed">Свалена Техника</option>
					</select>
				</td>
				<td align="right"><button type="button" name="Button" onclick="formSubmit();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	onInit();
</script>
