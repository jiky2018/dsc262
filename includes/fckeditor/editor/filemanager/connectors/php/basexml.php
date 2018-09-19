<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function SetXmlHeaders()
{
	ob_end_clean();
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Cache-Control: no-store, no-cache, must-revalidate');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache');
	header('Content-Type: text/xml; charset=utf-8');
}

function CreateXmlHeader($command, $resourceType, $currentFolder)
{
	setxmlheaders();
	echo '<?xml version="1.0" encoding="utf-8" ?>';
	echo '<Connector command="' . $command . '" resourceType="' . $resourceType . '">';
	echo '<CurrentFolder path="' . ConvertToXmlAttribute($currentFolder) . '" url="' . ConvertToXmlAttribute(GetUrlFromPath($resourceType, $currentFolder, $command)) . '" />';
	$GLOBALS['HeaderSent'] = true;
}

function CreateXmlFooter()
{
	echo '</Connector>';
}

function SendError($number, $text)
{
	if (isset($GLOBALS['HeaderSent']) && $GLOBALS['HeaderSent']) {
		SendErrorNode($number, $text);
		createxmlfooter();
	}
	else {
		setxmlheaders();
		echo '<?xml version="1.0" encoding="utf-8" ?>';
		echo '<Connector>';
		SendErrorNode($number, $text);
		echo '</Connector>';
	}

	exit();
}

function SendErrorNode($number, $text)
{
	echo '<Error number="' . $number . '" text="' . htmlspecialchars($text) . '" />';
}


?>
