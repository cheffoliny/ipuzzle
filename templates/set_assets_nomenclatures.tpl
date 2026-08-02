{literal}
	<script>
		rpc_debug = true;
		rpc_html_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		
	/*	function getResult() {
			select_all_options('account_documents');
			loadXMLDoc2('result',1)
			$('filters').style.display = "none";
				$('show_filters').value = "hide"
		}
		
		function showFilters() {
			if(	$('show_filters').value == "show") {
				$('filters').style.display = "none";
				$('show_filters').value = "hide";
				
			} else {
				$('filters').style.display = "block";
				$('show_filters').value = "show";
			}
			if(typeof(xslResizer) == 'function') {
				xslResizer();
			}
		}
		function openPerson( nIDPerson ) {
			if( parseInt( nIDPerson ) )
				dialogPerson( nIDPerson );
		}*/
		function formSubmit()
		{   select_all_options('account_attributes');
			loadXMLDoc2('save',3);
		}
	</script>
{/literal}

<div class="content ui-nomenclature-dialog-shell">
<form action="" name="form1" id="form1" class="ui-nomenclature-dialog ui-asset-dialog ui-asset-nomenclature-dialog" onSubmit="return false;">
	<input type="hidden" name="show_filters" id="show_filters" value="show">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	
			<div class="page_caption">{if $nID}Редакция на{else}Нова{/if} Номенклатура</div>
		<br />
		
		<table class="input ui-nomenclature-form">
			<tr class="even">
				<td align="right">Име:</td>
				<td>
					<input type=text name="sName" id="sName" style="width: 220px;" />
				</td>
			</tr>
			
			<tr class="odd">
				<td align="right">Група:</td>
				<td>
					<select name="nIDGroup" id="nIDGroup" style="width: 220px;" />
				</td>
		</table>
	
	
	<div id="filters" class="ui-asset-transfer-wrap">
	
	<center>
	  	  	
	  	<table class="search ui-asset-transfer-shell" cellspacing="3">
			<tr>
				<td valign="top" align="center">
					<fieldset class="ui-nomenclature-fieldset">
					<legend>Атрибути</legend>
						<table class="ui-nomenclature-transfer">
							<tr style="height: 5px;"><td colspan="3"></td></tr>
							<tr class="even">
								<td>
									<select name="all_attributes" id="all_attributes" size="10"  style="width: 250px;" ondblclick="move_option_to( 'all_attributes', 'account_attributes', 'right');" multiple>
									</select>
								</td>
								<td>
									<button type="button" class="search ui-nomenclature-transfer-button" name="button" title="Добави атрибут" onClick="move_option_to( 'all_attributes', 'account_attributes', 'right'); return false;"><span class="ui-icon ui-icon-right" aria-hidden="true"></span></button><br />
									<button type="button" class="ui-nomenclature-transfer-button" name="button" title="Премахни атрибут" onClick="move_option_to( 'all_attributes', 'account_attributes', 'left'); return false;"><span class="ui-icon ui-icon-left" aria-hidden="true"></span></button>
								</td>
								<td>
									<select name="account_attributes[]" id="account_attributes" size="10" style="width: 250px;" ondblclick="move_option_to( 'all_attributes', 'account_attributes', 'left');" multiple>
									</select>
								</td>
							</tr>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
	  	
	</center>
	
	</div>
	
	<hr>
	<br />
		<table class="input ui-nomenclature-actions">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search" onclick="formSubmit();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
					<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
				</td>
			</tr>
		</table>

</form>
</div>

{literal}
	<script>
		onInit();
	</script>
{/literal}
