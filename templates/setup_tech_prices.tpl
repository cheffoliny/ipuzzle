<script>
{literal}
	rpc_debug = true;
	
	function openTechPrice( id )
	{
		dialogSetSetupTechPrice('id=' + id, id);
	}

	function deleteTechPrice( id )
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
			<td class="page_name">Електронен договор - ТЕХНИКА</td>
		</tr>
	</table>
	
	<div align="right">
		<form action="" method="POST" id='form1' name="form1" onsubmit="return false;">
			<table class="search" width="700">
				<tr>
					<td>Дата на последната актуална ценова листа:</td>
					<td>&nbsp;</td>
					<td align="left">
						<input type="text" readonly="readonly" name="sListDate" id="sListDate" class="inp100" />
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Базова цена:</td>
					<td>&nbsp;</td>
					<td align="left">
						<input type="text" readonly="readonly" name="nBasePrice" id="nBasePrice" class="inp50" /> &nbsp; лв.
					</td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td>Оскъпяване:</td>
					<td>&nbsp;</td>
					<td align="left">
						<input type="text" readonly="readonly" name="nFactor" id="nFactor" class="inp50" /> &nbsp; лв.
					</td>
					<td class="buttons">
						{if $right_edit}<button onclick="openTechPrice( 1 );"><img src="images/plus.gif"> Редакция </button>
						{else}&nbsp;
						{/if}
					</td>
				</tr>
				<tr>
					<td>Последна редакция:</td>
					<td>&nbsp;</td>
					<td align="left">
						<input type="text" readonly="readonly" name="sUpdatedUser" id="sUpdatedUser" class="inp300" />
					</td>
					<td>&nbsp;</td>
				</tr>
			</table>
		</form>
	</div>
	
	<hr>
	
	<div id="result"></div>

</form>


<script>
	loadXMLDoc2('result');
</script>