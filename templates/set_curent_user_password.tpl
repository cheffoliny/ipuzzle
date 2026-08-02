<form id="form1" class="ui-access-dialog ui-access-password-dialog ui-current-user-password-dialog" onSubmit="return loadXMLDoc( 'update', 3 );">
	<div class="page_caption">
		<span class="ui-icon ui-icon-key" aria-hidden="true"></span>
		Промяна на парола{if $name} за {$name|escape:"html"}{/if}
	</div>

	<div id="search" class="ui-access-dialog-body">
		<fieldset class="ui-access-selection">
			<legend>Данни за достъп</legend>
			<table class="input ui-access-form-table ui-current-user-password-table">
				<tr>
					<td><label for="password">Стара парола</label></td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="ui-icon ui-icon-key" aria-hidden="true"></span>
							</div>
							<input class="form-control" type="password" name="password" id="password" value="" autocomplete="current-password" />
						</div>
					</td>
				</tr>
				<tr>
					<td><label for="new_password">Нова парола</label></td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="ui-icon ui-icon-lock" aria-hidden="true"></span>
							</div>
							<input class="form-control" type="password" name="new_password" id="new_password" value="" autocomplete="new-password" />
						</div>
					</td>
				</tr>
				<tr>
					<td><label for="confirm_password">Повтори новата парола</label></td>
					<td>
						<div class="input-group input-group-sm">
							<div class="input-group-prepend">
								<span class="ui-icon ui-icon-check" aria-hidden="true"></span>
							</div>
							<input class="form-control" type="password" name="confirm_password" id="confirm_password" value="" autocomplete="new-password" />
						</div>
					</td>
				</tr>
			</table>
		</fieldset>
	</div>

	<div class="ui-access-dialog-actions ui-final-actions" aria-label="Действия">
		<button type="submit" class="ui-current-password-save">
			<span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши
		</button>
		<button type="button" class="ui-current-password-close" onClick="parent.window.close(); return false;">
			<span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори
		</button>
	</div>
</form>
