<?php
				
		class APISetAttribute
		{
			public function result(DBResponse $oResponse)
			{
				
				$aTypes = array('text'=>'текст','number'=>'число','list'=>'списък');
				$nID = Params::get('nID',0);
				if(empty($nID)){
					
					$oMeasures = new DBMeasures();
					$aMeasures=  $oMeasures->getMeasures();
					$oResponse->setFormElement('form1','id_measure');
					$oResponse->setFormElementChild("form1","id_measure",array(),"-----Изберете-----");
					
					for($i=0;$i<count($aMeasures);$i++)
					{
						$oResponse->setFormElementChild("form1","id_measure",array("value"=>$aMeasures[$i]['id']),$aMeasures[$i]['description']);
					}
					
					APILog::Log(0,$aMeasures);
					 $oResponse->setFormElement("form1","type");
				
					$oResponse->setFormElementChild("form1","type",array("value"=>0),"---Изберете---");
					foreach ($aTypes as $k=>$v)
					{
						$oResponse->setFormElementChild("form1","type",array("name"=>$k, "id"=>$k, "value"=>$k),$v);
					}
					$oResponse->printResponse();
					return;
				}
				$aData= array();
				$aTypes = array('text'=>'текст','number'=>'число','list'=>'списък');
				
				
				$oMeasures = new DBMeasures();
				$aMeasures =$oMeasures->getAll();
				$oAtribute = new DBAttributes();
				$oAtribute->setAttributeValues($oResponse,$nID, $aData);
				APILog::Log(0,$aData);
				$oResponse->setFormElement("form1","name", NULL,$aData[0]['name']);
				
				$oResponse->setFormElement("form1","type");
				$oResponse->setFormElementChild('form1','type',array(),'---Изберете---');
				foreach ($aTypes as $k=>$v)
				{
					if($aData[0]['type_value']==$k)
					{
						$oResponse->setFormElementChild("form1","type",array("name"=>$k, "id"=>$k, "value"=>$k, "selected"=>"selected"),$v);
					}
					
					else	$oResponse->setFormElementChild("form1","type",array("name"=>$k, "id"=>$k, "value"=>$k),$v);
					
				}
				
					switch($aData[0]['type_value'])
					{
						case 'number':
						$aValues= explode(',',$aData[0]['type_values']);
							$sTypeValues = '<table class="input"   id="number">
												<tr class="even">
													<td align="right">
														Диапазон:
													</td>
													<td>
														&nbsp;&nbsp;&nbsp;от:&nbsp;<input type="text" style="width:45px;" name="from" value="'.$aValues[0].'">&nbsp;&nbsp;&nbsp;&nbsp; до:<input type="text" style="width:45px;" name="to" value="'.$aValues[1].'">
													</td>
												</tr>
											</table>';
							break;
						case 'text':
								$sTypeValues='<table class="input"  width="100%"  id="text">
												<tr class="even" >
													<td align="right" style="margin-left:60px;">
														&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Описание:
													</td>
													<td>
														<input type="text" class="large " name="text_value" value="'.$aData[0]['type_values'].'" >
													</td>
												</tr>
											</table>';
							break;
						case 'list':
								$sTypeValues='<table class="input"  width="100%"  id="list">
													<tr class="even">
														<td  align="right" style="margin-left:30px;">
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Елементи :
														</td>
														<td >
															<input type="text" id="type_values" name="list_values" class="large" value="'.$aData[0]['type_values'].'"/>
														</td>
													</tr>
												</table>';
							break;
					}
				$oResponse->setFormElement('form1','visibility',array('innerHTML'=>$sTypeValues),null);	
				if($aData[0]['type_value']=="text" || !$aData[0]['type_value']){
				$oResponse->setFormElement("form1","type_values",array("disabled"=>"disabled"),$aData[0]['type_values']);
				}
				else $oResponse->setFormElement("form1","type_values",NULL,$aData[0]['type_values']);
				
				
				if($aData[0]['is_required'])
					$oResponse->setFormElement("form1","is_required",array("checked"=>"checked"));
					
				if($aData[0]['type_value']!="number"){
					
					$oResponse->setFormElement("form1","id_measure",array("disabled"=>"disabled"));
				}
				else $oResponse->setFormElement("form1","id_measure");
				
				$oResponse->setFormElementChild("form1","id_measure",array("value"=>0),"---Изберете---");
				
				foreach ($aMeasures as $k=>$v)
				{
					if ($aData[0]['id_measure']==$v['id']) {
						$oResponse->setFormElementChild("form1","id_measure",array("value"=>$v['id'],"selected"=>"selected"),$v['description']);
					}
					else$oResponse->setFormElementChild("form1","id_measure",array("value"=>$v['id']),$v['description']);
				}
				
				
				if(isset($aValues)){
					$sInsertedValues = $aData[0]["type_value"].'&'.$aValues[0].'&'.$aValues[1];
					$sInsertedValues = htmlspecialchars($sInsertedValues);
				}
				else{
					$sInsertedValues = $aData[0]['type_value'].'&'.$aData[0]['type_values'];
					$sInsertedValues = htmlspecialchars($sInsertedValues);
				}
				
				$oResponse->setFormElement('form1','inserted_values',array('value'=>$sInsertedValues),$sInsertedValues);
				$oResponse->printResponse();
				
			}
			public function setValuesByType(DBResponse $oResponse)
			{
				$aParams = Params::getAll();
				if(!empty($aParams['nID'])){
					$aValues = explode('&amp;',$aParams['inserted_values']);
					APILog::Log(0,$aValues);
					if($aParams['type']==$aValues[0])
					{
						switch($aParams['type'])
						{
							case 'number':$sFields = '<table class="input"   id="number">
												<tr class="even">
													<td align="right">
														Диапазон:
													</td>
													<td>
														&nbsp;&nbsp;&nbsp;от:&nbsp;<input type="text" style="width:45px;" name="from" value="'.$aValues[1].'">&nbsp;&nbsp;&nbsp;&nbsp; до:<input type="text" style="width:45px;" name="to" value="'.$aValues[2].'">
													</td>
												</tr>
											</table>';
							break;
							case 'list' :
								$sFields = '<table class="input"  width="100%"  id="list">
													<tr class="even">
														<td  align="right" style="margin-left:30px;">
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Елементи :
														</td>
														<td >
															<input type="text" id="type_values" name="list_values" class="large" value="'.$aValues[1].'"/>
														</td>
													</tr>
												</table>';
							break;
							case 'text':
								$sFields='<table class="input"  width="100%"  id="text">
												<tr class="even" >
													<td align="right" style="margin-left:60px;">
														&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Описание:
													</td>
													<td>
														<input type="text" class="large " name="text_value" value="'.$aValues[1].'" >
													</td>
												</tr>
											</table>';
							break;
						}
					}
					else{
							switch($aParams['type'])
						{
							case 'number':$sFields = '<table class="input"   id="number">
												<tr class="even">
													<td align="right">
														Диапазон:
													</td>
													<td>
														&nbsp;&nbsp;&nbsp;от:&nbsp;<input type="text" style="width:45px;" name="from" value="">&nbsp;&nbsp;&nbsp;&nbsp; до:<input type="text" style="width:45px;" name="to" value="">
													</td>
												</tr>
											</table>';
							break;
							case 'list' :
								$sFields = '<table class="input"  width="100%"  id="list">
													<tr class="even">
														<td  align="right" style="margin-left:30px;">
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Елементи :
														</td>
														<td >
															<input type="text" id="type_values" name="list_values" class="large" value=""/>
														</td>
													</tr>
												</table>';
							break;
							case 'text':
								$sFields='<table class="input"  width="100%"  id="text">
												<tr class="even" >
													<td align="right" style="margin-left:60px;">
														&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Описание:
													</td>
													<td>
														<input type="text" class="large " name="text_value" value="" >
													</td>
												</tr>
											</table>';
							break;
						}
					}
					
				}
				else {
					switch($aParams['type'])
						{
							case 'number':$sFields = '<table class="input"   id="number">
												<tr class="even">
													<td align="right">
														Диапазон:
													</td>
													<td>
														&nbsp;&nbsp;&nbsp;от:&nbsp;<input type="text" style="width:25px;" name="from" value="">&nbsp;&nbsp;&nbsp;&nbsp; до:<input type="text" style="width:25px;" name="to" value="">
													</td>
												</tr>
											</table>';
							break;
							case 'list' :
								$sFields = '<table class="input"  width="100%"  id="list">
													<tr class="even">
														<td  align="right" style="margin-left:30px;">
															&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Елементи :
														</td>
														<td >
															<input type="text" id="type_values" name="list_values" class="large" value=""/>
														</td>
													</tr>
												</table>';
							break;
							case 'text':
								$sFields='<table class="input"  width="100%"  id="text">
												<tr class="even" >
													<td align="right" style="margin-left:60px;">
														&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Описание:
													</td>
													<td>
														<input type="text" class="large " name="text_value" value="" >
													</td>
												</tr>
											</table>';
							break;
						}
				}
				$oResponse->setFormElement('form1','visibility',array('innerHTML'=>$sFields),null);
				$oResponse->printResponse();
			}
			                                                                                                                                  
			public function save ( DBResponse $oResponse )
			{	
				
				$aData = array();
				$aData =Params::getAll();
				foreach ($aData as $k=>$v)
				{
					$v=trim($v);
				}
				if(empty($aData['name']))
				{
					throw new Exception('Не сте въвели име на атрибута!');
					
				}
				
				if($aData['type']=="list" && empty($aData['list_values']))
				{
					throw new Exception("Не сте въвели елементи на атрибута!");
					
				}
				if($aData['type']=="number")
				{
					if($aData['from'] == '' && !empty($aData['to']))
						throw new Exception("Въведете стойност 'от' на диапазона !");
					if($aData['to'] == '' && !empty($aData['from']))
						throw new Exception("Въведете стойност 'до' на диапазона !");
					
					if((!empty($aData['from']) && !empty($aData['to'])) && ($aData['to'] <= $aData['from']))
						throw new Exception(" Въведете коректни стойности на диапазона ! ");
					if( (!empty($aData['from']) && !empty($aData['to'])) &&(!is_numeric($aData['to']) || !is_numeric($aData['from'])))
						throw new Exception("  Въведете коректни стойности на диапазона ! ");
				}
				
			$aValues['id']             = empty($aData['nID'])? 0 : $aData['nID'];
			$aValues['id_measure']     = $aData["id_measure"];
			$aValues['name']           = $aData["name"];
			$aValues['type']           = $aData["type"];
			$aValues['is_require']     = $aData['is_required'];
			
			if($aData['type'] == 'number')
			{
				$aType_values = array($aData['from'],$aData['to']);
					$aValues['type_values'] = implode(',',$aType_values);
				
//				if(!empty($aData['from']) && !empty($aData['to']) )
//				{
//					$aType_values = array($aData['from'],$aData['to']);
//					$aValues['type_values'] = implode(',',$aType_values);
//				}
//				else if(empty($aData['from']) && !empty($aData['to']))
//				{
//					
//				} 
			}
			if($aData['type']=='list')
				$aValues['type_values'] = $aData["list_values"];
			if($aData['type']=='text')
				$aValues['type_values'] = $aData["text_value"];
			$aValues['is_required'] = $aData["is_required"];
				$oAttribute = new DBAttributes();
				$oAttribute->updateAttributeValues( $aValues);
				
				
				$oResponse->printResponse();
			}
		}

?>