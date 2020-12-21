{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{	
			loadXMLDoc2( 'load' );
		}
		
		function formSearch()
		{
			loadXMLDoc2('result');
		}
		
		function formLoad() {
			if ( document.getElementById('locked').value == 1 ) {
				document.getElementById('capt').style.background = 'red';
			} else {
				document.getElementById('capt').style.background = 'green';
			}
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
			if(sDateFrom) {
				Params += '&sDateFrom='+sDateFrom;
			}
			if(sDateTo) {
				Params += '&sDateTo='+sDateTo;
			}
			dialog_win('object_archiv'+Params,800,540,1,'object_archiv');
			
		}
		
		function openObjectSup(id) {
			sParams = 'nID='+id;
			dialogObjectSupport(sParams);
		}
		
		function toObjects() {
			id = $("nRegion").value;
			dialog_win( 'setup_objects&id_reg=' + id, 1000, 480, 1, 'setup_objects' );
		}
		
		function onChangeType() {
			if($('sfield'))	$('sfield').value = "";
		}
		
		function addSignal(id) {
			var nIDCard = $('nIDCard').value;
			
			dialogWCMoveAdd(id, nIDCard);
		}
		
		
		rpc_on_exit = function() {
			var text = $('result').innerHTML;
			text = text.replace(/red_tag/g,"<span style='color:red'>");
			text = text.replace(/tag_red_end/g,"</span>");
			$('result').innerHTML = text;
		}
	</script>
	<style>
		div#result
		{
			white-space:nowrap !important;

			overflow: auto;
		}
		div#result table td
		{	
			padding-right: 20px;
			white-space:nowrap !important;
		}
		div#result_data table
		{	
			width: 100%
			white-space:nowrap !important;
		}
	</style>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
<table border="0" width="100%" class="search">
	<tr>
		<td valign="top" align="left">
			
				<input type="hidden" id="locked" name="locked" value="{$locked|default:0}" />
				<input type="hidden" id="nIDCard" name="nIDCard" value="{$nIDCard|default:0}" />
				
				<div class="page_caption" id="capt" name="capt">Движение на патрули към работна карта № {$nIDCard} {if $locked}[ПРИКЛЮЧЕНА!]{/if}</div>
				
				<table cellspacing="0" cellpadding="0" width="100%" id="filter" >
					<tr>
						<td>{include file=working_card_tabs.tpl}</td>
					</tr>
		
				</table>
				<br>
				
				<table cellspacing="0" cellpadding="0" width="100%" >
					<tr>
						
					</tr>
				</table>
				
				<center>
						<table class="search" width="100%" >
							<tr>
								<td align="right" style="width:420px;">
									Шаблон:
								</td>
								<td style="width:180px;">
									<select name="schemes" id="schemes"></select>
								</td>
								<td align="left"style="width:150px;" colspan="7">
									<button style="width: 30px" id=b25 title="Нова група" name="Button5" onClick="openFilter(1);" ><img src="images/plus.gif" /></button>&nbsp;
									<button style="width: 30px" name="Button4" id=b25 title="Редактиране на филтър" onClick="openFilter(2);"><img src=images/edit.gif /></button>&nbsp;
									<button style="width: 30px" name="Button3" id=b25 title="Премахване на филтър" onClick="deleteFilter(schemes);"><img src=images/erase.gif /></button>
								</td>
									<td align="right"><button type="button" name="Button" onClick="addSignal(0);"><img src="images/plus.gif">Добави</button></td>
							</tr>
						</table>
				
						<table class="search" width="100%">
							<tr>
								<td align="left" style="width:120px;"><button type="button" name="Button" onClick="toObjects();" class="search">Към Обекти</button></td>
								<td style="width:150px;">
								&nbsp;
								</td>
								<td align="left" style="width:20px;">Тип:</td>
								<td align="left" style="width:40px;">
									<select style="width:100px;" name="type" id="type" onchange="onChangeType();">
										<option value="detailed">Подробна</option>
										<option value="totaled">Обобщена</option>
									</select>
								</td>
								<td align="left" style="width:40px;">Регион:&nbsp;</td>
								<td align="left" style="width: 200px;" valign="top">
									<select id="nRegion" name="nRegion" style="width: 220px;" ></select>
								</td>
								<td align="left" style="width:40px;">Позивна:&nbsp;</td>
								<td align="left" style="width:40px;">
									<input type="text" name="nPatrul" id="nPatrul" style="width: 50px;" />
								</td>
								<td align="left"><button type="button" name="Button" onClick="formSearch();" class="search"><img src="images/confirm.gif">Търси</button></td>
							</tr>
				  		</table>
				</center>
		</td>
	</tr>
</table>

<hr/>

<div id="result"></div>
</form>

<script>
	formLoad();
	
	onInit();
</script>