{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function onInit() {
		loadXMLDoc2('load');
	}

	function onChangeOffice() {
		loadXMLDoc2('getPatruls');
	}

	function formSubmit() {
		var openerFirm = window.opener && window.opener.document
			? window.opener.document.getElementById('nIDFirm')
			: null;

		if (openerFirm && parseInt(openerFirm.value, 10) > 0) {
			loadXMLDoc2('save');
		} else {
			loadXMLDoc2('save', 2);
		}
	}
</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" class="ui-patrol-editor ui-patruls-editor ui-nomenclature-dialog ui-operational-dialog" onsubmit="formSubmit(); return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}">

	<div class="modal-content ui-patrol-editor-content">
		<div class="modal-header">
			<strong>Редакция на позивни</strong>
			<button type="button" class="close" onclick="parent.window.close();" aria-label="Затвори">×</button>
		</div>

		<div class="modal-body ui-patrol-editor-body">
			<div class="input-group input-group-sm mb-2">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-building" aria-hidden="true" title="Фирма"></span></div>
				<select name="nIDFirm" id="nIDFirm" class="form-control" onchange="loadXMLDoc2('loadOffices')"></select>
			</div>
			<div class="input-group input-group-sm mb-2">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-location" aria-hidden="true" title="Регион"></span></div>
				<select name="nIDOffice" id="nIDOffice" class="form-control" onchange="onChangeOffice();"></select>
			</div>
			<label class="ui-patrol-field-label" for="sPatruls">
				<span class="ui-icon ui-icon-radio" aria-hidden="true"></span> Позивни към региона
			</label>
			<textarea name="sPatruls" id="sPatruls" class="form-control ui-patrol-callsigns" placeholder="Въведете позивните, разделени със запетая..."></textarea>
			<small class="form-text text-muted">Позивните се въвеждат като числа, разделени със запетая.</small>
		</div>
	</div>

	<nav class="modal-footer fixed-bottom ui-patrol-editor-actions ui-nomenclature-actions" aria-label="Действия с позивните">
		<button type="submit" class="btn btn-success"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
		<button type="button" class="btn btn-danger" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</nav>
</form>

<script>
	onInit();
</script>
