{literal}
<script>
	rpc_debug = true;
	rpc_html_debug = true;

    function signalIconClass(iconName) {
        var icon = String(iconName || '').toLowerCase();

        if (icon.indexOf('fire') !== -1 || icon.indexOf('flame') !== -1) return 'ui-icon-fire';
        if (icon.indexOf('battery') !== -1) return 'ui-icon-battery';
        if (icon.indexOf('door') !== -1 || icon.indexOf('open') !== -1) return 'ui-icon-door';
        if (icon.indexOf('bell') !== -1 || icon.indexOf('alarm') !== -1) return 'ui-icon-bell';
        if (icon.indexOf('bolt') !== -1 || icon.indexOf('power') !== -1) return 'ui-icon-bolt';
        if (icon.indexOf('phone') !== -1) return 'ui-icon-phone';
        if (icon.indexOf('shield') !== -1) return 'ui-icon-shield';

        return 'ui-icon-signal';
    }

    function decorateMessageResults() {
        jQuery("#result").find('[data-rl-ico]').each(function(){
            var jThis = jQuery(this);
            var fa_ico = jThis.data('rl-ico');
            var fa_sector = jThis.data('rl-sector');

            if(fa_ico) {
                var badge = jQuery('<span/>', {
                    'class': 'ui-signal-badge tt',
                    'title': jThis.attr('title') || '...'
                });
                jQuery('<span/>', {
                    'class': 'ui-icon ' + signalIconClass(fa_ico),
                    'aria-hidden': 'true'
                }).appendTo(badge);
                jQuery('<span/>', {
                    'class': 'ui-signal-sector',
                    'text': fa_sector || ''
                }).appendTo(badge);
                jThis.empty().append(badge);
            } else {
                jThis.text('...');
            }
        });

        jQuery('#result .tt').tooltip({
            html: true
        });

    }

    function runObjectMessageAction(action) {
        rpc_on_exit = function(errorCode) {
            rpc_on_exit = decorateMessageResults;
            if (!errorCode) {
                loadXMLDoc2('result');
            }
        };
        loadXMLDoc2(action);
    }

    rpc_on_exit = decorateMessageResults;

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
			runObjectMessageAction('delete');
		}
	}
	
	function newScheme() {
		var prom = prompt('Въведете име на схемата');
		if ( prom && prom.trim() !== '' ) {
			$('sSchemeName').value = prom.trim();
			runObjectMessageAction('newScheme');
		} else {
			alert('Добавянето отказано!');
		}
	}	

	function editScheme() {
		if ( confirm('Наистина ли желаете да редактирате шаблона с тези сигнали?') ) {
			runObjectMessageAction('editScheme');
		}
	}	
	
	function delScheme() {
		if ( confirm('Наистина ли желаете да премахнете шаблона?') ) {
			runObjectMessageAction('delScheme');
		}
	}
	
	function fromScheme() {
		if ( confirm('Наистина ли желаете да добавите сигналите от избрания шаблон?') ) {
			runObjectMessageAction('fromScheme');
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
					runObjectMessageAction('delete2');
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
        rpc_on_exit = function(errorCode) {
            rpc_on_exit = decorateMessageResults;
            if (!errorCode) {
                window.location.reload(true);
            }
        };
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

    }
</script>
{/literal}

<form name="form1" id="form1" class="ui-object-core ui-object-messages ui-message-screen" onsubmit="return false;">
    <input type="hidden" id="nID" name="nID" value="{$nID|default:0}" />
    <input type="hidden" id="nIDSignal" name="nIDSignal" value="0" />
    <input type="hidden" id="sSchemeName" name="sSchemeName" value="" />
    <input type="hidden" id="bEditStatuses" name="bEditStatuses" value="{$bEditStatuses|default:true}"/>
    <input type="hidden" id="isService" name="isService" value="{$isService|default:0}"/>

    {include file="object_tabs.tpl"}

    <div id="result" class="ui-object-result ui-object-messages-result ui-message-result" rpc_excel_panel="off" rpc_paging="off" rpc_resize="off"></div>

    <nav class="navbar fixed-bottom ui-object-actions ui-object-messages-actions ui-message-toolbar" id="search" aria-label="Действия със съобщения">
        <div class="ui-message-toolbar-section ui-message-receivers" title="Приемни станции">
            <div class="input-group input-group-sm">
                <span class="input-group-prepend input-group-text">
                    <span class="ui-icon ui-icon-signal" aria-hidden="true" title="Приемници..."></span>
                </span>
                <input class="form-control" type="text" name="receivers" id="receivers" title="Приемници" readonly />
            </div>
        </div>
        <div class="ui-message-toolbar-section ui-message-schemes" title="Шаблони">
            <div class="input-group input-group-sm ui-message-scheme-controls">
                    {if $edit.object_messages_edit}
                        <button type="button" class="input-group-prepend input-group-text ui-message-scheme-apply" onclick="fromScheme();" title="Използвай схемата с обекта" aria-label="Приложи избраната схема">
                            <span class="ui-icon ui-icon-list" aria-hidden="true"></span>
                        </button>
                    {else}
                        <span class="input-group-prepend input-group-text">
                            <span class="ui-icon ui-icon-list" aria-hidden="true"></span>
                        </span>
                    {/if}
                    <select class="form-control" id="scheme" name="scheme" ></select>
                    <div class="btn-group dropup">
                        <button id="btnGroupDrop1" type="button" class="btn btn-compact btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Действия с шаблони" aria-label="Действия с шаблони">
                            <span class="ui-icon ui-icon-more" aria-hidden="true"></span>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                            {if $edit.object_messages_edit}
                                <a class="dropdown-item" href="#" onclick="newScheme();" title="Създай нова схема">
                                    <span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Нова схема
                                </a>
                                <a class="dropdown-item" href="#" title="Редактиране на схема" onClick="editScheme();"><span class="ui-icon ui-icon-edit" aria-hidden="true"></span> Редактирай</a>
                                <a class="dropdown-item" href="#" title="Премахване на схема" onClick="delScheme();"><span class="ui-icon ui-icon-delete" aria-hidden="true"></span> Изтрий</a>
                            {else}
                                <a class="dropdown-item disabled" href="#" onclick="return false;"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Нова схема</a>
                                <a class="dropdown-item disabled" href="#" onclick="return false;"><span class="ui-icon ui-icon-edit" aria-hidden="true"></span> Редактирай</a>
                                <a class="dropdown-item disabled" href="#" onclick="return false;"><span class="ui-icon ui-icon-delete" aria-hidden="true"></span> Изтрий</a>
                            {/if}
                        </div>
                    </div>
            </div>
        </div>
        <div class="ui-message-toolbar-section ui-message-actions">
            <div class="input-group input-group-sm">
                {if $edit.object_messages_edit}
                <button type="button" class="btn btn-sm btn-success mr-1" onClick="editSignal(0);" title="Добави ново съобщение към обекта"><span class="ui-icon ui-icon-plus" aria-hidden="true"></span> Добави</button>
                {/if}
                <button type="button" class="btn btn-sm btn-danger" onClick="parent.window.close();"><span class="ui-icon ui-icon-close" aria-hidden="true"></span> Затвори </button>
            </div>
        </div>
    </nav>
    <div id="NoDisplay" style="display:none"></div>
</form>
