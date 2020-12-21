{literal}
<script>
	function formSave() {
		var nID = $('nIDCard').value;
		if ( document.getElementById('isLOCK').value == 'no' ) {
			select_all_options('account_regions');
			var obj = $('account_regions').value.length;
			if ( obj > 0 ) {
				loadXMLDoc2('save');
				rpc_on_exit = function() {
					window.location='page.php?page=working_card_patrol&entered=1&nIDCard='+nID;
					rpc_on_exit = function() {}
				}
			} else loadXMLDoc2('save');
			
		} else alert('Работната карта е затворена!');
	}
	
	function formClose() {
		if ( document.getElementById('isLOCK').value == 'no' ) {
			loadXMLDoc2('close', 1);
			document.getElementById('capt').style.background = 'red';
		} else alert('Работната карта е затворена!');
	}	
	
	function formLoad() {
		if ( document.getElementById('locked').value == 1 ) {
			document.getElementById('capt').style.background = 'red';
		} else {
			document.getElementById('capt').style.background = 'green';
		}

	}
</script>
{/literal}

{if !$nIDCard}

	<form name="form1" id="form1" onsubmit="return false;">
		<table width="100%" height="100%">
			<tr>
				<td align="center" valign="middle">
					<table style="width: 300px; border: solid 0.1mm Black" class="input">
						<tr>
							<td colspan="2" class="even" align="center">Нямате активна работна карта!</td>
						</tr>
						<tr>
							<td colspan="2" class="even" align="center">Желаете ли да бъде създадена?</td>
						</tr>
						<tr>
							<td align="right">
								<button type="submit" onclick="window.location.href='page.php?page=working_card_info&type=new'">Да</button>
							</td>
							<td align="left">
								<button onclick="window.location.href='page.php?page=working_cards'">Отказ</button>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</form>

{else}

<script>
	rpc_debug = true;
</script>

<div>
	<form name="form1" id="form1" onsubmit="return false;" >
		<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />
		<input type="hidden" id="isLOCK" name="isLOCK" value="no" />
		<input type="hidden" id="locked" name="locked" value="{$locked|default:0}" />
		
		<div class="page_caption" id="capt" name="capt">{if $nIDCard > 0}Работна карта № {$nIDCard}, Диспечер: {$sDispName}, Застъпване: {$sFrom}{if $sTo}, Отстъпване: {$sTo}{/if} {if $locked}[ПРИКЛЮЧЕНА!]{/if}{else}Нова работна карта{/if}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=working_card_tabs.tpl}</td>
			</tr>
			<tr class="odd">
				<td>
		  		    <table class="input">
		  		    	<tr style="height: 20px;">
		  		    		<td>&nbsp;</td>
		  		    	</tr>
						<tr class="odd">
						<td valign="top" align="center">
						<fieldset style="width: 550px;">
							<legend>Информация за работната карта:</legend>
							<table class="input" style="width: 500px;">
								<tr style="height: 5px;"><td colspan="2"></td></tr>
								<tr class="even">
									<td>Диспечер:&nbsp;</td>
									<td><input name="sDispatcher" id="sDispatcher" type="text" style="width: 381px;" readonly /></td>
								</tr>
								<tr class="even">
									<td>Период:&nbsp;</td>
									<td align="left" valign="top">
										застъпване: &nbsp;<input type="text" name="sFrom" id="sFrom" style="width: 100px;"  />&nbsp;&nbsp;&nbsp;&nbsp;
										приключване: &nbsp;<input type="text" name="sTo" id="sTo" style="width: 100px;" readonly /></td>
									</td>
								</tr>
								<tr style="height: 5px;"><td colspan="2"></td></tr>
							</table>
						</fieldset>
						</td></tr>


						<tr class="odd">
							<td valign="top" align="center">
								<fieldset style="width: 750px;">
								<legend>Избор на региони</legend>
									<table>
										<tr style="height: 5px;"><td colspan="3"></td></tr>
										<tr class="even">
											<td>
												<select name="all_regions" id="all_regions" size="10"  style="width: 300px;" ondblclick="move_option_to( 'all_regions', 'account_regions', 'right');" multiple>
												</select>
											</td>
											<td>
												{if $locked}
												<button class="search" style="width: 50px;" name="button" title="Добави регион" disabled><img src="images/mright.gif" /></button></br>
												<button name="button" style="width: 50px;" title="Премахни регион" disabled><img src="images/mleft.gif" /></button>
												{else}
												<button class="search" style="width: 50px;" name="button" title="Добави регион" onClick="move_option_to( 'all_regions', 'account_regions', 'right'); return false;"><img src="images/mright.gif" /></button></br>
												<button name="button" style="width: 50px;" title="Премахни регион" onClick="move_option_to( 'all_regions', 'account_regions', 'left'); return false;"><img src="images/mleft.gif" /></button>
												{/if}
											</td>
											<td>
												<select name="account_regions[]" id="account_regions" size="10" style="width: 300px;" ondblclick="move_option_to( 'all_regions', 'account_regions', 'left');" multiple>
												</select>
											</td>
										</tr>
										<tr style="height: 5px;"><td colspan="3"></td></tr>
									</table>
								</fieldset>
							</td>
						</tr>
						<tr valign="top">
							<td valign="top" align="center">
								<table style="width: 850px;" cellspacing="0" cellpadding="0" >
									<tr valign="top">
										<td valign="top" align="right">
											{if $locked}
											<button type="button" class="search" disabled><img src="images/disk.gif"/>Запази</button>&nbsp;
											<button type="button" id="b100" disabled><img src="images/confirm.gif" />Приключи</button>
											{else}
											<button type="button" class="search" onclick="formSave();"><img src="images/disk.gif"/>Запази</button>&nbsp;
											<button type="button" id="b100" onClick="formClose();"><img src="images/confirm.gif" />Приключи</button>
											{/if}
										</td>
									</tr>
								</table>									
							</td>
						</tr>
					</table>
					
				</td>
			</tr>
		</table>
	</form>
</div>

<script>
	formLoad();
	
	{if !$right_edit}
		if( form=document.getElementById( 'form1' ) )  
			for( i = 0; i < form.elements.length; i++ )form.elements[i].setAttribute( 'disabled', 'disabled' );
	{/if}
	
	{if $right_close}  
		$('b100').disabled = false;
	{/if}
	
	loadXMLDoc2('result');
</script>

{/if}