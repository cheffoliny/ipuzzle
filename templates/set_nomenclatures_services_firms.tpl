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

<form id="form1" class="ui-nomenclature-dialog ui-account-mapping-dialog" action="" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	
	<div class="page_caption">Услуги фирми - Редакция</div>
	
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
	<fieldset class="ui-nomenclature-fieldset">
	<table class="ui-nomenclature-transfer">
		<tr>
			<td>
				<select name="all_services" id="all_services" size="10"  style="width: 350px;" ondblclick="move_option_to( 'all_services', 'account_services', 'right');" multiple>
				</select>
			</td>
			<td>
				<button class="search ui-nomenclature-transfer-button" name="button" title="Добави услуга" onClick="move_option_to( 'all_services', 'account_services', 'right'); return false;"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button><br />
				<button class="ui-nomenclature-transfer-button" name="button" title="Премахни услуга" onClick="move_option_to( 'all_services', 'account_services', 'left'); return false;"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
			</td>
			<td>
				<select name="account_services[]" id="account_services" size="10" style="width: 350px;" ondblclick="move_option_to( 'all_services', 'account_services', 'left');" multiple>
				</select>
			</td>
		</tr>
	</table>
	</fieldset>
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
