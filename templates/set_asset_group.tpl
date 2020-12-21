{if $nID eq 0}
	<div class="page_caption">
		
				Добавяне на нова група
			
	</div>
{else}
	<div class="page_caption">
		
				Редакция на група
			
	</div>
	
{/if}

<script>
{literal}

	rpc_debug= true;
	function updateGroup()
	{
		loadXMLDoc2('update',3);
	}
	
	

{/literal}
</script>

<form id="form1">
<input type="hidden" name="id" id="id" value="{$nID}">
<input type="hidden" name="offset" id="offset"/>
	<table class="input">
			<tr class="odd">
				<td width="200">Наименование:</td>
				<td>
					<input type="text" name="name" id="name" class="inp250" />
				</td>
			</tr>
			<tr class="even">
				<td width="200">Подчинен на:</td>
				<td>
					<select name="parent_id" id="parent_id" class="select250" ></select> 
				</td>
			</tr>
		</table>
		
		<br />
		<table class="input">
			<tr class="odd">
				<td width="250">&nbsp;</td>
				<td style="text-align:right;">
					<button class="search"onclick="updateGroup();"> Запиши </button>
					<button onclick="window.close();"> Затвори </button>
				</td>
			</tr>
		</table>
</form>
<script>
	
	loadXMLDoc2('result');
	
</script>