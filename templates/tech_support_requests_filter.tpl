{literal}
<script>
	
	rpc_debug = true;

	function onInit() {
		loadXMLDoc2('load');
	}
	
	function formSubmit() {
		loadXMLDoc2('save',5);
	}
</script>
{/literal}

<form id="form1" action="" onsubmit="return false">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	
	<div class="page_caption">{if !$nID}Създаване{else}Редактиране{/if} на филтър</div>
	
	<table style="margin-top:20px;" class="input">
		<tr class="even">
			<td align="right" style="width:50px;">
				Име
			</td>
			<td>
				<input id="filter_name" name="filter_name" type="text" style="width:180px;">
			</td>
		</tr>
		<tr>
			<td colspan="2" style="padding-top:15px;">
				<fieldset>
				<legend>Видими полета</legend>
				<table class="input">
					<tr class="even">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="firm" id="firm">
						</td>
						<td>
							фирма
						</td>
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="office" id="office">
						</td>
						<td>
							регион
						</td>
					</tr>
					<tr class="odd">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="type" id="type">
						</td>
						<td>
							тип
						</td>
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="client" id="client">
						</td>
						<td>
							клиент
						</td>
					</tr>
					<tr class="even">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="created_user" id="created_user">
						</td>
						<td>
							съставил
						</td>
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="limit_card" id="limit_card">
						</td>
						<td>
							лимитна карта
						</td>
					</tr>
					<tr class="odd">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="make_planning_person_name" id="make_planning_person_name">
						</td>
						<td>
							планирал
						</td>
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="note" id="note">
						</td>
						<td>
							забележка
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr class="even">
			<td align="right">
				<input id="is_default" name="is_default" type="checkbox" class="clear">
			</td>
			<td>
				по подразбиране
			</td>
		</tr>
		<tr>
			<td style="text-align:right;" colspan="2">
				<br>
				<button type="button" class="search" onClick="formSubmit();"> Запиши </button>
				<button type="button" onClick="parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>
	
</form>

<script>
	onInit();
</script>