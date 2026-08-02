<div class="content">
	<form action="page.php?page=import_salary" method="POST" name="form1" class="ui-nomenclature-dialog ui-salary-import-dialog" enctype="multipart/form-data">
		<input type="hidden" name="MAX_FILE_SIZE" value="30000" />
		
		<div class="page_caption">Импортиране на заплати</div>
		<div class="ui-salary-import-body">
			<label class="ui-salary-import-field" for="browse_file">
				<span class="ui-salary-import-label"><span class="ui-icon ui-icon-file-excel" aria-hidden="true"></span> Файл със заплати</span>
				<input id="browse_file" name="browse_file" type="file" class="fixed" required>
			</label>
		</div>
		<div class="ui-nomenclature-actions ui-salary-import-actions">
			<button type="submit" class="search"><span class="ui-icon ui-icon-file-import" aria-hidden="true"></span> Импортирай</button>
			<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
		</div>
	</form>
</div>
