package {
	import mx.controls.Label;
	import mx.controls.dataGridClasses.*;

	public class OrderRenderer extends Label {
		override public function set data(value:Object):void {
			if(value != null) {
	  			super.data = value;

	  			if (value.order_status == 'canceled') {
			 		setStyle("color", 0xFF0000);
	 			} else if (value.order_status == 'opposite') {
	 				setStyle("color", 0x0000FF);
	 			} else {
		 			setStyle("color", 0x000000);
	 			}
	  		}
		}
	}

}