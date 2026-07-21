{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function onChangeFirm() {
		
			$('all_earnings').options.length = 0;
			$('account_earnings').options.length = 0;
			$('all_expenses').options.length = 0;
			$('account_expenses').options.length = 0;	
			
			loadXMLDoc2('result');
		}
		
		function formSubmit() {
			select_all_options('account_earnings');
			select_all_options('account_expenses');			
			loadXMLDoc2('save',3);
		}
	</script>

{/literal}

<form id="form1" class="ui-nomenclature-dialog ui-account-mapping-dialog" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">

	<div class="page_caption">Номенклатури фирми - Редакция</div>
	
	<center>
	<table class="input ui-nomenclature-form" style="margin:20px 0px 20px 0px;width:400px;">
		<tr>
			<td align="right">
				Фирма:
			</td>
			<td>
				<select id="nIDFirm" name="nIDFirm" onchange="onChangeFirm();"></select>
			</td>
		</tr>
	</table>
	
	<table class="input">
		<tr>
			<td align="center">
				<fieldset class="ui-nomenclature-fieldset">
				<legend>Номенклатури приходи</legend>
				<table class="ui-nomenclature-transfer">
					<tr>
						<td>
							<select name="all_earnings" id="all_earnings" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'right');" multiple>
							</select>
						</td>
						<td>
							<button class="search ui-nomenclature-transfer-button" name="button" title="Добави номенклатура приход" onClick="move_option_to( 'all_earnings', 'account_earnings', 'right'); return false;"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button><br />
							<button class="ui-nomenclature-transfer-button" name="button" title="Премахни номенклатура приход" onClick="move_option_to( 'all_earnings', 'account_earnings', 'left'); return false;"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
						</td>
						<td>
							<select name="account_earnings[]" id="account_earnings" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'left');" multiple>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align="center">
				<fieldset class="ui-nomenclature-fieldset">
				<legend>Номенклатури разходи</legend>
				<table class="ui-nomenclature-transfer">
					<tr>
						<td>
							<select name="all_expenses" id="all_expenses" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'right');" multiple>
							</select>
						</td>
						<td>
							<button class="search ui-nomenclature-transfer-button" name="button" title="Добави номенклатура разход" onClick="move_option_to( 'all_expenses', 'account_expenses', 'right'); return false;"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button><br />
							<button class="ui-nomenclature-transfer-button" name="button" title="Премахни номенклатура разход" onClick="move_option_to( 'all_expenses', 'account_expenses', 'left'); return false;"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
						</td>
						<td>
							<select name="account_expenses[]" id="account_expenses" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'left');" multiple>
							</select>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	</center>
	<table class="ui-nomenclature-actions" style="margin-top:20px;width:100%;">
		<tr>
			<td align="right">
				<button onclick="formSubmit();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span>Запиши</button>
				<button onclick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
			</td>
		</tr>
	</table>
</form>

<script>
	onInit();
</script>
