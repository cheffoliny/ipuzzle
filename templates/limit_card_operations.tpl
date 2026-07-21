{literal}
	<script>
		rpc_debug=true;

		function formClose() {
			opener.loadXMLDoc2('result');
			window.close();
		}

		function deleteOperation(id) {
			$('nIDOperation').value = id;
			
			if ( confirm('Желаете ли да бъде изтрита операцията?') ) {
				loadXMLDoc2('delete', 1);
			}
			
			$('nIDOperation').value = 0;
		}
		
		function addOperation(id) {
			dialogSetLimitCardOperation( id );
		}
	</script>
{/literal}

<div>
	<form name="form1" id="form1" class="ui-nomenclature-dialog ui-technical-dialog ui-limit-card-operations" onsubmit="return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
		<input type="hidden" id="nIDOperation" name="nIDOperation" value="0" />
		<input type="hidden" id="nIDOperation" name="nIDLimitCard" value="{$nID|default:0}" />
		<input type="hidden" id="nIDWork" name="nIDWork" value="{$work|default:0}" />
		
		<div class="page_caption">Операции към лимитна карта № {$nNum}</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
			<tr>
				<td>{include file="limit_card_tabs.tpl"}</td>
			</tr>
		</table>
		
		<table class="page_data ui-nomenclature-heading ui-technical-heading">
			<tr>
				<td class="buttons">
					<button type="button" id="btnAdd" name="btnAdd" class="search" onclick="addOperation(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Нова </button>
				</td>
			</tr>
		</table>
		
		<hr />
		
		<div id="result" class="ui-technical-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="width:700px; height:360px; overflow-x: auto; overflow-y: auto;"></div>
	
		<div id="search" class="ui-technical-actions-wrap" style="padding-top: 10px; width:700px;">
			<table class="input ui-nomenclature-actions ui-technical-actions">
				<tr valign="top" class="odd">
					<td valign="top" align="right" width="800px">
						<button type="button" id="b100" class="btn btn-xs btn-danger" onClick="formClose();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span>Затвори</button>
					</td>
					
				</tr>
			</table>
		</div>
	
		<div id="NoDisplay" style="display:none"></div>	
	</form>
</div>

<script>
	loadXMLDoc2( 'result' );
</script>

{if $lock eq 'closed' || $lock eq 'cancel' || $work eq 1}
	{literal}
	<script>
		if( form = document.getElementById('form1') ) {
			for( i = 0; i < form.elements.length - 1; i++ ) form.elements[i].setAttribute('disabled', 'disabled');
		}
	</script>
	{/literal}
{/if}
