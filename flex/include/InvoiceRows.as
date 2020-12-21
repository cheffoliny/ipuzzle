package {
	import mx.collections.ArrayCollection;
	
	[Bindable]
  	public class InvoiceRows extends ArrayCollection {
  		//private var _Total:Number = 0;
		[Bindable("collectionChange")]
        public function get Total():Number {
        	return getTotal();
        }
		[Bindable("collectionChange")]
        public function get checkedTotal():Number {
        	return getCheckedTotal(false);
        }
        [Bindable("collectionChange")]
        public function get uncheckedTotal():Number {
        	return getCheckedTotal(true);
        }

		//*
		override public function set source(s:Array):void {
			if (s == null) {
				super.source = s;
			} else {
				var newArr:Array = new Array(s.length);
				for (var i:int=0; i < newArr.length; i++){
					newArr[i] = new InvoiceRow(false, s[i].id, s[i].nomenclature, s[i].firm_region, s[i].direction,
					s[i].month, s[i].sum, s[i].payed, i+1);
				}
				super.source = newArr;
			}
		}
		[Inspectable(category="General", arrayType="Object")]
    	[Bindable("listChanged")] //superclass will fire this
		override public function get source():Array {
			return super.source;
		}
		//*/
  		/* public override function addItem(item:Object):void {
  			super.addItem(item);
  			addEventListener(CollectionEvent.COLLECTION_CHANGE, onCollectionChange);

  			this[this.length-1].row = this.length;
  		} */
  		/* public override function itemUpdated(item:Object, property:Object=null, oldValue:Object=null, newValue:Object=null):void {
  			super.itemUpdated(item, property, oldValue, newValue);
  			//_Total = getTotal();
  		} */
  		public override function addItemAt(item:Object, index:int):void {
  			super.addItemAt(item, index);
  			for(var i:int=index; i < this.length; i++){
  				this[i].row = i+1;
  			}
  			//_Total = getTotal();
  		}
  		public override function removeItemAt(index:int):Object {
  			var ret:Object = super.removeItemAt(index);
  			for(var i:int=0; i < this.length; i++){
  				this[i].row = i+1;
  			}
  			//_Total = getTotal();
  			return ret;
  		}
  		private function getTotal():Number {
  			var _Total:Number = 0;
  			for(var i:int=0; i < this.length; i++){
  				_Total += this[i].sum;
  			}
  			return _Total;
  		}
  		private function getCheckedTotal(payed:Boolean):Number {
  			var _Total:Number = 0;
  			if (payed){
  				for(var i1:int=0; i1 < this.length; i1++){
  					if ((this[i1] as InvoiceRow).payed == payed)
	  					_Total += this[i1].sum;
  				}
  			} else {
	  			for(var i:int=0; i < this.length; i++){
  					if ((this[i] as InvoiceRow).payed == payed && (this[i] as InvoiceRow).for_payment)
	  					_Total += this[i].sum;
  				}
  			}
  			return _Total;
  		}
  		/* private function onCollectionChange(evt:CollectionEvent):void {	
  		} */
  	}
}