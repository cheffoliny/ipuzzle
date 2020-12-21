{literal}
<script>
	rpc_debug = true;
	
	function test(id) {
		var period = id.options[id.selectedIndex].id.split(',');
		if (period[0] != undefined) {
			document.getElementById('sShiftFrom').value = period[0];
		} else document.getElementById('sShiftFrom').value = '';

		if (period[1] != undefined) {
			document.getElementById('sShiftTo').value = period[1];
		} else document.getElementById('sShiftTo').value = '';

		if (period[2] != undefined) {
			document.getElementById('sCode').value = period[2];
		} else document.getElementById('sCode').value = '';

		if (period[3] != undefined) {
			document.getElementById('sName').value = period[3];
		} else document.getElementById('sName').value = '';
	}
	
	function formSubmit() {
		if ( document.getElementById('nID').value > 0 ) {
			if( confirm("Промяната ще влезе в сила след днешна дата в НОВ график!") ) {
				loadXMLDoc2('save', 3);
			}
		} else loadXMLDoc2('save', 3);
	}
</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="{$nID}">
	<input type="hidden" id="nIDObject" name="nIDObject" value="{$nIDObj}">
		
		<table class="search" style="width: 100%;">
		<tr>
			<td colspan="2" class="header_buttons" style="height: 33px;">
			<span id="head_window">{if $nID}Редакция на{else}Нова{/if} смяна</span> 
			</td>
		</tr>
	
		<tr>

		<tr class="even">
			<td style="padding: 7px;">
			
			<div class="col-sm-6 col-md-2" style="width:402px; border: 0px solid gray;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/id.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sCode" id="sCode" class="inp100" placeholder="Код на смяна" />
					&nbsp;&nbsp;&nbsp;
					<input type="checkbox" name="nAuto" id="nAuto" class="clear" style="margin: 0px; padding: 0px;" />&nbsp;Автоматична
				</div>
			</div>
			
			<div class="col-sm-6 col-md-2" style="width:362px; border: 0px solid gray;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/alpha.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sName" id="sName"  class="inp300" placeholder="Наименование на смяна" />
				</div>
			</div>
					
			<div class="col-sm-6 col-md-2" style="width:362px; border: 0px solid gray;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/layouts.png" style="width: 12px; height: 12px;"></span>
					<select name="nType" id="nType" class="select300" onChange="test(this);" ></select>
				</div>
			</div>
				
			<div class="col-sm-6 col-md-2" style="width:362px; border: 0px solid gray;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
					<select name="sMode" id="sMode" class="select300" >
						<option value="none">Изберете вид</option>
						<option value="day">Дневна</option>
						<option value="night">Нощна</option>
						<option value="full">Денонощна</option>
						<option value="leave">Отпуск</option>
						<option value="sick">Болничен</option>
					</select>
				</div>
			</div>
			
			<div class="col-sm-6 col-md-2" style="width:360px; border: 0px solid gray;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/start.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sShiftFrom" id="sShiftFrom" class="inp100" placeholder="Начало..." onKeyPress="return formatTime(event);" maxlength="6" />
					<span class="input-group-addon-default">&nbsp;ч.&nbsp;</span>
					&nbsp;
					<span class="input-group-addon"><img src="images/glyphicons/stop.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sShiftTo" id="sShiftTo" class="inp100" placeholder="...Край" onKeyPress="return formatTime(event);" maxlength="6" />
					<span class="input-group-addon-default">&nbsp;ч.&nbsp;</span>
				</div>
			</div>
			
			<div class="col-sm-6 col-md-2" style="width:360px; border: 0px solid gray;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/24.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sDuration" id="sDuration" class="inp100" placeholder="Отработени..." onKeyPress="return formatTime(event);" maxlength="6" />
					<span class="input-group-addon-default">&nbsp;ч.&nbsp;</span>
					&nbsp;
					<span class="input-group-addon"><img src="images/glyphicons/24.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sRealTime" id="sRealTime" class="inp100" placeholder="Продължителност..." readonly />
					<span class="input-group-addon-default">&nbsp;ч.&nbsp;</span>
				</div>
			</div>
			
			<div class="col-sm-6 col-md-2" style="width:360px;">
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/money.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sStake" id="sStake" class="inp100" placeholder="Ставка..." onKeyPress="return formatMoney(event);" />						
					<span class="input-group-addon-default">лв.</span>
					&nbsp;
					<span class="input-group-addon"><img src="images/glyphicons/money.png" style="width: 12px; height: 12px;"></span>
					<input type="text" name="sStakeDuty" id="sStakeDuty" class="inp100" placeholder="Ставка за смяна..." readonly />
					<span class="input-group-addon-default">лв.</span>
				</div>
			</div>		
			
			<div class="col-sm-6 col-md-2" style="width:362px;">
				Допълнителна информация
				<div class="input-group" style="margin: 2px;">			
					<span class="input-group-addon"><img src="images/glyphicons/info.png" style="width: 12px; height: 12px;"></span>
					<textarea name="sDescription" id="sDescription" style="width: 297px; height: 100px;" /></textarea>
				</div>
			</div>
					
		</td>
		</tr>
	</table>
	
		
	<table class="page_data">
		<tr>
			<td width="50">&nbsp;</td>
			<td style="text-align:right; padding: 5px 1px 5px 0;">
				<button onClick="formSubmit()" class="btn btn-xs btn-success"><img src="images/glyphicons/save.png" style="width: 14px; height: 14px;"> Запиши </button>
				<button onClick="parent.window.close();" class="btn btn-xs btn-danger"><img src="images/glyphicons/cancel.png" style="width: 14px; height: 14px;"> Затвори </button>
			</td>
		</tr>
	</table>
			
</form>

<script>
	loadXMLDoc2('load');
</script>