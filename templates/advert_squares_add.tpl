{literal}
	<script>
		function upload()
		{
			loadXMLDoc2( 'upload' );
		}
	</script>
{/literal}

<div class="content">
	<form action="" method="POST" name="form1" enctype="multipart/form-data">
		
		<input type="hidden" name="MAX_FILE_SIZE" value="102400" />
		
		<div class="page_caption">Добавяне на Рекламно Каре</div>
		
		<div id="search">
			<div id="win" style="width: 460px; height: 70px; overflow: auto;">
				<table class="input">
					<tr class="even"><td colspan="2">&nbsp;</td></tr>
					<tr class="odd">
						<td width="180">Рекламно Каре:</td>
						<td><input id="image" name="image" type="file" class="i40" /></td>
					</tr>
				</table>
			</div>
		</div>
		
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align: right;">
					<button type="submit" class="search" onClick="upload();" > Запиши </button>
					<button onClick="parent.window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
	</form>
</div>