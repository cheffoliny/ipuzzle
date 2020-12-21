<script>
{literal}
	rpc_debug = true;
	
	function openAssistant( id )
	{
		dialogSetSetupAssistant('id=' + id, id);
	}

	function deleteAssistant( id )
	{
		if( confirm('Наистина ли желаете да премахнете записа?') )
		{
			$('nID').value = id;
			loadXMLDoc2( 'delete', 1 );
		}
	}
	
{/literal}
</script>

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Електронен договор - РЕКЛАМНИ СЪТРУДНИЦИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openAssistant( 0 );"><img src="images/plus.gif"> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center>
		<table class="search">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'genregions' );" />&nbsp;&nbsp;
				</td>
				<td>&nbsp;</td>
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDRegion" id="nIDRegion" />
				</td>
				<td>&nbsp;</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc2( 'result' );"><img src="images/confirm.gif">Търси</button></td>
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
			loadXMLDoc2( "result" );
			
			rpc_on_exit = function() {};
		}
		loadXMLDoc2( 'load' );
	</script>
{/literal}