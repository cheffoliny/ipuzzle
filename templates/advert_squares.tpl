{literal}
	<script>
	
		rpc_debug = true;
		rpc_autonumber = "off";
		
		function addAdvertSquare()
		{
			dialog_win( "advert_squares_add", 400, 130, 1, "advert_squares_add" );
		}
		
		function defaultSquare( id )
		{
			loadXMLDoc2( "current&nID=" + id );
		}
		
		function deleteSquare( id )
		{
			if( confirm( "Изтриване на Записа?" ) )
			{
				loadXMLDoc2( "delete&nID=" + id );
			}
		}
		
		function getResult()
		{
			loadXMLDoc2( "result" );
		}
		
		function setTooltip( nOn, sImg )
		{
			var oTooltip 		= document.getElementById( "tooltip" );
			var oTooltipImage 	= document.getElementById( "tooltip_image" );
			
			if( nOn )oTooltipImage.src = sImg;
			oTooltip.style.display = nOn ? "block" : "none";
		}
		
		function setTooltipLocation()
		{
			var mouseX = window.event.clientX;
			var mouseY = window.event.clientY;
			
			var oTooltip = document.getElementById( "tooltip" );
			
			oTooltip.style.left = mouseX + 5;
			oTooltip.style.top 	= mouseY + 5;
		}
	
	</script>
{/literal}

<div class="content">
	<form action="" name="form1" onsubmit="return false;">
		
		<div class="page_caption">Рекламно Каре</div>

		<div id="tooltip" name="tooltip" style="position: absolute; display: none; border: 1px solid rgb( 0, 0, 0 );">
			<img id="tooltip_image" name="tooltip_image" />
		</div>
		
		<table cellspacing="0" cellpadding="0" width="100%" id="filter">
			<tr>
				<td>{include file=finance_instruments_tabs.tpl}</td>
			</tr>
		</table>

		<br />
		<table width="100%">
			<tr>
				<td align="right">
					{if $right_edit}<button onclick="addAdvertSquare();"><img src="images/plus.gif"> Добави </button>{/if}
				</td>
			</tr>
		</table>
		
		<hr />
		
		<div id="result" rpc_paging="off" rpc_excel_panel="off"></div>
	</form>
</div>

<script>
	getResult();
</script>