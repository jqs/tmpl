<?php
/***
* template_cls.4.php
* 
* version 1.6.3
* ----------------
* (c) 2000 - 2005 James Q. Stansfield (jqs@iridani.net)
*
***/
define("cTEMPLATE_VERSION", "1.6.3");
define("LD_DISP", 0);
define("LD_RET", 1);
define("LD_ARR", 10);
define("TK_DISP", 0);
define("TK_LD_PARSE", 1);
define("TK_LD_DISP", 2);
define("TK_VAR", 3);
define("TK_PARSE", 5);
define("TK_PARSE_ARR", 6);

class template_cls
{	
	// Declarations
	var $gstrTemplateFile;
	var $gobjTokens = array();
	var $gblnReturn;
	var $gstrOpenTag;
	var $gstrCloseTag;
	var $gintLength = 100;
	var $gintCounter = 1;
	var $gastrData;
	
	function template_cls() {
		//echo "Creating new instance of template_cls.<br>";
		$this->gblnReturn=false;
		$this->gstrTemplateFile="";
		$this->gstrOpenTag="[%";
		$this->gstrCloseTag="%]";
		$this->gintCounter = 1;
		$this->gastrData = array();
		//register_shutdown_function(array(&$this, '_destruct'));
	}
	
	function destruct() {
		//echo "Destroying instance of template_cls.<br>";
		unset($this->gobjTokens);
	}
	// Required Public Properies / Methods
	// -----------------------------------
	// TemplateFile(string)
	function TemplateFile($filespec) {
		if (is_string($filespec)) {
			$this->gstrTemplateFile = $filespec;
		}
	}
	// OpenTag(string)
	function OpenTag($oTag) {
		if (is_string($oTag)) {
			$gstrOpenTag = $oTag;
		}
	}
	// CloseTag(string)
	function CloseTag($cTag) {
		if (is_string($cTag)) {
			$gstrCloseTag = $cTag;
		}
	}
	// CI(boolean)
	// Reset
	function Reset() {
		$this->__destruct();
		$this->__construct();
	}
	// AddToken(string, string, variant)
	function AddToken($strToken, $intType, $strData) {
		if (is_string($strToken)) {
			$this->gobjTokens[$strToken] = array($intType, $strData);
		}
	}
	// DelToken(string)
	function DelToken($strToken) {
		if (is_string($strToken)) {
			if (isset($this->gobjTokens[$strToken])) {
				unset($this->gobjTokens[$strToken]);
			}
		}
	}
	// RemoveAllTokens
	function RemoveAllTokens() {
		unset($this->gobjTokens);
	}
	// parseTemplateFile
	function parseTemplateFile() {
		if (is_string($this->gstrTemplateFile)) {
			$this->parseFile($this->gstrTemplateFile);
		}
	}
	// parseTemplateString(string)
	function parseTemplateString($strtemplate) {
		if (is_string($strtemplate)) {
			$this->parseString($strtemplate);
		}
	}
	// getParsedTemplateFile
	function getParsedTemplateFile() {
		if (is_string($this->gstrTemplateFile)) {
			$this->stringReset();
			$this->gblnReturn = true;
			return $this->parseFile($this->gstrTemplateFile);
		}
	}
	// getParsedTemplateString(string)
	function getParsedTemplateString($strTemplate) {
		if (is_string($strTemplate)) {
			$this->stringReset();
			$this->gblnReturn = true;
			return $this->parseString($strTemplate);
		}
	}
	// loadFile(string, string, byref variant)
	function loadFile($strFileSpec, $intType, &$varData) {
		$return = false;
		if (is_file($strFileSpec)) {
			$file = file_get_contents($strFileSpec);
			switch ($intType) {
			case LD_DISP:
				$this->printLine($file);
				break;
			case LD_RET:
				$varData = $file;
				break;
			case LD_ARR:
				$varData = preg_split('/\n|\r|\n\r/', $file);
				break;
			}
			$return = true;
		}
		return $return;
	}
	// fileExists(string)
	function fileExists($strFileSpec) {
		//This isn't really needed as we are using the built-in PHP function now, but it left for
		//full compatibility with the ASP version.
		$return = false;
		if (is_file($strFileSpec)) {
			$return = true;
		}
		return $return;
	}
	// Required Private Properties / Methods
	// -------------------------------------
	// parseFile(string)
	function parseFile($strFileSpec) {
		//$strData();
		if (is_file($strFileSpec)) {
			if ($this->loadFile($strFileSpec, LD_ARR, $strData)) {
				$this->parseArray($strData);
			} else {
				//Error
			}
		}
		if ($this->gblnReturn) {
			return $this->getString();
		}
	}
	// parseString(string)
	function parseString($strString) {
		if (is_string($strString)) {
			$this->parseArray(preg_split('/\n|\r|\n\r/', $strString));
		}
		if ($this->gblnReturn) {
			return $this->getString();
		}
	}
	// parseArray(array)
	function parseArray($strArray) {
		foreach ($strArray as $line) {
			$this->parseLine($line . "\n\r");
		}
	}
	// parseLine(string)
	function parseLine($line) {
		//$intA, $intB, $strLine, $strLine2, $strToken;
		$intA = strpos($line, $this->gstrOpenTag);
		//PHP strpos returns a zero indexed value
		if (!($intA === false)) {
			$strLine = substr($line, 0, $intA);
			//echo "\n\r$strLine\n\r$intA\n\r";
			$intB = strpos($line, $this->gstrCloseTag);
			if ($intB >= 0) {
				$strLine2 = substr($line, $intB + strlen($this->gstrCloseTag));
				$strToken = trim( substr( $line, $intA + strlen($this->gstrOpenTag), ($intB - $intA - strlen($this->gstrOpenTag)) ) );
					$this->printLine($strLine);
				$this->getToken($strToken);
				$this->parseLine($strLine2);
			} else {
				$this->printLine($line);
			}
		} else {
			$this->printLine($line);
		}
	}
	// getToken(string)
	function getToken($strToken) {
		if (isset($this->gobjTokens[$strToken])) {
			$intTokenType = $this->gobjTokens[$strToken][0];
			$strTokenData = $this->gobjTokens[$strToken][1];
			switch ($intTokenType) {
			case TK_DISP:
				$this->printLine($strTokenData);
				break;
			case TK_LD_PARSE:
				if ($this->loadFile($strTokenData, LD_ARR, $strData)) {
					$this->parseArray($strData);
				}
				break;
			case TK_LD_DISP:
				if ($this->loadFile($strTokenData, LD_RET, $strData)) {
					$this->printLine($strData);
				}
				break;
			case TK_PARSE:
				$this->parseLine($strTokenData);
				break;
			case TK_PARSE_ARR:
				$this->parseArray($strTokenData);
				break;
			}
		} else {
			//What to do with an unfound token?
		}
	}
	// printLine(string)
	function printLine($line) {
		//Next line is required in PHP otherwise we are adding too many blank entries in the array
		if (strlen($line)) {
			if ($this->gblnReturn) {
				$this->add2String($line);
			} else {
				echo $line;
			}
		}
	}
	// stringReset
	function stringReset() {
		unset($this->gastrData);
		$this->gintLength = 100;
		$this->gintCounter = 1;
		$this->gastrData = array();
	}
	// getString
	function getString() {
		return implode("", $this->gastrData);
		//May need to add a \n\r as 'glue'
	}
	// add2String
	function add2String($line) {
		if ($this->gintCounter >= $this->gintLength) {
			$strTempData = $this->getString();
			$this->stringReset();
			$line = $strTempData . $line;
		}
		$this->gastrData[$this->gintCounter] = $line;
		$this->gintCounter++;
	}
}
?>