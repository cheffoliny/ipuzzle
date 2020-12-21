package net.telepol.telenet {
	[Bindable]
	public class InvoiceRowSale {
		public function InvoiceRowSale() {
			
		}
		private function getDate(d:String):Date {
			return new Date(
				int(d.substr(0,4)), 	// year
				int(d.substr(5,2))-1,	// month
				int(d.substr(8,2))		// day
			);
		}
		
		public var row:int;
    	public var id:String;
    	public var month:Date;
    	public var obj:Object;
    	public var usluga:Object;
    	public var edcena:Number;
    	public var kolich:int;
    	public var medinica:String;
    	public var sum:Number;
    	public var for_payment:Boolean;
	}
}