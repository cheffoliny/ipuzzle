{literal}
<script>
	function tab_href(page) {
		var assetId = document.getElementById('nID');
		var target = document.getElementById(page);

		if (!assetId || parseInt(assetId.value, 10) <= 0) {
			alert('Активът още не е въведен!');
			return false;
		}

		if (!target) return false;
		target.href = 'page.php?page=' + page + '&nID=' + assetId.value;
		return true;
	}
</script>
{/literal}

<ul class="nav nav-tabs ui-asset-tabs ui-asset-info-tabs" aria-label="Досие на актив">
	<li class="nav-item">
		{if $page eq 'asset_info'}
			<a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-info" aria-hidden="true"></span> Информация</a>
		{else}
			<a class="nav-link" href="#" onclick="return tab_href('asset_info');" id="asset_info"><span class="ui-icon ui-icon-info" aria-hidden="true"></span> Информация</a>
		{/if}
	</li>
	<li class="nav-item">
		{if $page eq 'asset_info_ppp'}
			<a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-document" aria-hidden="true"></span> ППП</a>
		{else}
			<a class="nav-link" href="#" onclick="return tab_href('asset_info_ppp');" id="asset_info_ppp"><span class="ui-icon ui-icon-document" aria-hidden="true"></span> ППП</a>
		{/if}
	</li>
	<li class="nav-item">
		{if $page eq 'asset_info_sub_assets'}
			<a class="nav-link active" href="#" aria-current="page"><span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> Подчинени активи</a>
		{else}
			<a class="nav-link" href="#" onclick="return tab_href('asset_info_sub_assets');" id="asset_info_sub_assets"><span class="ui-icon ui-icon-cubes" aria-hidden="true"></span> Подчинени активи</a>
		{/if}
	</li>
</ul>
