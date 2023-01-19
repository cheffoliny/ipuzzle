{literal}
	<script>
		rpc_debug = true;
		rpc_method = "POST";
		
		function onInit() {
			loadXMLDoc2( 'load');
		}
		
		function getResult() {
			select_all_options('account_earnings');
			select_all_options('account_expenses');
			loadXMLDoc2('save',5)
		}

	</script>
{/literal}

<form action="" method="POST" name="form1" id="form1" onsubmit="return false;">
	<input type="hidden" name="nID" id="nID" value="{$nID}">

	<div class="modal-content pb-3">
		<div class="modal-header">
			<h6 class="modal-title text-white" id="exampleModalLabel">{if !$nID }Добавяне{else}Редактиране{/if} на шаблон с филтри</h6>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="parent.window.close();">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
		<nav class="modal-body">


			<div class="row mb-1">
				<div class="col-6 pl-1">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="fas fa-hashtag fa-fw" data-fa-transform="right-22 down-10" title="Име на филтър..."></span>
						</div>
						<input class="form-control" id="name" name="name" type="text"  placeholder="Име на филтър..." />
					</div>
				</div>
				<div class="col-6">
					<div class="custom-control custom-checkbox">
{*						<input class="custom-control-input" type="checkbox" id="is_default" name="is_default" />*}
{*						<label class="custom-control-label" for="is_default">Направи основен</label>*}
					</div>
				</div>
			</div>


	<div class="row">
		<div class="col">
			<nav id="navbar-example" class="navbar navbar-light bg-secondary text-white my-3">
				Колони за визуализация
			</nav>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="fix_salary" name="fix_salary"  />
						<label class="custom-control-label" for="fix_salary">фиксирана заплата</label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="min_salary" name="min_salary"  />
						<label class="custom-control-label" for="min_salary">минимална заплата </label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="insurance" name="insurance"  />
						<label class="custom-control-label" for="insurance">осигурителна ставка </label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="trial" name="trial"  />
						<label class="custom-control-label" for="trial">пробен период</label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="due_days" name="due_days"  />
						<label class="custom-control-label" for="due_days">полагаем отпуск</label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="used_days" name="used_days"  />
						<label class="custom-control-label" for="used_days">използван отпуск</label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="remain" name="remain"  />
						<label class="custom-control-label" for="remain">оставащ отпуск</label>
					</div>
					<div class="custom-control custom-checkbox ">
						<input class="custom-control-input" type="checkbox" id="egn" name="egn"  />
						<label class="custom-control-label" for="egn">ЕГН</label>
					</div>
				</div>

		<div class="col">
			<nav id="navbar-example" class="navbar navbar-light bg-secondary text-white my-3">
				Наработки
			</nav>

			<table>
				<tr style="height: 5px;"><td></td></tr>
				<tr class="even">
					<td>
						<select name="all_earnings" id="all_earnings" size="10"  style="width: 100px; height:250px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'right');" multiple>
						</select>
					</td>
					<td>
						<button class="btn btn-sm btn-success h-100" name="button" title="Добави наработка" onClick="move_option_to( 'all_earnings', 'account_earnings', 'right'); return false;">
							<i class="far fa-plus"></i>
						</button></br>
						<button class="btn btn-sm btn-danger h-50" name="button" title="Премахни наработка" onClick="move_option_to( 'all_earnings', 'account_earnings', 'left'); return false;">
							<i class="far fa-minus"></i>
						</button>
					</td>
					<td>
						<select name="account_earnings[]" id="account_earnings" size="10" style="width: 100px;height:250px;" ondblclick="move_option_to( 'all_earnings', 'account_earnings', 'left');" multiple>
						</select>
					</td>
				</tr>
				<tr style="height: 5px;"><td></td></tr>
			</table>

		</div>
		<div class="col">
			<nav id="navbar-example" class="navbar navbar-light bg-secondary text-white my-3">
				Удръжки
			</nav>

			<table>
				<tr style="height: 5px;"><td></td></tr>
				<tr class="even">
					<td>
						<select name="all_expenses" id="all_expenses" size="10"  style="width: 100px;height:250px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'right');" multiple>
						</select>
					</td>
					<td>
						<button class="btn btn-sm btn-success h-100" name="button" title="Добави удръжка" onClick="move_option_to( 'all_expenses', 'account_expenses', 'right'); return false;">
							<i class="far fa-plus"></i>
						</button></br>
						<button class="btn btn-sm btn-danger h-50" name="button" title="Премахни удръжка" onClick="move_option_to( 'all_expenses', 'account_expenses', 'left'); return false;">
							<i class="far fa-minus"></i>
						</button>
					</td>
					<td>
						<select name="account_expenses[]" id="account_expenses" size="10" style="width: 100px;height:250px;" ondblclick="move_option_to( 'all_expenses', 'account_expenses', 'left');" multiple>
						</select>
					</td>
				</tr>
				<tr style="height: 5px;"><td></td></tr>
</table>

			<input type="checkbox" class="clear" name="ear_exp" id="ear_exp"/>  наработки - удръжки
		</div>
	</div>
	<nav class="navbar fixed-bottom flex-row mb-2 py-0 navbar-expand-lg py-md-1">
		<div class="col-12 col-sm-12 col-lg-12">
			<div class="input-group input-group-sm text-right">
				<button class="btn btn-block btn-sm btn-primary" type="button" onClick="getResult();"><i class="fa fa-plus"></i> Добави</button>
			</div>
		</div>
	</nav>


</form>

{literal}
	<script>
		onInit();
	</script>
{/literal}