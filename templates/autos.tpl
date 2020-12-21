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

<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" name="id" id="id" value="0">
	<input type="hidden" name="nID" id="nID" value="0">
	<table class = "page_data">
		<tr>
			<td class="page_name">Автомобили - АВТОМОБИЛИ</td>
			<td class="buttons">
				{if $right_edit}<button onclick="editAuto( 0 );"><img src="images/plus.gif"> Добави </button>
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
					<select class="default" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" />
				</td>
				<td align="right">Регион</td>
				<td>
					<select class="default" name="nIDOffice" id="nIDOffice" />
				</td>
				<td align="right"><button name="Button" onclick="getResult();"><img src="images/confirm.gif">Търси</button></td>
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