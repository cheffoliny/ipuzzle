package net.telepol.telenet {
	import mx.controls.DataGrid;
	
	public class customDataGrid extends DataGrid {
		override public function createItemEditor(colIndex:int, rowIndex:int):void {
			super.createItemEditor(colIndex, rowIndex);
			
			if (itemEditorInstance.height <= rowHeight)
				return;
			//var row:int = itemEditorInstance.y / rowHeight;
        	var gh:int = unscaledHeight - headerHeight;
        	var eh:int = 220;
        	var current_y:int = (rowIndex - verticalScrollPosition) * rowHeight;
        	
        	if (current_y + eh > gh)
				itemEditorInstance.move(itemEditorInstance.x, Math.max(current_y + rowHeight - eh, 0));
			else
				itemEditorInstance.move(itemEditorInstance.x, current_y);

			itemEditorInstance.setActualSize(itemEditorInstance.width + 1, eh);
		}
	}
}