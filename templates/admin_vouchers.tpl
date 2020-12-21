{literal}
	<script>
		rpc_debug = true;
		
		function openPerson( id )
		{
			var nYear = $("nYear").value;
			var nMonth = $("nMonth").value;
			
			dialogPersonSalary( id, nMonth, nYear );
		}
		
		function openPersonPrevMonth( id )
		{
			var nYear = $("nYear").value;
			var nMonth = $("nMonth").value;
			
			nMonth--;
			if( nMonth < 1 ) { nMonth = 12; nYear--; }
			
			dialogPersonSalary( id, nMonth, nYear );
		}
		
		function openFilter( type )
		{
			var id;
			
			if( type == 1 )
			{
				dialogAdminVouchersFilter( 0 );
			}
			else
			{
				id = $('nIDScheme').value;
				if( id != 0 )
				{
					dialogAdminVouchersFilter( id );
				}
			}
		}
		
		function deleteFilter()
		{
			if( $("nIDScheme").value > 0 )
			{
				if( confirm( "Наистина ли желаете да премахнeте филтъра?" ) )
				{
					loadXMLDoc2( "deleteFilter", 6 );
				}
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<table class="page_data">
		<tr>
			<td class="page_name">Работни Заплати - Отпуски / Доп. Ваучери</td>
		</tr>
	</table>
	
	<center>
		<table class="input" style="width: 800px;">
			<tr>
				<td align="right">Филтри:&nbsp;</td>
				<td align="left">
					<table class="input" cellpadding="0" cellspacing="0" style="width: 320px;">
						<tr>
							<td align="left">
								<select name="nIDScheme" id="nIDScheme" class="select200"></select>&nbsp;
							</td>
							<td align="left">
								<button style="width: 30px" id=b25 title="Нов филтър" name="Button5" onClick="openFilter( 1 );" ><img src="images/plus.gif" /></button>&nbsp;
								<button style="width: 30px" name="Button4" id=b25 title="Редактиране на филтър" onClick="openFilter( 2 );"><img src=images/edit.gif /></button>&nbsp;
								<button style="width: 30px" name="Button3" id=b25 title="Премахване на филтър" onClick="deleteFilter();"><img src=images/erase.gif /></button>
							</td>
						</tr>
					</table>
				</td>
				
				<td colspan="5">&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Фирма:&nbsp;</td>
				<td align="left">
					<select id="nIDFirm" name="nIDFirm" class="select200" onchange="loadXMLDoc2( 'loadOffices' );"/>
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">Регион:&nbsp;</td>
				<td align="left">
					<select id="nIDOffice" name="nIDOffice" class="select200" onchange="loadXMLDoc2( 'loadObjects' );"/>
				</td>
				
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td align="right">Обект:&nbsp;</td>
				<td align="left">
					<select id="nIDObject" name="nIDObject" style="width: 350px;" />
				</td>
				
				<td>&nbsp;</td>
				
				<td align="left" colspan="2">
					Год
					<input style="width: 40px; text-align: right" onkeypress="return formatDigits( event );" name="nYear" id="nYear" type="text" value="{$year}"/>&nbsp;&nbsp;
					Мес
					<input style="width: 30px; text-align: right" onkeypress="return formatDigits( event );" name="nMonth" id="nMonth" type="text" value="{$month}"/>&nbsp;&nbsp;
				</td>
				
				<td>&nbsp;</td>
				
				<td align="right">
					<button onclick="loadXMLDoc2( 'result' );" name="Button"><img src="images/confirm.gif"> Търси </button>
				</td>
			</tr>
		</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		rpc_on_exit = function()
		{
			rpc_on_exit = function() {}
			
			loadXMLDoc2( "result" );
		}
		
		loadXMLDoc2( "load" );
	</script>
{/literal}