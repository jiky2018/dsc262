<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function print_textinputs_var()
{
	global $textinputs;

	foreach ($textinputs as $key => $val) {
		echo 'textinputs[' . $key . '] = decodeURIComponent("' . $val . "\");\n";
	}
}

function print_textindex_decl($text_input_idx)
{
	echo 'words[' . $text_input_idx . "] = [];\n";
	echo 'suggs[' . $text_input_idx . "] = [];\n";
}

function print_words_elem($word, $index, $text_input_idx)
{
	echo 'words[' . $text_input_idx . '][' . $index . '] = \'' . escape_quote($word) . "';\n";
}

function print_suggs_elem($suggs, $index, $text_input_idx)
{
	echo 'suggs[' . $text_input_idx . '][' . $index . '] = [';

	foreach ($suggs as $key => $val) {
		if ($val) {
			echo '\'' . escape_quote($val) . '\'';

			if (($key + 1) < count($suggs)) {
				echo ', ';
			}
		}
	}

	echo "];\n";
}

function escape_quote($str)
{
	return preg_replace('/\'/', '\\\'', $str);
}

function error_handler($err)
{
	echo 'error = \'' . preg_replace('/[\'\\\\]/', '\\\\$0', $err) . "';\n";
}

function print_checker_results()
{
	global $aspell_prog;
	global $aspell_opts;
	global $tempfiledir;
	global $textinputs;
	global $input_separator;
	$aspell_err = '';
	$tempfile = tempnam($tempfiledir, 'aspell_data_');

	if ($fh = fopen($tempfile, 'w')) {
		for ($i = 0; $i < count($textinputs); $i++) {
			$text = urldecode($textinputs[$i]);
			$text = preg_replace('/<[^>]+>/', ' ', $text);
			$lines = explode("\n", $text);
			fwrite($fh, "%\n");
			fwrite($fh, '^' . $input_separator . "\n");
			fwrite($fh, "!\n");

			foreach ($lines as $key => $value) {
				fwrite($fh, '^' . $value . "\n");
			}
		}

		fclose($fh);
		$cmd = $aspell_prog . ' ' . $aspell_opts . ' < ' . $tempfile . ' 2>&1';

		if ($aspellret = shell_exec($cmd)) {
			$linesout = explode("\n", $aspellret);
			$index = 0;
			$text_input_index = -1;

			foreach ($linesout as $key => $val) {
				$chardesc = substr($val, 0, 1);
				if (($chardesc == '&') || ($chardesc == '#')) {
					$line = explode(' ', $val, 5);
					print_words_elem($line[1], $index, $text_input_index);

					if (isset($line[4])) {
						$suggs = explode(', ', $line[4]);
					}
					else {
						$suggs = array();
					}

					print_suggs_elem($suggs, $index, $text_input_index);
					$index++;
				}
				else if ($chardesc == '*') {
					$text_input_index++;
					print_textindex_decl($text_input_index);
					$index = 0;
				}
				else {
					if (($chardesc != '@') && ($chardesc != '')) {
						$aspell_err .= $val;
					}
				}
			}

			if ($aspell_err) {
				$aspell_err = 'Error executing';
				error_handler($aspell_err);
			}
		}
		else {
			error_handler('System error');
		}
	}
	else {
		error_handler('System error');
	}

	unlink($tempfile);
}

function addslashes_d($val)
{
	if (empty($value)) {
		return $value;
	}
	else {
		return is_array($value) ? array_map('addslashes_d', $value) : addslashes($value);
	}
}

error_reporting(0);
header('Content-type: text/html; charset=utf-8');
$aspell_prog = '"C:\\Program Files\\Aspell\\bin\\aspell.exe"';
$lang = 'en_US';
$aspell_opts = '-a --lang=' . $lang . ' --encoding=utf-8 -H --rem-sgml-check=alt';
$tempfiledir = './';
$spellercss = '../spellerStyle.css';
$word_win_src = '../wordWindow.js';

if (!get_magic_quotes_gpc()) {
	$_POST['textinputs'] = addslashes_d($_POST['textinputs']);
}

$textinputs = $_POST['textinputs'];
$input_separator = 'A';
echo "<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">\r\n<link rel=\"stylesheet\" type=\"text/css\" href=\"";
echo $spellercss;
echo "\" />\r\n<script language=\"javascript\" src=\"";
echo $word_win_src;
echo "\"></script>\r\n<script language=\"javascript\">\r\nvar suggs = new Array();\r\nvar words = new Array();\r\nvar textinputs = new Array();\r\nvar error;\r\n";
print_textinputs_var();
print_checker_results();
echo "\r\nvar wordWindowObj = new wordWindow();\r\nwordWindowObj.originalSpellings = words;\r\nwordWindowObj.suggestions = suggs;\r\nwordWindowObj.textInputs = textinputs;\r\n\r\nfunction init_spell() {\r\n    // check if any error occured during server-side processing\r\n    if( error ) {\r\n        alert( error );\r\n    } else {\r\n        // call the init_spell() function in the parent frameset\r\n        if (parent.frames.length) {\r\n            parent.init_spell( wordWindowObj );\r\n        } else {\r\n            alert('This page was loaded outside of a frameset. It might not display properly');\r\n        }\r\n    }\r\n}\r\n\r\n\r\n\r\n</script>\r\n\r\n</head>\r\n<!-- <body onLoad=\"init_spell();\">        by FredCK -->\r\n<body onLoad=\"init_spell();\" bgcolor=\"#ffffff\">\r\n\r\n<script type=\"text/javascript\">\r\nwordWindowObj.writeBody();\r\n</script>\r\n\r\n</body>\r\n</html>\r\n";

?>
