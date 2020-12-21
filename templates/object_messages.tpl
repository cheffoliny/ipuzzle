{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

    rpc_on_exit = function() {

        jQuery("#result").find('[data-rl-ico]').each(function(){
            var jThis = jQuery(this);
            var fa_ico = (jThis.data('rl-ico'));
            var fa_sector = (jThis.data('rl-sector'));

            if(jThis.data('rl-ico')) {
                jThis.html('<span class="fa-layers fa-lg fa-fw tt mx-2" title="..."><i class="'+ fa_ico +'"></i><span class="fa-layers-text text-white bg-primary" data-fa-transform="shrink-6 right-12 up-6" style="padding: 2px; z-index: 10;">'+ fa_sector +'</span></span>');
            } else {
                jThis.html('...');
            }
        });

        jQuery('.tt').tooltip({
            html: true
        });

    };

    //jQuery
    jQuery(function ($) {

        $(document).ready(function(){
            loadXMLDoc2('result');
        });

    });
	
	function editSignal(id) {
		//alert(id);
		var obj = document.getElementById('nID').value;
		dialogSetSetupSignalMessage(id, obj);
	}

	function deleteSignal(id) {
		if ( confirm('Наистина ли желаете да премахнете сигнала?') ) {
			$('nIDSignal').value = id;
			loadXMLDoc2('delete', 1);
            window.location.reload( true );
		}
	}
	
	function newScheme() {
		if ( (prom = prompt('Въведете име на схемата')) && (prom.value != "") ) {
			$('sSchemeName').value = prom;
			loadXMLDoc2('newScheme', 1);
		} else {
			alert('Добавянето отказано!');
		}
	}	

	function editScheme() {
		if ( confirm('Наистина ли желаете да редактирате шаблона с тези сигнали?') ) {
			loadXMLDoc2('editScheme', 1);
		}
	}	
	
	function delScheme() {
		if ( confirm('Наистина ли желаете да премахнете шаблона?') ) {
			loadXMLDoc2('delScheme', 1);
		}
	}
	
	function fromScheme() {
		if ( confirm('Наистина ли желаете да добавите сигналите от избрания шаблон?') ) {
			loadXMLDoc2('fromScheme', 1);
		}
	}		

	
	function just_do_it() {
		switch (getById('sel').value) {
			case '1':
				checkAll( true );
				break;
			case '2':
				checkAll( false );
				break;			
			case '3':
				if ( confirm('Наистина ли желаете да изтриете избраните сигнали?') ) {
					loadXMLDoc2('delete2', 1);
				}
				break;
		}
	}

	function checkAll( bChecked ) {
		var aCheckboxes = document.getElementsByTagName('input');
		
		for( var i=0; i<aCheckboxes.length; i++ ) {
			if ( aCheckboxes[i].type.toLowerCase() == 'checkbox' )
				aCheckboxes[i].checked = bChecked;
		}
	}	
	
	function load() {
		loadXMLDoc2('result');
	}

	function techSupport() {
		var id = $('nID').value;
			
		dialogTechSupport(id);
	}

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
</script>
{/literal}

<form name="form1" id="form1" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="nIDSignal" name="nIDSignal" value="0" />
    <input type="hidden" id="sSchemeName" name="sSchemeName" value="" />
    <input type="hidden" id="bEditStatuses" name="bEditStatuses" value="{$bEditStatuses|default:true}"/>
    <input type="hidden" id="isService" name="isService" value="{$isService|default:0}"/>

    {include file=object_tabs.tpl}

    <div id="result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

    <nav class="navbar fixed-bottom flex-row pt-1 py-md-0 navbar-expand-lg" id="search">
        <div class="col-3 col-sm-4 col-lg-4" title="Редактиране на приемните станции">
            <div class="input-group input-group-sm">
                <div class="input-group-prepend" onClick="editShifts(0);">
                    <span class="fas fas fa-rss-square" data-fa-transform="right-20 down-10" title="Приемници..."></span>
                </div>
                <input class="form-control" type="text" name="receivers" id="receivers" title="Приемници" disabled />
            </div>
        </div>
        <div class="col-3 col-sm-4 col-lg-4" title="Шаблони">
            <div class="input-group input-group-sm">
                <div class="btn-group input-group-sm ml-1" role="group">
                    {if $edit.object_messages_edit}
                        <div class="input-group-prepend" onclick="fromScheme();" title="Използвай схемата с обекта">
                            <span class="far fa-list-alt" data-fa-transform="right-20 down-10"></span>
                        </div>
                    {else}
                        <div class="input-group-prepend">
                            <span class="far fa-list-alt" data-fa-transform="right-20 down-10"></span>
                        </div>
                    {/if}
                    <select class="form-control" id="scheme" name="scheme" ></select>
                    <div class="btn-group dropup">
                        <button id="btnGroupDrop1" type="button" class="btn btn-compact btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" >

                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            {if $edit.object_messages_scheme}
                                <a class="dropdown-item" href="#" onclick="newScheme();" title="Създай нова схема">
                                    <i class="fa fa-plus"></i> Нова схема
                                </a>
                            {else}
                                <a class="dropdown-item" href="#"><i class="fa fa-plus" ></i> Нова схема</a>
                            {/if}

                            <a class="dropdown-item"   name="Button4"	id="b25" title="Редактиране на филтър" 	onClick="openFilter( 2 );"			>Редактирай</a>
                            <a class="dropdown-item"  name="Button3"	id="b25" title="Премахване на филтър"	onClick="deleteFilter( schemes );"	>Изтрий</a>
                        </div>
                    </div>
                </div>
                    {**}
                    {**}
                    {*<span class="input-group-addon">				*}
                    {*{if $edit.object_messages_scheme}*}
                    {*<a href="#" onclick="editScheme();">*}
                    {*<img src="images/glyphicons/edit2.png" style="width: 12px; height: 12px;" title="Редактирай избраната схема според текущите сигнали" />*}
                    {*</a>*}
                    {*{else}*}
                    {*<img src="images/glyphicons/edit2.png" style="width: 12px; height: 12px;" title="Редактирай избраната схема според текущите сигнали" />*}
                    {*{/if}*}
                    {*</span>*}
                    {**}
                    {*<span class="input-group-addon-warning">*}
                    {*{if $edit.object_messages_scheme}*}
                    {*<a href="#" onclick="delScheme();">*}
                    {*<img src="images/glyphicons/del2.png" style="width: 12px; height: 12px;" title="Премахни избраната схема" />*}
                    {*</a>*}
                    {*{else}*}
                    {*<img src="images/glyphicons/del2.png" style="width: 12px; height: 12px;" title="Премахни избраната схема" />*}
                    {*{/if}*}
                    {*</span>*}
                    {**}
                    {*</div>	*}
                    {*</td>*}
                    {**}
                    {*<td style="width: 40px; text-align: right; padding: 2px;">*}
                    {*<div class="input-group">*}
                    {*<span class="input-group-addon-warning">*}
                    {*<img src="images/glyphicons/alarm.png" style="width: 12px; height: 12px;" title="Алармиращи съобщения" /></span>*}
                    {*<input type="checkbox" id="nReact" name="nReact" class="clear" onClick="load();" title="Алармиращи съобщения" />*}

            </div>
        </div>
        <div class="col-6 col-sm-4 col-lg-4">
            <div class="input-group input-group-sm text-right">
                <button class="btn btn-sm btn-success mr-1" onClick="editSignal(0);" title="Добави ново съобщение към обекта"><i class="fa fa-plus"></i> Добави</button>
                <button class="btn btn-sm btn-danger"	    onClick="parent.window.close();"><i class="far fa-window-close" ></i> Затвори </button>
            </div>
        </div>
    </nav>
    <div id="NoDisplay" style="display:none"></div>
</form>

<script>
	//loadXMLDoc2('result');

	{if !$edit.object_messages_edit}{literal}
		if ( form=document.getElementById('form1') ) {
			for(i=0;i<form.elements.length-1;i++) form.elements[i].setAttribute('disabled','disabled');
		}{/literal}
	{/if}	
</script>
