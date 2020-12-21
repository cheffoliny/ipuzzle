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
		
		function toSalary( id )
		{
			var oSum = document.getElementById( "v_sum[" + id + "]" );
			var oIsAvailable = document.getElementById( "is_available[" + id + "]" );
			
			if( oSum && oIsAvailable )
			{
				rpc_on_exit = function()
				{
					getResult();
				}
				
				loadXMLDoc2( "toSalary&id_person=" + id + "&sum=" + oSum.value + "&is_available=" + oIsAvailable.value );
			}
		}
		
		function getResult()
		{
			rpc_on_exit = function()
			{
				var text = $('result').innerHTML;
				text = text.replace( /btns/g, "<button onclick=\"" );
				text = text.replace( /btnm/g, "\" " );
				text = text.replace( /btne/g, "><img src=\"images/confirm.gif\">Прехвърли</button>" );
				$('result').innerHTML = text;
			}
			
			loadXMLDoc2( "result" );
		}
		
		function checkAll( bChecked )
		{
			var aCheckboxes = document.getElementsByTagName( 'input' );
			
			for( var i = 0; i < aCheckboxes.length; i++ )
			{
				if( aCheckboxes[i].type.toLowerCase() == 'checkbox' )
				{
					if( !aCheckboxes[i].disabled )
					{
						aCheckboxes[i].checked = bChecked;
					}
				}
			}
		}
		
		function just_do_it()
		{
			switch( getById( 'sel' ).value )
			{
				case '1':
					checkAll( true );
					break;
				
				case '2':
					checkAll( false );
					break;
				
				case '3':
					rpc_on_exit = function() { getResult(); }
					loadXMLDoc2( "groupToSalary" );
					break;
			}
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" onSubmit="return false;">
	<table class="page_data">
		<tr>
			<td class="page_name">Работни Заплати - Ваучери</td>
		</tr>
	</table>
	
	<center>
		<table class="input" style="width: 800px;">
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
					<button onclick="getResult();" name="Button"><img src="images/confirm.gif"> Търси </button>
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
			getResult();
		}
		
		loadXMLDoc2( "load" );
	</script>
{/literal}