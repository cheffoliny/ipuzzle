{literal}
<script>
	rpc_debug=true;
	
	function upload() {
		loadXMLDoc('upload');
	}
</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" enctype="multipart/form-data">
		<input type="hidden" id="id" name="id" value="{$id}">
		
		<div class="page_caption">{if $id}Зареждане на длъжностна характеристика{/if}</div>

		<div id="search">
		<div id="win" style="width:460px; height:70px;overflow: auto;">
			<table class="input">
				<tr class="even"><td colspan="2">&nbsp;</td></tr>
				<tr class="odd">
					<td width="180">Изберете файла:</td>
					<td><input id="image" name="image" type="file" class="i40" /></td>
				</tr>
			</table>
		</div>
		</div>

		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button type="submit" class="search" onClick="upload();" > Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
</div>