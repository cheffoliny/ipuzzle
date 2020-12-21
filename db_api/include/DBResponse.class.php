<?php
	require_once('db_include.inc.php');
	require_once("include/export_to_excel/export_to_excel.php");
	require_once("pdf/pdf_general_report.php");
	require_once("pdf/pdf_salary_total.php");
	require_once("pdf/pdf_object_archiv.php");
	require_once("pdf/pdf_missing_documents.php");
	require_once("pdf/pdf_sale_doc.php");
    require_once("pdf/pdf_invoice.php");
	
	/**
	*	Клас предназначен да съхранява данните непосредствено преди трансформирането им в XML, XLS и т.н.
	*
	*	@name DBResponse
	*	@author dido2k (Диян Гочев)
	*	@category DBAPI
	*/
	
	class DBResponse
	{
		/**
		*	DBLog Съдържа debug информация от ADODB
		*	@filesource dbapi/include/DBLog.class.php 
		*/
		var $oLog = NULL;
		
		/**
		*	DBDebug Съдържа debug информация която ние сме сложили с функция setDebug()
		*	@filesource dbapi/include/DBDebug.class.php 
		*/
		var $oDebug	= NULL;
		
		/**
		*	DBError Съдържа информация за статуса на изпълнението на операцията
		*	@filesource dbapi/include/DBError.class.php 
		*/
		var $oError	= NULL;	
		
		/**
		*	DBResult съдържа информация от извлечена от База Данни (данни, пейджинг, сортиране, и т.н)
		*	@filesource dbapi/include/DBResult.class.php 
		*/
		var $oResult = NULL;	
		
		/**
		*	DBAction съдържа информация за попълване на клиентската форма с информация
		*	@filesource dbapi/include/DBAction.class.php 
		*/
		var $oAction = NULL;

		var $bPrint = true;
		
		// flex bla bla
		private $aFlexVars 		= array();
		private $aFlexControls 	= array();
		private $oHiddenParams 	= array();		
		
		
		/**
		*	Стандартен конструктор за класа. Инициализира член-променливите си.
		*	
		*	@name function __construct()
		*	@access public
		*/
		function __construct()
		{
			$this->oLog		= new DBLog();
			$this->oDebug	= new DBDebug();
			$this->oError	= new DBError();
			$this->oResult	= new DBResult();
			$this->oAction	= new DBAction();
		}
		
		/**
		*	Сетва debug информация за извеждане в debug прозореца
		*
		*	@name function setDebug()
		*	@access public
		*	@param string sInfo дебъг информация която ще се покаже в debug прозореца.
		*	@param string sFile файла в който се сетва информацията (използвайте константата __FILE__)
		*	@param int nLine линията от файла в който се сетва информацията (използвайте константата __LINE__)
		*	@return void
		*/
		function setDebug( $sInfo, $sFile = NULL, $nLine = NULL )
		{
			if( is_array( $sInfo ) || is_object( $sInfo ) )
				$sInfo = print_r( $sInfo, true );
				
			if( !empty( $sInfo ) )
				array_push($this->oDebug->aDebugElements, new DBDebugElement($sInfo, $sFile, $nLine));
		}
		
		/**
		*	Присвоява статус на изпълнение на операцията
		*
		*	@name function setError()
		*	@access public
		*	@param int nCode номер на грешка (DBAPI_ERR..)
		*	@param string sMsg Съобщение за грешка, ако не бъде подадено генерира по подразбиране спред първия параметър
		*	@return void
		*/
		function setError($nCode = DBAPI_ERR_SUCCESS, $sMsg = NULL, $sFile = NULL, $nLine = NULL)
		{
			$this->oError->setError($nCode, $sMsg, $sFile, $nLine);
		}
		
		/**
		*	Присвоява статус на изпълнение на операцията
		*
		*	@name function getErrorMessage()
		*	@access public
		*	@return string sMsg Съобщение за грешка, ако не бъде подадено генерира по подразбиране спред първия параметър
		*/
		function getErrorMessage()
		{
			return $this->oError->sMsg;
		}
		
		/**
		*	Инициализира правата за достъп
		*
		*	@name function setAccessLevels()
		*	@access public
		*	@param array aAccessLevels масив то стрингове - нива на достъп
		*	@return void
		*/
		function setAccessLevels( &$aAccessLevels )
		{
			assert( is_array( $aAccessLevels ) );
			
			$this->oResult->aAccessLevels = $aAccessLevels;
		}
		
		
		/**
		*	Инициализира поле от помощен (преди основния) хедър на таблица на резултат от справка
		*	
		*	@name function setTitle()
		*	@access public
		*	@param int nRow номер на ред
		*	@param int nCol номер на колона
		*	@param string sCaption заглавие на помощния ред
		*	@param array aAttributes асоциативен масив с атрубути които ще се сетнат на <th>-то
		*	@return void
		*/
		
		function setTitle($nRow, $nCol, $sCaption, $aAttributes = array())
		{
			$oTitle = new DBTitle();
			$oTitle->sCaption = $sCaption;
			
			if( is_array( $aAttributes ) )
				$oTitle->aAttributes = $aAttributes;
			
			$this->oResult->aTitles[ $nRow ][ $nCol ] = $oTitle;
		}
		
		/**
		*	Инициализира поле от хедъра на таблица на резултат от справка
		*	
		*	@name function setField()
		*	@access public
		*	@param string sField име на полето на латиница, примерно: 'updated_user'
		*	@param string sCaption съдържа стринга който ще се изведе, примерно: "Последно Редактирал"
		*	@param string sTitle ще попълни атрубута title на тага <th> в таблицата
		*	@param string sImg url на картинката, която искате да се изведе в хедъра
		*	@param string sLink име на JS функция която ще се извика при "кликване" на клетка от колоната на хедъра
		*	@param string sBtn текст на бутон, ако колоната на това поле ще съдържа бутони
		*	@param array aAttributes асоциативен масив с атрубути които ще се сетнат на <th>-то
		*	@return void
		*/
		function setField($sField, $sCaption = NULL, $sTitle = NULL, $sImg = NULL, $sLink = NULL, $sBtn = NULL, $aAttributes = array())
		{
			if( !array_key_exists($sField, $this->oResult->aFields) )
				$this->oResult->aFields[ $sField ] = new DBField();
				
			if( !is_null( $sCaption ) )		$this->oResult->aFields[ $sField ]->sCaption	= $sCaption;
			if( !is_null( $sTitle	) )		$this->oResult->aFields[ $sField ]->sTitle		= $sTitle;
			elseif( !is_null( $sCaption ) )	$this->oResult->aFields[ $sField ]->sTitle		= $sCaption;
			if( !is_null( $sImg		) )		$this->oResult->aFields[ $sField ]->sImg		= $sImg;	//URL
			if( !is_null( $sLink	) )		$this->oResult->aFields[ $sField ]->sLink		= $sLink;
			if( !is_null( $sBtn		) )		$this->oResult->aFields[ $sField ]->sBtn		= $sBtn;
			
			if( is_array( $aAttributes ) )
			{
				foreach($aAttributes as $key => $value)
					$this->oResult->aFields[ $sField ]->aAttributes[ $key ] = $value;
			}
		}

		/**
		*	Поставя текста който ще се изведе на съответния хедър
		*	забележка: полето вече трябва да е инициализирано с функция setField
		*
		*	@name function setFieldCaption()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param string sCatpion Текст който ще се изведе
		*	@return void
		*/
		function setFieldCaption($sField, $sCaption)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
				$this->oResult->aFields[ $sField ]->sCaption = $sCaption;
		}
		
		/**
		*	Поставя атрибута 'title' на елемента <th> в таблицата
		*	забележка: полето вече трябва да е инициализирано с функция setField
		*
		*	@name function setFieldTitle()
		*	@access public
		*	@param string sField име на полето, пример: 'udpated_user'
		*	@param string sTitle Текст който ще се изведе като title на <th> -то
		*	@return void
		*/
		function setFieldTitle($sField, $sTitle)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
				$this->oResult->aFields[ $sField ]->sTitle = $sTitle;
		}
		
		/**
		*	Поставя url на картинка, която да се изведе в хедъра
		*	забележка: полето вече трябва да е инициализирано с функция setField
		*
		*	@name function setFieldImg()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param string sImg URL на картинката
		*	@return void
		*/
		function setFieldImg($sField, $sImg)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
				$this->oResult->aFields[ $sField ]->sImg = $sImg;
		}
		
		/**
		*	Функция за указване на името на javascript функция която ще се извика при "кликване" в клетка в
		*	колоната на полето, на функцията ще се подаде като параметър id-то на реда
		*	забележка: полето вече трябва да е инициализирано с функция setField
		*
		*	@name function setFieldLink()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param string sLink името на javascript функцията която ще се извика
		*	@return void
		*/
		function setFieldLink($sField, $sLink)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
				$this->oResult->aFields[ $sField ]->sLink = $sLink;
		}
		
		/**
		*	Функцията указва че поле ще съдържа бутони и задава текст на бутона
		*
		*	@name function setFieldBtn()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param string sBtn текста който ще се изпише на бутоните от колоната
		*	@return void
		*/
		function setFieldBtn($sField, $sBtn)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
				$this->oResult->aFields[ $sField ]->sBtn = $sBtn;
		}
		
		/**
		*	Функцията поставя атрибути на <th> -то от хедъра на справката
		*
		*	@name function setFieldAttributes()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param array aAttributes асоциативен масив с атрибутите които трябва да бъдат поставени в хедъра на полето (<th>-то) от таблицата
		*	@return void
		*/
		function setFieldAttributes($sField, $aAttributes)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
			{
				if( is_array( $aAttributes ) )
				{
					foreach($aAttributes as $key => $value)
						$this->oResult->aFields[ $sField ]->aAttributes[ $key ] = $value;
				}
			}
		}
		
		/**
		*	Функцията прави дефиниция на клетките в даден филд т.е. определя типа им
		*
		*	@name function setFieldData()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param string sTagName име на тага
		*	@param array aAttributes асоциативен масив с атрибутите които трябва да бъдат поставени в хедъра на полето (<th>-то) от таблицата
		*	@param mixed mValue value на нода
		*	@return int Връща индекс в масива където е прибавен елемента, така че по късно чрез този индекс да може да се добави child
		*/
		function setFieldData($sField, $sTagName, $aAttributes = NULL, $mValue = NULL)
		{
			if( !array_key_exists($sField, $this->oResult->aFields) )
				return -1;
	
			$oDataElement = new DBFormElement();
			$oDataElement->sTagName = $sTagName;
			$oDataElement->mValue	= $mValue;
			
			if( is_array( $aAttributes ) )
			{
				foreach($aAttributes as $key => $value)
					$oDataElement->aAttributes[ $key ] = $value;
			}
			
			$nIndex = array_push($this->oResult->aFields[ $sField ]->aDataElements, $oDataElement);
			
			return ( --$nIndex );
		}
		
		/**
		*	Функцията прави дефиниция на клетките в даден филд т.е. определя типа им
		*
		*	@name function setFieldDataChild()
		*	@access public
		*	@param string sField име на полето, пример: 'updated_user'
		*	@param int index на парент елемент върнат от setFieldData
		*	@param string sTagName име на тага
		*	@param array aAttributes асоциативен масив с атрибутите които трябва да бъдат поставени в хедъра на полето (<th>-то) от таблицата
		*	@param mixed mValue value на нода
		*	@return void
		*/
		function setFieldDataChild($sField, $nParent, $sTagName, $aAttributes = NULL, $mValue)
		{
			if( array_key_exists($sField, $this->oResult->aFields) )
			{
				$oDataElement = new DBFormElement();
				$oDataElement->sTagName = $sTagName;
				$oDataElement->mValue	= $mValue;
				
				if( is_array( $aAttributes ) )
				{
					foreach($aAttributes as $key => $value)
						$oDataElement->aAttributes[ $key ] = $value;
				}
				
				if( array_key_exists($nParent, $this->oResult->aFields[ $sField ]->aDataElements) )
					$this->oResult->aFields[ $sField ]->aDataElements[ $nParent ]->aChilds[] = $oDataElement;
			}
		}
		
		/**
		*	Поставя двумерен масив с данни за изобразяване в таблица с резултат от справката
		*
		*	@name function setData()
		*	@access public
		*	@param array aData двумерен масив с данните към справката
		*	@return void
		*/
		function setData( $aData )
		{	
			assert( is_array( $aData ) );

			$this->oResult->aData = $aData;
		}
		
		/**
		*	Поставя поле за тотал в справката под формата на име-стойност
		*
		*	@name function addTotal()
		*	@access public
		*	@param string sName име на тотала
		*	@param int nValue стойност на тотала
		*	@return void
		*/
		function addTotal($sName, $nValue)
		{
			$this->oResult->aTotal[ $sName ] = $nValue;
		}
		
		/**
		*	Инициализиране на стойности за изобразяване на пейджинг
		*
		*	@name function setPaging()
		*	@access public
		*	@param int nRowLimit броя на редове на текущата страница
		*	@param int nRowTotal общия брой на всички редовете в справката
		*	@param int nCurPage текуща страница на справката
		*	@return void
		*/
		function setPaging($nRowLimit, $nRowTotal, $nCurPage)
		{
			if( empty( $this->oResult->oPaging ) )
				$this->oResult->oPaging = new DBPaging();
				
			$this->oResult->oPaging->nRowLimit	= $nRowLimit;
			$this->oResult->oPaging->nRowTotal	= $nRowTotal;
			$this->oResult->oPaging->nCurPage	= $nCurPage;
			$this->oResult->oPaging->nPageTotal = $nRowTotal ? ceil($nRowTotal / $nRowLimit) : 0;
		}
		
		/**
		*	Поставя полето по което е сортирани данните и типа на сортиране ascedenting или descedenting
		*
		*	@name function setSort()
		*	@access public
		*	@param string sSortField име на поле по което се сортира
		*	@param int типа на сортиране (DBAPI_SORT_ASC | DBAPI_SORT_DESC)
		*	@return void
		*/
		function setSort($sSortField, $nSortType)
		{
			if( empty( $this->oResult->oPaging ) )
				$this->oResult->oPaging = new DBPaging();
				
			$this->oResult->oPaging->sSortField = $sSortField;
			$this->oResult->oPaging->nSortType	= ($nSortType == DBAPI_SORT_DESC) ? DBAPI_SORT_DESC : DBAPI_SORT_ASC;
		}
		
		/**
		*	Функцията позволява извеждане на алерт при клиента
		*
		*	@name function setAlert()
		*	@access public
		*	@param string sAlert Съобщението което да се изведе на клиента
		*	@return void
		*/
		function setAlert( $sAlert )
		{
			if( !empty( $sAlert ) )
				$this->oAction->aAlerts[] = $sAlert;
		}
		
		/**
		*	Инициализира поле във web форма <form> (<input .. или <textarea..)
		*
		*	@name function setFormElement()
		*	@access public
		*	@param mixed mIDForm ID на формата
		*	@param mixed mID ID на елемента от формата който ще инициализираме с данни
		*	@param array aAttributes асоциативен масив с атрибути, които ще поставим на елемента от формата
		*	@param mixed mValue стойност която ще поставим във атрибута 'value' или между таговете на елемента
		*	@return void
		*/
		function setFormElement($mIDForm, $mID, $aAttributes = NULL, $mValue = NULL)
		{
			assert( !empty( $mID ) );
			
			if( !isset( $this->oAction->aForms[ $mIDForm ] ) )
				$this->oAction->aForms[ $mIDForm ] = new DBForm();
				
			if( !isset( $this->oAction->aForms[ $mIDForm ]->aFormElements[ $mID ] ) )
				$this->oAction->aForms[ $mIDForm ]->aFormElements[ $mID ] = new DBFormElement();

			$this->oAction->aForms[ $mIDForm ]->aFormElements[ $mID ]->mValue = htmlspecialchars($mValue);

			if( is_array( $aAttributes ) )
				$this->setFormElementAttributes($mIDForm, $mID, $aAttributes);
		}
		
		/**
		*	Функцията поставя атрибут на даден елемент от формата
		*	забележка: елемента предварително трябва да е сетнат с функция setFormElement
		*
		*	@name function setFormElementAttribute()
		*	@access public
		*	@param mixed mIDForm ID на формата
		*	@param mixed mID ID на елемента от формата който ще инициализираме с данни
		*	@param string sName име на атрибута, който ще поставяме на елемента
		*	@param mixed mValue стойност на атрибута, който ще поставяме на елемента
		*	@return void
		*/
		function setFormElementAttribute($mIDForm, $mID, $sName, $mValue)
		{
			assert( !empty( $mIDForm ) && !empty( $mID ) );
			
			if( !isset( $this->oAction->aForms[ $mIDForm ] ) )
				$this->oAction->aForms[ $mIDForm ] = new DBForm();
				
			if( !isset( $this->oAction->aForms[ $mIDForm ]->aFormElements[ $mID ] ) )
				$this->oAction->aForms[ $mIDForm ]->aFormElements[ $mID ] = new DBFormElement();
				
			$this->oAction->aForms[ $mIDForm ]->aFormElements[ $mID ]->aAttributes[ $sName ] = $mValue;
		}
		
		/**
		*	Функцията поставя атрибути на даден елемент от формата
		*	забележка: елемента предварително трябва да е сетнат с функция setFormElement
		*
		*	@name function setFormElementAttributes()
		*	@access public
		*	@param mixed mIDForm ID на формата
		*	@param mixed mID ID на елемента от формата който ще инициализираме с данни
		*	@param array aAttributes асоциативен масив с атрибути, които ще поставим на елемента от формата
		*	@return void
		*/
		function setFormElementAttributes($mIDForm, $mID, $aAttributes)
		{
			assert( !empty( $mIDForm )	);
			assert( !empty( $mID )		);
				
			if( is_array( $aAttributes ) )
			{
				foreach( $aAttributes as $sKey => $mValue )
					$this->setFormElementAttribute($mIDForm, $mID, $sKey, $mValue);
			}
		}
		
		/**
		*	Тази функция се изполва за попълване на елементи (тагове) <option> на елемент <select>
		*
		*	@name function setFormElementChild()
		*	@access public
		*	@param mixed mIDForm ID на формата
		*	@param mixed mIDParent ID на select-a
		*	@param array aAttributes асоциативен масив с атрибути, който ще се поставят на съответния <option>
		*	@param mixed mValue стойност която ще се постави между таговете на елемента (<option>mValue</option>)
		*	@return void
		*/
		function setFormElementChild($mIDForm, $mIDParent, $aAttributes = array(), $mValue = NULL)
		{
			assert( !empty( $mIDParent ) );
			
			$oElement = new DBFormElement();
			$oElement->aAttributes = $aAttributes;
			$oElement->mValue = htmlspecialchars($mValue);

			array_push($this->oAction->aForms[ $mIDForm ]->aFormElements[ $mIDParent ]->aChilds, $oElement);
		}
		
		/**
		*	Функцията ще постави атрибути на дадена клетка в резултата
		*	
		*	@name function setDataAttributes()
		*	@access public
		*	@param int nID ID(индекса)на реда от резултата
		*	@param string sField име на поле от реда
		*	@param array aAttributes асоциативен масив с атрибути, който ще се поставят на съответната клетка
		*	@return void
		*/
		function setDataAttributes($nID, $sField, $aAttributes)
		{
			if( is_array( $aAttributes ) )
				$this->oResult->aDataAttributes[ $nID ][ $sField ] = $aAttributes;
		}
		
		/**
		*	Функцията ще постави атрибути на даден ред (tr) в резултата
		*	
		*	@name function setRowAttributes()
		*	@access public
		*	@param int nID ID(индекса)на реда от резултата
		*	@param array aAttributes асоциативен масив с атрибути, който ще се поставят на съответният ред
		*	@return void
		*/
		function setRowAttributes($nID, $aAttributes)
		{
			if( is_array( $aAttributes ) )
				$this->oResult->aRowAttributes[ $nID ] = $aAttributes;
		}

		/**
		*	Функция за експорт на инициализираните данни към PFD
		*
		*	@name function toPDF()
		*	@author paco (Пламен)
		*	@access public
		*	@param string sDocumentTitle	- Име на справката
		*	@param string cOrientation		- ориентация на лист L, P
		*	@param string sFileName			- името на файла, който да се изгенерира
		*	@param string sPDFType			- типа на PDF класа, който да се използва за генериране на справката
		*/
		function toPDF($sDocumentTitle = 'Справка', $cOrientation = 'L', $sFileName="doc", $sPDFType = 'pdf_general_result', $sDestination = '')
		{
			switch($sPDFType) 
			{
				case 'pdf_general_result' : 
					$oPDF = new grPDF($cOrientation);
					$oPDF -> PrintReport( $this, $sDocumentTitle, $sFileName, $sDestination ) ;
					break;
					
				case 'pdf_salary_total' :
					$oPDF = new stPDF($cOrientation);
					$oPDF -> PrintReport( $this, $sDocumentTitle, $sFileName, $sDestination ) ;
					break;
					
				case 'pdf_object_archiv' :
					$oPDF = new oaPDF($cOrientation);
					$oPDF -> PrintReport( $this, $sDocumentTitle, $sFileName, $sDestination ) ;
					break;					

				case 'pdf_missing_documents' :
					$oPDF = new mdPDF($cOrientation);
					$oPDF -> PrintReport( $this, $sDocumentTitle, $sFileName, $sDestination ) ;
					break;
					
				default :
					$oPDF = new errPDF($cOrientation);
					$oPDF -> PrintReport( $this, $sDocumentTitle, $sFileName, $sDestination ) ;
			}

		}

		/**
		*	Функция за експорт на инициализираните данни към XML
		*
		*	@name function toXLS()
		*	@author AI-Killer (Аспарух)
		*	@access public
		*	@param string filename
		*	@param string caption
		*/
		function toXLS($filename, $caption)
		{
			$data['caption'] = $caption;
			$data['fields']  = array();
			$data['data']    = array();
			
			foreach( $this->oResult->aTitles as $nRow => $aCols )
			{
				foreach( $aCols as $nCol => $oTitle )
				{
					$data['fields'][$nRow][$nCol]['caption'] 	= $oTitle->sCaption;
					$data['fields'][$nRow][$nCol]['colspan'] 	= ( $nCol != 0 && isset( $oTitle->aAttributes['colspan'] ) ) ? $oTitle->aAttributes['colspan'] : 1;
					$data['fields'][$nRow][$nCol]['rowspan'] 	= 1;
				}
			}
			
			$i = 0;
			$nRow++;
			
			//print_r($this->oResult);
			foreach ($this->oResult->aFields as $k => $v)
			{
				if( empty($v->sBtn) )
				{
					$data['fields'][$nRow][$i]['caption'] 	= $v->sCaption;
					$data['fields'][$nRow][$i]['colspan'] 	= 1;
					$data['fields'][$nRow][$i]['rowspan'] 	= 1;
					$data['fields'][$nRow][$i]['attributes']= $v->aAttributes;
				}
				$i++;
			}
			
			$r=0; $c=0;
			foreach($this->oResult->aData as $row_key => $row){
				foreach($this->oResult->aFields as $fields_key => $field){
					if( empty($field->sBtn) )
					{
						$data['data'][$r][$c++] = isset($row[$fields_key]) ? $row[$fields_key] : '';
					}
				}
				$r++; $c=0;
			}
			
			if( !empty( $this->oResult->aTotal ) ) {
				foreach($this->oResult->aFields as $sField => $oField )	{	
					$data['totals'][$c++] = isset( $this->oResult->aTotal[$sField] ) ? $this->oResult->aTotal[$sField] : '';
				}
			}
			
			Export_XLS($data, $filename, $caption);
		}
		
		public function toAMF() {
			$res = new FlexResponse();
			
			foreach( $this->aFlexVars as $value ) {
				$res->variables[] = $value;
			}
			
			foreach( $this->aFlexControls as $value ) {
				$res->controls[] = $value;
			}
			
			$res->alerts 	= $this->oAction->aAlerts;
			$res->error		= $this->oError;
			
			$res->hiddenParams = $this->oHiddenParams;
	
			return $res;
		}		
		
		public function SetFlexVar( $sName, $oVar ) {
			$var = new FlexVar();
			
			$var->name 	= $sName;
			$var->value = $oVar;
			
			$this->aFlexVars[$sName] = $var;
		}		
		
		public function SetFlexControl( $sName, $aAttributes = array(), $aMethods = array() ) {
			$control = new FlexControl();
			
			$control->name 			= $sName;
			$control->attributes 	= $aAttributes;
			//$control->methods = $aMethods;
			$this->aFlexControls[$sName] = $control;
		}	
		
		public function SetFlexControlDefaultValue( $sName, $sDefaultField, $oValue ) {
			if ( array_key_exists($sName, $this->aFlexControls) ) {
				$this->aFlexControls[$sName]->defaultField = $sDefaultField;
				$this->aFlexControls[$sName]->defaultValue = $oValue;
			}
		}
		
		public function SetFlexControlAttr( $sName, $sAttrName, $oAttrValue ) {
			if ( array_key_exists($sName, $this->aFlexControls) ) {
				$this->aFlexControls[$sName]->attributes[] = array('name' => $sAttrName, 'value' => $oAttrValue );
			}
		}		
		
		public function SetHiddenParam( $sName, $oParam ) {
			$this->oHiddenParams[$sName] = $oParam;
		}		
		
		public function SetFlexControlMethod( $sName, $sMethodName ) {
			// :)
		}		
		
		function toXLSforMissingDocuments($filename, $caption)
		{
			$data['caption'] = $caption;
			$data['fields']  = array();
			$data['data']    = array();			
			$i = 0;
			
			foreach ($this->oResult->aFields as $k => $v)
			{
				if( empty($v->sBtn) )
				{
					if( $i < 2 ) {
						$data['fields'][0][$i]['caption'] 	= $v->sCaption;
						$data['fields'][0][$i]['colspan'] 	= 1;
						$data['fields'][0][$i]['rowspan'] 	= 1;
						$data['fields'][0][$i]['attributes']= $v->aAttributes;
					}
				}
				$i++;
			}

			$r=0; $c=0;
			foreach($this->oResult->aData as $row_key => $row){
				foreach($this->oResult->aFields as $fields_key => $field){
					if( empty($field->sBtn) )
					{
						if( $c < 2 ) {
							$data['data'][$r][$c++] = isset($row[$fields_key]) ? $row[$fields_key] : '';
						} else {
							$r++;
							$data['data'][$r][0] = 'Липсващи документи:';
							$r++;		
							$c2 = 0;
							$aMissingDocuments = explode(',',$row[$fields_key]);
							foreach( $aMissingDocuments as $value ) {
								$data['data'][$r][$c2++] = $value;
								if($c2 > 1) {
									$r++;
									$c2 = 0;
								}
							}	
							$r++;	
						}
				
					}
				}
				$r++; $c=0;
			}
			
			Export_XLS($data, $filename, $caption);
		}
		
		function dataFormat( $oField, $nRowKey, $sField, $sContent )
		{
			$aAttribute = array();
			
			if( !empty($oField->aAttributes['DATA_FORMAT']) )
				switch( $oField->aAttributes['DATA_FORMAT'] )
				{
					case DF_STRING:
									$aAttribute = array('style' => 'padding-right:20px;');
									break;
					case DF_CENTER:
									$aAttribute = array('style' => 'text-align:center;');
									break;
	
					case DF_DIGIT : 
									$sContent = sprintf("%01.2f", $sContent);
									$aAttribute = array( 'align' => 'right' );
									break;
					case DF_FLOAT : 
									$sContent = sprintf("%01.3f", $sContent);
									$aAttribute = array( 'align' => 'right' );
									break;
					case DF_NUMBER : 
									$sContent = sprintf("%01.0f", $sContent);
									$aAttribute = array( 'align' => 'right' );
									break;
					case DF_CURRENCY : 
//									$sContent = sprintf("%01.2f лв.", $sContent);
									$sContent = number_format($sContent, 2 , '.' , ' ' )." лв.";
									$aAttribute = array( 'align' => 'right' );
									break;
					case DF_CURRENCY4 : 
//									$sContent = sprintf("%01.4f лв.", $sContent);
                                    $sContent = number_format($sContent, 4 , '.' , ' ' )." лв.";
									$aAttribute = array( 'align' => 'right' );
									break;
                    case DF_CURRENCY6 :
//                                    $sContent = sprintf("%01.6f лв.", $sContent);
                                    $sContent = number_format($sContent, 6 , '.' , ' ' )." лв.";
                                    $aAttribute = array( 'align' => 'right' );
                                    break;
					case DF_DATE : 
									$nTime = mysqlDateToTimestamp( $sContent ); 
									$sContent = !empty($nTime) ? date("d.m.Y", $nTime) : "";
									$aAttribute = array( 'align' => 'center' );
									break;
					case DF_TIME : 
									$nTime = mysqlDateToTimestamp( $sContent ); 
									$sContent = !empty($nTime) ? date("H:i:s", $nTime) : "";
									$aAttribute = array( 'align' => 'center' );
									break;
					case DF_DATETIME : 
									$nTime = mysqlDateToTimestamp( $sContent ); 
									$sContent = !empty($nTime) ? date("d.m.Y H:i:s", $nTime) : "";
									$aAttribute = array( 'align' => 'center' );
									break;
					case DF_MONTH	:
									$nTime = mysqlDateToTimestamp( $sContent);
									$sContent = !empty($nTime) ? date("m.Y",$nTime) : "";
									$aAttribute = array( 'align' => 'center');
					case DF_ZEROLEADNUM :
									$aAttribute = array( 'align' => 'right' );
									break;
					case DF_PERCENT :
									$sContent = sprintf("%01.2f %%", $sContent);
									$aAttribute = array( 'align' => 'center' );
									break;
				}
			
			if( !empty($aAttribute) )
			{
				if( empty( $this->oResult->aDataAttributes[ $nRowKey ][ $sField ] ) )
				{
					$this->oResult->aDataAttributes[ $nRowKey ][ $sField ] = array();
					
				}
				foreach( $aAttribute as $nAKey => $sAVal )
					$this->oResult->aDataAttributes[ $nRowKey ][ $sField ][$nAKey] = $sAVal;
			}
			return $sContent;
		}
		
		/**
		*	Функция за експорт на дървовиден резултат към XML.
		*/
		function toXMLTree()
		{
			header( "Content-type: application/xml; charset=UTF-8" );
			
			$oDoc = new DOMDocument( "1.0", "UTF-8" );
			
			$oDoc->encoding = "UTF-8";
			$oDoc->formatOutput = true;
			
			$oElResponse = $oDoc->createElement( "response" );
			$oElData = $oDoc->createElement( "tree_data" );
			$oSubElements = array();

			foreach( $this->oResult->aData as $nKey => $aValue )
			{
				$nID 		= isset( $aValue['id'] ) ? $aValue['id'] : 0;
				$nIDParent 	= isset( $aValue['id_parent'] ) ? $aValue['id_parent'] : 0;
				$sName 		= isset( $aValue['name'] ) ? $aValue['name'] : "";
				$sValue 	= isset( $aValue['value'] ) ? $aValue['value'] : "";
				$aParams 	= isset( $aValue['params'] ) ? $aValue['params'] : array();
				
				// Validate
				if( empty( $nID ) || isset( $oSubElements[$nID] ) ) continue;
				if( !empty( $nIDParent ) && !isset( $oSubElements[$nIDParent] ) ) continue;
				if( empty( $sName ) || is_numeric( $sName ) ) continue;
				// End Validate
				
				$oSubElements[$nID] = $oDoc->createElement( $sName );
				
				if( !empty( $sValue ) ) $oSubElements[$nID]->nodeValue = $sValue;
				if( !empty( $aParams ) ) foreach( $aParams as $sParam => $sParamValue ) $oSubElements[$nID]->setAttribute( $sParam, $sParamValue );
				
				if( !empty( $nIDParent ) ) $oSubElements[$nIDParent]->appendChild( $oSubElements[$nID] );
				else $oElData->appendChild( $oSubElements[$nID] );
			}
			
			$oElResponse->appendChild( $oElData );
			$oDoc->appendChild( $oElResponse );
			
			$mResult = $oDoc->saveXML();
			
			return $mResult;
		}
		
		/**
		*	Функция за експорт на инициализираните данни към XML
		*	
		*	@name function toXML()
		*	@access public
		*	@return string XML като стринг
		*/
		function toXML()
		{	
			header("Content-type: application/xml; charset=UTF-8");

			$oDoc = new DOMDocument('1.0', 'UTF-8');
			
			$oDoc->encoding = "UTF-8";
			$oDoc->formatOutput = true;					//formatira XML-a na redove :)
			
			$oElResponse	= $oDoc->createElement("response");
			$oElAction		= $oDoc->createElement("action");
			$oElForm		= $oDoc->createElement("form");
			$oElData		= $oDoc->createElement("data");
			$oElTotal		= $oDoc->createElement("total");
			
			if( defined('EOL_DEBUG') && EOL_DEBUG )
			{
				//<php>
				if( !empty( APILog::$aLogs ) )
				{
					$oElPHP = $oDoc->createElement("php");
					
					foreach( APILog::$aLogs as $oLog )
					{
						$oEl = $oDoc->createElement("e");	
						
						if( !empty( $oLog->nCode ) )
							$oEl->appendChild( $oDoc->createElement('code', $oLog->nCode) );
							
						if( !empty( $oLog->sMsg ) )
							$oEl->appendChild( $oDoc->createElement('msg', $oLog->sMsg) );
						
						if( !empty( $oLog->sFile ) )
							$oEl->appendChild( $oDoc->createElement('file', $oLog->sFile));
						
						if( !empty( $oLog->nLine ) )
							$oEl->appendChild( $oDoc->createElement('line', $oLog->nLine));
							
						$oElPHP->appendChild( $oEl );
					}
					
					$oElResponse->appendChild( $oElPHP );
				}
				//</php>
				
				//<sql>
				if( !empty( DBLog::$aLines ) )
				{
					$oElDB = $oDoc->createElement("sql");
					
					foreach( DBLog::$aLines as $sLine )
					{
						$oEl = $oDoc->createElement("e", $sLine);
						$oElDB->appendChild( $oEl );
					}
					
					$oElResponse->appendChild( $oElDB );
				}
				//</sql>
				
				//<debug>
				if( !empty( $this->oDebug->aDebugElements ) )
				{
					$oElDebug = $oDoc->createElement("debug");
					
					foreach( $this->oDebug->aDebugElements as $oElement )
					{
						$oEl = $oDoc->createElement("e");	
						
						if( !empty( $oElement->sInfo ) )
							$oEl->appendChild( $oDoc->createElement('info', $oElement->sInfo) );
						
						if( !empty( $oElement->sFile ) )				
							$oEl->appendChild( $oDoc->createElement('file', $oElement->sFile));
						
						if( !empty( $oElement->nLine ) )
							$oEl->appendChild( $oDoc->createElement('line', $oElement->nLine));
							
						$oElDebug->appendChild( $oEl );
					}
					
					$oElResponse->appendChild( $oElDebug );
				}
				// </debug>
			} // if( defined('EOL_DEBUG') && EOL_DEBUG )
			
			//<error>
			if( $this->oError->nCode != DBAPI_ERR_SUCCESS )
			{
				$oElError = $oDoc->createElement("error");
				$oElError->appendChild( $oDoc->createElement('code', $this->oError->nCode) );
				$oElError->appendChild( $oDoc->createElement('message', $this->oError->sMsg));
				$oElResponse->appendChild( $oElError );
			}
			//</error>
			
			//<action>
			
			foreach( $this->oAction->aAlerts as $sAlert )
				$oElAction->appendChild( $oDoc->createElement('alert', $sAlert));
			
			if( !empty( $this->oAction->aForms ) )
			{
				foreach( $this->oAction->aForms AS $nIDForm => $oForm )
				{
					if( !empty( $oForm->aFormElements ) )
					{
						$oElForm = $oDoc->createElement("form");
						$oElForm->setAttribute('id', $nIDForm);
						
						foreach($oForm->aFormElements AS $nIDFormElement => $oFormElement)
						{
							$oElFormElement = $oDoc->createElement('e', $oFormElement->mValue);
							$oElFormElement->setAttribute('id', $nIDFormElement);

							foreach( $oFormElement->aAttributes AS $key => $value )
								$oElFormElement->setAttribute($key, $value);
							
							foreach($oFormElement->aChilds AS $oFormElementChild)
							{
								$oElFormElementChild = $oDoc->createElement('option', $oFormElementChild->mValue);
							
								foreach( $oFormElementChild->aAttributes AS $key => $value )
									$oElFormElementChild->setAttribute($key, $value);
								
								$oElFormElement->appendChild( $oElFormElementChild );
							}
							
							$oElForm->appendChild( $oElFormElement );
						}
						
						$oElAction->appendChild( $oElForm );
					}
				}
			}
			
			if( $oElAction->hasChildNodes() )
				$oElResponse->appendChild( $oElAction );
				
			//</action>
			
			//<result>
			
			$oElResult = $oDoc->createElement("result");
			
			//<title>
			if( !empty( $this->oResult->aTitles ) )
			{
				$oElTitle = $oDoc->createElement("title");
				
				foreach( $this->oResult->aTitles as $aRow )
				{
					$oElRow = $oDoc->createElement("r");
						
					foreach( $aRow as $oField )
					{
						$oElCell = $oDoc->createElement("c", $oField->sCaption);
						
						foreach( $oField->aAttributes as $key => $value )
							$oElCell->setAttribute($key, $value);
						
						$oElRow->appendChild( $oElCell );
					}
					
					$oElTitle->appendChild( $oElRow );
				}
				
				$oElResult->appendChild( $oElTitle );
			}
			//</title>
			
			//<fields> 
			if( !empty( $this->oResult->aFields ) )
			{
				$oElFields	= $oDoc->createElement("fields");
				
				foreach($this->oResult->aFields as $sField => $oField)
				{
					$oElField = $oDoc->createElement('c');
					$oElField->appendChild( $oDoc->createElement('name', $sField) );
					
					if( !empty( $oField->sCaption ) )
						$oElField->appendChild( $oDoc->createElement('caption', $oField->sCaption) );
						
					if( !empty( $oField->sTitle ) )
						$oElField->appendChild( $oDoc->createElement('title', $oField->sTitle) );
					
					if( !empty( $oField->sImg ) )
						$oElField->appendChild( $oDoc->createElement('img', $oField->sImg) );
					
					if( !empty( $oField->sLink ) )
						$oElField->appendChild( $oDoc->createElement('link', $oField->sLink) );
						
					if( !is_null( $oField->sBtn ) )
						$oElField->appendChild( $oDoc->createElement('btn', $oField->sBtn) );
						
					if( !empty( $oField->aDataElements ) )
					{
						$oElFieldData = $oDoc->createElement('data');
						
						foreach( $oField->aDataElements as $oDataElement )
						{
							$oElDataElement = $oDoc->createElement($oDataElement->sTagName, $oDataElement->mValue);
							
							foreach( $oDataElement->aAttributes AS $key => $value )
								$oElDataElement->setAttribute($key, $value);
							
							foreach($oDataElement->aChilds AS $oDataElementChild)
							{
								$oElDataElementChild = $oDoc->createElement($oDataElementChild->sTagName, $oDataElementChild->mValue);
							
								foreach( $oDataElementChild->aAttributes AS $key => $value )
									$oElDataElementChild->setAttribute($key, $value);
								
								$oElDataElement->appendChild( $oElDataElementChild );
							}
							
							$oElFieldData->appendChild( $oElDataElement );
						}
						
						$oElField->appendChild( $oElFieldData );
					}
					
					foreach($oField->aAttributes as $key => $value)
						$oElField->setAttribute($key, $value);
					
					$oElFields->appendChild( $oElField );
				}
				
				$oElResult->appendChild( $oElFields );
			
				//</fields>
			
				//<data>
				foreach($this->oResult->aData as $r_key => $r)
				{
					$oElRow = $oDoc->createElement('r');
					
					if( isset( $r['id'] ) )
					{
						$oElRow->setAttribute('id', $r['id']);
						
						if( isset($this->oResult->aRowAttributes[ $r['id'] ]) && is_array($this->oResult->aRowAttributes[ $r['id'] ]) )
						{
							foreach( $this->oResult->aRowAttributes[ $r['id'] ] as $key => $value )
								$oElRow->setAttribute($key, $value);
						}
					}
					
					foreach($this->oResult->aFields as $sField => $oField )
					{	
						if( !is_null( $oField->sBtn ) )		//Пропускаме бутоните
							$r[ $sField ] = '';
						
						if( array_key_exists($sField, $r) )
						{
							$oElCell = $oDoc->createElement('c');
							
							if( isset( $r[ $sField ] ) )
								$oElCell->nodeValue = htmlspecialchars($this->dataFormat( $oField, $r_key, $sField, $r[ $sField ] ));

							if(
								isset( $this->oResult->aDataAttributes[ $r_key ][ $sField ] ) &&
								is_array( $this->oResult->aDataAttributes[ $r_key ][ $sField ] )
							)
							{
								foreach( $this->oResult->aDataAttributes[ $r_key ][ $sField ] as $key => $value )
									$oElCell->setAttribute($key, $value);
							}
							
							$oElRow->appendChild( $oElCell );
						}
					}
					
					$oElData->appendChild( $oElRow );	
				}
				
				$oElResult->appendChild( $oElData );
				//</data>
				
				//<total>
				if( !empty( $this->oResult->aTotal ) )
				{
					$oElTotal->appendChild( $oDoc->createElement('c', "") );
					foreach($this->oResult->aFields as $sField => $oField )
					{	
						$sValue = "";
						if( isset( $this->oResult->aTotal[$sField] ) )
							$sValue = $this->dataFormat( $oField, NULL, $sField,  $this->oResult->aTotal[$sField] );
							
						$oElTotal->appendChild( $oDoc->createElement('c', $sValue) );
					
						$oElResult->appendChild( $oElTotal );
					}
				}
				//</total>
				
				//<paging>
				
				if( !empty( $this->oResult->oPaging ) )
				{
					$oElPaging = $oDoc->createElement("paging");
					
					if( !empty( $this->oResult->oPaging->nCurPage ) )
					{
						$oElPaging->appendChild( $oDoc->createElement('rows_total',		$this->oResult->oPaging->nRowTotal) );
						$oElPaging->appendChild( $oDoc->createElement('rows_per_page',	$this->oResult->oPaging->nRowLimit) );
						$oElPaging->appendChild( $oDoc->createElement('current_page',	$this->oResult->oPaging->nCurPage) );
						$oElPaging->appendChild( $oDoc->createElement('page_total',		$this->oResult->oPaging->nPageTotal) );
					}
					
					if( !empty( $this->oResult->oPaging->sSortField ) )
					{
						$oElPaging->appendChild( $oDoc->createElement('sfield',	$this->oResult->oPaging->sSortField) );
						$oElPaging->appendChild( $oDoc->createElement('stype', $this->oResult->oPaging->nSortType) );
					}
					
					$oElResult->appendChild( $oElPaging );
				}
				//</paging>
				
				if( $oElResult->hasChildNodes() )
					$oElResponse->appendChild( $oElResult );
					
				//</result>
			}
			
			$oDoc->appendChild( $oElResponse );
			
			return $oDoc->saveXML();
		}
		
		public function printResponse( $sCaption = NULL, $sFileName = NULL, $bPortrait = TRUE )
		{

            if(empty($sFileName)) {
                $sFileName = 'report';
            }
			
		    if( AmfServer::isAmfRequest() ) {
                Params::set("api_action", "export_to_amf");    
		    }
		    
			switch( Params::get("api_action", "") )
			{
				case 'export_to_xls':
//					if($sFileName == 'missing_documents') {
//						$this->toXLSforMissingDocuments($sFileName.'.xls', $sCaption);
//					} else {
						$this->toXLS($sFileName.'.xls', $sCaption);
//					}
					die();
				case 'export_to_pdf':
					switch($sFileName) {
						case 'salary_total': $this->toPDF('Работна заплата','P','salary_total.pdf','pdf_salary_total'); break;
						case 'missing_documents': $this->toPDF('Липсващи документи','L','missing_documents.pdf','pdf_missing_documents'); break;
						case 'object_archiv': $this->toPDF('Архив на обект','P','object_archiv.pdf','pdf_object_archiv'); break;
						default: $this->toPDF($sCaption, $bPortrait ? 'P' : 'L', $sFileName.'.pdf'); break;
					}
					die();
				case 'export_to_amf':
				    //do nothing
				    break;
				default:
					switch( $sFileName )
					{
						case "states":
							if($this->bPrint){
								print $this->toXMLTree();
							}
							break;
						
						default:
							if($this->bPrint){
								print $this->toXML();
							}
							break;
					}
					break;
			}
		}
	}
?>