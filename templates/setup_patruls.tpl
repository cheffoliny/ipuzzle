{literal}
	<script>
		rpc_debug = true;
		
		function editPatruls(id) {
			dialogSetupPatruls(id);
		}
		
		function delPatruls(id) {
			if ( confirm('Наистина ли желаете да премахнете патрулите от този обект?') ) {
				$('nID').value = id;
				loadXMLDoc2('delete', 1);
			}
		}
		
	</script>
{/literal}
<form action="" name="form1" id="form1" onSubmit="return false;">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<table class="page_data">
		<tr>
			<td class="page_name">Патрули - ПОЗИВНИ</td>
			<td class="buttons"> 
				{if $right_edit}<button class="search" onclick="editPatruls(0);"><img src="images/plus.gif"> Добави </button> 
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
					<select name="nIDFirm" id="nIDFirm"/>
				</td>
				<td align="right"><button name="Button" onclick="loadXMLDoc2('result');"><img src="images/confirm.gif">Търси</button></td>
			</tr>
	  	</table>
	</center>

	<hr>
	
	<div id="result"></div>

</form>

<script>
	loadXMLDoc2('load');
</script>

