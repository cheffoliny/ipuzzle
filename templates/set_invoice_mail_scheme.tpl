{literal}
<script>

rpc_debug=true;
rpc_method="POST";

function onInit() {
	loadXMLDoc2('result');
}

function update() {
	loadXMLDoc2('update', 2);
	//parent.window.close();
}

</script>
{/literal}

<div id="search">
	<div class="page_caption">Имейл настройки</div>
</div>

<form id="form1" name="form1">
	<input type="hidden" id="nID" name="nID" value="0" />
	
	<div id=builder>
		<table width="100%;" class="page_data">
			
		
         <tr class="odd">
            <td colspan="6" valign="top" align="center">
				
            	<fieldset style="width: 680px;">
				<legend>ftp настройки</legend>
				<table class="input">
					<tr>
            		    <td align="left" style="width: 130px;">Сървър/Директория:</td>
						<td colspan="3"><input type="text" id="ftp_host" name="ftp_host" style="width: 530px;" /></td>
					</tr>

					<tr>
            		    <td align="left">Потребител:</td>
						<td style="width: 210px;"><input type="text" id="ftp_user" name="ftp_user" style="width: 200px;" /></td>
            		    <td align="right" style="width: 120px;">Парола:&nbsp;&nbsp;</td>
						<td style="width: 220px;"><input type="password" id="ftp_password" name="ftp_password" style="width: 205px;" /></td>						
					</tr>
								
				</table>
				</fieldset>
	       	</td>
	       </tr>			
		
           <tr class="odd">
            <td colspan="6" valign="top" align="center">
				
            	<fieldset style="width: 680px;">
				<legend>e-mail настройки</legend>
				<table class="input">
					<tr>
            		    <td align="left" style="width: 80px;">Тема:</td>
						<td colspan="3"><input type="text" id="email_subject" name="email_subject" style="width: 570px;" /></td>
					</tr>

					<tr>
            		    <td align="left">e-mail от:</td>
						<td style="width: 230px;"><input type="text" id="email_from" name="email_from" style="width: 220px;" /></td>
            		    <td align="right" style="width: 120px;">връщане към:&nbsp;&nbsp;</td>
						<td style="width: 220px;"><input type="text" id="email_reply" name="email_reply" style="width: 208px;" /></td>						
					</tr>
					
					<tr>
						<td align="left" colspan="4">Текст:</td>
					</tr>
					
					<tr align="center">
						<td colspan="4">
							<textarea id="textarea" name="textarea" style="width: 660px; height: 320px;"></textarea>
						</td>
					</tr>
								
				</table>
				</fieldset>
	       	 </td>
	       </tr>		
		
			<tr><td>&nbsp;</td></tr>		
		
		</table>
	</div>
	
	<div id="search">
		<table width="100%" cellspacing=5px>
			<tr>
				<td align="right" valign="bottom">
					<button id=b100 onClick="update()"><img src=images/confirm.gif />Запиши</button>&nbsp;
					<button id=b101 onClick="parent.window.close()"><img src="images/cancel.gif" />Затвори</button>
				</td>
			</tr>
		</table>
	</div>
</form>

<script>
onInit();
</script>
