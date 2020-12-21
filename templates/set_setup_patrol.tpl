{literal}

<script>
	rpc_debug=true;
	
	function onInit() {
		loadXMLDoc2('result');
	}
	
	
	function formSelect() {
		document.getElementById('sAct').value = 'choice';
		loadXMLDoc2('result');
	}
	
	function formSave() {
		select_all_options('choice_persons');	
		loadXMLDoc2('save', 3);
	}	
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard}">
		<input type="hidden" id="sAct" name="sAct" value="list">
		
		<div class="page_caption">{if $nID}Информация за Патрул{else}Нов Патрул{/if}</div>

		<br />
		<table class="input">
			<tr class="odd">
				<td width="50">Регион:</td>
				<td colspan="3">
					<select name="nRegion" id="nRegion" style="width: 300px;" onChange="formSelect();" />
				</td>
			</tr>
			<tr class="even">
				<td>Позивна:</td>
				<td colspan="3">
					<select name="nIDPatrul" id="nIDPatrul" style="width: 300px;" />
				</td>
			</tr>
			<tr class="odd">
				<td>Автомобил:</td>
				<td style="width: 455px;">
					<select name="nAuto" id="nAuto" style="width: 300px;" />
				</td>
				<td align="right">Нач. км:&nbsp;</td>
				<td align="left">
					<input type="text" id="startKm" name="startKm" style="width: 100px; text-align: right;" onkeypress="return formatNumber(event);" />
				</td>
			</tr>
			<tr class="even">
				<td valign="top" align="center" colspan="4">
					<fieldset >
					<legend>Избор на служител</legend>
						<table>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
							<tr class="even">
								<td>
									<select name="all_persons" id="all_persons" style="width: 300px; height: 200px;" ondblclick="move_option_to( 'all_persons', 'choice_persons', 'right');" multiple>
									</select>
								</td>
								<td>
									<button class="search" style="width: 50px;" name="button" title="Добави патрул" onClick="move_option_to( 'all_persons', 'choice_persons', 'right'); return false;"><img src="images/mright.gif" /></button></br>
									<button name="button" style="width: 50px;" title="Премахни патрул" onClick="move_option_to( 'all_persons', 'choice_persons', 'left'); return false;"><img src="images/mleft.gif" /></button>
								</td>
								<td>
									<select name="choice_persons[]" id="choice_persons" style="width: 300px; height: 200px;" ondblclick="move_option_to( 'all_persons', 'choice_persons', 'left');" multiple>
									</select>
								</td>
							</tr>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
		
		<br />
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="button" onClick="formSave();" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
		
	</form>
</div>

<script>
	loadXMLDoc2('result');
</script>

{if $nID}
	{literal}
	<script>
		if( form = document.getElementById('form1') ) {
			for( i = 0; i < form.elements.length - 1; i++ ) form.elements[i].setAttribute('disabled', 'disabled');
		}
	</script>
	{/literal}
{/if}