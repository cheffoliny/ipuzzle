{literal}
	<script>
		rpc_debug = true;
		
		function onInit()
		{
			loadXMLDoc2( 'load' ,1);
			loadXMLDoc2('result',1)
		}
		function getResult()
		{
			loadXMLDoc2('result',1)
		}
		function delAutoModel(id)
		{
			if ( confirm('Наистина ли желаете да премахнете модела?') ) 
			{
				$('nID').value = id;
				loadXMLDoc2('delete', 1);
			}
		}
		function editAutoModel(id) 
		{
			//alert(id);
			dialogSetupAutoModel(id);
		}
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-nomenclature-list" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data ui-nomenclature-heading">
		<tr>
			<td class="page_name">Автомобили - МОДЕЛИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="editAutoModel( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<center class="ui-nomenclature-filter-wrap">
		<table class="search table-secondary ui-nomenclature-filter">
			<tr>
				<td align="right">Марка</td>
				<td>
					<select class="default form-control" name="id_mark" id="id_mark" />
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
