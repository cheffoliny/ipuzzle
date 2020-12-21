{literal}
<script>
	rpc_debug = true;
	
	function formSubmit() {
		loadXMLDoc2('save', 3);
		//loadXMLDoc2('save', 0);
	}

	function loadForm() {
		loadXMLDoc2('load');
	}
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}">
		
		<div class="page_caption">{if $nID}Редакция на{else}Нов{/if} проблем</div>

		<table class="input" width="100%">
			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
			<tr class="even">
				<td style="width: 140px; text-align: right;">Тип:</td>
				<td>
					<select name="sTroubleType" id="sTroubleType" style="width: 240px;" onChange="loadForm();" >
					</select>
				</td>
			</tr>
			
			<tr class="odd" align="center">
				<td style="width: 400px;" colspan="2" >
					<fieldset style="border: 1px solid black; vertical-align: top;" >
					<legend>Проблем</legend>
						<table class="input">
							<tr>
								<td style="width: 90px; text-align: right;">Тип</td>
								<td align="left">
									<select name="nTroubleTypeName" id="nTroubleTypeName" style="width: 240px;" ></select>
								</td>
							</tr>
							<tr>
								<td style="width: 90px; text-align: right;">Информация</td>
								<td align="left">
									<textarea style="width: 280px; height: 70px;" id="sTroubleInfo" name="sTroubleInfo" ></textarea>
								</td>
							</tr>
							
							<tr><td style="height: 5px;" colspan="2"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>
			
			<tr class="odd">
				<td style="width: 400px;" colspan="2">
					<fieldset style="border: 1px solid black; vertical-align: top;" >
					<legend>Причина</legend>
						<table class="input">
							<tr>
								<td style="width: 90px; text-align: right;">Тип</td>
								<td align="left">
									<select name="nReasonTypeName" id="nReasonTypeName" style="width: 240px;" ></select>
								</td>
							</tr>
							<tr>
								<td style="width: 90px; text-align: right;">Информация</td>
								<td align="left">
									<textarea style="width: 280px; height: 70px;" id="sReasonInfo" name="sReasonInfo" ></textarea>
								</td>
							</tr>
							
							<tr><td style="height: 5px;" colspan="2"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>

			<tr class="odd"><td colspan="2" style="height: 5px;"></td></tr>
		</table>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" onClick="formSubmit()" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadForm();
</script>