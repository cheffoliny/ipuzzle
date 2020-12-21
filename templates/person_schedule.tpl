{literal}

<style rel="stylesheet" type="text/css">

	table
	{
		empty-cells: show !important;
	}
	
	table td
	{
		empty-cells: show !important;
	}
	
	table.shifts
	{
		margin: auto;
		text-align: center;
		width: auto !important;
	}
	
	table.shifts td
	{
		width: 50px !important;
        display: inline-block !important;
		margin-bottom: 5px;
        margin-bottom: 5px;
	}
	
	table.shifts button
	{
		width: 50px;
	}
	
	table.shifts button.selected
	{
		background-image: none;
		background-color: Silver;
	}

	table.result td
	{
		white-space: nowrap !important;
	}
	


	

	
	table.result td.stake
	{
		text-align: right;
	}
	
	/*table.result td.days*/
	/*{*/
	/*	cursor: default;*/
	/*}*/
	
	/*table.result td.personName*/
	/*{*/
	/*}*/
	
	table.result td.planMoney,
	table.result td.planDuration,
	table.result td.realMoney,
	table.result td.realDuration
	{
		text-align: center;
	}
	
	table.result tr.total td
	{
		font-weight: normal;
	}
	
	/*table.foother*/
	/*{*/
	/*	margin-top: 10px;*/
	/*}*/
	
	/*table.foother button*/
	/*{*/
	/*	font-family: Tahoma;*/
	/*	font-size: 11px;*/
	/*	padding: 2px 5px 2px 5px;*/
	/*}*/
	
	/*#divTitle*/
	/*{*/
	/*	font-size: 20px;*/
	/*	text-align: center;*/
	/*	margin: auto;*/
	/*	font-weight: bold;*/
	/*	padding: 10px;*/
	/*}*/
	
	td.busy
	{
		background-color: Red;
	}
	
	td.isValidatedStart
	{
		background-color: #eff465;
	}
</style>

<script>

	rpc_debug = true;
	rpc_method = 'post';
	rpc_xsl = 'xsl/person_schedule.xsl';
	rpc_html_debug = true;
	
	function onChangeFirm()
	{
		loadXMLDoc2( "getOffices" );
	}
	
	function onChangeOffice()
	{
		loadXMLDoc2( "getObjects" );
	}
	
	function onChangeObject()
	{
		loadXMLDoc2( "getYearMonth" );
	}
	
	function onInit()
	{
		$("container").style.width = ( window.innerWidth || document.body.offsetWidth ) + 'px';
	}
	
	function onShiftClick( event )
	{
		event = event || window.event;
		
		var oButton = event.srcElement;
		
		var oClassNames = new Framework.ClassNames();
		
		var sClassName = "selected";
		
		var aButtons = $('tableShifts').getElementsByTagName("BUTTON");
		
		if( aButtons && aButtons.length )
		{
			for( var i = 0; i < aButtons.length; i++ )
			{
				if( oClassNames.hasClass( aButtons[i], sClassName ) )
					oClassNames.removeClass( aButtons[i], sClassName );
			}
		}
		
		oClassNames.addClass( oButton, sClassName );
		
		$('selectedShift').value = oButton.firstChild ? oButton.firstChild.nodeValue : "#0";
		
		// BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
		var sIDShiftDuration = oButton.id.replace( /btnShift/, "shiftCoefDuration" );
		var oShiftDuration = document.getElementById( sIDShiftDuration );
		if( oShiftDuration )
		{
			if( oShiftDuration.value == "" )oShiftDuration.value = "00:00";
			$("nSelectedShiftDuraton").value = oShiftDuration.value;
		}
		
		var sIDShiftIsLeave = oButton.id.replace( /btnShift/, "shiftIsLeave" );
		var oShiftIsLeave = document.getElementById( sIDShiftIsLeave );
		if( oShiftIsLeave )
		{
			if( oShiftIsLeave.value == "" )oShiftIsLeave.value = "0";
			$("nSelectedShiftIsLeave").value = oShiftIsLeave.value;
		}
		// END CODE : Person Shift Hours Limit
	}
	
	function onDayClick( event )
	{
		event = event || window.event;
		
		var oTd = event.srcElement;
		
		var oClassNames = new Framework.ClassNames();
		
		var sClassName 			= "invalidated";
		var sClassHoliday 		= "holiday";
		var sClassWeekend 		= "weekend";
		var sClassLeavePlanned 	= "leave_planned";
		var sClassLeavePlannedW = "leave_planned_weekend";
		var sClassHospital 		= "hospital";
		var sClassHospitalW 	= "hospital_weekend";
		
		// BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
		//--Get Person
		var nIDPerson = "";
		var sCurrentSymbol = "]";
		
		var i = oTd.id.length - 1;
		
		while( sCurrentSymbol != "[" && i > 0 )
		{
			if( oTd.id.substr( i - 1, 1 ) != "[" )
			{
				nIDPerson = oTd.id.substr( i - 1, 1 ) + nIDPerson;
			}
			
			sCurrentSymbol = oTd.id.substr( i - 1, 1 );
			i--;
		}
		//--End Get Person
		
		var sIDShift = oTd.id.replace( /c/, "sid" );
		var nValueChange = 0;
		
		var oShift = document.getElementById( sIDShift );
		// END CODE : Person Shift Hours Limit
		
		if( oClassNames.hasClass( oTd, sClassLeavePlanned ) || oClassNames.hasClass( oTd, sClassLeavePlannedW ) )
		{
			alert( "Има запазена молба за отпуск за тази дата!" );
			return false;
		}
		
		if( oClassNames.hasClass( oTd, sClassHospital ) || oClassNames.hasClass( oTd, sClassHospitalW ) )
		{
			alert( "Има запазен болничен за тази дата!" );
			return false;
		}
		
		if( ( oClassNames.hasClass( oTd, sClassHoliday ) || oClassNames.hasClass( oTd, sClassWeekend ) ) && $("nSelectedShiftIsLeave").value == "1" )
		{
			alert( "Отпуск не може да се планира за празнични или неработни дни!" );
			return false;
		}
		
		if( oClassNames.hasClass( oTd, sClassName ) )
		{
			if( !oTd.firstChild )
				oTd.appendChild( document.createTextNode("") );
			
			// BEGIN CODE : Person Shift Hours Limit ( Часове смени за служител към края и началото на месеца )
			if( oShift && $("selectedShift").value != '' )
			{
				changeShiftHoursEnd( nIDPerson, true, oShift.value );
				
				oShift.value = $("nSelectedShiftDuraton").value;
				changeShiftHoursEnd( nIDPerson, false, oShift.value );
			}
			// END CODE : Person Shift Hours Limit
			
			if( $("selectedShift").value == '#0' )
			{
				oTd.firstChild.nodeValue = "";
			}
			else if( $("selectedShift").value != '' )
			{
				oTd.firstChild.nodeValue = $("selectedShift").value || "";
			}
			else alert('Изберете тип на смяна!');
		}
	}
	
	function getTimeSum( bNegate, sTime1, sTime2 )
	{
		var aTime1 = sTime1.split( ":" );
		var aTime2 = sTime2.split( ":" );
		
		if( !aTime1[0] )aTime1[0] = 0; else aTime1[0] = parseFloat( aTime1[0] );
		if( !aTime1[1] )aTime1[1] = 0; else aTime1[1] = parseFloat( aTime1[1] );
		if( !aTime2[0] )aTime2[0] = 0; else aTime2[0] = parseFloat( aTime2[0] );
		if( !aTime2[1] )aTime2[1] = 0; else aTime2[1] = parseFloat( aTime2[1] );
		
		if( aTime1[0] < 0 )
		{
			aTime1[1] = -aTime1[1];
		}
		
		aTime1[1] += aTime1[0] * 60;
		aTime2[1] += aTime2[0] * 60;
		
		if( bNegate )aTime1[1] -= aTime2[1];
		else aTime1[1] += aTime2[1];
		
		aTime1[0] = parseInt( aTime1[1] / 60 );
		aTime1[1] = aTime1[1] % 60;
		
		if( aTime1[1] < 0 )aTime1[1] = -aTime1[1];
		
		aTime1[0] = String( aTime1[0] );
		aTime1[1] = String( aTime1[1] );
		aTime2[0] = String( aTime2[0] );
		aTime2[1] = String( aTime2[1] );
		
		sTime = ( ( aTime1[0].length < 2 ) ? "0" + aTime1[0] : aTime1[0] ) + ":" + ( ( aTime1[1].length < 2 ) ? "0" + aTime1[1] : aTime1[1] );
		
		return sTime;
	}
	
	function getRoundTime( sTime )
	{
		var aTime = sTime.split( ":" );
		
		if( !aTime[0] )aTime[0] = 0; else aTime[0] = parseInt( aTime[0] );
		if( !aTime[1] )aTime[1] = 0; else aTime[1] = parseInt( aTime[1] );
		
		if( aTime[1] > 30 )aTime[0] = ( aTime[0] < 0 ) ? aTime[0] - 1 : aTime[0] + 1;
		
		return aTime[0];
	}
	
	function changeShiftHoursEnd( nIDPerson, bNegate, sValueChange )
	{
		var oCell = document.getElementById( "c[shift_hours][" + nIDPerson + "]" );
		var oReal = document.getElementById( "real_hours[" + nIDPerson + "]" );
		var oTotl = document.getElementById( "c[shift_hours][__TOTAL__]" );
		
		if( oCell && oReal && oTotl )
		{
			//Sum Real Hours
			var sNewTime = getTimeSum( bNegate, oReal.value, sValueChange );
			
			oReal.value = sNewTime;
			//End Sum Real Hours
			
			aValues = oCell.innerHTML.split( " / " );
			aTotals = oTotl.innerHTML.split( " / " );
			
			aTotals[1] = parseInt( aTotals[1] );
			aTotals[0] = parseInt( aTotals[0] );
			
			aTotals[1] -= parseInt( aValues[1] );
			aValues[1] = getRoundTime( oReal.value );
			aTotals[1] += parseInt( aValues[1] );
			
			//Coloring
			if( parseInt( aValues[1] ) < 0 )oCell.style.color = "0000FF";
			if( parseInt( aValues[1] ) == 0 )oCell.style.color = "00BB00";
			if( parseInt( aValues[1] ) > 0 )oCell.style.color = "FF0000";
			//End Coloring
			
			oCell.innerHTML = aValues.join( " / " );
			oTotl.innerHTML = aTotals.join( " / " );
		}
		
		return true;
	}
	
	function getPersonIDs()
	{
		var oTable = $("tableResult");
		var aTRs = oTable.getElementsByTagName( "TR" );
		
		var aResult = new Array();
		
		if( aTRs )
		{
			for( var i = 0; i < aTRs.length; i++ )
			{
				if( parseInt( aTRs[i].id ) )
					aResult.push( aTRs[i].id );
			}
		}
		
		return aResult;
	}
	
	function cleanShifts()
	{
		var oClassNames = new Framework.ClassNames();
		
		var sClassName = "invalidated";
		
		var aPersonIDs = getPersonIDs();
		
		for( var i = 1; i <= 31; i++ )
		{
			for( var k = 0; k < aPersonIDs.length; k++ )
			{
				var oTd = $('c[' + i + '][' + aPersonIDs[ k ] + ']');
				
				if( oTd )
				{
					if( oClassNames.hasClass( oTd, sClassName ) )
					{
						if( oTd.firstChild )
							oTd.firstChild.nodeValue = "";
					}
				}
			}
		}
	}
	
	function serialize()
	{
		var aPairs = new Array();
		
		var aPersonIDs = getPersonIDs();
		
		for( var i = 1; i <= 31; i++ )
		{
			for( var k = 0; k < aPersonIDs.length; k++ )
			{
				var sID = 'c[' + i + '][' + aPersonIDs[ k ] + ']';
				var oTd = $( sID );
				
				if( oTd )
					aPairs.push( encodeURIComponent( sID ) + '=' + encodeURIComponent( ( oTd.firstChild ? oTd.firstChild.nodeValue : "" ) ) );
			}
		}
		
		return aPairs.join( '&' );
	}
	
	function onSave()
	{
		if( !confirm( "Желаете ли да запазите графика ?" ) )
			return;
		
		loadXMLDoc( "save&" + serialize() );
	}
	
	function onPrint()
	{
		var type = $("nIDPrintType").value;
		
		if( type == 3 )
		{
			loadDirect( "export_to_xls" );
		}
		else
		{
			loadDirect( "export_to_pdf" );
		}
	}
	
	function validate()
	{
		{/literal}
			{if $super_right neq 1}alert( "Нямате достатъчно права за тази операция" );{else}
		{literal}
		
		var oDate = new Date();
		
		var nYear  =  $("nResultYear").value;
		var nMonth =  $("nResultMonth").value;
		var nDay   = oDate.getDate();
		
		var sDefault = nDay + '.' + nMonth + '.' + nYear + ' ' + zero_padding( oDate.getHours() + '', 2, '0' ) + ':' + zero_padding( oDate.getMinutes() + '', 2, '0' );
		
		do
		{
			var sDate = prompt( "Въведете дата до която да се валидират смените:", sDefault );
			
			if( sDate == null )
				return;
			
			if( !sDate.length )
			{
				alert( "Въведете дата!" );
				continue;
			}
			
			if( !sDate.match( new RegExp( "^\\d{1,2}\\.\\d{1,2}\\.\\d{4} \\d{2}\\:\\d{2}$" ) ) )
			{
				alert( "Невалидна дата!" );
				continue;
			}
			
			break;
		}
		while( true );
		
		loadXMLDoc2( "validate&sValidateDate=" + sDate + '&' + serialize() );
		
		{/literal}
			{/if}
		{literal}
	}
	
	function autoValidate()
	{
		$("Validate").onclick = function() {};
		loadXMLDoc2( "autoValidate" );
	}
	
	function invalidate()
	{
		{/literal}
			{if $super_right neq 1}alert( "Нямате достатъчно права за тази операция" );{else}
		{literal}
		
		var oDate = new Date();
		
		var nYear  =  $("nResultYear").value;
		var nMonth =  $("nResultMonth").value;
		var nDay   = oDate.getDate();
		
		var sDefault = '01.' + nMonth + '.' + nYear + ' 00:00';
		
		do
		{
			var sDate = prompt( "Въведете дата от която да се девалидират смените:", sDefault );
			
			if( sDate == null )
				return;
			
			if( !sDate.length )
			{
				alert( "Въведете дата!" );
				continue;
			}
			
			if( !sDate.match( new RegExp( "^\\d{1,2}\\.\\d{1,2}\\.\\d{4} \\d{2}\\:\\d{2}$" ) ) )
			{
				alert( "Невалидна дата!" );
				continue;
			}
			
			break;
		}
		while( true );
		
		loadXMLDoc2( "invalidate&sInvalidateDate=" + sDate );
		
		{/literal}
			{/if}
		{literal}
	}
	
	function onClickPerson( nIDPerson )
	{
		if( parseInt( nIDPerson ) )
			dialogPerson( nIDPerson );
	}
	
	function getHours()
	{
		var firm 	= 0;
		var region 	= 0;
		var object 	= 0;
		
		if( parseInt( $("nIDFirm").value ) )
		{
			firm = parseInt( $("nIDFirm").value );
		}
		
		if( parseInt( $("nIDOffice").value ) )
		{
			region = parseInt( $("nIDOffice").value );
		}
		
		if( parseInt( $("nIDObject").value ) )
		{
			object = parseInt( $("nIDObject").value );
		}
		
		dialogHours( firm, region, object );
	}
</script>

{/literal}

<form action="" name="form1" id="form1" onsubmit="return false;">

	<input type="hidden" id="nIDSelectObject" name="nIDSelectObject" value="{$nIDSelectObject|default:0}"/>
	<input type="hidden" id="nCustomDate" name="nCustomDate" value="{$nCustomDate|default:0}"/>
	<input type="hidden" id="nHolidayStakeFactor" name="nHolidayStakeFactor" value="{$nHolidayStakeFactor|default:1.0}"/>
	
	<input type="hidden" id="nSelectedShiftIsLeave" name="nSelectedShiftIsLeave" value="0"/>
	<input type="hidden" id="nSelectedShiftDuraton" name="nSelectedShiftDuraton" value="00:00"/>

	{include file='tabs_setup_personnel.tpl'}

	<div>
		<div class="row justify-content-start pl-3 pb-1 pt-2 table-secondary">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fas fa-tag fa-fw" data-fa-transform="right-22 down-10" title="Фирма..."></span>
					</div>
					<select class="form-control" name="nIDFirm" id="nIDFirm" onchange="onChangeFirm();">
						<option value="0"> -- Избери фирма -- </option>
					</select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						{*Администрация:&nbsp;*}
						<span class="fas fa-tags fa-fw" data-fa-transform="right-22 down-10" title="Офис..."></span>
					</div>
					<select class="form-control" name="nIDOffice" id="nIDOffice" onchange="onChangeOffice();"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="far fa-home-alt fa-fw" data-fa-transform="right-22 down-10" title="Статус..."></span>
					</div>
					<select class="form-control" id="nIDObject" name="nIDObject" onchange="onChangeObject();"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="fas fa-calendar-alt fa-fw" data-fa-transform="right-20 down-10" title="Месец..."></span>
					</div>
					<select class="form-control" id="sYearMonth" name="sYearMonth">
						<option value="0"> -- Изберете -- </option>
					</select>
				</div>
			</div>
			<div class="col-6 col-sm-8 col-lg-4 pl-3">
				<div class="input-group input-group-sm">
					<input class="inp25 py-1" type="text" id="max_hours" name="max_hours" style="font-weight: bold; height: 26px; color: red;" title="Норма часове" readonly/>
					<input class="inp25 py-1" type="text" id="max_shifts" name="max_shifts" style="font-weight: bold; height: 26px; color: red;" title="Норма смени" readonly/>
					<button class="btn btn-sm btn-info mx-1" type="button" name="viewHours" onClick="getHours();" title="Брой часове"><i class="far fa-clock"></i></button>
					<button class="btn btn-sm btn-success" type="button" onClick="return loadXMLDoc2( 'result' );" name="Button"><i class="far fa-search"></i> График</button>
					<!-- <button type="button" onClick="return loadXMLDoc2( 'correctAllShiftHours' );" name="Button"><img src="images/confirm.gif">Часове</button> -->
					<!-- <button type="button" onClick="return loadXMLDoc2( 'saveAllLeavesToSchedule' );" name="Button"><img src="images/confirm.gif">Отпуски</button> -->

{*					{if $auto_schedule}*}
{*					<button type="button" name="Validate" onClick="return autoValidate();" class="search"><img src="images/reload.gif">Валидация</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;*}
{*					{/if}*}
				</div>
			</div>
		</div>
	</div>

	<div id="result"></div>
</form>

<script>
	loadXMLDoc2( "init" );
	{if $nIDSelectObject}
		{literal}
			rpc_on_exit = function()
			{
				loadXMLDoc2( "result" );
				rpc_on_exit = function() {};
			}
		{/literal}
	{/if}
</script>