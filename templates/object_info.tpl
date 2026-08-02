{literal}
	<script>
		rpc_debug = true;
		rpc_method='POST';

        function onInit() {

            $('start_time').disabled = true;

			if($('bEditStatuses').value == false) {
				$('statuses').disabled = true;
				$('functions').disabled = true;
				$('objtype').disabled = true;
				$('phone').disabled = true;
				$('isSOD').disabled = true;
				$('isFO').disabled = true;
				$('nIDFirm').disabled = true;
				$('nIDOffice').disabled = true;
				$('nIDReactionFirm').disabled = true;
				$('nIDReactionOffice').disabled = true;
			}

            loadXMLDoc2('result');

            rpc_on_exit = function ( nCode ) {
				if( !parseInt( nCode ) ) {
//					if($('have_init').value == '0') {
//						$('have_init').value = 1;
//						if($('have_sod_under_executer').value == '1' ) {
//							$('reaction').style.display = "none";
//							$('sod_under_executer').style.display = "inline";
//						} else {
//							$('reaction').style.display = "inline";
//							$('sod_under_executer').style.display = "none";
//						}
//					}
				}

				var id = $('nID').value;
				var bn = jQuery('#faces');
				

				if ( parseInt(id,10) > 0 ) {
					bn.removeAttr('disabled');
				}

                jQuery('#form1 *').filter(':input').each(function(){

                    if(this.nodeName == 'SELECT') {
                        jQuery(this).data('originalValue',jQuery('#'+jQuery(this).attr('id')+' option[value="'+jQuery(this).val()+'"]').text());
                    } else if(jQuery(this).is(':checkbox')) {
                        if(jQuery(this).is(":checked")){
                            jQuery(this).data('originalValue','on');
                        } else {
                            jQuery(this).data('originalValue','off');
                        }
                    } else {
                        jQuery(this).data('originalValue',jQuery(this).val());
                    }
                });
			}

		}
		
//		function onChangeReactionFirms() {
////			if($('nIDReactionFirm').value == '-1') {
////				$('reaction').style.display = "none";
////				$('sod_under_executer').style.display = "block";
////			} else {
////				$('reaction').style.display = "block";
////				$('sod_under_executer').style.display = "none";
//				loadXMLDoc2('loadReactionOffices');
//			}
//		}
		
		function delFace(id) {
			if ( confirm('Наистина ли желаете да премахнете МОЛ-ът към този обект?') ) {
	
				$('FaceID').value = id;
				//alert(id);
				loadXMLDoc2('deleteFace',1);
			}
		}
		
		function editFace(id) {
			var id_obj;
			id_obj = $('nID').value;
			if( id_obj > 0 ) {
				dialogSetupFace(id,id_obj);
			} else {
				alert('Първо запишете новия обект и после въведете МОЛ-ове');
			}
		}
				
		function formSubmit() {
			//loadXMLDoc2( 'save',2);	
			loadXMLDoc2( 'save' );	

			rpc_on_exit = function() {
				var id = $('nID').value;
				var bn = $('faces');
				
				if ( parseInt(id) > 0 ) {
					bn.setAttribute('disabled', '');
				}
                saveObjectHistory();
			}
		}

        function saveObjectHistory() {

            var historyData  = {};
            historyData['historyData'] = JSON.stringify(getSerializedForHistory());
            historyData['id_object'] = jQuery('#nID').val();
            loadJSON('logObjectHistory',historyData,function(data) {});

        }

        function getSerializedForHistory() {

            var data = [];

            jQuery('#form1 *').filter(':input').each(function(){
                var tmp = {};

                tmp.name = jQuery(this).data('alias');

                if(tmp.name != undefined) {
                    if (this.nodeName == 'SELECT') {

                        tmp.value = jQuery('#' + jQuery(this).attr('id') + ' option[value="' + jQuery(this).val() + '"]').text();

                    } else if(jQuery(this).is(':checkbox')) {
                        if(jQuery(this).is(":checked")){
                            tmp.value = 'on';
                        } else {
                            tmp.value = 'off';
                        }
                    } else {
                        tmp.value = jQuery(this).val();
                    }

                    tmp.originalValue = jQuery(this).data('originalValue');
                    if (tmp.value != tmp.originalValue) {
                        tmp.changed = true;
                    }
                    data.push(tmp);
                }
            });

            return data;

        }

		// function techSupport() {
		// 	var id = $('nID').value;
        //
		// 	dialogTechSupport(id);
		// }

        function ServiceStatus() {

            if(intval(jQuery('#isService').val()) )
            {
                //обекта е в сервизен - сваляме го
                loadXMLDoc2('closeServiceStatus');
            }
            else
            {
                //пускаме го в сервизен
                loadXMLDoc2('setServiceStatus');
            }

            rpc_on_exit = function() {
                window.location.reload( true );
            }

        }
		
		function makePayment() {
			var id = $('nID').value;
			dialogSaleForObject(id);
		}
		
		InitSuggestForm = function() {		
			for(var i = 0; i < suggest_elements.length; i++) {
				if( suggest_elements[i]['id'] == 'nIDCity' ) {
					suggest_elements[i]['suggest'].setSelectionListener( onSuggestCity );
				}
			}
		}
				
		function onSuggestCity ( aParams ) {
//			$('nIDCity').value = aParams.KEY;
//			alert(aParams.KEY);
			var aParts = aParams.KEY.split(';');
			
			$('id_city').value = aParts[0];
			$('nIDCity').value = aParts[1];	

			loadXMLDoc2('loadCityAreas');		
		}

        function onCityChange() {
            if(empty(jQuery('#nIDCity').val())) {
                $('id_city').value = 0;
            }
        }
	</script>
{/literal}
{if $isSOD}
    {assign var=labelSOD value='btn btn-compact btn-light active'}
{else}
    {assign var=labelSOD value='btn btn-compact btn-light'}
{/if}
{if $isFO}
    {assign var=labelFO value='btn btn-compact btn-light active'}
{else}
    {assign var=labelFO value='btn btn-compact btn-light'}
{/if}


<form name="form1" id="form1" class="ui-object-core ui-object-info" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="FaceID" name="FaceID" value="0" />
    <input type="hidden" id="sName" name="sName" value="" />
    {*<input type="hidden" id="have_sod_under_executer" name="have_sod_under_executer" value="0"/>*}
    {*<input type="hidden" id="have_init" name="have_init" value="0" />	*}
    <input type="hidden" id="id_face" name="id_face" value="0">
    <input type="hidden" id="id_city" name="id_city" value="0">
    <input type="hidden" id="bEditStatuses" name="bEditStatuses" value="{$bEditStatuses|default:true}"/>
    <input type="hidden" id="isService" name="isService" value="{$isService|default:0}"/>
    <!--added for test History! You can try to remove them!-->

    <input type="hidden" class="input-transparent-no-borders form-control-inp120" id="geo_lat" name="geo_lat" data-alias="geo_lat" readonly />
    <input type="hidden" class="input-transparent-no-borders form-control-inp120" id="geo_lan" name="geo_lan" data-alias="geo_lan" readonly />

    {include file='object_tabs.tpl'}

    <div class="container-fluid mb-4 mx-2 ui-object-info-fields">
        <div class="row clearfix mt-2">
            <div class="col-2 px-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-home" title="Номер на обект" aria-hidden="true"></span>
                    </div>
                    <input class="form-control form-control" type="text" name="num" id="num" onkeypress="return formatDigits(event);"  placeholder="Номер на обект..." />
                    <input type="hidden" name="oldNum" id="oldNum"/>
                </div>
            </div>
            <div class="col-7 px-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-home" title="Име на обекта" aria-hidden="true"></span>
                    </div>
                    <input class="form-control" type="text" name="name" id="name"  placeholder="Име на обекта..." />
                </div>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-eye" title="Състояние" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="statuses" id="statuses" ></select>
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-2 px-1">
                <div class="input-group input-group-sm">
                    <input readonly class="form-control-plaintext text-white" name="start_time" id="start_time" />
                </div>
            </div>
            <div class="col-7 px-1">
                <div class="row clearfix">
                    <div class="input-group input-group-sm col-sm-6">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-location" title="Населено място" aria-hidden="true"></span>
                        </div>
                        <input class="form-control" name="nIDCity" id="nIDCity" suggest="suggest" queryType="onSuggestCity" onchange="onCityChange()" onpast="onCityChange()" placeholder=" Гр./с..."/>
                    </div>
                    <div class="input-group input-group-sm col-sm-6">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-map" title="Квартал" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDArea" id="nIDArea" ></select>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm pl-0">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-tag" title="Тип на обекта" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="objtype" id="objtype" ></select>
                    <input class="form-control" type="hidden" name="phone" id="phone" placeholder=" 0хххх..." />
                </div>
            </div>
        </div>
        <div class="row clearfix mt-2">
            <div class="col-2 px-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-crop" title="Дистанция" aria-hidden="true"></span>
                    </div>
                    <input class="form-control form-control-inp50" type=text name="nDistance" id="nDistance" onkeypress="return formatDigits(event);" />
                </div>
            </div>
            <div class="col-7 px-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-road" title="Ул./№/бл./вх./ет./ап./Местност" aria-hidden="true"></span>
                    </div>
                    <input class="form-control" type=text name="sAddress" id="sAddress" placeholder="Ул./Местност" />
                </div>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-code" title="Дейност" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="functions" id="functions" ></select>
                </div>
            </div>
        </div>
        <div class="row clearfix mt-4">
            <div class="col-2 px-1">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-clock" title="Край на работно време" aria-hidden="true"></span>
                    </div>
                    <input class="form-control" type=text name="work_time_alert" id="work_time_alert" onkeypress="return formatTimeS(event);" placeholder="00:00:00" />
                </div>
            </div>
            <div class="col-7 px-1">
                <div class="row clearfix">
                    <div class="input-group input-group-sm col-sm-6">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-tag" title="Административна фирма" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDFirm" id="nIDFirm" onchange="loadXMLDoc2('loadOffices')" ></select>
                    </div>
                    <div class="input-group input-group-sm col-sm-6">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-car" title="Реакция от фирма" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDReactionFirm" id="nIDReactionFirm" onchange="loadXMLDoc2('loadReactionOffices');"></select>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-settings" title="Сервизна фирма" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="nIDTechFirm" id="nIDTechFirm" onchange="loadXMLDoc2('loadTechOffices')" ></select>
                </div>
            </div>
        </div>
        <div class="row clearfix mt-2">
            <div class="col-2 px-1">
                <div class="btn-group btn-group-sm btn-group-toggle" data-toggle="buttons">
                    <label title="СОД" class="{$labelSOD}">
                        <input type="checkbox" name="isSOD" id="isSOD" autocomplete="on" /><span class="ui-icon ui-icon-car" aria-hidden="true"></span>
                    </label>
                    <label title="ФО" class="{$labelFO}">
                        <input type="checkbox" name="isFO" id="isFO" value="0" autocomplete="on" /><span class="ui-icon ui-icon-calendar" aria-hidden="true"></span>
                    </label>
                </div>
            </div>
            <div class="col-7 px-1">
                <div class="row clearfix">
                    <div class="input-group input-group-sm col-sm-6">
                         <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-tags" title="Администрация от" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDOffice" id="nIDOffice" ></select>
                    </div>
                    <div class="input-group input-group-sm col-sm-6">
                        <div class="input-group-prepend">
                            <span class="ui-icon ui-icon-car" title="Реакция от" aria-hidden="true"></span>
                        </div>
                        <select class="form-control" name="nIDReactionOffice" id="nIDReactionOffice" ></select>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-settings" title="Сервизен офис" aria-hidden="true"></span>
                    </div>
                    <select class="form-control" name="nIDTechOffice" id="nIDTechOffice" data-alias="id_tech_office"></select>
                </div>
            </div>
        </div>

        <div class="row clearfix mt-2">
            <div class="col-12 pl-1 pr-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-info" title="Информация" aria-hidden="true"></span>
                    </div>
                    <textarea class="form-control py-0" rows="3" id="operativ_info" name="operativ_info" placeholder="Оперативна информация" title="Оперативна информация" ></textarea>
                </div>
            </div>
        </div>
        <div class="row clearfix mt-2">
            <div class="col-12 pl-1 pr-3">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="ui-icon ui-icon-info" title="Информация" aria-hidden="true"></span>
                    </div>
                    <textarea class="form-control py-0" rows="3" id="tech_info" name="tech_info" placeholder="Информация за сервиз" title="Информация за сервиз" ></textarea>
                </div>
            </div>
        </div>
    </div>
<div class="p-0 m-0 ui-object-result-shell" style="overflow: auto; position: relative; height: 300px;">
    <div class="w-100 p-0 m-0 ui-object-result" id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="on" style="overflow: auto;"></div>
</div>
<nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg ui-object-actions ui-object-info-actions" id="search">
    <div class="col-6 col-sm-7 col-lg-7 pl-0">
        <div class="input-group input-group-sm">
            <div class="input-group-prepend">
                <span class="ui-icon ui-icon-file" title="Име за фактура" aria-hidden="true"></span>
            </div>
            <input class="form-control" type="text" id="invoice_name" name="invoice_name" placeholder="Име за фактура..." />
        </div>
    </div>
    <div class="col-6 col-sm-5 col-lg-5 my-2 text-right">
        <div class="input-group input-group-sm text-right">
            <button type="button" class="btn btn-sm btn-light mr-1" onclick="editFace(0)"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Контакт </button>
            <button type="button" class="btn btn-sm btn-success mr-1" onClick="formSubmit();"><span class="ui-icon ui-icon-save" aria-hidden="true"></span> Запиши </button>
            <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
        </div>
    </div>
</nav>
</form>



<script>
    onInit();

    {if !$nID && !$bEditStatuses}
    {literal}
    if ( form=document.getElementById('form1') ) {
        for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled', 'disabled');
    }
    {/literal}
    {else}
    {if !$edit.object_info_edit}{literal}
    if ( form=document.getElementById('form1') ) {
        for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
    }{/literal}
    {/if}
    {/if}
</script>
