<script>
{literal}
	rpc_debug=true;

	function openPPP( id )
	{
		var params = 'id='+id;
		params += '&id_limit_card=' + $('nID').value;
		params += '&id_object=' + $('nIDObject').value;
		
		dialogPPP2( params );
	}
{/literal}
</script>

<div>
	<form name="form1" id="form1" class="ui-nomenclature-list ui-technical-list ui-limit-card-ppp" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
		<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
		
		<div class="page_caption">ППП към лимитна карта № {$nNum}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file="limit_card_tabs.tpl"}</td>
			</tr>
		</table>
		
		<table class="page_data ui-nomenclature-heading ui-technical-heading">
			<tr>
				<td class="buttons">
					<button type="button" class="search" onclick="openPPP( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Нов </button>
				</td>
			</tr>
			<tr>
				<td align="center">
					<input type="text" name="nIDPPPLink" id="nIDPPPLink" class="inp50" onkeypress="return formatDigits(event);"> &nbsp;
					<button type="button" class="search" onclick="loadXMLDoc( 'linkppp', 1 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави ППП </button>
				</td>
			</tr>
		</table>
		
		<hr />
		
		<div id="result" class="ui-technical-result"></div>
	</form>
</div>

<script>
	loadXMLDoc2( 'result' );
</script>

{if $lock eq 'closed' || $lock eq 'cancel'}
	{literal}
	<script>
		if( form = document.getElementById( 'form1' ) )
		{
			for( i = 0; i < form.elements.length; i++ ) form.elements[i].setAttribute( 'disabled', 'disabled' );
		}
	</script>
	{/literal}
{/if}
