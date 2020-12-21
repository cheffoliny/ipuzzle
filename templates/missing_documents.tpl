{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		
		function getResult() {
			select_all_options('account_documents');
			loadXMLDoc2('result',1)
			$('filters').style.display = "none";
				$('show_filters').value = "hide"
		}
		
		function showFilters() {
			if(	$('show_filters').value == "show") {
				$('filters').style.display = "none";
				$('show_filters').value = "hide";
				
			} else {
				$('filters').style.display = "block";
				$('show_filters').value = "show";
			}
			if(typeof(xslResizer) == 'function') {
				xslResizer();
			}
		}
		function openPerson( nIDPerson ) {
			if( parseInt( nIDPerson ) )
				dialogPerson( nIDPerson );
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="show_filters" id="show_filters" value="show">
	<div class="page_caption">Липсващи или с изтекла валидност документи</div>
	
	<table width="100%">
		<tr>
			<td align="right">
				<button type="button" style="width: 30px;" onClick="showFilters();"><img src="images/search2.gif"></button>
			</td>
		</tr>
	</table>
	
	<div id="filters">
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Фирма:&nbsp;</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион:&nbsp;</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" />
				</td>
				<td align="right"><button name="Button" onclick="getResult();"><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>	  	
  		<table class="search" cellspacing="3">
			<tr>
				<td valign="top" align="center">
					<fieldset style="width: 600px;">
					<legend>Липсващи документи</legend>
						<table>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
							<tr class="even">
								<td>
									<select name="all_documents" id="all_documents" size="10"  style="width: 250px;" ondblclick="move_option_to( 'all_documents', 'account_documents', 'right');" multiple>
									</select>
								</td>
								<td>
									<button class="search" style="width: 50px;" name="button" title="Добави документ" onClick="move_option_to( 'all_documents', 'account_documents', 'right'); return false;"><img src="images/mright.gif" /></button></br>
									<button name="button" style="width: 50px;" title="Премахни документ" onClick="move_option_to( 'all_documents', 'account_documents', 'left'); return false;"><img src="images/mleft.gif" /></button>
								</td>
								<td>
									<select name="account_documents[]" id="account_documents" size="10" style="width: 250px;" ondblclick="move_option_to( 'all_documents', 'account_documents', 'left');" multiple>
									</select>
								</td>
							</tr>
							<tr style="height: 5px;"><td colspan="3"></td></tr>
						</table>
					</fieldset>
				</td>
			</tr>
		</table>
	  	
	</center>
	
	</div>
	
	<hr>
	
	<div id="result"></div>

</form>


{literal}
	<script>
		onInit();
	</script>
{/literal}