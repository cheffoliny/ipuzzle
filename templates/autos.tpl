{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( 'load');
		}
		function getResult()
		{
			loadXMLDoc2('result',1)
		}
		function delAuto(id)
		{
			if ( confirm('Наистина ли желаете да премахнете автомобила?') ) 
			{
				$('nID').value = id;
				loadXMLDoc2('delete', 1);
			}
		}
		function editAuto(id) 
		{
			//alert(id);
			dialogSetupAuto(id);
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Автомобили - АВТОМОБИЛИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="editAuto( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter">
			<tr>
				<td align="right">Фирма</td>
				<td>
					<select class="default form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион</td>
				<td>
					<select class="default form-control" name="nIDOffice" id="nIDOffice" />
				</td>
			<td align="right"><button name="Button" onclick="getResult();"><span class="ui-icon ui-icon-search" aria-hidden="true"></span>Търси</button></td>
			</tr>
	  	</table>
	</center>
	
	<hr>
	
	<div id="result"></div>

</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}
