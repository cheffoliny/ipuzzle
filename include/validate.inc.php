<?php
/*
 * @name Validate
 * @author Boro - 03.06
 * @param $variable			- Проверяваната променлива
 * @param $result			- Резултат от проверката	
 * @param $trueResult		- Резултат, който се връща при валидна проверка (подразбиране TRUE), 
 * 								персонално настройване на отговор при истина
 * @param $falseResult		- Резултат, който се връща при невалидна проверка (подразбиране FALSE)
 * 								персонално настройване на отговор при неистина
 * @param $emptyResult		- при празна стойност на променливата: $variable, по подразбиране връща TRUE
 * @param $errResult		- генерирани съобщения от метода в хода на работа
 * @param $paramResult		- масив от върнати параметри, спесифични за всеки метод
 * @example 				$oValidate = new Validate();
							$oValidate->variable = "127581901";
							$oValidate->falseResult = "Грешен БУЛСТАТ";
							$oValidate->trueResult = "Верен БУЛСТАТ";
							$oValidate->checkBULSTAT();
							print $oValidate->result;			
*/
class Validate {
	
	public $variable; 					
	public $result;								
	public $trueResult 	= true;		
	public $falseResult 	= false;
	public $emptyResult 	= true;
	public $errResult 		= '';
	public $paramResult 	= array();

	
	
	/** ---------------------------------------------------
 	* Проверка валидността на мейл
 	* @name checkEMAIL ()
 	* @example 
 	* 			$variable 	= "my_mail@my_company.com";
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	function checkEMAIL () {
		
		
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}

		if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $this->variable))  {
			$this->result = $this->falseResult;
			return; 
		}
	
		$email_array = explode("@", $this->variable);
		$local_array = explode(".", $email_array[0]);
		
		for ($i = 0; $i < sizeof($local_array); $i++) {
			if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
		 		$this->result = $this->falseResult;
				return; 
			}  
		} 
		
		if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
			$domain_array = explode(".", $email_array[1]);
			if (sizeof($domain_array) < 2) {
				$this->result = $this->falseResult;
				return; 
			}
		
			for ($i = 0; $i < sizeof($domain_array); $i++) {
				if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
					$this->result = $this->falseResult;
					return;
				}
			}
		}
		
		$this->result = $this->trueResult;	
		
	}
	
	/** ---------------------------------------------------
 	* Проверка валидността на URL
 	* @name checkURL ()
 	* @example 
 	* 			$variable 	= "ftp://ftp.telepol.net";
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	function checkURL () {
		if (empty ($this->variable)) {
			 $this->result = $this->emptyResult;
			return true;
		}

		$RegExp ="/(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/";
			  
		if (!preg_match($RegExp, $this->variable)) 
			$this->result = $this->falseResult;
        else 
        	$this->result = $this->trueResult;
			
	}
	
	
	/** ---------------------------------------------------
 	* Проверка валидността на банкова сметка по стандарта IBAN
 	* @name checkIBAN ()
 	* @example 
 	* 			$variable 	= "BG79UBBS84231003000111" - сметка в ОББ банка
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	function checkIBAN () {
		if (empty ($this->variable)) {
			 $this->result = $this->emptyResult;
			return true;
		}
			 
		$weightsIBAN = array( 1,10, 3,30, 9,90,27,76,81,34,
								49, 5,50,15,53,45,62,38,89,17,
								73,51,25,56,75,71,31,19,93,57,
								85,74,61,28,86,84);
		$alpha2num = array(
								'A'=>10,'B'=>11,'C'=>12,'D'=>13,'E'=>14,'F'=>15,'G'=>16,'H'=>17,
								'I'=>18,'J'=>19,'K'=>20,'L'=>21,'M'=>22,'N'=>23,'O'=>24,'P'=>25,
								'Q'=>26,'R'=>27,'S'=>28,'T'=>29,'U'=>30,'V'=>31,'W'=>32,'X'=>33,'Y'=>34,'Z'=>35);
	
		// актуални данни от http://www.ecbs.org/
		$country_len = array(
								'AD'=>24, 'AT'=>20, 'BE'=>16, 'CZ'=>24, 'DK'=>18, 'FI'=>18, 'FR'=>27, 'DE'=>22,
								'GR'=>27, 'HU'=>28, 'IS'=>26, 'IE'=>22, 'IT'=>27, 'LU'=>20, 'NL'=>18,
								'NO'=>15, 'PL'=>28, 'PT'=>25, 'SI'=>19, 'ES'=>24, 'SE'=>24, 'CH'=>21,
								'GB'=>22, 'BG'=>22);
	 
		$IBAN = str_replace(array('-','/',' ',"\t","\n"), '', $this->variable);
		$IBAN = strtoupper($IBAN);
		$IBAN = ereg_replace("IBAN", "", $IBAN); // според ISO 13616 е позволено да има IBAN рефикс
		
		
		
		// преместване кода на държавата и контролната цифра накрая на стринга
		// Използва се при пресмятане на теглата 
		$matches = array();
		$RegExp = '/^([A-Z][A-Z])(\d\d)([\dA-Z]{1,30})$/';
		if (!preg_match($RegExp, $IBAN, $matches)) {
			$this->result = $this->falseResult;
			$this->errResult 	= "Некоректен запис в кода!";
			return;
		}

		$IBAN = $matches[3] . $matches[1] . $matches[2];

		$cc = $matches[1];
		if (!isset($country_len[$cc]) || $country_len[$cc] != strlen($IBAN)) {
			$this->result = $this->falseResult;
			$this->errResult 	= "Дължината на сметката е некоректна!";
			return;
		}

		// съпоставка на буквите с числата
		$IBAN = strtr($IBAN, $alpha2num);

		// допустима дължина не повече от 36 (30 - за сметката, 4 за държавата, 2 контролна сума
		if (!is_numeric($IBAN) || strlen($IBAN) > 36) {
			$this->result = $this->falseResult;
			return;
		}
			
		$IBAN = strrev($IBAN);

		$this->result = ($this->_get_control_number($IBAN, $weightsIBAN, 97) == 1) ? $this->trueResult : $this->falseResult;
		if ($this->_get_control_number($IBAN, $weightsIBAN, 97) == 1) 
			$this->result = $this->trueResult;
		else {
			$this->result = $this->falseResult;
			$this->errResult 	= "Грешна контролна цифра!";
		}
			
			
	}

    /* 30.04.2014 Стефан Миланов
     * По международен стандарт BIC кода е или 8 или 11 символа...
     * Т.е. задължителни 4 начални са букви.
     */
    function checkBIC () {
        if (empty ($this->variable)) {
            $this->result = $this->emptyResult;
            return true;
        }

        $BIC = str_replace(array('-','/',' ',"\t","\n"), '', $this->variable);
        $BIC = strtoupper($BIC);

        $RegExp = '/^[A-Z]{4}[0-9A-Z]{2}[0-9A-Z]{2}([0-9A-Z]{3})?\z/i';
        if ( preg_match($RegExp, $BIC) ) {
            return true;
        } else {
            $this->result = $this->falseResult;
            $this->errResult = "Некоректен запис в кода на BIC!";
            return false;
        }


        if ( mb_strlen($BIC) < 8 || mb_strlen($BIC) > 11 ) {
            $this->result = $this->falseResult;
            $this->errResult = "Некоректна дължина на BIC!";
            return false;
        }

    }

	/** ---------------------------------------------------
 	* Проверка валидността на ЕГН
 	* @name checkEGN ()
 	* @example 
 	* 			$variable 	= "8141010016" - съгласно ГРАО: http://www.grao.bg/esgraon.html
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	
	function checkEGN () {
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}

		$EGN = $this->variable;		 

		$RegExp = '/^([0-9]{9}[0-9]{1})$/';
		if (!preg_match($RegExp, $EGN, $matches)) {
			$this->result = $this->falseResult;
			return true;
		}
			
		$coeffs = array (2, 4, 8, 5, 10, 9, 7, 3, 6); 
		$days 	= array	(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		
		$digits = array();
		for ($i = 0; $i < strlen($EGN); $i++)
			$digits[$i] = substr($EGN, $i, 1);
	
		if (count($digits)!=10) {
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен брой цифри";
			return true;
		}
				
		$dd 	= $digits[4] * 10 + $digits[5];
		$mm 	= $digits[2] * 10 + $digits[3];
		$yy 	= $digits[0] * 10 + $digits[1];
		$yyyy 	= null;

		
		if ($mm >= 1 && $mm <= 12) { $yyyy = 1900 + $yy; }
		elseif ($mm >= 21 && $mm <= 32) { $mm -= 20; $yyyy = 1800 + $yy; }
		elseif ($mm >= 41 && $mm <= 52) { $mm -= 40; $yyyy = 2000 + $yy; }
		else { 
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен месец. Позиции 3 и 4.";
			return true;
		}

		// Високосна година
		$days[1] += $yyyy % 4 == 0 && ($yyyy % 400 == 0 || $yyyy % 100 != 0)  ? 1 : 0;
		
		if (!($dd >= 1 && $dd <= $days[$mm - 1])) { 
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен ден. Позиции 5 и 6.";
			return true;
		}

		$checksum = 0;
		
		for ($j = 0; $j < count($coeffs); $j++) 
			$checksum += $digits[$j] * $coeffs[$j]; 
		
		$checksum %= 11;
		
		if (10 == $checksum) 
			$checksum = 0;

		if ($digits[9] != $checksum) { 
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Грешна контролна цифра на въведеният ЕГН. Позиция 10.";
			return true;
		}
	
		$this->result 		= $this->trueResult;
	}
	
	
	
	/** ---------------------------------------------------
 	* Проверка валидността на БУЛСТАТ
 	* @name checkBULSTAT ()
 	* @example 
 	* 			$variable 	= "127581901" - съгласно http://bulstat.registryagency.bg/
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	
	function checkBULSTAT () {
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}

		$BULSTAT = $this->variable;		 

		$RegExp = '/^([0-9]{8}[0-9]{1})$/';
		if (!preg_match($RegExp, $BULSTAT, $matches)) {
			$this->result = $this->falseResult;
			return true;
		}
			
		$coeffs = array (1, 2, 3, 4, 5, 6, 7, 8); 
		
		$digits = array();
		for ($i = 0; $i < strlen($BULSTAT); $i++)
			$digits[$i] = substr($BULSTAT, $i, 1);
	
		if (count($digits)!=9) {
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен брой цифри";
			return true;
		}
	
		$checksum = 0;
		
		for ($j = 0; $j < count($coeffs); $j++) 
			$checksum += $digits[$j] * $coeffs[$j]; 
		
		$checksum %= 11;
		
		if (10 == $checksum) 
			$checksum = 0;

		if ($digits[8] != $checksum) { 
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Грешна контролна цифра на въведеният БУЛСТАТ. Позиция 9";
			return true;
		}
		
		$this->result 		= $this->trueResult;
	}
	
	
	
	/** ---------------------------------------------------
 	* Проверка валидността на ПОЩЕНСКИ КОД
 	* @name checkPOSTCODE ()
 	* @example 
 	* 			$variable 	= "7300" - http://www.bgpost.bg/index.htm#Bul/codes.htm
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	
	function checkPOSTCODE () {
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}

		$POSTCODE = $this->variable;		 

		$RegExp = '/^([0-9]{3}[0-9]{1})$/';
		if (!preg_match($RegExp, $POSTCODE, $matches)) {
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен брой цифри";
			return true;
		}
		
		$this->result 		= $this->trueResult;
	}
	
	
	/** ---------------------------------------------------
 	* Проверка валидността на GSM номер в България
 	* @name checkGSM ()
 	* @example 
 	* 			$variable 	= "0899139981" 
 	* 			return $result
 	* 			$this->paramResult['code'] 		= '089';
 	* 			$this->paramResult['phone'] 	= '9139981';
 	* 			$this->paramResult['operator'] 	= 'globul';
 	* -----------------------------------------------------	
 	**/
	
	function checkGSM () {
		
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}
		
		$GSM = $this->variable;		 

		$RegExp = '/^([0-9]{9}[0-9]{1})$/';
		if (!preg_match($RegExp, $GSM, $matches)) {
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен брой цифри";
			return true;
		}
		
		$aOperators = array();
		$aOperators['087'] = 'vivatel';
		$aOperators['088'] = 'mtel';
		$aOperators['089'] = 'globul';
		$aOperators['098'] = 'bob';
		
		$sCode  = substr($GSM, 0,3);
		
		if (!isset ($aOperators[$sCode])) {
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Непознат мобилен оператор";
			return true;
		}
		
		$this->paramResult['code'] 		= $sCode;
		$this->paramResult['phone'] 	= substr($GSM, 3,7);
		$this->paramResult['operator'] 	= $aOperators[$sCode];
		
		
		$this->result 		= $this->trueResult;
	}
	
	
	/**
	* Калкулира контролната сума на число
	* 
	* @param string $number чиското подадено като стринг
	* @param array $weights масив от тегловните коефициенти
	* @param int $mod_ подразбиране 10
	* @param int $subtract подразбиране 0
	* @param bool $allow_high (optional) true ако метода може да връща числа по-големи от 10 разряда
	* @return int - контролната сума или false
	*/
	function _get_control_number($number, &$weights, $mod_ = 10, $subtract = 0, $allow_high = false) {
	
		$sum = $this->_mult_weights($number, $weights);
		if ($sum == -1)
			return false;

		$mod = $this->_modf($sum, $mod_);  

		if ($subtract > $mod)
			$mod = $subtract - $mod;

		if ($allow_high === false)
			$mod %= 10;           
		return $mod;
	}

	function _mult_weights($number, &$weights) {
		if (!is_array($weights))
			return false;

		$sum = 0;
       
		$count = min(count($weights), strlen($number));
		if ($count == 0) 
			return false;
			
		for ($i=0; $i<$count; ++$i) {
			$sum += intval(substr($number,$i,1)) * $weights[$i];
		}

		return $sum;
	}

	 function _modf($val, $div) {
        if( function_exists('bcmod') ){
            return bcmod($val,$div);
        } else if (function_exists('fmod')) {
            return fmod($val,$div);
        }
        $r = $a / $b;
        $i = intval($r);
        return intval(($r - $i) * $b);
    }
    
    
    
    function checkEIN () {
    	
    	if (empty ($this->variable) || !is_numeric($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}
    	
    	$BULSTAT = (string)$this->variable;
		
		$nCount = strlen( $BULSTAT );
			
		if( !in_array($nCount, array(9, 13)) ){
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен брой цифри!";
			return true;
		}
			
		$nChecksum = 0;
			
		for($i=0; $i<8; $i++)
			$nChecksum += ($i + 1) * $BULSTAT[ $i ];
			
		$nChecksum %= 11;
			
		if( $nChecksum != 10 ){
			if( $BULSTAT[ 8 ] != $nChecksum ){
				$this->result 		= $this->falseResult;
				$this->errResult 	= "Невалидна контролна цифра!";
				return true;
			}
		} else {
			$nChecksum = 0;
			
			for($i=0; $i<8; $i++)
				$nChecksum += ($i + 3) * $BULSTAT[ $i ];
					
			$nChecksum %= 11;
				
			if( $nChecksum != 10 ){
				if( $BULSTAT[ 8 ] != $nChecksum ){
					$this->result 		= $this->falseResult;
					$this->errResult 	= "Невалидна контролна цифра!";
					return true;
				}
			} else {
				if( $BULSTAT[ 8 ] != 0 ){
					$this->result 		= $this->falseResult;
					$this->errResult 	= "Невалидна контролна цифра!";
					return true;
				}
			}
		}
			
		if( $nCount == 13 ){
			$aMultiplier = array(2, 7, 3, 5);
				
			$nChecksum = 0;
				
			for($i=0; $i<count( $aMultiplier ); $i++)
				$nChecksum += $aMultiplier[ $i ] * $BULSTAT[ $i + 8 ];
					
			$nChecksum %= 11;
			
		
			if( $nChecksum != 10 ){
			
				if( $BULSTAT[ 12 ] != $nChecksum ){
					$this->result 		= $this->falseResult;
					$this->errResult 	= "Невалидна контролна цифра!";
					return true;
				}
			
			} else {
			
				$nChecksum = 0;
				$aMultiplier = array(4, 9, 5, 7);
			
				for($i=0; $i<count( $aMultiplier ); $i++)
					$nChecksum += $aMultiplier[ $i ] * $BULSTAT[ $i + 8 ];
					
				$nChecksum %= 11;
			
				if( $nChecksum != 10 ){
					if( $BULSTAT[ 12 ] != $nChecksum ){
						$this->result 		= $this->falseResult;
						$this->errResult 	= "Невалидна контролна цифра!";
						return true;
					}
				} else {
					
					if( $BULSTAT[ 12 ] != 0 ){
						$this->result 		= $this->falseResult;
						$this->errResult 	= "Невалидна контролна цифра!";
						return true;
					}
				}
			
			}
		
		}
			
			
		$this->result 		= $this->trueResult;
	} 
	
	
	/** ---------------------------------------------------
 	* Проверка валидността на НОМЕР НА ЛИЧНА КАРТА			- приложен е алгоритъм подобен на валидирането на ЕГН!
 	* @name checkIDCARD ()
 	* @example 
 	* 			$variable 	= "305675765" 			
 	* 			return $result		
 	* -----------------------------------------------------	
 	**/
	
	function checkIDCARD () {
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}

		$IDCARD = $this->variable;		 

		$RegExp = '/^([0-9]{8}[0-9]{1})$/';
		if (!preg_match($RegExp, $IDCARD, $matches)) {
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Некоректен брой цифри";
			return true;
		}
		
		$coeffs = array (2, 4, 8, 5, 10, 9, 7, 3); 
		
		$digits = array();
		for ($i = 0; $i < strlen($IDCARD); $i++)
			$digits[$i] = substr($IDCARD, $i, 1);
	
		$checksum = 0;
		
		for ($j = 0; $j < count($coeffs); $j++) 
			$checksum += $digits[$j] * $coeffs[$j]; 
		
		$checksum %= 11;
		
		if (10 == $checksum) 
			$checksum = 0;

		if ($digits[8] != $checksum) { 
			$this->result 		= $this->falseResult;
			$this->errResult 	= "Грешна контролна цифра на въведения номер. Позиция 9";
			return true;
		}
		
		$this->result 		= $this->trueResult;
	}
	
	/**
	 * Проверява за валидност на ЕГН/EIN по зададен низ
	 * 
	 * @name trimEin()
	 * @author Павел Петров
	 *
	 * @param string $ein
	 * @return bool
	 */
	public function trimAndCheck_EinEGN( ) {
		if (empty ($this->variable)) {
			$this->result = $this->emptyResult;
			return true;
		}

		$sEin 		= $this->variable;
		$aMatrix	= array( "0", "1", "2", "3", "4", "5", "6", "7", "8", "9" );
		
		for ( $i = 0; $i < strlen($ein); $i++ ) {
			if ( in_array($ein[$i], $aMatrix) ) {
				$sEin .= $ein[$i];
			}
		}
				
		// Чужденци
		if ( $sEin == "999999999999999" ) {
			$this->result = $this->trueResult;
			return true;
		}
					
		if ( (strlen($sEin) == 9) || (strlen($sEin) == 13) ) {
			if ( strlen($sEin) == 13 ) {
				$base 	= substr($sEin, 0, 9);
				$ext	= substr($sEin, -4);
						
				$this->variable = $base;
				$this->checkEIN();	 					
			} else {
				$this->variable = $sEin;
				$this->checkEIN();
			}
			return true;
		} elseif ( strlen($sEin) == 10 ) {
			$this->variable = $sEin;
			$this->checkEGN();
			return true;
		}
		
		$this->result = $this->falseResult;
	}


	/**
	 * Валидира тел. номер - само цифри, ако има няколко номера трябва да са разделени със ','
	 *
	 * @param string $sPhone
	 * @param string $sExeptionMessage
	 * @return string валиден телефон
	 */
	public function checkPhone($sPhone, $sExeptionMessage = null, $bAllowEmpty = false, $bCleanResult = false) {
		$this->variable = (string) $sPhone;
		$this->errResult = $sExeptionMessage;

		$aPhones = preg_split('/[,;]/', $sPhone);

		foreach ($aPhones as $k=>$phone)
		{
			if($bCleanResult) {
				$aPhones[$k] = preg_replace("/[^\+\d]/",'',$aPhones[$k]);

				if(empty($aPhones[$k]))
				{
					if($bCleanResult) {
						unset($aPhones[$k]);
					}
					else {
						$this->result = $this->falseResult;
						return true;
					}
				}
				elseif(!preg_match("/^\+?\d{5,150}$/",$aPhones[$k]))
				{
					$this->result = $this->falseResult;
					return true;
				}
			}
			else {
				if(!preg_match("/^\+?\d{5,150}$/",$aPhones[$k])) {
					$this->result = $this->falseResult;
					return true;
				}
			}
		}

		if(empty($aPhones))
		{
			if($bAllowEmpty)
				return '';
			else
				$this->result = $this->falseResult;
				return true;
		}

		$this->paramResult = implode(',', $aPhones);
		$this->result = $this->trueResult;

		return true;
	}
}