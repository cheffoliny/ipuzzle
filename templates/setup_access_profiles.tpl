{literal}
	<script>
		rpc_debug = true;
		
		function profile_new()
		{
			dialog_win('set_setup_access_profile&id=0',750,580,0,'set_setup_access_profile');
		}

		function profile_edit(id)
		{
			dialog_win('set_setup_access_profile&id='+id,750,580,0,'set_setup_access_profile');
		}

		function view_accounts(id)
		{
			document.location.href ='page.php?page=admin_setup_access_accounts&id_profile='+id;
		}

		function profile_delete(id)
		{
			if( confirm('Наистина ли желаете да премахнeте профила?') )
			{
				document.getElementById('id').value=id;
				loadXMLDoc('delete');
			}
		}
	</script>
{/literal}

<form action="" id="form1" name="form1" onSubmit="return false">
	<input type=hidden name="id" id="id"value="">

	<div id="search">
		<table class = "page_data">
			<tr>
				<td class="page_name">Номенклатури - ПОТРЕБИТЕЛСКИ ПРОФИЛИ</td>
				<td class="buttons"> 
					{if $right_edit}<button id="b70" onClick="profile_new()"><img src="images/plus.gif">Добави</button>
					{else}&nbsp;
					{/if}
				</td>
			</tr>
		</table>

		<hr>
	</div>

	<div id="result"></div>

</form>

<script>
	loadXMLDoc('result');
</script>