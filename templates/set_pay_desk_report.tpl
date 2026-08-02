{literal}
	<script>
		rpc_debug = true;

		function update()
		{
			loadXMLDoc2( 'save', 3 );
			return false;
		}
	</script>
{/literal}

<dlcalendar click_element_id="editDate" input_element_id="sDate" tool_tip="Изберете дата"></dlcalendar>

<div class="content ui-finance-report-dialog ui-pay-desk-report-dialog">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog" onsubmit="return update();">
		<div class="page_caption">Форма Приключване</div>

		<div class="ui-finance-report-dialog-body">
			<label class="ui-finance-report-field" for="sDate">
				<span>Дата:</span>
				<span class="input-group input-group-sm">
					<input type="text" name="sDate" id="sDate" class="form-control" onkeypress="return formatDate( event, '.' );" />
					<span class="input-group-append">
						<button type="button" id="editDate" class="btn btn-light ui-inline-calendar-trigger" title="Изберете дата" aria-label="Изберете дата"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span></button>
					</span>
				</span>
			</label>

			<label class="ui-finance-report-field" for="nIDPayDesk">
				<span>Касов апарат:</span>
				<select class="form-control form-control-sm" name="nIDPayDesk" id="nIDPayDesk"></select>
			</label>

			<label class="ui-finance-report-field" for="nOborot">
				<span>Сума оборот:</span>
				<span class="input-group input-group-sm">
					<input type="text" class="form-control" id="nOborot" name="nOborot" onkeypress="return formatMoney( event );" />
					<span class="input-group-append"><span class="input-group-text">€</span></span>
				</span>
			</label>

			<label class="ui-finance-report-field" for="nStorno">
				<span>Сума сторно:</span>
				<span class="input-group input-group-sm">
					<input type="text" class="form-control" id="nStorno" name="nStorno" onkeypress="return formatMoney( event );" />
					<span class="input-group-append"><span class="input-group-text">€</span></span>
				</span>
			</label>
		</div>

		<div class="ui-dialog-action-bar">
			<button type="submit" class="btn btn-sm btn-success"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
			<button type="button" class="btn btn-sm btn-danger" onclick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
		</div>
	</form>
</div>

<script>
	loadXMLDoc2( 'init' );
</script>
