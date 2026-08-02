{literal}
	<script>
		rpc_debug = true;
		var filterVisible = false;

		function formChange(name) {
			if ( name == 'nIDFirm' ) {
				$('nIDOffice').value = 0;
			}

			loadXMLDoc('load');
		}

		function personnel(id) {
			dialogPerson(id);
		}

		function showFilter(type) {
			if ( type == 'new' ) {
				var id = 0;
			} else if ( type == 'edit' ) {
				var id = document.getElementById('tabs').value;
			}

			dialogVisibleTabs(id);
		}

		function delFilter() {
			if ( confirm('Наистина ли желаете да премахнете филтъра?') ) {
				loadXMLDoc('delete', 1);

				rpc_on_exit = function() {
					loadXMLDoc('load');

					rpc_on_exit = function() {};
				}
			}
		}

		function numKeyed( e ) {
			if ( e.keyCode && e.keyCode == 13 ) {
				loadXMLDoc('result');
			}
		}

		function hideDiv( load ) {
			var e = document.getElementById("filter");
			var h = document.getElementById("hide");
			var s = document.getElementById("show");

			e.style.display	= "none";
			h.style.display = "none";
			s.style.display = "block";
			filterVisible	= false;

			if ( load == 1 ) {
				loadXMLDoc('result');
			}
		}

		function fixFilter() {
			var e = document.getElementById("filter");
			var h = document.getElementById("hide");
			var s = document.getElementById("show");

			switch( filterVisible ) {
				case true:
					hideDiv(1);
					break;

				case false:
					e.style.display	= "block";
					h.style.display = "block";
					s.style.display = "none";
					filterVisible	= true;
					break;
			}
		}
		
	</script>
{/literal}

<form action="" name="form1" id="form1" class="ui-personnel-admin" onSubmit="return loadXMLDoc('result')" onkeyup="return numKeyed( event );">

	{include file='tabs_setup_personnel.tpl'}

	<div class="ui-personnel-admin-shell">
		<div class="row justify-content-start pl-3 pb-1 pt-2 table-secondary ui-personnel-admin-filter">
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-tag" aria-hidden="true" title="Фирма..."></span>
					</div>
					<select class="form-control" name="nIDFirm" id="nIDFirm" onChange="formChange(this.name);"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2 pl-0">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						{*Администрация:&nbsp;*}
						<span class="ui-icon ui-icon-tags" aria-hidden="true" title="Офис..."></span>
					</div>
					<select class="form-control" name="nIDOffice" id="nIDOffice" onChange="formChange(this.name);"></select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-toggle" aria-hidden="true" title="Статус..."></span>
					</div>
					<select class="form-control select200" name="sStatus" id="sStatus">
						<option value="active"	>Активни	</option>
						<option value="moved"	>Преместени	</option>
						<option value="vacate"	>Напуснали	</option>
						<option value="all"		>Всички		</option>
					</select>
				</div>
			</div>
			<div class="col-6 col-sm-4 col-lg-2">
				<div class="input-group input-group-sm">
					<div class="input-group-prepend">
						<span class="ui-icon ui-icon-signature" aria-hidden="true" title="Име..."></span>
					</div>
					<input class="form-control" type="text" name="sName" id="sName" title="Име..."/>
				</div>
			</div>
			<div class="col-6 col-sm-8 col-lg-4 pl-3">
				<div class="input-group input-group-sm">
					<button type="button" id="hide" onclick="hideDiv(0);" class="btn btn-sm btn-light mr-2" style="display: none;" title="Скрий разширения филтър"><span class="ui-icon ui-icon-compress" aria-hidden="true"></span></button>
					<button type="button" id="show" onclick="fixFilter();" class="btn btn-sm btn-light mr-2" title="Покажи разширения филтър"><span class="ui-icon ui-icon-expand" aria-hidden="true"></span></button>

					<button type="button" class="btn btn-sm btn-success mr-2" onclick="personnel( 0 );"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави </button>
					<button class="btn btn-sm btn-info" type="submit" name="Button"><span class="ui-icon ui-icon-search" aria-hidden="true"></span> Търси</button>
				</div>
			</div>
		</div>
		<div id="filter" style="display: none;" class="pl-3 pb-1 table-secondary ui-personnel-admin-filter ui-personnel-admin-advanced-filter">
			<div class="row clearfix mb-1">
				<div class="col-6 col-sm-4 col-lg-2">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="ui-icon ui-icon-id-card" aria-hidden="true" title="Длъжност..."></span>
						</div>
						<select class="form-control" name="nPositions" id="nPositions" title="Длъжност"></select>
					</div>
				</div>
				<div class="col-6 col-sm-4 col-lg-2 pl-0">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="ui-icon ui-icon-home" aria-hidden="true" title="Обект на месторабота..."></span>
						</div>
						<select class="form-control" name="nIDObject" id="nIDObject" title="Обект..."></select>
					</div>
				</div>
				<div class="col-6 col-sm-4 col-lg-2">
					<div class="input-group input-group-sm">
						<div class="input-group-prepend">
							<span class="ui-icon ui-icon-phone" aria-hidden="true" title="Телефон за контакт..."></span>
						</div>
						<input class="form-control inp200" id="nMobile" name="nMobile" type="text" maxlength="10" onkeypress="return formatDigits(event);" placeholder="Телефон..."/>
					</div>
				</div>
				<div class="col-6 col-sm-4 col-lg-2">
					<div class="input-group input-group-sm">

					</div>
				</div>
				<div class="col-6 col-sm-8 col-lg-4">
					<div class="input-group input-group-sm">
						<div class="btn-group input-group-sm" role="group">
							<div class="input-group-prepend">
								<span class="ui-icon ui-icon-filter" aria-hidden="true" title="Филтър"></span>
							</div>
							<select class="form-control" id="tabs" name="tabs"></select>
							<button id="btnGroupDrop1" type="button" class="btn btn-compact btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">

							</button>
							<div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
								<a class="dropdown-item dropdown-item-menu" name="Button5" title="Нов филтър" onclick="showFilter('new');">Добави</a>
								<a class="dropdown-item dropdown-item-menu" name="Button4" title="Редактиране на филтър" onclick="showFilter('edit');">Редактирай</a>
								<a class="dropdown-item dropdown-item-menu" name="Button3" title="Премахване на филтър" onclick="delFilter();">Изтрий</a>
							</div>
						</div>
					</div>
					<div class="col-0 col-sm-0 col-lg-1"></div>
					<div class="col-0 col-sm-0 col-lg-1"></div>
				</div>
			</div>
		</div>

	
		<div id="result" class="ui-personnel-admin-result"></div>
	</div>

</form>

{literal}
	<script>
		loadXMLDoc('load');
	</script>
{/literal}
