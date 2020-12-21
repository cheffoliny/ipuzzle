package
{
	import flash.display.DisplayObject;
	import flash.events.MouseEvent;
	import flash.text.TextFormat;
	import flash.text.TextLineMetrics;
	
	import mx.controls.AdvancedDataGrid;
	import mx.controls.advancedDataGridClasses.AdvancedDataGridColumn;
	import mx.controls.advancedDataGridClasses.AdvancedDataGridItemRenderer;
	import mx.controls.dataGridClasses.DataGridItemRenderer;
	import mx.controls.listClasses.IDropInListItemRenderer;
	import mx.core.UIComponent;
	import mx.core.UITextField;

	public class AutoResizableADG extends AdvancedDataGrid
	{
		public function AutoResizableADG()
		{
			// call super
			super();
		}
		
		/**
	     *  Returns the header separators between column headers, 
	     *  and populates the <code>separators</code> Array with the separators returned.
	     * 
	     *  @param i The number of separators to return.
	     *
	     *  @param seperators Array to be populated with the header objects.
	     *
	     *  @param headerLines The parent component of the header separators. 
	     *  Flex calls the <code>headerLines.getChild()</code> method internally to return the separators.
	     */
		override protected function getSeparator(i:int, seperators:Array, headerLines:UIComponent):UIComponent
	    {
	        var sep:UIComponent = super.getSeparator(i, seperators, headerLines);
	        sep.doubleClickEnabled = true;
            // Add listener for Double Click
            DisplayObject(sep).addEventListener(
                MouseEvent.DOUBLE_CLICK, columnResizeDoubleClickHandler);
	        return sep;
	    }
	    
	/**
     *  @private
     *  Indicates where the right side of a resized column appears.
     */
    private function columnResizeDoubleClickHandler(event:MouseEvent):void
    {
    	// check if the ADG is enabled and the columns are resizable
        if (!enabled || !resizableColumns)
            return;
        
        var target:DisplayObject = DisplayObject(event.target);
        var index:int = target.parent.getChildIndex(target);
        // get the columns array
        var optimumColumns:Array = getOptimumColumns();
        
        // check for resizable column
        if (!optimumColumns[index].resizable)
            return;
        
        // calculate the maxWidth - we can optimize this calculation
        if(listItems)
        {
        	var len:int = listItems.length;
        	var maxWidth:int = 0;
        	for(var i:int=0;i<len;i++)
        	{
        		if(listItems[i][index] is IDropInListItemRenderer)
        		{
        			var lineMetrics:TextLineMetrics = measureText(IDropInListItemRenderer(listItems[i][index]).listData.label);
        			if(lineMetrics.width > maxWidth)
    					maxWidth = lineMetrics.width ;
        		}
        	}
        }
        
        // set the column's width
        optimumColumns[index].width = maxWidth + getStyle("paddingLeft") + 
                            getStyle("paddingRight") + 8;
    }
	
	public function optimizeDataGrid(widthPadding:uint = 0,heightPadding:uint = 0):void {
		var col:uint;
		var tf:TextFormat;
		var renderer:UITextField;
		var widths:Array = new Array(this.columnCount);
//		var height:uint = 0;
		var dgCol:Object;
		 
		if ((this.columnCount > 0) && (this.dataProvider != null)) {
			for (col = 0; col < this.columnCount; ++col)
				widths[col] = -1;
			for each (var item:Object in this.dataProvider.source.source) {
				for (col = 0; col < this.columnCount; ++col) {
					renderer = new AdvancedDataGridItemRenderer();
					this.addChild(renderer);
					dgCol = this.columns[col];
					renderer.text = dgCol.itemToLabel(item);
					widths[col] = Math.max(renderer.measuredWidth, widths[col]);
//					height = Math.max(renderer.measuredHeight, height);
					this.removeChild(renderer);
				}
			}
			for (col = 0; col < this.columnCount; ++col){
				// Added to take into account header text
				renderer = new DataGridItemRenderer();
				this.addChild(renderer);
				renderer.text = (this.columns[col] as AdvancedDataGridColumn).headerText;
				widths[col] = Math.max(renderer.measuredWidth,widths[col]);
				this.removeChild(renderer);
				if (widths[col] != -1)
					this.columns[col].width = widths[col] + widthPadding;
			}
//			if (height != 0)
//				this.rowHeight = height + heightPadding;
		}
	}
		
	}
	
}