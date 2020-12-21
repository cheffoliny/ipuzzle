{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2('result');
		}
		
		function onChangeFirm() {
		
			$('all_services').options.length = 0;
			$('account_services').options.length = 0;
				
			loadXMLDoc2('result');
		}
		
		function formSubmit() {
			select_all_options('account_services');			
			loadXMLDoc2('save',3);
		}
	</script>

{/literal}

<form id="form1" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<div class="page_caption">Услуги фирми - Редакция</div>
	
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
	<table>
		<tr>
			<td>
				<select name="all_services" id="all_services" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_services', 'account_services', 'right');" multiple>
				</select>
			</td>
			<td>
				<button class="search" style="width: 50px;" name="button" title="Добави услуга" onClick="move_option_to( 'all_services', 'account_services', 'right'); return false;"><img src="images/mright.gif" /></button></br>
				<button name="button" style="width: 50px;" title="Премахни услуга" onClick="move_option_to( 'all_services', 'account_services', 'left'); return false;"><img src="images/mleft.gif" /></button>
			</td>
			<td>
				<select name="account_services[]" id="account_services" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_services', 'account_services', 'left');" multiple>
				</select>
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