{literal}
	<script>
		rpc_debug = true;
		/*
		function onInit() {
			loadXMLDoc2( 'load');
		}
		*/

		
	</script>
{/literal}



<form action="page.php?page=set_salary_ticket_import" method="POST" name="form1" enctype="multipart/form-data" onsubmit="">
	<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
	<input type="hidden" name="page" value="set_salary_ticket_import">
	<div class="page_caption">Импортиране на фиш</div>
	
	<table class="search" style="width:100%;">
		<tr>
			<td align="center">
				<br>
				Зa&nbsp;&nbsp;&nbsp;&nbsp;
				мес.
				<input style="width:30px; text-align:right" onkeypress="return formatDigits(event);" name="month" id="month" type="text" value="{$month}"/>&nbsp;&nbsp;
				год.
				<input style="width:40px; text-align:right" onkeypress="return formatDigits(event);" name="year" id="year" type="text" value="{$year}"/>&nbsp;&nbsp;
			</td>
		</tr>
		<tr>
			<td>
				<br>
				<img src="images/pdf2.gif">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:400px;" type="file" name="pdf_ticket" id="pdf_ticket" />
			</td>
		</tr>
		<tr>
			<td>

				<img src="images/excel.gif">&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:400px;" type="file" name="excel_file" id="excel_file" />
			</td>
		</tr>
		<tr class="odd">
			<td style="text-align:right;">
				<br>
				<button type="submit" class="search"> Импортирай </button>
				<button onClick="parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>
	


</form>


{literal}
	<script>
		//onInit();
	</script>
{/literal}