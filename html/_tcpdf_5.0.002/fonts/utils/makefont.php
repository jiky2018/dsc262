<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function MakeFont($fontfile, $fmfile, $embedded = true, $enc = 'cp1252', $patch = array())
{
	set_magic_quotes_runtime(0);
	ini_set('auto_detect_line_endings', '1');

	if (!file_exists($fontfile)) {
		exit('Error: file not found: ' . $fontfile);
	}

	if (!file_exists($fmfile)) {
		exit('Error: file not found: ' . $fmfile);
	}

	$cidtogidmap = '';
	$map = array();
	$diff = '';
	$dw = 0;
	$ffext = strtolower(substr($fontfile, -3));
	$fmext = strtolower(substr($fmfile, -3));

	if ($fmext == 'afm') {
		if (($ffext == 'ttf') || ($ffext == 'otf')) {
			$type = 'TrueType';
		}
		else if ($ffext == 'pfb') {
			$type = 'Type1';
		}
		else {
			exit('Error: unrecognized font file extension: ' . $ffext);
		}

		if ($enc) {
			$map = ReadMap($enc);

			foreach ($patch as $cc => $gn) {
				$map[$cc] = $gn;
			}
		}

		$fm = ReadAFM($fmfile, $map);

		if (isset($widths['.notdef'])) {
			$dw = $widths['.notdef'];
		}

		if ($enc) {
			$diff = MakeFontEncoding($map);
		}

		$fd = MakeFontDescriptor($fm, empty($map));
	}
	else if ($fmext == 'ufm') {
		$enc = '';
		if (($ffext == 'ttf') || ($ffext == 'otf')) {
			$type = 'TrueTypeUnicode';
		}
		else {
			exit('Error: not a TrueType font: ' . $ffext);
		}

		$fm = ReadUFM($fmfile, $cidtogidmap);
		$dw = $fm['MissingWidth'];
		$fd = MakeFontDescriptor($fm, false);
	}

	$s = '<?php' . "\n";
	$s .= '$type=\'' . $type . "';\n";
	$s .= '$name=\'' . $fm['FontName'] . "';\n";
	$s .= '$desc=' . $fd . ";\n";

	if (!isset($fm['UnderlinePosition'])) {
		$fm['UnderlinePosition'] = -100;
	}

	if (!isset($fm['UnderlineThickness'])) {
		$fm['UnderlineThickness'] = 50;
	}

	$s .= '$up=' . $fm['UnderlinePosition'] . ";\n";
	$s .= '$ut=' . $fm['UnderlineThickness'] . ";\n";

	if ($dw <= 0) {
		if (isset($fm['Widths'][32]) && (0 < $fm['Widths'][32])) {
			$dw = $fm['Widths'][32];
		}
		else {
			$dw = 600;
		}
	}

	$s .= '$dw=' . $dw . ";\n";
	$w = MakeWidthArray($fm);
	$s .= '$cw=' . $w . ";\n";
	$s .= '$enc=\'' . $enc . "';\n";
	$s .= '$diff=\'' . $diff . "';\n";
	$basename = substr(basename($fmfile), 0, -4);

	if ($embedded) {
		if (($type == 'TrueType') || ($type == 'TrueTypeUnicode')) {
			CheckTTF($fontfile);
		}

		$f = fopen($fontfile, 'rb');

		if (!$f) {
			exit('Error: Unable to open ' . $fontfile);
		}

		$file = fread($f, filesize($fontfile));
		fclose($f);

		if ($type == 'Type1') {
			$header = ord($file[0]) == 128;

			if ($header) {
				$file = substr($file, 6);
			}

			$pos = strpos($file, 'eexec');

			if (!$pos) {
				exit('Error: font file does not seem to be valid Type1');
			}

			$size1 = $pos + 6;
			if ($header && (ord($file[$size1]) == 128)) {
				$file = substr($file, 0, $size1) . substr($file, $size1 + 6);
			}

			$pos = strpos($file, '00000000');

			if (!$pos) {
				exit('Error: font file does not seem to be valid Type1');
			}

			$size2 = $pos - $size1;
			$file = substr($file, 0, $size1 + $size2);
		}

		$basename = strtolower($basename);

		if (function_exists('gzcompress')) {
			$cmp = $basename . '.z';
			SaveToFile($cmp, gzcompress($file, 9), 'b');
			$s .= '$file=\'' . $cmp . "';\n";
			print('Font file compressed (' . $cmp . ")\n");

			if (!empty($cidtogidmap)) {
				$cmp = $basename . '.ctg.z';
				SaveToFile($cmp, gzcompress($cidtogidmap, 9), 'b');
				print('CIDToGIDMap created and compressed (' . $cmp . ")\n");
				$s .= '$ctg=\'' . $cmp . "';\n";
			}
		}
		else {
			$s .= '$file=\'' . basename($fontfile) . "';\n";
			print("Notice: font file could not be compressed (zlib extension not available)\n");

			if (!empty($cidtogidmap)) {
				$cmp = $basename . '.ctg';
				$f = fopen($cmp, 'wb');
				fwrite($f, $cidtogidmap);
				fclose($f);
				print('CIDToGIDMap created (' . $cmp . ")\n");
				$s .= '$ctg=\'' . $cmp . "';\n";
			}
		}

		if ($type == 'Type1') {
			$s .= '$size1=' . $size1 . ";\n";
			$s .= '$size2=' . $size2 . ";\n";
		}
		else {
			$s .= '$originalsize=' . filesize($fontfile) . ";\n";
		}
	}
	else {
		$s .= '$file=' . "'';\n";
	}

	$s .= '?>';
	SaveToFile($basename . '.php', $s);
	print('Font definition file generated (' . $basename . ".php)\n");
}

function ReadMap($enc)
{
	$file = dirname(__FILE__) . '/enc/' . strtolower($enc) . '.map';
	$a = file($file);

	if (empty($a)) {
		exit('Error: encoding not found: ' . $enc);
	}

	$cc2gn = array();

	foreach ($a as $l) {
		if ($l[0] == '!') {
			$e = preg_split('/[ \\t]+/', rtrim($l));
			$cc = hexdec(substr($e[0], 1));
			$gn = $e[2];
			$cc2gn[$cc] = $gn;
		}
	}

	for ($i = 0; $i <= 255; $i++) {
		if (!isset($cc2gn[$i])) {
			$cc2gn[$i] = '.notdef';
		}
	}

	return $cc2gn;
}

function ReadUFM($file, &$cidtogidmap)
{
	$cidtogidmap = str_pad('', 256 * 256 * 2, "\x00");
	$a = file($file);

	if (empty($a)) {
		exit('File not found');
	}

	$widths = array();
	$fm = array();

	foreach ($a as $l) {
		$e = explode(' ', chop($l));

		if (count($e) < 2) {
			continue;
		}

		$code = $e[0];
		$param = $e[1];

		if ($code == 'U') {
			$cc = (int) $e[1];

			if ($cc != -1) {
				$gn = $e[7];
				$w = $e[4];
				$glyph = $e[10];
				$widths[$cc] = $w;

				if ($cc == ord('X')) {
					$fm['CapXHeight'] = $e[13];
				}

				if ((0 <= $cc) && ($cc < 65535) && $glyph) {
					$cidtogidmap[$cc * 2] = chr($glyph >> 8);
					$cidtogidmap[($cc * 2) + 1] = chr($glyph & 255);
				}
			}

			if (isset($gn) && ($gn == '.notdef') && !isset($fm['MissingWidth'])) {
				$fm['MissingWidth'] = $w;
			}
		}
		else if ($code == 'FontName') {
			$fm['FontName'] = $param;
		}
		else if ($code == 'Weight') {
			$fm['Weight'] = $param;
		}
		else if ($code == 'ItalicAngle') {
			$fm['ItalicAngle'] = (double) $param;
		}
		else if ($code == 'Ascender') {
			$fm['Ascender'] = (int) $param;
		}
		else if ($code == 'Descender') {
			$fm['Descender'] = (int) $param;
		}
		else if ($code == 'UnderlineThickness') {
			$fm['UnderlineThickness'] = (int) $param;
		}
		else if ($code == 'UnderlinePosition') {
			$fm['UnderlinePosition'] = (int) $param;
		}
		else if ($code == 'IsFixedPitch') {
			$fm['IsFixedPitch'] = $param == 'true';
		}
		else if ($code == 'FontBBox') {
			$fm['FontBBox'] = array($e[1], $e[2], $e[3], $e[4]);
		}
		else if ($code == 'CapHeight') {
			$fm['CapHeight'] = (int) $param;
		}
		else if ($code == 'StdVW') {
			$fm['StdVW'] = (int) $param;
		}
	}

	if (!isset($fm['MissingWidth'])) {
		$fm['MissingWidth'] = 600;
	}

	if (!isset($fm['FontName'])) {
		exit('FontName not found');
	}

	$fm['Widths'] = $widths;
	return $fm;
}

function ReadAFM($file, &$map)
{
	$a = file($file);

	if (empty($a)) {
		exit('File not found');
	}

	$widths = array();
	$fm = array();
	$fix = array('Edot' => 'Edotaccent', 'edot' => 'edotaccent', 'Idot' => 'Idotaccent', 'Zdot' => 'Zdotaccent', 'zdot' => 'zdotaccent', 'Odblacute' => 'Ohungarumlaut', 'odblacute' => 'ohungarumlaut', 'Udblacute' => 'Uhungarumlaut', 'udblacute' => 'uhungarumlaut', 'Gcedilla' => 'Gcommaaccent', 'gcedilla' => 'gcommaaccent', 'Kcedilla' => 'Kcommaaccent', 'kcedilla' => 'kcommaaccent', 'Lcedilla' => 'Lcommaaccent', 'lcedilla' => 'lcommaaccent', 'Ncedilla' => 'Ncommaaccent', 'ncedilla' => 'ncommaaccent', 'Rcedilla' => 'Rcommaaccent', 'rcedilla' => 'rcommaaccent', 'Scedilla' => 'Scommaaccent', 'scedilla' => 'scommaaccent', 'Tcedilla' => 'Tcommaaccent', 'tcedilla' => 'tcommaaccent', 'Dslash' => 'Dcroat', 'dslash' => 'dcroat', 'Dmacron' => 'Dcroat', 'dmacron' => 'dcroat', 'combininggraveaccent' => 'gravecomb', 'combininghookabove' => 'hookabovecomb', 'combiningtildeaccent' => 'tildecomb', 'combiningacuteaccent' => 'acutecomb', 'combiningdotbelow' => 'dotbelowcomb', 'dongsign' => 'dong');

	foreach ($a as $l) {
		$e = explode(' ', rtrim($l));

		if (count($e) < 2) {
			continue;
		}

		$code = $e[0];
		$param = $e[1];

		if ($code == 'C') {
			$cc = (int) $e[1];
			$w = $e[4];
			$gn = $e[7];

			if (substr($gn, -4) == '20AC') {
				$gn = 'Euro';
			}

			if (isset($fix[$gn])) {
				foreach ($map as $c => $n) {
					if ($n == $fix[$gn]) {
						$map[$c] = $gn;
					}
				}
			}

			if (empty($map)) {
				$widths[$cc] = $w;
			}
			else {
				$widths[$gn] = $w;

				if ($gn == 'X') {
					$fm['CapXHeight'] = $e[13];
				}
			}

			if ($gn == '.notdef') {
				$fm['MissingWidth'] = $w;
			}
		}
		else if ($code == 'FontName') {
			$fm['FontName'] = $param;
		}
		else if ($code == 'Weight') {
			$fm['Weight'] = $param;
		}
		else if ($code == 'ItalicAngle') {
			$fm['ItalicAngle'] = (double) $param;
		}
		else if ($code == 'Ascender') {
			$fm['Ascender'] = (int) $param;
		}
		else if ($code == 'Descender') {
			$fm['Descender'] = (int) $param;
		}
		else if ($code == 'UnderlineThickness') {
			$fm['UnderlineThickness'] = (int) $param;
		}
		else if ($code == 'UnderlinePosition') {
			$fm['UnderlinePosition'] = (int) $param;
		}
		else if ($code == 'IsFixedPitch') {
			$fm['IsFixedPitch'] = $param == 'true';
		}
		else if ($code == 'FontBBox') {
			$fm['FontBBox'] = array($e[1], $e[2], $e[3], $e[4]);
		}
		else if ($code == 'CapHeight') {
			$fm['CapHeight'] = (int) $param;
		}
		else if ($code == 'StdVW') {
			$fm['StdVW'] = (int) $param;
		}
	}

	if (!isset($fm['FontName'])) {
		exit('FontName not found');
	}

	if (!empty($map)) {
		if (!isset($widths['.notdef'])) {
			$widths['.notdef'] = 600;
		}

		if (!isset($widths['Delta']) && isset($widths['increment'])) {
			$widths['Delta'] = $widths['increment'];
		}

		for ($i = 0; $i <= 255; $i++) {
			if (!isset($widths[$map[$i]])) {
				print('Warning: character ' . $map[$i] . " is missing\n");
				$widths[$i] = $widths['.notdef'];
			}
			else {
				$widths[$i] = $widths[$map[$i]];
			}
		}
	}

	$fm['Widths'] = $widths;
	return $fm;
}

function MakeFontDescriptor($fm, $symbolic = false)
{
	$asc = (isset($fm['Ascender']) ? $fm['Ascender'] : 1000);
	$fd = 'array(\'Ascent\'=>' . $asc;
	$desc = (isset($fm['Descender']) ? $fm['Descender'] : -200);
	$fd .= ',\'Descent\'=>' . $desc;

	if (isset($fm['CapHeight'])) {
		$ch = $fm['CapHeight'];
	}
	else if (isset($fm['CapXHeight'])) {
		$ch = $fm['CapXHeight'];
	}
	else {
		$ch = $asc;
	}

	$fd .= ',\'CapHeight\'=>' . $ch;
	$flags = 0;
	if (isset($fm['IsFixedPitch']) && $fm['IsFixedPitch']) {
		$flags += 1 << 0;
	}

	if ($symbolic) {
		$flags += 1 << 2;
	}
	else {
		$flags += 1 << 5;
	}

	if (isset($fm['ItalicAngle']) && ($fm['ItalicAngle'] != 0)) {
		$flags += 1 << 6;
	}

	$fd .= ',\'Flags\'=>' . $flags;

	if (isset($fm['FontBBox'])) {
		$fbb = $fm['FontBBox'];
	}
	else {
		$fbb = array(0, $desc - 100, 1000, $asc + 100);
	}

	$fd .= ',\'FontBBox\'=>\'[' . $fbb[0] . ' ' . $fbb[1] . ' ' . $fbb[2] . ' ' . $fbb[3] . ']\'';
	$ia = (isset($fm['ItalicAngle']) ? $fm['ItalicAngle'] : 0);
	$fd .= ',\'ItalicAngle\'=>' . $ia;

	if (isset($fm['StdVW'])) {
		$stemv = $fm['StdVW'];
	}
	else {
		if (isset($fm['Weight']) && preg_match('/(bold|black)/i', $fm['Weight'])) {
			$stemv = 120;
		}
		else {
			$stemv = 70;
		}
	}

	$fd .= ',\'StemV\'=>' . $stemv;

	if (isset($fm['MissingWidth'])) {
		$fd .= ',\'MissingWidth\'=>' . $fm['MissingWidth'];
	}

	$fd .= ')';
	return $fd;
}

function MakeWidthArray($fm)
{
	$s = 'array(';
	$cw = $fm['Widths'];
	$els = array();
	$c = 0;

	foreach ($cw as $i => $w) {
		if (is_numeric($i)) {
			$els[] = (($c++ % 10) == 0 ? "\n" : '') . $i . '=>' . $w;
		}
	}

	$s .= implode(',', $els);
	$s .= ')';
	return $s;
}

function MakeFontEncoding($map)
{
	$ref = readmap('cp1252');
	$s = '';
	$last = 0;

	for ($i = 32; $i <= 255; $i++) {
		if ($map[$i] != $ref[$i]) {
			if ($i != ($last + 1)) {
				$s .= $i . ' ';
			}

			$last = $i;
			$s .= '/' . $map[$i] . ' ';
		}
	}

	return rtrim($s);
}

function SaveToFile($file, $s, $mode = 't')
{
	$f = fopen($file, 'w' . $mode);

	if (!$f) {
		exit('Can\'t write to file ' . $file);
	}

	fwrite($f, $s, strlen($s));
	fclose($f);
}

function ReadShort($f)
{
	$a = unpack('n1n', fread($f, 2));
	return $a['n'];
}

function ReadLong($f)
{
	$a = unpack('N1N', fread($f, 4));
	return $a['N'];
}

function CheckTTF($file)
{
	$f = fopen($file, 'rb');

	if (!$f) {
		exit('Error: unable to open ' . $file);
	}

	fseek($f, 4, SEEK_CUR);
	$nb = readshort($f);
	fseek($f, 6, SEEK_CUR);
	$found = false;

	for ($i = 0; $i < $nb; $i++) {
		if (fread($f, 4) == 'OS/2') {
			$found = true;
			break;
		}

		fseek($f, 12, SEEK_CUR);
	}

	if (!$found) {
		fclose($f);
		return NULL;
	}

	fseek($f, 4, SEEK_CUR);
	$offset = readlong($f);
	fseek($f, $offset, SEEK_SET);
	fseek($f, 8, SEEK_CUR);
	$fsType = readshort($f);
	$rl = ($fsType & 2) != 0;
	$pp = ($fsType & 4) != 0;
	$e = ($fsType & 8) != 0;
	fclose($f);
	if ($rl && !$pp && !$e) {
		print("Warning: font license does not allow embedding\n");
	}
}

$arg = $GLOBALS['argv'];

if (3 <= count($arg)) {
	ob_start();
	array_shift($arg);

	if (sizeof($arg) == 3) {
		$arg[3] = $arg[2];
		$arg[2] = true;
	}
	else {
		if (!isset($arg[2])) {
			$arg[2] = true;
		}

		if (!isset($arg[3])) {
			$arg[3] = 'cp1252';
		}
	}

	if (!isset($arg[4])) {
		$arg[4] = array();
	}

	makefont($arg[0], $arg[1], $arg[2], $arg[3], $arg[4]);
	$t = ob_get_clean();
	print(preg_replace('!<BR( /)?>!i', "\n", $t));
}
else {
	print("Usage: makefont.php <ttf/otf/pfb file> <afm/ufm file> <encoding> <patch>\n");
}

?>
