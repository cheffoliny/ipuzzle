<div class="content">
	
	<form action="page.php?page=import_nomenclatures" method="POST" name="form1" enctype="multipart/form-data" onsubmit="">
		<input type="hidden" name="MAX_FILE_SIZE" value="150000">
		<input type="hidden" name="page" value="import_salary">
		
		<div class="page_caption">Импорт на номенклатури</div>
		</br>
		<table class="input">
			<tr class="edd">
				<td width="220">Файл</td>
				<td><input id="browse_file" name="browse_file" type="file" class="fixed" size="30"></td>
			</tr>
		</table>
		</br>
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search"> Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
	
</div>