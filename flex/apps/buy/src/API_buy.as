package {
	import mx.core.Application;
	import mx.managers.CursorManager;
	
	import telenet.api.BaseAMFRPC;

	public class API_buy extends BaseAMFRPC {
		public function API_buy() {
			super();
			setService("buy");
		}
		public function init(doc_id:String='0'):void {
			var id:String = doc_id;
			if (Application.application.parameters['id'] != '') {
				id = Application.application.parameters['id'];
			}
			CursorManager.setBusyCursor();
			super.WebService.init(id);
		}
		public function save():void {
			var params:Object = getRequestParams();
			CursorManager.setBusyCursor();
			super.WebService.save(params);
		}
		
		public function makeOrder():void {
			var params:Object = getRequestParams();
			CursorManager.setBusyCursor();
			super.WebService.makeOrder(params);
		}

		public function suggest(field:String, value:String):void {
			CursorManager.setBusyCursor();
			super.WebService.suggest(field, value);
		}
	}
}