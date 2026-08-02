{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function saveParking() {
		loadXMLDoc2('save', 3);
	}
</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" class="ui-patrol-editor ui-patrol-parking-editor" onsubmit="saveParking(); return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}">

	<div class="modal-content ui-patrol-editor-content">
		<div class="modal-header">
			<strong>{if $nID}Редакция на{else}Нова{/if} стоянка</strong>
			<button type="button" class="close" onclick="parent.window.close();" aria-label="Затвори">×</button>
		</div>

		<div class="modal-body ui-patrol-editor-body">
			<div class="input-group input-group-sm mb-2">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-name" aria-hidden="true" title="Наименование"></span></div>
				<input type="text" name="sName" id="sName" class="form-control" placeholder="Наименование на стоянката..." />
			</div>
			<div class="input-group input-group-sm mb-2">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-building" aria-hidden="true" title="Фирма"></span></div>
				<select name="nIDFirm" id="nIDFirm" class="form-control" onchange="loadXMLDoc2('loadOffices')"></select>
			</div>
			<div class="input-group input-group-sm mb-2">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-location" aria-hidden="true" title="Регион"></span></div>
				<select name="nIDOffice" id="nIDOffice" class="form-control"></select>
			</div>
			<label class="ui-patrol-field-label" for="sDescription">
				<span class="ui-icon ui-icon-info" aria-hidden="true"></span> Допълнителна информация
			</label>
			<textarea name="sDescription" id="sDescription" class="form-control ui-patrol-description" placeholder="Описание..."></textarea>
		</div>
	</div>

	<nav class="modal-footer fixed-bottom ui-patrol-editor-actions" aria-label="Действия със стоянката">
		<button type="submit" class="btn btn-success"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
		<button type="button" class="btn btn-danger" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</nav>
</form>

<script>
	loadXMLDoc2('load');
</script>
