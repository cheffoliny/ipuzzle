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
	<form name="form1" id="form1" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
		<input type="hidden" id="nIDObject" name="nIDObject" value="0" />
		
		<div class="page_caption">ППП към лимитна карта № {$nNum}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file=limit_card_tabs.tpl}</td>
			</tr>
		</table>
		
		<table class = "page_data">
			<tr>
				<td class="buttons">
					<button onclick="openPPP( 0 );"><img src="images/plus.gif"> Нов </button>
				</td>
			</tr>
			<tr>
				<td align="center">
					<input type="text" name="nIDPPPLink" id="nIDPPPLink" class="inp50" onkeypress="return formatDigits(event);"> &nbsp;
					<button onclick="loadXMLDoc( 'linkppp', 1 );"><img src="images/plus.gif"> Добави ППП </button>
				</td>
			</tr>
		</table>
		
		<hr />
		
		<div id="result"></div>
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
