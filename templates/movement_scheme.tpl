{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		
		function getResult() {
			loadXMLDoc2('save',5)
		}

	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-dialog ui-configuration-dialog ui-movement-scheme-dialog" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	
	<div class="page_caption">{if !$nID }Добавяне{else}Редактиране{/if} на шаблон</div>
	<br>
	<table class="page_data ui-nomenclature-form">
		<tr>
			<td align="right">
				Име:
			</td>
			<td>
				<input style="width:200px;" type="text" id="name" name="name"/>
			</td>
		</tr>
	</table>

	<table class="ui-movement-scheme-fields" border="0">
		<tr>
			<td>
				<table border="0">
					<tr>
						
						<td style="width:300px;"  valign="top" align="center">
								<fieldset class="ui-nomenclature-fieldset">
								<legend>Видими полета</legend>
								<table>
									<tr>
										<td>
											<input type="checkbox" class="clear ui-nomenclature-checkbox" name="office" id="office"/>  регион
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear ui-nomenclature-checkbox" name="start_time" id="start_time" />  време на оповестяване 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear ui-nomenclature-checkbox" name="end_time" id="end_time"/>  време на пристигане 
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear ui-nomenclature-checkbox" name="reason_time" id="reason_time"/>  време на освобождаване
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear ui-nomenclature-checkbox" name="stay_time" id="stay_time"/>  престой
										</td>
									</tr>
									<tr>
										<td>
											<input type="checkbox" class="clear ui-nomenclature-checkbox" name="note" id="note"/> бележка
										</td>
									</tr>
								</table>
								</fieldset>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<input type="checkbox" class="clear ui-nomenclature-checkbox" name="def" id="def"/>  шаблон по подразбиране
			</td>
		</tr>
	</table>
	<table class="ui-nomenclature-actions" border="0" width="100%">
		<tr class="odd">
			<td style="text-align:right;">
				<button type="button" class="search" onClick="getResult();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
				<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
			</td>
		</tr>
	</table>


</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}
