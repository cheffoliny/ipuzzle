package {
	import flash.display.Graphics;
	import mx.controls.advancedDataGridClasses.AdvancedDataGridGroupItemRenderer;

	public class TreeItemWithTotals extends AdvancedDataGridGroupItemRenderer {
		public function TreeItemWithTotals() {
			super();
		}
		
		override protected function updateDisplayList(unscaledWidth:Number, unscaledHeight:Number):void {
			super.updateDisplayList(unscaledWidth, unscaledHeight);

			if (data) {
				if (data.hasOwnProperty('is_total') && (data.is_total == 1)){
					icon.visible = false;
					label.x = 0;
					var g:Graphics = graphics;
					g.clear();
					g.beginFill(0x92ffbb);
					g.drawRect(0, -2, unscaledWidth, unscaledHeight+2);
					g.endFill();
				} else {
					icon.visible = true;
					var g1:Graphics = graphics;
					g1.clear();
				}
			}
		}

	}
}