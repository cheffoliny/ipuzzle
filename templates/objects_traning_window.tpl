{literal}
	<script xmlns="http://www.w3.org/1999/html">
		//
		rpc_debug = true;
		filterVisible = false;

		function onInit() {
			loadXMLDoc2('result');
			rpc_on_exit = function() {

			//	setStyles();
			};
		}

		function getResult() {
			loadXMLDoc2('result');
		}

		function setIds() {

			var aID = [] , aTmp = [];

			jQuery("input[type='checkbox']").each(function() {
				var jThis = jQuery(this);

				if(jThis.is(":checked")) {
					var sID = jThis.attr('id');

					if(sID.includes("cancel_checkbox")) {
						sID = sID.replace('cancel_checkbox[','');
						sID = sID.replace(']','');
						aTmp = sID.split('@');

						aID.push(aTmp[0]);
					}
				}
			});
			alert(aID);
			if(!empty(aID)) {
				jQuery('#sIDForCancel').val(aID.join(','));

			}
			else {
				jQuery('#sIDForCancel').val('');
			}
		}

		function just_do_it() {

			switch($('sel').value) {

				case 'mark_all':
					if ( confirm('Наистина ли желаете да маркирате всички записи?') ) {
						checkAll(true);
					}
					break;

				case 'unmark_all':
					if ( confirm('Наистина ли желаете да отмаркирате всички записи?') ) {
						checkAll(false);
					}
					break;
				case 'del':
					if ( confirm('Наистина ли желаете да върнете маркираните записи?') ) {
						setIds();

						loadXMLDoc2('cancel2');
						rpc_on_exit = function() {
							window.location.reload();
							rpc_on_exit = function() {}
						}

					}
					break;

			}
		}

	</script>

{/literal}
<dlcalendar click_element_id="imgDeadlineFrom" input_element_id="sDeadlineFrom" tool_tip="Изберете дата"></dlcalendar>
<dlcalendar click_element_id="imgDeadlineTo" input_element_id="sDeadlineTo" tool_tip="Изберете дата"></dlcalendar>
<form action="" name="form1" id="form1" onSubmit="return false;" class="form-horizontal" role="form">
	<input type="hidden" name="nIDPerson" id="nIDPerson" value="{$nIDPerson}">
	<input type="hidden" name="nIDOffice" id="nIDOffice" value="{$nIDOffice}">

	<input type="hidden" name="sType" id="sType" value="{$sType}">
	<input type="hidden" id="sIDForCancel" name="sIDForCancel" value="" />

	<ul class="nav nav-tabs nav-intelli">
		<li class="nav-item text-center text-white px-4 py-2 mb-1" title="...">Задачи на {$sPerson}</li>
	</ul>

	<div id="result" rpc_excel_panel="on" rpc_paging="on"></div>
</form>

<script>
	onInit();
</script>