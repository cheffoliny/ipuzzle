{literal}
	<script>
	
		rpc_debug = true;
		
		function openConcession( id )
		{
			dialogConcession( id );
		}
		
		function deleteConcession( id )
		{
			if( confirm( "Изтриване на записа?" ) )
			{
				rpc_on_exit = function()
				{
					loadXMLDoc2( 'result' );
					
					rpc_on_exit = function() {}
				}
				
				loadXMLDoc2( 'delete&id=' + id );
			}
		}
		
		function onInit()
		{
			loadXMLDoc2( 'result' );
		}
	
	</script>
{/literal}

<div>
	<form name="form1" id="form1" onsubmit="return false;">
		<div class="page_caption">Отстъпки</div>

		<table cellspacing="0" cellpadding="0" width="100%" id="filter">
			<tr>
				<td>{include file=finance_instruments_tabs.tpl}</td>
			</tr>
		</table>

		<table class="input">
			<tr>
				<td align="right">
					{if $right_edit}<button onclick="openConcession( 0 );"><img src="images/plus.gif"> Добави </button>{else}&nbsp;{/if}
				</td>
			</tr>
		</table>
		
		<hr>
		
		<div id="result" rpc_resize="on" style="overflow: auto;"></div>
	</form>
</div>

<script>
	onInit();
</script>