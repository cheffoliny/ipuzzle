<script>
{literal}
	
	rpc_debug = true;
	
	function openRequest( id )
	{
		var params = 'id=' + id;
		dialogRequest( params );
	}

	function deleteRequest( id )
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
			<td class="page_name">Задачи</td>
			<td class="buttons">
				{if $right_edit}<button onclick="openRequest( 0 );"><img src="images/plus.gif"> Нова </button>
				{else}&nbsp;
				{/if}
			</td>
		</tr>
	</table>
	
	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2('result');
</script>