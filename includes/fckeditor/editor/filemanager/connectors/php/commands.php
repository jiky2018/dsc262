<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function GetFolders($resourceType, $currentFolder)
{
	$sServerDir = ServerMapFolder($resourceType, $currentFolder, 'GetFolders');
	$aFolders = array();
	$oCurrentFolder = opendir($sServerDir);

	while ($sFile = readdir($oCurrentFolder)) {
		if (($sFile != '.') && ($sFile != '..') && is_dir($sServerDir . $sFile)) {
			$aFolders[] = '<Folder name="' . ConvertToXmlAttribute($sFile) . '" />';
		}
	}

	closedir($oCurrentFolder);
	echo '<Folders>';
	natcasesort($aFolders);

	foreach ($aFolders as $sFolder) {
		echo $sFolder;
	}

	echo '</Folders>';
}

function GetFoldersAndFiles($resourceType, $currentFolder)
{
	$sServerDir = ServerMapFolder($resourceType, $currentFolder, 'GetFoldersAndFiles');
	$aFolders = array();
	$aFiles = array();
	$oCurrentFolder = opendir($sServerDir);

	while ($sFile = readdir($oCurrentFolder)) {
		if (($sFile != '.') && ($sFile != '..')) {
			if (is_dir($sServerDir . $sFile)) {
				$aFolders[] = '<Folder name="' . ConvertToXmlAttribute($sFile) . '" />';
			}
			else {
				$iFileSize = @filesize($sServerDir . $sFile);

				if (!$iFileSize) {
					$iFileSize = 0;
				}

				if (0 < $iFileSize) {
					$iFileSize = round($iFileSize / 1024);

					if ($iFileSize < 1) {
						$iFileSize = 1;
					}
				}

				$aFiles[] = '<File name="' . ConvertToXmlAttribute($sFile) . '" size="' . $iFileSize . '" />';
			}
		}
	}

	natcasesort($aFolders);
	echo '<Folders>';

	foreach ($aFolders as $sFolder) {
		echo $sFolder;
	}

	echo '</Folders>';
	natcasesort($aFiles);
	echo '<Files>';

	foreach ($aFiles as $sFiles) {
		echo $sFiles;
	}

	echo '</Files>';
}

function CreateFolder($resourceType, $currentFolder)
{
	if (!isset($_GET)) {
		global $_GET;
	}

	$sErrorNumber = '0';
	$sErrorMsg = '';

	if (isset($_GET['NewFolderName'])) {
		$sNewFolderName = $_GET['NewFolderName'];
		$sNewFolderName = SanitizeFolderName($sNewFolderName);

		if (strpos($sNewFolderName, '..') !== false) {
			$sErrorNumber = '102';
		}
		else {
			$sServerDir = ServerMapFolder($resourceType, $currentFolder, 'CreateFolder');

			if (is_writable($sServerDir)) {
				$sServerDir .= $sNewFolderName;
				$sErrorMsg = CreateServerFolder($sServerDir);

				switch ($sErrorMsg) {
				case '':
					$sErrorNumber = '0';
					break;

				case 'Invalid argument':
				case 'No such file or directory':
					$sErrorNumber = '102';
					break;

				default:
					$sErrorNumber = '110';
					break;
				}
			}
			else {
				$sErrorNumber = '103';
			}
		}
	}
	else {
		$sErrorNumber = '102';
	}

	echo '<Error number="' . $sErrorNumber . '" originalDescription="' . ConvertToXmlAttribute($sErrorMsg) . '" />';
}

function FileUpload($resourceType, $currentFolder, $sCommand)
{
	if (!isset($_FILES)) {
		global $_FILES;
	}

	$sErrorNumber = '0';
	$sFileName = '';
	if (isset($_FILES['NewFile']) && !is_null($_FILES['NewFile']['tmp_name'])) {
		global $Config;
		$oFile = $_FILES['NewFile'];
		$sServerDir = ServerMapFolder($resourceType, $currentFolder, $sCommand);
		$sFileName = $oFile['name'];
		$sFileName = SanitizeFileName($sFileName);
		$sOriginalFileName = $sFileName;
		$sExtension = substr($sFileName, strrpos($sFileName, '.') + 1);
		$sExtension = strtolower($sExtension);

		if (isset($Config['SecureImageUploads'])) {
			if (($isImageValid = IsImageValid($oFile['tmp_name'], $sExtension)) === false) {
				$sErrorNumber = '202';
			}
		}

		if (isset($Config['HtmlExtensions'])) {
			if (!IsHtmlExtension($sExtension, $Config['HtmlExtensions']) && (($detectHtml = DetectHtml($oFile['tmp_name'])) === true)) {
				$sErrorNumber = '202';
			}
		}

		if (!$sErrorNumber && IsAllowedExt($sExtension, $resourceType)) {
			$iCounter = 0;

			while (true) {
				$sFilePath = $sServerDir . $sFileName;

				if (is_file($sFilePath)) {
					$iCounter++;
					$sFileName = RemoveExtension($sOriginalFileName) . '(' . $iCounter . ').' . $sExtension;
					$sErrorNumber = '201';
				}
				else {
					move_uploaded_file($oFile['tmp_name'], $sFilePath);
					if (($sExtension == 'jpg') || ($sExtension == 'jpeg') || ($sExtension == 'png') || ($sExtension == 'gif') || ($sExtension == 'bmp')) {
						require_once ROOT_PATH . '/includes/cls_image.php';
						$image = new cls_image($GLOBALS['_CFG']['bgcolor']);
						if ((0 < intval($GLOBALS['_CFG']['watermark_place'])) && !empty($GLOBALS['_CFG']['watermark'])) {
							$image->add_watermark($sFilePath, '', '../../../../../' . $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']);
						}
					}

					if (is_file($sFilePath)) {
						if (isset($Config['ChmodOnUpload']) && !$Config['ChmodOnUpload']) {
							break;
						}

						$permissions = 511;
						if (isset($Config['ChmodOnUpload']) && $Config['ChmodOnUpload']) {
							$permissions = $Config['ChmodOnUpload'];
						}

						$oldumask = umask(0);
						chmod($sFilePath, $permissions);
						umask($oldumask);
					}

					break;
				}
			}

			if (file_exists($sFilePath)) {
				if (isset($isImageValid) && ($isImageValid === -1) && (IsImageValid($sFilePath, $sExtension) === false)) {
					@unlink($sFilePath);
					$sErrorNumber = '202';
				}
				else {
					if (isset($detectHtml) && ($detectHtml === -1) && (DetectHtml($sFilePath) === true)) {
						@unlink($sFilePath);
						$sErrorNumber = '202';
					}
				}
			}
		}
		else {
			$sErrorNumber = '202';
		}
	}
	else {
		$sErrorNumber = '202';
	}

	$sFileUrl = CombinePaths(GetResourceTypePath($resourceType, $sCommand), $currentFolder);
	$sFileUrl = CombinePaths($sFileUrl, $sFileName);
	SendUploadResults($sErrorNumber, $sFileUrl, $sFileName);
	exit();
}

function MoreFileUpload($resourceType, $currentFolder, $sCommand)
{
	if (!isset($_FILES)) {
		global $_FILES;
	}

	global $Config;
	$sErrorNumber = '0';
	$sFileName = '';

	if (is_array($_FILES['NewFile']['name'])) {
		foreach ($_FILES['NewFile']['name'] as $key => $value) {
			if (!empty($_FILES['NewFile']['tmp_name'][$key])) {
				$sServerDir = ServerMapFolder($resourceType, $currentFolder, $sCommand);
				$sFileName = $_FILES['NewFile']['name'][$key];
				$sFileName = SanitizeFileName($sFileName);
				$sOriginalFileName = $sFileName;
				$sExtension = substr($sFileName, strrpos($sFileName, '.') + 1);
				$sExtension = strtolower($sExtension);

				if (isset($Config['SecureImageUploads'])) {
					if (($isImageValid = IsImageValid($_FILES['NewFile']['tmp_name'][$key], $sExtension)) === false) {
						$sErrorNumber = '202';
					}
				}

				if (isset($Config['HtmlExtensions'])) {
					if (!IsHtmlExtension($sExtension, $Config['HtmlExtensions']) && (($detectHtml = DetectHtml($_FILES['NewFile']['tmp_name'][$key])) === true)) {
						$sErrorNumber = '202';
					}
				}

				if (!$sErrorNumber && IsAllowedExt($sExtension, $resourceType)) {
					$iCounter = 0;

					while (true) {
						$sFilePath = $sServerDir . $sFileName;

						if (is_file($sFilePath)) {
							$iCounter++;
							$sFileName = RemoveExtension($sOriginalFileName) . '(' . $iCounter . ').' . $sExtension;
							$sErrorNumber = '201';
						}
						else {
							move_uploaded_file($_FILES['NewFile']['tmp_name'][$key], $sFilePath);
							if (($sExtension == 'jpg') || ($sExtension == 'jpeg') || ($sExtension == 'png') || ($sExtension == 'gif') || ($sExtension == 'bmp')) {
								require_once ROOT_PATH . '/includes/cls_image.php';
								$image = new cls_image($GLOBALS['_CFG']['bgcolor']);
								if ((0 < intval($GLOBALS['_CFG']['watermark_place'])) && !empty($GLOBALS['_CFG']['watermark'])) {
									$image->add_watermark($sFilePath, '', '../../../../../' . $GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']);
								}
							}

							if (is_file($sFilePath)) {
								if (isset($Config['ChmodOnUpload']) && !$Config['ChmodOnUpload']) {
									break;
								}

								$permissions = 511;
								if (isset($Config['ChmodOnUpload']) && $Config['ChmodOnUpload']) {
									$permissions = $Config['ChmodOnUpload'];
								}

								$oldumask = umask(0);
								chmod($sFilePath, $permissions);
								umask($oldumask);
							}

							break;
						}
					}

					if (file_exists($sFilePath)) {
						if (isset($isImageValid) && ($isImageValid === -1) && (IsImageValid($sFilePath, $sExtension) === false)) {
							@unlink($sFilePath);
							$sErrorNumber = '202';
						}
						else {
							if (isset($detectHtml) && ($detectHtml === -1) && (DetectHtml($sFilePath) === true)) {
								@unlink($sFilePath);
								$sErrorNumber = '202';
							}
						}
					}
				}
				else {
					$sErrorNumber = '202';
				}

				if ($sErrorNumber == '202') {
					$sFileUrl = CombinePaths(GetResourceTypePath($resourceType, $sCommand), $currentFolder);
					$sFileUrl = CombinePaths($sFileUrl, $sFileName);
					SendUploadResults($sErrorNumber, $sFileUrl, $sFileName);
				}
			}
			else {
				continue;
			}
		}

		$sFileUrl = CombinePaths(GetResourceTypePath($resourceType, $sCommand), $currentFolder);
		$sFileUrl = CombinePaths($sFileUrl, $sFileName);
		SendUploadResults($sErrorNumber, $sFileUrl, $sFileName, $key);
	}
	else {
		$sErrorNumber = '202';
		$sFileUrl = CombinePaths(GetResourceTypePath($resourceType, $sCommand), $currentFolder);
		$sFileUrl = CombinePaths($sFileUrl, $sFileName);
		SendUploadResults($sErrorNumber, $sFileUrl, $sFileName);
	}

	exit();
}


?>
