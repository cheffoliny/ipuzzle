<script>
	rpc_debug = true;
</script>

<div class="content">
	<form action="" method="POST" name="form1" id="form1" class="ui-nomenclature-dialog ui-salary-import-dialog ui-fix-salary-dialog" onsubmit="loadXMLDoc2('save', 2); return false;">
		<input type="hidden" id="nID" name="nID" value="{$nID}">
		
		<div class="page_caption">Добавяне на фиксирани заплати</div>
		<div class="ui-salary-import-body">
			<div class="ui-salary-import-period">
				<label for="year"><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span> Година</label>
				<input onkeypress="return formatDigits(event);" name="year" id="year" type="number" min="2007" max="2050" value="{$year}" required>
				<label for="month">Месец</label>
				<input onkeypress="return formatDigits(event);" name="month" id="month" type="number" min="1" max="12" value="{$month}" required>
			</div>
			<p class="ui-salary-import-note"><span class="ui-icon ui-icon-info" aria-hidden="true"></span> Ще бъдат преизчислени фиксираните и минималните заплати за избрания период.</p>
		</div>
		<div class="ui-nomenclature-actions ui-salary-import-actions">
			<button type="submit" class="search"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши</button>
			<button type="button" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори</button>
		</div>
		
	</form>
</div>
