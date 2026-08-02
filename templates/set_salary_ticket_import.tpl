{literal}
	<script>
		rpc_debug = true;
	</script>
{/literal}

<form action="page.php?page=set_salary_ticket_import" method="POST" name="form1" class="ui-nomenclature-dialog ui-salary-import-dialog ui-salary-ticket-import-dialog" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
	<input type="hidden" name="page" value="set_salary_ticket_import">
	<div class="page_caption">Импортиране на фиш</div>

	<div class="ui-salary-import-body">
		<div class="ui-salary-import-period">
			<label for="month"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span> Месец</label>
			<input onkeypress="return formatDigits(event);" name="month" id="month" type="number" min="1" max="12" value="{$month}" required>
			<label for="year">Година</label>
			<input onkeypress="return formatDigits(event);" name="year" id="year" type="number" min="2001" max="2049" value="{$year}" required>
		</div>
		<label class="ui-salary-import-field" for="pdf_ticket">
			<span class="ui-salary-import-label"><span class="ui-icon ui-icon-file-pdf" aria-hidden="true"></span> PDF фишове</span>
			<input type="file" name="pdf_ticket" id="pdf_ticket" accept=".pdf,application/pdf" required>
		</label>
		<label class="ui-salary-import-field" for="excel_file">
			<span class="ui-salary-import-label"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Excel справка</span>
			<input type="file" name="excel_file" id="excel_file" accept=".xls,.xlsx" required>
		</label>
	</div>

	<div class="ui-nomenclature-actions ui-salary-import-actions">
		<button type="submit" class="search"><span class="ui-icon ui-icon-file-import" aria-hidden="true"></span> Импортирай</button>
		<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
	</div>
</form>
