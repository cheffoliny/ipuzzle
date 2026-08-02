{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function viewAsset(id) {
		dialogAssetInfo(id);
	}
</script>
{/literal}

<form id="form1" name="form1" class="ui-asset-subview ui-asset-children-subview" onsubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID|default:0}">

	<header class="ui-asset-subview-heading">
		<h1><span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> Подчинени активи на актив №{$nID|default:0}</h1>
	</header>

	{include file="asset_info_tabs.tpl"}

	<div id="result" class="ui-asset-subview-result"></div>
</form>

<script>
	loadXMLDoc2('result');
</script>
