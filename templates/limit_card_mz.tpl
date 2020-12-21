<script>
	rpc_debug=true;
</script>

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
		
		<div class="page_caption">Материални запаси към лимитна карта № {$nNum}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=limit_card_tabs.tpl}</td>
			</tr>
		</table>
		
		<hr />
		
		<div id="result"></div>
	</form>
</div>

<script>
	loadXMLDoc2( 'result' );
</script>

{if $lock eq 'closed'}
	{literal}
	<script>
		if( form = document.getElementById('form1') ) {
			for( i = 0; i < form.elements.length - 1; i++ ) form.elements[i].setAttribute('disabled', 'disabled');
		}
	</script>
	{/literal}
{/if}
