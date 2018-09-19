<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
class FileUpload
{
	private $filepath;
	private $allowtype = array('gif', 'jpg', 'png', 'jpeg');
	private $maxsize = 1000000;
	private $israndname = true;
	private $originName;
	private $tmpFileName;
	private $fileType;
	private $fileSize;
	private $newFileName;
	private $errorNum = 0;
	private $errorMess = '';

	public function __construct($options = array())
	{
		foreach ($options as $key => $val) {
			$key = strtolower($key);

			if (!in_array($key, get_class_vars(get_class($this)))) {
				continue;
			}

			$this->setOption($key, $val);
		}
	}

	private function getError()
	{
		$str = '上传文件<font color=\'red\'>' . $this->originName . '</font>时出错：';

		switch ($this->errorNum) {
		case 4:
			$str .= '没有文件被上传';
			break;

		case 3:
			$str .= '文件只被部分上传';
			break;

		case 2:
			$str .= '上传文件超过了HTML表单中MAX_FILE_SIZE选项指定的值';
			break;

		case 1:
			$str .= '上传文件超过了php.ini 中upload_max_filesize选项的值';
			break;

		case -1:
			$str .= '末充许的类型';
			break;

		case -2:
			$str .= '文件过大，上传文件不能超过' . $this->maxSize . '个字节';
			break;

		case -3:
			$str .= '上传失败';
			break;

		case -4:
			$str .= '建立存放上传文件目录失败，请重新指定上传目录';
			break;

		case -5:
			$str .= '必须指定上传文件的路径';
			break;

		default:
			$str .= '末知错误';
		}

		return $str . '<br>';
	}

	private function checkFilePath()
	{
		if (empty($this->filepath)) {
			$this->setOption('errorNum', -5);
			return false;
		}

		if (!file_exists($this->filepath) || !is_writable($this->filepath)) {
			if (!@mkdir($this->filepath, 493)) {
				$this->setOption('errorNum', -4);
				return false;
			}
		}

		return true;
	}

	private function checkFileSize()
	{
		if ($this->maxsize < $this->fileSize) {
			$this->setOPtion('errorNum', '-2');
			return false;
		}
		else {
			return true;
		}
	}

	private function checkFileType()
	{
		if (in_array(strtolower($this->fileType), $this->allowtype)) {
			return true;
		}
		else {
			$this->setOption('errorNum', -1);
			return false;
		}
	}

	private function setNewFileName()
	{
		if ($this->israndname) {
			$this->setOption('newFileName', $this->proRandName());
		}
		else {
			$this->setOption('newFileName', $this->originName);
		}
	}

	private function proRandName()
	{
		$fileName = date('YmdHis') . rand(100, 999);
		return $fileName . '.' . $this->fileType;
	}

	private function setOption($key, $val)
	{
		$this->$key = $val;
	}

	public function uploadFile($fileField)
	{
		$return = true;

		if (!$this->checkFilePath()) {
			$this->errorMess = $this->getError();
			return false;
		}

		$name = $_FILES[$fileField]['name'];
		$tmp_name = $_FILES[$fileField]['tmp_name'];
		$size = $_FILES[$fileField]['size'];
		$error = $_FILES[$fileField]['error'];

		if (is_Array($name)) {
			$errors = array();

			for ($i = 0; $i < count($name); $i++) {
				if ($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i])) {
					if (!$this->checkFileSize() || !$this->checkFileType()) {
						$errors[] = $this->getError();
						$return = false;
					}
				}
				else {
					$error[] = $this->getError();
					$return = false;
				}

				if (!$return) {
					$this->setFiles();
				}
			}

			if ($return) {
				$fileNames = array();

				for ($i = 0; $i < count($name); $i++) {
					if ($this->setFiles($name[$i], $tmp_name[$i], $size[$i], $error[$i])) {
						$this->setNewFileName();

						if (!$this->copyFile()) {
							$errors = $this->getError();
							$return = false;
						}
						else {
							$fileNames[] = $this->newFileName;
						}
					}
				}

				$this->newFileName = $fileNames;
			}

			$this->errorMess = $errors;
			return $return;
		}
		else {
			if ($this->setFiles($name, $tmp_name, $size, $error)) {
				if ($this->checkFileSize() && $this->checkFileType()) {
					$this->setNewFileName();

					if ($this->copyFile()) {
						return true;
					}
					else {
						$return = false;
					}
				}
				else {
					$return = false;
				}
			}
			else {
				$return = false;
			}

			if (!$return) {
				$this->errorMess = $this->getError();
			}

			return $return;
		}
	}

	private function copyFile()
	{
		if (!$this->errorNum) {
			$filepath = rtrim($this->filepath, '/') . '/';
			$filepath .= $this->newFileName;

			if (@move_uploaded_file($this->tmpFileName, $filepath)) {
				return true;
			}
			else {
				$this->setOption('errorNum', -3);
				return false;
			}
		}
		else {
			return false;
		}
	}

	private function setFiles($name = '', $tmp_name = '', $size = 0, $error = 0)
	{
		$this->setOption('errorNum', $error);

		if ($error) {
			return false;
		}

		$this->setOption('originName', $name);
		$this->setOption('tmpFileName', $tmp_name);
		$arrStr = explode('.', $name);
		$this->setOption('fileType', strtolower($arrStr[count($arrStr) - 1]));
		$this->setOption('fileSize', $size);
		return true;
	}

	public function getNewFileName()
	{
		return $this->newFileName;
	}

	public function getErrorMsg()
	{
		return $this->errorMess;
	}
}


?>
