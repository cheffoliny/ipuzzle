package net.telepol.telenet {
	[Bindable]
	public class InvoiceRow {
		public static const ADD_TASK:String = "+Добави ред+";
		
    	public function InvoiceRow(addRow:Boolean=false,
    			id:String='',
    			nomenclature:Object=null, firm_region:Object=null, direction:Object=null,
    			month:String='', sum:Number=0, payed:Boolean=false, row:int=1, note:String='') {
    		this.id = id;
    		this.row = row;
    		this.addRow = addRow;
    		if (addRow){
    			//this.nomenclature = {name: ADD_TASK};
    			//this.firm_region = {fcode:0, firm:'', rcode:0, region:''};
    			this.nomenclature = {name: ''};
    			this.firm_region = {fcode:0, firm: ADD_TASK, rcode:0, region:''};
    			this.direction = {id:0, name:''};
    		} else {
    			this.nomenclature = nomenclature;
    			this.firm_region = firm_region;
    			this.direction = direction;
    		}

    		if (month == null || month == '')
    			this.month = new Date();
    		else
    			this.month = getDate(month);
    		this.month["date"] = 1;
    		this.month["hours"] = 0;
    		this.month["minutes"] = 0;
    		this.month["seconds"] = 0;
    		this.month["milliseconds"] = 0;

			this.note = note;

      		this.sum = sum;
      		this.payed = payed;
      		if (payed)
      			this.for_payment = false;
      		else
      			this.for_payment = true;
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
    	public var addRow:Boolean;
    	public var nomenclature:Object;
    	public var firm_region:Object;
    	public var direction:Object;
    	public var month:Date;
		public var sum:Number;
		public var note:String;
		public var payed:Boolean;
		public var for_payment:Boolean;
  	}
}