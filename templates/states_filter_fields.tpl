{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( 'load' );
		}
		
		function saveData()
		{
			var nID = $('nID').value;
			
			loadXMLDoc2( 'save' );
			rpc_on_exit = function()
			{
				window.location = 'page.php?page=states_filter_totals&id=' + nID;
				rpc_on_exit = function() {}
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<div class="page_caption">
		Редактиране на филтър
	</div>

	<table  cellspacing="0" cellpadding="0" width="100%"  border="0" id="filter" >
  		<tr>
  			<td>
  				{include file=states_filter_tabs.tpl}
  				<br>
  			</td>
  		</tr>
  	</table>
	
	<table border="0" class="input">
		<tr>
			<td>
				<fieldset>
				<legend>Видими полета</legend>
				<table class="input" border="0">
					<tr class="even">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="field_office" id="field_office" />
						</td>
						<td align="left">
							Регион
						</td>
					</tr>
					<tr class="odd">
						<td>
							<input type="checkbox" class="clear" name="field_storage_type" id="field_storage_type"/>
						</td>
						<td>
							Склад/Обект/Служител
						</td>
					</tr>
					<tr class="even">
						<td>
							<input type="checkbox" class="clear" name="field_nomenclature_type" id="field_nomenclature_type"/>
						</td>
						<td>
							Тип Номенклатура
						</td>
					</tr>
					<tr class="odd">
						<td>
							<input type="checkbox" class="clear" name="field_count" id="field_count"/>
						</td>
						<td>
							Количество
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr class="odd">
			<td style="text-align:right;">
				<br><br><br><br><br><br><br><br><br>
				<button type="button" class="search" onClick="saveData();"> Запиши </button>
				<button type="button" onClick="opener.window.loadXMLDoc2( 'load' ); parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>
</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}