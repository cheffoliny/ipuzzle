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

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">

	<div class="page_caption">Номенклатури фирми - Редакция</div>
	
	<center>
	<table class="input" style="margin:20px 0px 20px 0px;width:400px;">
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
				<fieldset>
				<legend>Номенклатури приходи</legend>
				<table>
					<tr>
						<td>
							<select name="all_earnings" id="all_earnings" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'right');" multiple>
							</select>
						</td>
						<td>
							<button class="search" style="width: 50px;" name="button" title="Добави номенклатура приход" onClick="move_option_to( 'all_earnings', 'account_earnings', 'right'); return false;"><img src="images/mright.gif" /></button></br>
							<button name="button" style="width: 50px;" title="Премахни номенклатура приход" onClick="move_option_to( 'all_earnings', 'account_earnings', 'left'); return false;"><img src="images/mleft.gif" /></button>
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
				<fieldset>
				<legend>Номенклатури разходи</legend>
				<table>
					<tr>
						<td>
							<select name="all_expenses" id="all_expenses" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'right');" multiple>
							</select>
						</td>
						<td>
							<button class="search" style="width: 50px;" name="button" title="Добави номенклатура разход" onClick="move_option_to( 'all_expenses', 'account_expenses', 'right'); return false;"><img src="images/mright.gif" /></button></br>
							<button name="button" style="width: 50px;" title="Премахни номенклатура разход" onClick="move_option_to( 'all_expenses', 'account_expenses', 'left'); return false;"><img src="images/mleft.gif" /></button>
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
	<table style="margin-top:20px;width:100%;">
		<tr>
			<td align="right">
				<button onclick="formSubmit();"><img src="images/confirm.gif">Запиши</button>
				<button onclick="window.close();"><img src="images/cancel.gif">Затвори</button>
			</td>
		</tr>
	</table>
</form>

<script>
	onInit();
</script>