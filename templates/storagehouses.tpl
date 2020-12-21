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

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	<input type="hidden" id="nIDRegion" name="nIDRegion" value="0" />
	<input type="hidden" id="id_firm" name="id_firm" value="1" />
	
	<table class="page_data">
		<tr>
			<td class="page_name">Склад - СКЛАДОВЕ</td>
			<td class="buttons"> 
				{if $right_edit}<button class="search" onclick="editStoragehouse(0);"><img src="images/plus.gif"> Добави </button>
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
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="Offices();" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Тип Склад</td>
				<td>
					<select name="sType" id="sType" class="select150">
						<option value="">-- Всички --</option>
						<option value="new">Нова Техника</option>
						<option value="virtual">Виртуален</option>
						<option value="recik">Рециклирана Техника</option>
						<option value="removed">Свалена Техника</option>
					</select>
				</td>
				<td align="right"><button name="Button" onclick="formSubmit();"><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>
</form>

<script>
	onInit();
</script>