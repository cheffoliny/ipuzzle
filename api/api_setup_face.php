<?php
	
	class ApiSetupFace
	{
		function load(DBResponse $oResponse)
		{
			$nID = Params::get('nID');
			$id_obj = Params::get('id_obj','');
			
			if ( !empty($nID)) {
				$oDBFaces = new DBFaces();
				$oObjects = new DBObjects();
				
				$aFace = $oDBFaces->getFace($nID);
				$nIDFace = $oObjects->getIdFace($id_obj);
				
				$oResponse->setFormElement('form1','sName',array(),$aFace['name']);
				$oResponse->setFormElement('form1','sPhone',array(),$aFace['phone']);
				$oResponse->setFormElement('form1','sPost',array(),$aFace['post']);
				$oResponse->setFormElement('form1','id_face',array(),$nIDFace);
				
				if( $nID == $nIDFace ) {
					$oResponse->setFormElement('form1','isMOL',array('checked' => 'checked'));
				}
			} 
			
			$oResponse->printResponse();
		}
		function save()
		{
			$nID = Params::get('nID',0);
			$sName = Params::get('sName','');
			$sPhone = Params::get('sPhone','');
			$sPost = Params::get('sPost','');
			$id_obj = Params::get('id_obj','');
			$nIDFace = Params::get('id_face','0');
			$isMOL = Params::get('isMOL','0');
			
			
			$oDBFaces = new DBFaces();
			$oObjects = new DBObjects();
			
			
			$aData = array();
			$aData['id'] = $nID;
			$aData['id_obj'] = $id_obj;
			$aData['name'] = $sName;
			$aData['phone'] = $sPhone;
			$aData['post'] = $sPost;
			$oDBFaces->update($aData);
			
			$nID = $aData['id'];
			
			if( $nID != $nIDFace &&  !empty($isMOL)) {
				$oObjects->setIdFace($nID,$id_obj);
			} 
			
			if( $nID == $nIDFace && empty($isMOL)) {
				$oObjects->setIdFace('0',$id_obj);
			}
			
		}
	}
	
?>