<div class="content">
	<form action="page.php?page=import_salary_gsm" method="POST" name="form1" class="ui-nomenclature-dialog ui-salary-import-dialog" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="2097152">
		<input type="hidden" name="page" value="import_salary">
		
		<div class="page_caption">Импортиране на фактура МТЕЛ</div>
		<div class="ui-salary-import-body">
			<div class="ui-salary-import-period">
				<label for="year"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span> Година</label>
				<input onkeypress="return formatDigits(event);" name="year" id="year" type="number" min="2007" max="2050" value="{$year}" required>
				<label for="month">Месец</label>
				<input onkeypress="return formatDigits(event);" name="month" id="month" type="number" min="1" max="12" value="{$month}" required>
			</div>
			<label class="ui-salary-import-field" for="browse_file">
				<span class="ui-salary-import-label"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Файл с фактура</span>
				<input id="browse_file" name="browse_file" type="file" class="fixed" required>
			</label>
		</div>
		<div class="ui-nomenclature-actions ui-salary-import-actions">
			<button type="submit" class="search"><span class="ui-icon ui-icon-file-import" aria-hidden="true"></span> Импортирай</button>
			<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
		</div>
	</form>
</div>
