{literal}
	<script>
		rpc_debug = true;
		
		function onInit() {
			loadXMLDoc2( 'load');
		}

		function saveData() {
			loadXMLDoc2('save',5);
		}
		
		function onclickCheck(chk) {
			//alert(chk);
			if(chk == true) {
				$('total_count').disabled = false;
				$('total_price').disabled = false;
				$('sFromDate').disabled = false;
				$('sPeriod').disabled = false;
			} else {
				$('total_count').disabled = true;
				$('total_price').disabled = true;
				$('sFromDate').disabled = true;
				$('sPeriod').disabled = true;
			}
		}
	</script>
{/literal}

<dlcalendar click_element_id="editFromDate" 	input_element_id="sFromDate" 	tool_tip="Изберете дата"></dlcalendar>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">
	<div class="page_caption">
		Редактиране на филтър
	</div>

	<table  cellspacing="0" cellpadding="0" width="100%"  border="0" id="filter" >
  		<tr>
  			<td>
  				{include file=states_filter_tabs.tpl}
  				<br>
  			</td>
  		</tr>
  	</table>
	
	<table border="0" class="input">
		<tr class="even">
			<td>
				&nbsp;
			</td>
			<td style="width:100px;" align="right">
				<input type="checkbox" class="clear" name="auto" id="auto" onclick="onclickCheck(this.checked);"/>
			</td>
			<td>
				Автоматичен филтър
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<fieldset>
				<legend>Тотали</legend>
				<table class="input">
					<tr class="even">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="total_count" id="total_count" />
						</td>
						<td>
							Количество
						</td>
					</tr>
					<tr class="odd">
						<td style="width:20px;">
							<input type="checkbox" class="clear" name="total_price" id="total_price"/>
						</td>
						<td>
							Цена
						</td>
					</tr>
				</table>
				</fieldset>
			</td>
		</tr>
		<tr class="even">
			<td>
				&nbsp;
			</td>
			<td style="width:100px;">
				Дата на пускане
			</td>
			<td align="left">
				<input type="text" name="sFromDate" id="sFromDate" class="inp100" onkeypress="return formatDate(event, '.');" />
				&nbsp;
				<img src="images/cal.gif" border="0" align="absmiddle" style="cursor: pointer;" width="16" height="16" id="editFromDate" />
			</td>
		</tr>
		<tr class="odd">
			<td>
				&nbsp;
			</td>
			<td style="width:100px;">
				През период от
			</td>
			<td>
				<select name="sPeriod" id="sPeriod" style="width:100px;">	
					<option value="day">ден</option>
					<option value="week">седмица</option>
					<option value="month">месец</option>
				</select>
			</td>
		</tr>
		<tr class="odd">
			<td colspan="2" style="width:100px;">&nbsp;</td>
			<td style="text-align:right;" valign="bottom">
				<br><br><br><br><br><br><br><br>
				<button type="button" class="search" onClick="saveData();"> Запиши </button>
				<button type="button" onClick="window.opener.loadXMLDoc2('load');parent.window.close();"> Затвори </button>
			</td>
		</tr>
	</table>


</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}