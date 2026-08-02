{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

	function onInit() {
		loadXMLDoc2('result');
	}

	function formSelect() {
		document.getElementById('sAct').value = 'choice';
		loadXMLDoc2('result');
	}

	function formSave() {
		select_all_options('choice_persons');
		loadXMLDoc2('save', 3);
	}
</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" class="ui-patrol-editor ui-road-list-editor" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID|default:0}">
	<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}">
	<input type="hidden" id="sAct" name="sAct" value="list">

	<div class="modal-content ui-patrol-editor-content">
		<div class="modal-header">
			<strong>{if $nID}Информация за патрул{else}Нов патрул{/if}</strong>
			<button type="button" class="close" onclick="parent.window.close();" aria-label="Затвори">×</button>
		</div>

		<div class="modal-body ui-patrol-editor-body">
			<div class="ui-patrol-main-fields">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-location" aria-hidden="true" title="Регион"></span></div>
					<select name="nRegion" id="nRegion" class="form-control" onchange="formSelect();"></select>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-radio" aria-hidden="true" title="Позивна"></span></div>
					<select name="nIDPatrul" id="nIDPatrul" class="form-control"></select>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-car" aria-hidden="true" title="Автомобил"></span></div>
					<select name="nAuto" id="nAuto" class="form-control"></select>
				</div>
				<div class="input-group input-group-sm">
					<div class="input-group-prepend"><span class="ui-icon ui-icon-gauge" aria-hidden="true" title="Начален километраж"></span></div>
					<input type="text" id="startKm" name="startKm" class="form-control text-right" onkeypress="return formatNumber(event);" placeholder="Нач. км..." />
				</div>
			</div>

			<fieldset class="ui-patrol-person-transfer">
				<legend><span class="ui-icon ui-icon-users" aria-hidden="true"></span> Избор на служители</legend>
				<div class="ui-patrol-transfer-grid">
					<label for="all_persons">Свободни служители</label>
					<span aria-hidden="true"></span>
					<label for="choice_persons">Избрани служители</label>
					<select name="all_persons" id="all_persons" class="form-control" ondblclick="move_option_to('all_persons', 'choice_persons', 'right');" multiple></select>
					<div class="ui-patrol-transfer-actions">
						<button type="button" class="btn btn-primary" title="Добави служител" onclick="move_option_to('all_persons', 'choice_persons', 'right');">
							<span class="ui-icon ui-icon-arrow-right" aria-hidden="true"></span>
						</button>
						<button type="button" class="btn btn-secondary" title="Премахни служител" onclick="move_option_to('all_persons', 'choice_persons', 'left');">
							<span class="ui-icon ui-icon-arrow-left" aria-hidden="true"></span>
						</button>
					</div>
					<select name="choice_persons[]" id="choice_persons" class="form-control" ondblclick="move_option_to('all_persons', 'choice_persons', 'left');" multiple></select>
				</div>
			</fieldset>
		</div>
	</div>

	<nav class="modal-footer fixed-bottom ui-patrol-editor-actions" aria-label="Действия с патрула">
		{if !$nID}<button type="button" class="btn btn-success" onclick="formSave();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>{/if}
		<button type="button" class="btn btn-danger" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</nav>
</form>

<script>
	onInit();
</script>

{if $nID}
{literal}
<script>
	var patrolForm = document.getElementById('form1');
	if (patrolForm) {
		var patrolFields = patrolForm.querySelectorAll('input:not([type="hidden"]), select, textarea');
		for (var i = 0; i < patrolFields.length; i++) patrolFields[i].setAttribute('disabled', 'disabled');
	}
</script>
{/literal}
{/if}
