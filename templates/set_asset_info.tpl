{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function submitAssetPeriod() {
		rpc_on_exit = function(nCode) {
			if (parseInt(nCode, 10)) return;

			if (window.opener && typeof window.opener.test === 'function') {
				window.opener.test();
			}
			if (window.opener && window.opener.location) {
				window.opener.location.reload();
			}
			parent.window.close();
		};
		loadXMLDoc2('save', 0);
	}
</script>
{/literal}

<form name="form1" id="form1" class="ui-asset-editor ui-asset-period-editor" onsubmit="submitAssetPeriod(); return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}">

	<div class="modal-content ui-asset-editor-content">
		<div class="modal-header">
			<strong>Редакция на амортизационен период</strong>
			<button type="button" class="close" onclick="parent.window.close();" aria-label="Затвори">×</button>
		</div>
		<div class="modal-body ui-asset-editor-body">
			<div class="input-group input-group-sm">
				<div class="input-group-prepend"><span class="ui-icon ui-icon-calendar" aria-hidden="true" title="Амортизационен период"></span></div>
				<input type="text" name="amort_period" id="amort_period" class="form-control text-right" onkeypress="return formatDigits(event);" placeholder="Период в месеци..." />
				<div class="input-group-append">месеца</div>
			</div>
		</div>
	</div>

	<nav class="modal-footer fixed-bottom ui-asset-editor-actions" aria-label="Действия с амортизационния период">
		<button type="submit" class="btn btn-success"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
		<button type="button" class="btn btn-danger" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</nav>
</form>

<script>
	loadXMLDoc2('load');
</script>
