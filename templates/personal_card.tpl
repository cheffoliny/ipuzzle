

<form name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />	
	<input type="hidden" id="earning" name="earning" value="0" />
	<input type="hidden" id="nIDLimitCard" name="nIDLimitCard" value="0" />
	
	<table  cellspacing="0" cellpadding="0" width="100%" height="4%" id="filter" >
		<tr>
			<td>{include file=personal_card_tabs.tpl}</td>
		</tr>
	</table>
	
	<table border='2' width="100%" height="96%">
		<tr>
			<td width="50%">
				<iframe id="personal_card_limit_card" width="100%" height="100%" frameborder=1 src='page.php?page=personal_card_limit_card&id_limit_card={$nIDLimitCard}'>
				</iframe>
			</td>
			<td width="50%">
				<iframe id="personal_card_operations" width="100%" height="100%" frameborder=1 src='page.php?page=personal_card_operations&id_limit_card={$nIDLimitCard}'>
				</iframe>
			</td>
		</tr>
		<tr>
			<td colspan="2" style="height:50px;" >
				<iframe id="personal_card_schedule" height="100px;" width="100%" frameborder=1 src='page.php?page=personal_card_schedule'>
				</iframe>
			</td>
		</tr>
	</table>	
</form>


