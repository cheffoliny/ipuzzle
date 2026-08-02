{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function viewPPP(id) {
		dialogAssetsPPP(id);
	}
</script>
{/literal}

<form id="form1" name="form1" class="ui-asset-subview ui-asset-ppp-subview" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />

	<header class="ui-asset-subview-heading">
		<h1><span class="ui-icon ui-icon-document" aria-hidden="true"></span> ППП за актив №{$nID|default:0}</h1>
	</header>

	{include file="asset_info_tabs.tpl"}

	<div id="result" class="ui-asset-subview-result"></div>
</form>

<script>
	loadXMLDoc2('result');
</script>
