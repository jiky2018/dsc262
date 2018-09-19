<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class FCKeditor
{
	/**
     * Name of the FCKeditor instance.
     *
     * @access protected
     * @var string
     */
	public $InstanceName;
	/**
     * Path to FCKeditor relative to the document root.
     *
     * @var string
     */
	public $BasePath;
	/**
     * Width of the FCKeditor.
     * Examples: 100%, 600
     *
     * @var mixed
     */
	public $Width;
	/**
     * Height of the FCKeditor.
     * Examples: 400, 50%
     *
     * @var mixed
     */
	public $Height;
	/**
     * Name of the toolbar to load.
     *
     * @var string
     */
	public $ToolbarSet;
	/**
     * Initial value.
     *
     * @var string
     */
	public $Value;
	/**
     * This is where additional configuration can be passed.
     * Example:
     * $oFCKeditor->Config['EnterMode'] = 'br';
     *
     * @var array
     */
	public $Config;

	public function FCKeditor($instanceName)
	{
		$this->InstanceName = $instanceName;
		$this->BasePath = '/fckeditor/';
		$this->Width = '100%';
		$this->Height = '200';
		$this->ToolbarSet = 'Default';
		$this->Value = '';
		$this->Config = array();
	}

	public function Create()
	{
		echo $this->CreateHtml();
	}

	public function CreateHtml()
	{
		$HtmlValue = htmlspecialchars($this->Value);
		$Html = '';

		if (!isset($_GET)) {
			global $HTTP_GET_VARS;
			$_GET = $HTTP_GET_VARS;
		}

		if ($this->IsCompatible()) {
			if (isset($_GET['fcksource']) && ($_GET['fcksource'] == 'true')) {
				$File = 'fckeditor.original.html';
			}
			else {
				$File = 'fckeditor.html';
			}

			$Link = $this->BasePath . 'editor/' . $File . '?InstanceName=' . $this->InstanceName;

			if ($this->ToolbarSet != '') {
				$Link .= '&amp;Toolbar=' . $this->ToolbarSet;
			}

			$Html .= '<input type="hidden" id="' . $this->InstanceName . '" name="' . $this->InstanceName . '" value="' . $HtmlValue . '" style="display:none" />';
			$Html .= '<input type="hidden" id="' . $this->InstanceName . '___Config" value="' . $this->GetConfigFieldString() . '" style="display:none" />';
			$Html .= '<iframe id="' . $this->InstanceName . '___Frame" src="' . $Link . '" width="' . $this->Width . '" height="' . $this->Height . '" frameborder="0" scrolling="no"></iframe>';
		}
		else {
			if (strpos($this->Width, '%') === false) {
				$WidthCSS = $this->Width . 'px';
			}
			else {
				$WidthCSS = $this->Width;
			}

			if (strpos($this->Height, '%') === false) {
				$HeightCSS = $this->Height . 'px';
			}
			else {
				$HeightCSS = $this->Height;
			}

			$Html .= '<textarea name="' . $this->InstanceName . '" rows="4" cols="40" style="width: ' . $WidthCSS . '; height: ' . $HeightCSS . '">' . $HtmlValue . '</textarea>';
		}

		return $Html;
	}

	public function IsCompatible()
	{
		return fckeditor_iscompatiblebrowser();
	}

	public function GetConfigFieldString()
	{
		$sParams = '';
		$bFirst = true;

		foreach ($this->Config as $sKey => $sValue) {
			if ($bFirst == false) {
				$sParams .= '&amp;';
			}
			else {
				$bFirst = false;
			}

			if ($sValue === true) {
				$sParams .= $this->EncodeConfig($sKey) . '=true';
			}
			else if ($sValue === false) {
				$sParams .= $this->EncodeConfig($sKey) . '=false';
			}
			else {
				$sParams .= $this->EncodeConfig($sKey) . '=' . $this->EncodeConfig($sValue);
			}
		}

		return $sParams;
	}

	public function EncodeConfig($valueToEncode)
	{
		$chars = array('&' => '%26', '=' => '%3D', '"' => '%22');
		return strtr($valueToEncode, $chars);
	}
}

function FCKeditor_IsCompatibleBrowser()
{
	if (isset($_SERVER)) {
		$sAgent = $_SERVER['HTTP_USER_AGENT'];
	}
	else {
		global $HTTP_SERVER_VARS;

		if (isset($HTTP_SERVER_VARS)) {
			$sAgent = $HTTP_SERVER_VARS['HTTP_USER_AGENT'];
		}
		else {
			global $HTTP_USER_AGENT;
			$sAgent = $HTTP_USER_AGENT;
		}
	}

	if ((strpos($sAgent, 'MSIE') !== false) && (strpos($sAgent, 'mac') === false) && (strpos($sAgent, 'Opera') === false)) {
		$iVersion = (double) substr($sAgent, strpos($sAgent, 'MSIE') + 5, 3);
		return 5.5 <= $iVersion;
	}
	else if (strpos($sAgent, 'Gecko/') !== false) {
		$iVersion = (int) substr($sAgent, strpos($sAgent, 'Gecko/') + 6, 8);
		return 20030210 <= $iVersion;
	}
	else if (strpos($sAgent, 'Opera/') !== false) {
		$fVersion = (double) substr($sAgent, strpos($sAgent, 'Opera/') + 6, 4);
		return 9.5 <= $fVersion;
	}
	else if (preg_match('|AppleWebKit/(\\d+)|i', $sAgent, $matches)) {
		$iVersion = $matches[1];
		return 522 <= $matches[1];
	}
	else {
		return false;
	}
}


?>
