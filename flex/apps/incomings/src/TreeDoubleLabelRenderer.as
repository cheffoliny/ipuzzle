package {
import mx.controls.Label;
import mx.controls.treeClasses.TreeItemRenderer;
import mx.controls.treeClasses.TreeListData;

public class TreeDoubleLabelRenderer extends TreeItemRenderer {
	protected var lSum:Label;
	
	override protected function createChildren():void {
		super.createChildren();
		
		lSum = new Label();
		lSum.setStyle("textAlign", 'right');
		lSum.width = 100;
		lSum.height = 20;

		addChild(lSum);
	}
	
	override protected function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void {
		super.updateDisplayList(unscaledWidth, unscaledHeight);
		if (data) {
			label.text = data.label;
			lSum.text = data.sum;
			
			label.width = label.width - lSum.width;
			lSum.x = label.x + label.width;
			lSum.y = label.y;
			
			if(TreeListData(super.listData).hasChildren) {
				lSum.setStyle("color", 0xFF0000);
			} else {
				lSum.setStyle("color", 0x000000);
			}
			//label.background = true;
        	//label.backgroundColor = 0xEEEEEE;
		}
	}
	
	override public function get data():Object {
		return super.data;
	}

	override public function set data(value:Object):void {
		if(value) {
			super.data = value;
		}
	}
}
}