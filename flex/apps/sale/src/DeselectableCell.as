package {
import mx.controls.Label;
import mx.controls.dataGridClasses.*;

public class DeselectableCell extends Label {

	override public function set data(value:Object):void {
		if(value != null) {
	  		super.data = value;
			if(value.for_payment || (value.id > 0) || (value.addRow)) {
				setStyle("color", 0x000000);
				setStyle("fontSize", 10);
			} else {
				setStyle("color", 0xb7babc);
				setStyle("fontSize", 9);
			}
		}
	}
}

}