{literal}
	<script>
		rpc_debug = true;
	</script>
{/literal}

{if $nIDCard}
	<div>
		<form name="form1" id="form1" onsubmit="return false;" >
			<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />
			
			<div class="page_caption" id="capt" name="capt">Техници</div>
			
			<table cellspacing="0" cellpadding="0" width="100%">
				<tr>
					<td valign="top" align="left">{include file=working_card_tabs.tpl}</td>
				</tr>
			</table>
			
			<br />
			<div id="result" style="overflow: auto;"></div>
		</form>
	</div>
{/if}

<script>
	loadXMLDoc2( 'result' );
</script>