{literal}
	<script>
		rpc_debug = true;
		rpc_method = "post";
		
		function refreshCalendar()
		{
			document.getElementById( "result" ).src = "templates/holidays_calendar.html";
		}
		
		function displayLegend()
		{
			switch( $("sType").value )
			{
				case "holiday":
					$("legend").style.backgroundColor = "#FF6677";
					break;
				
				case "restday":
					$("legend").style.backgroundColor = "#FFDDDD";
					break;
				
				case "workday":
					$("legend").style.backgroundColor = "#FFFFDD";
					break;
			}
		}
		
		function shiftYear( step )
		{
			var nYear = parseInt( $("nYear").value );
			nYear += step;
			$("nYear").value = nYear;
			
			result();
		}
		
		function result()
		{
			rpc_on_exit = function()
			{
				rpc_on_exit = function() {}
				
				refreshCalendar();
			}
			
			loadXMLDoc2( 'result' );
		}
		
		function save( nDay, nMonth, nYear, nWeekday )
		{
			rpc_on_exit = function()
			{
				result();
			}
			
			loadXMLDoc2( "save&day=" + nDay + "&month=" + nMonth + "&year=" + nYear + "&weekday=" + nWeekday );
		}
		
		function printPDF()
		{
			window.open( "api/api_general.php?" +
				"action_script=api/api_setup_holidays.php" +
				"&api_action=export_to_pdf" +
				"&nYear=" + $("nYear").value +
				"&rpc_version=2",
				"holidays_pdf"
			);
		}
	</script>
	
	<style>
		iframe
		{
			border: 0px solid #FFFFFF;
		}
	</style>
{/literal}

<form name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="sInfo" id="sInfo" value=""/>
	<input type="hidden" name="sWorkdays" id="sWorkdays" value=""/>
	
	<table class="page_data">
		<tr>
			<td class="page_name">Номенклатури - Празнични / Работни Дни</td>
			
			<td align="right">
				<table class="input" cellpadding="0" cellspacing="0" style="width: 480px;" border="0">
					<tr class="odd">
						<td align="left">
							<table class="input" cellpadding="0" cellspacing="0" style="width: 170px;" border="0">
								<tr class="odd" valign="bottom">
									<td align="right">Тип:&nbsp;</td>
									
									<td align="left">
										<select name="sType" id="sType" class="select100" onchange="displayLegend();">
											<option value="holiday">Празничен</option>
											<option value="restday">Не-работен</option>
											<option value="workday">Работен</option>
										</select>
									</td>
									<td align="left">
										<div id="legend" style="width: 30px; height: 20px; border: 1px solid;">&nbsp;</div>
									</td>
								</tr>
							</table>
						</td>
						<td align="right">
							<table class="input" cellpadding="0" cellspacing="0" style="width: 260px;" border="0">
								<tr class="odd">
									<td align="center">Година:&nbsp;</td>
									
									<td align="center">
										<button onclick="shiftYear( -1 );" style="width: 20px;"><img src="images/mleft.gif"></button>
									</td>
									<td valign="middle" align="center">
										<input type="text" name="nYear" id="nYear" class="inp75" value="{$nYear}" style="text-align: center;" readonly/>
									</td>
									<td align="center">
										<button onclick="shiftYear( 1 );" style="width: 20px;"><img src="images/mright.gif"></button>
									</td>
									<td align="right">
										<button class="btn btn-xs btn-info" onclick="printPDF();">
                                            <i class="fa fa-file-pdf-o"></i> PDF</button>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	
	<hr>
	
	<iframe id="result" frameborder="0"></iframe>
</form>

{literal}
	<script>
		function onResize()
		{
			var oIFrameTable = document.getElementById( "result" );
			
			if( oIFrameTable )
			{
				var myWidth = 0;
				var myHeight = 0;
				
				if( typeof( window.innerWidth ) == 'number' )
				{
					//Non-IE
					myWidth = window.innerWidth;
					myHeight = window.innerHeight;
				}
				else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) )
				{
					//IE 6+ in 'standards compliant mode'
					myWidth = document.documentElement.clientWidth;
					myHeight = document.documentElement.clientHeight;
				}
				else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) )
				{
					//IE 4 compatible
					myWidth = document.body.clientWidth;
					myHeight = document.body.clientHeight;
				}
				
				oIFrameTable.height = myHeight - 50;
				oIFrameTable.width = myWidth;
			}
		}
		
		onResize();
		window.onresize = onResize;
		
		result();
		displayLegend();
	</script>
{/literal}