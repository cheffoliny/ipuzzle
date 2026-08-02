{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function updateGroup() {
		loadXMLDoc2('update', 3);
	}
</script>
{/literal}

<form id="form1" class="ui-asset-editor ui-asset-group-editor" onsubmit="updateGroup(); return false;">
	<input type="hidden" name="id" id="id" value="{$nID|default:0}">
	<input type="hidden" name="offset" id="offset">

	<div class="modal-content ui-asset-editor-content">
		<div class="modal-header">
			<strong>{if $nID eq 0}Добавяне на нова група{else}Редакция на група{/if}</strong>
			<button type="button" class="close" onclick="window.close();" aria-label="Затвори">×</button>
		</div>
		<div class="modal-body ui-asset-editor-body">
			<div class="input-group input-group-sm mb-2">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-name" aria-hidden="true" title="Наименование"></span></div>
				<input type="text" name="name" id="name" class="form-control" placeholder="Наименование на групата..." />
			</div>
			<div class="input-group input-group-sm">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-branch" aria-hidden="true" title="Подчинена на"></span></div>
				<select name="parent_id" id="parent_id" class="form-control"></select>
			</div>
		</div>
	</div>

	<nav class="modal-footer fixed-bottom ui-asset-editor-actions" aria-label="Действия с групата">
		<button type="submit" class="btn btn-success"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
		<button type="button" class="btn btn-danger" onclick="window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</nav>
</form>

<script>
	loadXMLDoc2('result');
</script>
