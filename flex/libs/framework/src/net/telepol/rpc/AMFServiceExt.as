package net.telepol.rpc
{
	import flash.events.Event;
	import flash.utils.describeType;
	import flash.utils.flash_proxy;
	import flash.utils.getDefinitionByName;
	
	import mx.collections.ArrayCollection;
	import mx.controls.Alert;
	import mx.controls.ComboBox;
	import mx.controls.DataGrid;
	import mx.controls.HorizontalList;
	import mx.controls.Label;
	import mx.controls.List;
	import mx.controls.RadioButton;
	import mx.controls.TileList;
	import mx.core.Application;
	import mx.formatters.DateFormatter;
	import mx.rpc.events.FaultEvent;
	import mx.rpc.events.ResultEvent;
	import mx.rpc.remoting.mxml.RemoteObject;
	import mx.styles.CSSStyleDeclaration;
	import mx.styles.StyleManager;
	
	import net.telepol.res.Resources;
	import net.telepol.ui.AutoComplete;
	import net.telepol.utils.ClassUtils;
	
	use namespace flash_proxy;

	public dynamic class AMFServiceExt extends RemoteObject {
		
		protected var appVariables:XMLList = null;
		protected var dateFormatter:DateFormatter = new DateFormatter();
		protected var hiddenParams:Object;

		private var alert:Alert;
		private var alertCSS:CSSStyleDeclaration;
		private var resources:Resources = Resources.instance;
		
		
		public function AMFServiceExt() {
			
			super();
			
			this.source = "api_" + ClassUtils.getShortClassName( Application.application );
			this.destination = this.source;
			
			this.addEventListener( ResultEvent.RESULT, resultHandler );
			this.addEventListener( FaultEvent.FAULT, faultHandler );
			
			dateFormatter.formatString = "YYYY-MM-DD";

			alertCSS = StyleManager.getStyleDeclaration("Alert");
			
			this.showBusyCursor = true; 
		}
		
		override flash_proxy function callProperty( name:*, ... args:Array ):* {
			
			return super.callProperty.call( this, name, getRequestParams(args) );
	    }
	    
	   	private function doVariables(response:FlexResponse):void {
			if ((response != null) && (response.variables != null)){
				// for each variable
				for each(var currentVar:FlexVar in response.variables){
					// get variable description by it's Name
					var var_description:XMLList = appVariables.(attribute("name") == currentVar.name);
					if (var_description.length() > 0){
						var prop:XML = var_description[0];
						var access:String = prop.attribute("access");
						var className:String = prop.attribute("type");
						var cls:Class = getDefinitionByName(className) as Class;
						
						// get variable
						var variable:Object = Application.application[currentVar.name];
						if (variable == null)
							// create it
							variable = new cls();

						if ((variable is cls) && (currentVar.value is cls))
							variable = (currentVar.value as cls);
						else if ((variable is ArrayCollection) && (currentVar.value is Array))
							variable.source = currentVar.value;

						//var test1:XMLList = describeType(Application.application).method.(attribute("declaredBy") == Application.application.className);
						// invoke method
						//Application.application["test"]();
				
						// set variable
						Application.application[currentVar.name] = variable;
					} else {
						// can't find public variable with this name
						//TODO: log variable
					}
				}
			}
		}

		private function doControls(response:FlexResponse):void {
			if ((response != null) && (response.controls != null)){
				for each(var currentControl:FlexControl in response.controls){
					var control_description:XMLList = appVariables.(attribute("name") == currentControl.name);
					if (control_description.length() > 0){
						var prop:XML = control_description[0];
						
						var control:Object = Application.application[currentControl.name];
						
						// set control attributes
						for each(var attr:Object in currentControl.attributes){
							try {
								if (control.hasOwnProperty(attr.name))
									control[attr.name] = attr.value;
								else
									control.setStyle(attr.name, attr.value);
							} catch(e:Error){
								//Error #1034: Type Coercion failed: cannot convert "" to Function.
								if (e.errorID == 1034)
									control[attr.name] = null;
								trace(e.message);
							}
						}
						//control['drawFocus'](true);

						// set default values
						if ((currentControl.defaultField != null) && (currentControl.defaultValue != null)){
							trace(currentControl.defaultField);
							trace(currentControl.defaultValue);
							if ((control is ComboBox) || (control is List) ||
								(control is TileList) || (control is HorizontalList)){
								if (control.dataProvider != null){
									var selectedIndex:int = -1;
									if (control.dataProvider is Array){
										selectedIndex = getIndexByValue(control.dataProvider as Array,
											currentControl.defaultField, 
											currentControl.defaultValue);
									} else if (control.dataProvider is ArrayCollection){
										selectedIndex = getIndexByValue(control.dataProvider.source,
											currentControl.defaultField, 
											currentControl.defaultValue);
									}
									control.selectedIndex = selectedIndex;
									if ((control is List) || (control is TileList) || (control is HorizontalList))
										control.callLater(control.scrollToIndex, [selectedIndex]);
								}
							}
							if (control is Label){
								//if ((control as Label).data)
							}
						}
					}
				}
			}
		}
		private function getIndexByValue(arr:Array, field:String, val:Object): int {
			var l:int = arr.length;
			for(var i:int = 0; i < l; i++){
				if (arr[i] && arr[i][field] == val)
					return i; 
			}
			return -1;
		}

		protected function Call(func_name:String):void {
			//var o:Object = getRequestParams();

			//CursorManager.setBusyCursor();
			//var op:AbstractOperation = web_service.getOperation(func_name);
			//ao.arguments = new Object();
			//ao.arguments.sMessage = "as";
			//op.send('');
		}
		
		private function showAlert(color:Object):void {
			//alertCSS.setStyle("modalTransparencyColor", color);
			//alertCSS.setStyle("themeColor", color);
			alert = Alert.show("The quick brown fox...");
			//alertCSS.setStyle("modalTransparencyColor", null);
			alert.isPopUp = false;
		}
		private function resultHandler(e:ResultEvent):void {
			if (appVariables == null)
				appVariables = describeType(Application.application).accessor.(attribute("declaredBy") == Application.application.className);

			if (e.result is FlexResponse){
				var response:FlexResponse = (e.result as FlexResponse);
				// 1. Error
				//		if we have an error, show it end exit
				if (response.error && response.error.nCode == 0){
					// 2. Variables
					doVariables(response);
					// 3. Controls
					doControls(response);
					// 4. Hidden params
					//if ((response.hiddenParams != null) && (response.hiddenParams.hasOwnProperty('nID')))
					//hiddenParams = response.hiddenParams;
					if (response.hiddenParams != null) {
						for (var item:* in response.hiddenParams)
        					if (item != "mx_internal_uid"){
        						if (hiddenParams == null)
        							hiddenParams = {};
        						hiddenParams[item] = response.hiddenParams[item];
        					}
					}
					// 5. Logs
					// 6. Alerts
					if (response.alerts && response.alerts.length > 0){
						var alertMsg:String = "";
						for each(var msgAlert:String in response.alerts)
							alertMsg += msgAlert + "\n";
						Alert.show(alertMsg);
						//showAlert('#FFbaba');
					}
					
					if (response.logs && response.logs.length > 0){
						var logMsg:String = "";
						for each(var msgLog:String in response.alerts)
							logMsg += msgLog + "\n";
						Alert.show(logMsg);
						//showAlert('#FFbaba');
					}
					
				} else {
					Alert.show(response.error.sMsg, 'Код на грешка: '+response.error.nCode);
				}
			} else {
				trace(e.result);
			}

			dispatchEvent(new Event("onResponse"));
		}
		
		private function faultHandler( event:FaultEvent ):void {
			Alert.show( event.fault.faultString, "Error", 4, null, null, resources.ImgLevelError);
			dispatchEvent(new Event("onFault"));
		}
		
		public function getRequestParams(params:Array = null):Object {
			var ret:Object = new Object();
			
			// get all html values
			for (var name:String in Application.application.parameters) {
				try{
					ret['htmlParams'][name] = Application.application.parameters[name];
				} catch (e:Error){
					//Alert.show(e.message);
					ret['htmlParamsErr'] = e.message;
				}
			}
			if (hiddenParams)
				ret['hiddenParams'] = hiddenParams;

			if (params && params.length > 0){
				var lastVarType:String = describeType(params[params.length-1]).@name[0];
				
				for (var current_param:int=0; current_param < params.length; current_param++){
					if (params[current_param] is FlexVar)
						ret[params[current_param].name] = params[current_param].value;
				}
				
				if (lastVarType == 'Boolean'){
					//if last var is Boolean (false)
					var bb:Boolean = params[params.length-1];

					if (! bb)
						return ret;
				} else {
				}
			}
			
			if (appVariables == null)
				appVariables = describeType(Application.application).accessor.(attribute("declaredBy") == Application.application.className);

			for each(var prop:XML in appVariables){
				var prop_type:String = prop.@type;
				
				//Alert.show(prop_type);
				// TODO:
				var current_var:Object = Application.application[prop.@name];
				if (current_var is DataGrid){
					trace(prop.@name);
					requestParamDataProvider(ret, prop);
					continue;
				}
				if (current_var is AutoComplete){
					trace(prop.@name);
					requestParamByPropName(ret, prop, 'currentText');
					continue;
				}
				switch (prop_type){
					case "mx.controls::List":
					case "mx.controls::ComboBox":
					case "mx.controls::TileList":
					case "mx.controls::HorizontalList":
						requestParamSelectedItem(ret, prop);
						break;
					case "mx.controls::CheckBox":
					case "mx.controls::RadioButton":
						requestParamSelected(ret, prop);
						break;
					case "mx.controls::TextInput":
					case "mx.controls::TextArea":			
						requestParamByPropName(ret, prop, 'text');
						break;
					case "mx.controls::RichTextEditor":
						requestParamByPropName(ret, prop, 'htmlText');
						break;
					case "mx.controls::NumericStepper":
					case "mx.controls::HSlider":
					case "mx.controls::VSlider":
						requestParamByPropName(ret, prop, 'value');
						break;
					case "mx.controls::ColorPicker":
						requestParamByPropName(ret, prop, 'selectedColor');
						break;
					case "mx.controls::DateField":
					case "mx.controls::DateChooser":
						requestParamSelectedDate(ret, prop);
						break;
					case "mx.controls::RadioButtonGroup":
						requestParamRadioGroup(ret, prop);
						break;
					case "mx.controls::RadioButton":
						break;
				}
			}

			return ret;
		}
		private function requestParamDataProvider(obj:Object, comp:XML):void {
			var varname:String = comp.@name;
			var cls:Class = getDefinitionByName(comp.@type) as Class;
			
			if (varname != ""){
				var cp:Object = Application.application[varname] as cls;
				if (cp && cp.hasOwnProperty('dataProvider') && cp.dataProvider != null){
					if (cp.dataProvider is Array)
						obj[varname] = cp.dataProvider;
					else if (cp.dataProvider is ArrayCollection)
						obj[varname] = cp.dataProvider.source;
				}
			}
		}
		private function requestParamSelectedItem(obj:Object, comp:XML):void {
			var varname:String = comp.@name;
			var cls:Class = getDefinitionByName(comp.@type) as Class;

			if (varname != ""){
				var cp:Object = Application.application[varname] as cls;
				if (cp && (cp.selectedItem != null)){
					if (cp.selectedItem.hasOwnProperty('id'))
						obj[varname] = cp.selectedItem.id;
					else
						obj[varname] = cp.selectedItem;
				}
			}
		}
		private function requestParamSelected(obj:Object, comp:XML):void {
			var varname:String = comp.@name;
			var cls:Class = getDefinitionByName(comp.@type) as Class;
			
			if (varname != ""){
				var cp:Object = Application.application[varname] as cls;
				// skip RadioButtons in group
				if (cp && (cp is RadioButton) && (cp as RadioButton).groupName != "radioGroup"){
					return;
				}
				if (cp)
					obj[varname] = cp.selected;
			}
		}
		private function requestParamByPropName(obj:Object, comp:XML, propname:String):void {
			var varname:String = comp.@name;
			var cls:Class = getDefinitionByName(comp.@type) as Class;
			
			if (varname != ""){
				var cp:Object = Application.application[varname] as cls;
				if (cp)
					obj[varname] = cp[propname];
			}
		}
		private function requestParamSelectedDate(obj:Object, comp:XML):void {
			var varname:String = comp.@name;
			var cls:Class = getDefinitionByName(comp.@type) as Class;
			
			if (varname != ""){
				var cp:Object = Application.application[varname] as cls;
				if (cp && (cp.selectedDate != null))
					obj[varname] = dateFormatter.format(cp.selectedDate);
			}
		}
		private function requestParamRadioGroup(obj:Object, comp:XML):void {
			var varname:String = comp.@name;
			var cls:Class = getDefinitionByName(comp.@type) as Class;
			
			if (varname != ""){
				var cp:Object = Application.application[varname] as cls;
				if (cp && (cp.selection != null))
					obj[varname] = cp.selection.id;
				else
					obj[varname] = "";
			}
		}

		
	}
}