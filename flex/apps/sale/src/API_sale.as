package {
	import mx.core.Application;
	import mx.managers.CursorManager;
	
	import telenet.api.BaseAMFRPC;

	public class API_sale extends BaseAMFRPC {
		public function API_sale() {
			super();
			setService("sale");
		}
		public function init(doc_id:String='0'):void {
			var id:String = doc_id;
			if (Application.application.parameters['id'] != '') {
				id = Application.application.parameters['id'];
			}
			CursorManager.setBusyCursor();
			super.WebService.init(id);
		}
	}
}