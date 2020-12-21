{literal}
	<script>
	
		rpc_debug = true;
		
		function onInit()
		{	
			loadXMLDoc2('load');
		}
		
		function formSearch()
		{
			loadXMLDoc2('result');
		}
		
		function stopMovement(id) {
			dialogStopMovement(id);
		}
		
		function deleteFilter(schemes) {
			if(schemes.value > 0)
				if( confirm('Наистина ли желаете да премахнeте филтърът?') ) {
					loadXMLDoc('deleteFilter',6);
				}
		}
		
		function openFilter(type) {
			var id;
			if(type == 1) {
				dialogMovementScheme(0);
			} else {
				id = $('schemes').value;
				if(id != 0) {
					dialogMovementScheme(id);
				}
			}
		}
		
		function openObjectArchiv(id,sAlarmType,sDateFrom,sDateTo) {
			var Params = '';
			if(id) {
				Params += '&nID='+id;
			}
			if(sAlarmType) {
				Params += '&sAlarmType='+sAlarmType;
			}
//			if(sDateFrom) {
//				Params += '&sDateFrom='+sDateFrom;
//			}
//			if(sDateTo) {
//				Params += '&sDateTo='+sDateTo;
//			}
			//			sParams = 'nID=' + id;
			if($('date_from').value != '') {
				Params += '&sDateFrom=' + $('date_from').value;
			}
			if($('date_to').vlaue != '') {
				Params += '&sDateTo=' + $('date_to').value;
			}
			
			dialog_win('object_archiv'+Params,800,540,1,'object_archiv');
			
		}
		
		function openObjectSup(id) {

			dialogObjectSupport(sParams);
		}
		
		function onChangeType(obj) {
			if(obj.value == 'totaled') {
				$('sAlarmType').options[1].selected = true;
			} else {
				$('sAlarmType').options[0].selected = true;
			}
			if($('sfield'))	$('sfield').value = "";
		}
		
		function toObjects()
		{
			id = $("nRegion").value;
			dialog_win( 'setup_objects&id_reg=' + id, 1000, 480, 1, 'setup_objects' );
		}
	
		rpc_on_exit = function() {
			var text = $('result').innerHTML;
			text = text.replace(/red_tag/g,"<span style='color:red'>");
			text = text.replace(/tag_red_end/g,"</span>");
			$('result').innerHTML = text;
		}
	</script>
	
	<style>
		div#result table td
		{
			white-space: nowrap !important;
			padding-right: 20px;
		}
	</style>
{/literal}

<dlcalendar click_element_id="img_date_from" input_element_id="date_from" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="img_date_to" input_element_id="date_to" tool_tip="Изберете дата"></dlcalendar>

			<form name="form1" id="form1" onsubmit="return false;">	
				<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />
				
				<div class="page_caption" id="capt" name="capt">РК: Движение на патрули</div>
				
				<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
					<tr>
						<td>{include file=working_card_tabs.tpl}</td>
					</tr>
				</table>
				<br>
				
				
					<table class="search">
						<tr>
							<td height="30px" valign="top" align="left">
								<button type="button" name="Button" onClick="toObjects();" class="search">Към Обекти</button>
							</td>
							<td style="width:200px;">
								&nbsp;
							</td>
							<td align="right">
								Шаблон:
							</td>
							<td style="width:180px;">
								<select name="schemes" id="schemes"></select>
							</td> 
							
							<td colspan="7">
								<button style="width: 30px" id=b25 title="Нова група" name="Button5" onClick="openFilter(1);" ><img src="images/plus.gif" /></button>&nbsp;
								<button style="width: 30px" name="Button4" id=b25 title="Редактиране на филтър" onClick="openFilter(2);"><img src=images/edit.gif /></button>&nbsp;
								<button style="width: 30px" name="Button3" id=b25 title="Премахване на филтър" onClick="deleteFilter(schemes);"><img src=images/erase.gif /></button>
							</td>
						</tr>
					</table>
				<center>
					<table class="search" border="0">	
						<tr>
							<td align="right">Тип:</td>
							<td>
								<select style="width:100px;" name="type" id="type" onchange="onChangeType(this);">
									<option value="detailed">Подробна</option>
									<option value="totaled">Обобщена</option>
								</select>
							</td>
							<td align="right" style="width:70px;">Регион:</td>
							<td align="left" valign="top" colspan="3">
								<select id="nRegion" name="nRegion" style="width: 260px;" ></select>
							</td>
							<td align="right" style="width:70px;">Позивна:</td>
							<td align="left">
								<input type="text" name="nPatrul" id="nPatrul" style="width: 50px;" />
							</td>
						</tr>
						<tr>
							<td>
								Аларми:
							</td>
							<td>
								<select id="sAlarmType" name="sAlarmType" style="width:100px;">
									<option value="all" selected>Всички</option>
									<option value="visited">Посетени</option>
									<option value="notvisited">Непосетени</option>
								</select>
							</td>
							<td align="right">от дата:</td>
							<td>
								<input type="text" id="date_from" name="date_from" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
								<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_from" />
							</td>
							<td align="right">до дата:</td>
							<td>
								<input type="text" id="date_to" name="date_to" class="inp75" onkeypress="return formatDate(event, '.');" size="10" maxlength="10" title="ДД.ММ.ГГГГ" />
								<img src="images/cal.gif" border="0" align="absmiddle" style="cursor:pointer;" width="16" height="16" id="img_date_to" />
							</td>	
							<td align="right" colspan="2">
								<button type="button" name="Button" onClick="formSearch();" class="search">
									<img src="images/confirm.gif">
									Търси
								</button>
							</td>	
						</tr>
			  		</table>
				</center>
				
				<hr>	
				<div id="result"></div>
			</form>

<script>
	onInit();
</script>