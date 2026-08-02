{literal}
	<script>
		rpc_debug = true;
		
		function openOrder( id )
		{
			var person = document.getElementById('id').value;
			dialogAttestation( id, person );
		}

		function delOrder( id )
		{
			if( confirm( 'Наистина ли желаете да премахнете записа?' ) )
			{
				$('to_del').value = id;
				loadXMLDoc( 'delete', 1 );
			}
		}
	</script>
{/literal}

<form name="form1" id="form1" class="ui-personnel-orders" onsubmit="return false;">
	<input type="hidden" id="id" name="id" value="{$id_person|default:0}" />
	<input type="hidden" id="nEnableRefresh" name="nEnableRefresh" value="{$enable_refresh|default:1}" />
	<input type="hidden" name="to_del" id="to_del" value="0" />

	<div class="page_caption">Атестации за {$person_name}</div>
	{include file="person_tabs.tpl"}

	<div class="ui-personnel-orders-toolbar">
		{if $personnel_edit}
			<button type="button" class="btn btn-sm btn-success" onclick="return openOrder(0);"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
		{/if}
	</div>

	<div id="result" class="ui-personnel-result ui-personnel-orders-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off" style="height: 350px; overflow: auto;"></div>

	<div class="ui-personnel-list-actions ui-personnel-orders-actions">
		<button type="button" class="btn btn-sm btn-danger" onClick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</div>
</form>

<script>
	loadXMLDoc( 'result' );
	
	{if !$personnel_edit}
	if( form=document.getElementById( 'form1' ) )  
			for( i = 0; i < form.elements.length - 1; i++ )form.elements[i].setAttribute( 'disabled', 'disabled' );
	{/if}
</script>
