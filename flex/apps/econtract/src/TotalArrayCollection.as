package {
	import mx.collections.ArrayCollection;
	
	[Bindable]
	public class TotalArrayCollection extends ArrayCollection {
		
		private var _Total:Number = 0;
		private var _field:String = 'sum';
		
		public function TotalArrayCollection(field:String, src:Array = null) {
			this._field = field;
			this.source = src;
		}

		[Bindable("collectionChange")]
        public function get Total():Number {
        	return _Total;
        }

		override public function set source(s:Array):void {
			super.source = s;
			if (super.source)
				_Total = getTotal();
		}
		
		public override function addItemAt(item:Object, index:int):void {
			_Total += item[_field];
  			super.addItemAt(item, index);
  			//_Total = getTotal();
  		}
  		public override function removeItemAt(index:int):Object {
  			if (super.length > 1)
  				_Total -= super.getItemAt(index)[_field];
  			else
  				_Total = 0;
  			var ret:Object = super.removeItemAt(index);
  			//_Total = getTotal();
  			return ret;
  		}
  		
		private function getTotal():Number {
  			var _Total:Number = 0;
  			for(var i:int=0; i < this.length; i++){
  				_Total += this[i][_field];
  			}
  			return _Total;
  		}
	}
}