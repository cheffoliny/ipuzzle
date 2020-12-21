package telenet.api {

	[RemoteClass(alias="FlexResponse")]
	[Bindable]
	public class FlexResponse {
		// array of FlexVar-s
		public var variables:Array;
		// array of FlexControl-s
		public var controls:Array;
		// array of Logs
		public var logs:Array;
		public var alerts:Array;
		public var error:Object;
		// hidden params (constants)
		public var hiddenParams:Object;
	}
}