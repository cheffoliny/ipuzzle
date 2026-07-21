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

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Електронен договор - РЕКЛАМНИ СЪТРУДНИЦИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openAssistant( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter ui-nomenclature-filter-wide">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2( 'genregions' );" />&nbsp;&nbsp;
				</td>
				<td>&nbsp;</td>
				<td align="right">Регион</td>
				<td>
					<select class="default form-control" name="nIDRegion" id="nIDRegion" />
				</td>
				<td>&nbsp;</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc2( 'result' );"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
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
