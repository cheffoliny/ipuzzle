<?php

Class kmz {
	
	var $dom;
	var $aNodes;
	
	function __construct() 
	{
		$dom =& $this->dom;
		$aNodes =& $this->aNodes;
		
		$dom = new DOMDocument('1.0','UTF-8');
		
		$node = $dom->createElementNS('http://earth.google.com/kml/2.1','kml');
		$aNodes['parNode'] = $dom->appendChild($node);
		$doc = $dom->createElement('Document');
		$aNodes['parNode'] = $aNodes['parNode']->appendChild($doc);
	}

	

	public function addFolder( $sFName = NULL )
	{
		$dom =& $this->dom;
		$aNodes =& $this->aNodes;
		
		$fnode = $dom->createElement('Folder');
		$oFolderNode = $aNodes['parNode']->appendChild($fnode);	
		
		if( $sFName ) 
		{
			$fname = $dom->createElement('name', $sFName );
			$nameNode = $oFolderNode->appendChild($fname);
			
		}
		
		return $oFolderNode;
	}

	
	public function addStyle( $sStyleId, $aStyleParams = array( "PolyStyle" => array( 'color' => NULL ), "IconStyle" => array( 'href' => NULL, 'color' => NULL, 'color' => NULL ) ) )
	{
		$dom =& $this->dom;
		$aNodes =& $this->aNodes;
		
		//TODO: да се проверява дали съществува такъв Style, ако съществува да се ъпдейтне
		
		//<Style id="transYellowPoly">
		//<IconStyle>
		//        <Icon>
		//          <href>http://maps.google.com/mapfiles/kml/pal3/icon19.png</href>
		//        </Icon>
		//</IconStyle>
		//      <LineStyle>
		//        <width>1.5</width>
		//      </LineStyle>
		//      <PolyStyle>
		//        <color>7d00ffff</color>
		//      </PolyStyle>
		//</Style>
				
		$style = $dom->createElement("Style");
		$style->setAttribute('id', substr($sStyleId, 1) );
		$styleNode = $aNodes['parNode']->appendChild($style);
		
		if( isset($aStyleParams[ 'PolyStyle' ])  )
		{ 
			$polyStyle = $dom->createElement("PolyStyle");
			$polyStyleNode = $styleNode->appendChild($polyStyle);
			
			if( isset( $aStyleParams[ 'PolyStyle' ][ 'color' ] ) ) 
			{
				if( substr( $aStyleParams[ 'PolyStyle' ][ 'color' ], 0,1 ) == '#' ) 
				{ 
					$sColor = $this->rgb2bgr(substr($aStyleParams[ 'PolyStyle' ][ 'color' ],1) );
				} else { 
					$sColor = $this->rgb2bgr($aStyleParams[ 'PolyStyle' ][ 'color' ]);
				}	
				
				$polyColor = $dom->createElement( "color", '80'.$sColor );
				
				$polyStyleNode->appendChild( $polyColor );
				
				$polyOutline = $dom->createElement( 'outline', 0);
				$polyStyleNode->appendChild( $polyOutline );
			}
		}
		
		if( isset($aStyleParams[ 'IconStyle' ]['href'])  )
		{ 
			$iconStyle = $dom->createElement("IconStyle");
			$iconStyleNode = $styleNode->appendChild($iconStyle);
			
			if( isset( $aStyleParams[ 'IconStyle' ][ 'href' ] ) ) 
			{
				$icon = $dom->createElement( "Icon" );
				$iconNode = $iconStyleNode->appendChild( $icon );
				$href = $dom->createElement( "href", $aStyleParams[ 'IconStyle' ][ 'href' ]  );
				$iconNode->appendChild( $href );
			}
			
			if( isset( $aStyleParams[ 'IconStyle' ][ 'color' ] ) ) 
			{
				$color = $dom->createElement( "color", $aStyleParams[ 'IconStyle' ][ 'color' ]  );
				$iconStyleNode->appendChild( $color );
			}
			
			if( isset( $aStyleParams[ 'IconStyle' ][ 'scale' ] ) ) 
			{
				$scale = $dom->createElement( "scale", $aStyleParams[ 'IconStyle' ][ 'scale' ]  );
				$iconStyleNode->appendChild( $scale );
			}

		}
		
		return $sStyleId;
	}

	
	public function setStyle( $oNode, $sStyleName )
	{
		$dom =& $this->dom;
			
		$styleUrl = $dom->createElement('styleUrl', ''.$sStyleName );
		$styleUrlNode = $oNode->appendChild($styleUrl);
		
	}

	public function addPolygon( $oFolderNode, $sName, $aValue = array() )
	{
		$dom =& $this->dom;
 
		
//	kml output
//		<coordinates>	
//				26.9348949,		43.268769544,	100
//                			26.936171468,	43.268769544,	100
//                			26.936171468,	43.270046112,	100
//                			26.9348949,		43.270046112,	100
//                			26.9348949,		43.268769544,	100    
//		</coordinates>
//		
//		$aValue = array(
//					array('lon'=>'',  'lat'=>'', 'alt'=>''),
//					array('lon'=>'',  'lat'=>'', 'alt'=>''),
//					array('lon'=>'',  'lat'=>'', 'alt'=>'')
//					..................
//		)
		

		$sValue = '';
		
		foreach( $aValue as $Value)
		{
			$sValue .= implode( ',', $Value );
			$sValue .= '  ';
		}
		
		$node = $dom->createElement('Placemark');
		$placeNode = $oFolderNode->appendChild( $node );
		//$placeNode->setAttribute('id', $sName );
		if( isset( $sName ) )
		{
			$nameNode = $dom->createElement( 'name', $sName );
			$placeNode->appendChild($nameNode);
		}
		$poly = $dom->createElement( 'Polygon' );
		$polyNode = $placeNode->appendChild( $poly );
		$outBound = $dom->createElement( 'outerBoundaryIs' );
		$outBondNode = $polyNode->appendChild( $outBound );
		$LinearRing = $dom->createElement( 'LinearRing' );
		$LinearRingNode = $outBondNode->appendChild( $LinearRing );
		$coordinates = $dom->createElement( 'coordinates', $sValue );
		$coordinatesNode = $LinearRingNode->appendChild( $coordinates );
		
		return $placeNode;
				
	}
	
	
	public function addMarker( $oFolderNode, $sName = NULL, $sDesc = NULL, $aValue = array( 'lat' => NULL, 'lon' => NULL, 'alt' => NULL ) )
	{
		$dom =& $this->dom;

		//$aValue = array( 'lat' => '43.26', 'lon' => '26.90'  );
		
		$sValue = implode( ',', $aValue);
				
		$node = $dom->createElement('Placemark');
		$placeNode = $oFolderNode->appendChild($node);
		
		//$placeNode->setAttribute('id', $sName );
		if( isset( $sName ) )
		{
		$nameNode = $dom->createElement('name', $sName );
		$placeNode->appendChild($nameNode);
		}
		
		if( isset( $sDesc ) )
		{
		$descNode = $dom->createElement('description', $sDesc );
		$placeNode->appendChild($descNode);
		}
		
		
		$point = $dom->createElement( 'Point' );
		$pointNode = $placeNode->appendChild( $point );
		$coordinates = $dom->createElement('coordinates', $sValue );
		$coordinatesNode = $pointNode->appendChild($coordinates);
		
		return $placeNode;
	}
 
	
	public function saveKml( $sFileName )
	{
		$dom =& $this->dom;
		$dom->formatOutput = true;
		
		$dom->save( $sFileName ); 
	}
	

	public function saveKmz( $sFileName )
	{
		$dom =& $this->dom;
		$dom->formatOutput = true;
		$sTmpFileName = "./storage/tmp.kml";
		$dom->save( $sTmpFileName );

		$kmz = new ZipArchive();
		$kmz->open( $sFileName, ZIPARCHIVE::CREATE );
		$kmz->addFile( $sTmpFileName,'doc.kml' );
		$kmz->close();
		
		unlink( $sTmpFileName );
		
	}
	
	
	private function rgb2bgr( $sRgb )
	{
		if(substr($sRgb,0,1) == '#' ) 
		{ 
			$sRgb = substr( $sRgb, 1 );
		}
		
		$r = substr( $sRgb, 0, 2 );
		$g = substr( $sRgb, 2, 2 ); 
		$b = substr( $sRgb, 4, 2 );
		
		return $b.$g.$r;
	}

 
	
	
}

?>