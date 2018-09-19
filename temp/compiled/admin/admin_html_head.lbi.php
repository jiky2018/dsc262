<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title><?php echo $this->_var['lang']['cp_home']; ?><?php if ($this->_var['ur_here']): ?> - <?php echo $this->_var['ur_here']; ?> <?php endif; ?></title>
<meta name="robots" content="noindex, nofollow">
<link rel="shortcut icon" href="../favicon.ico" />
<link rel="icon" href="../animated_favicon.gif" type="image/gif" />

<link rel="stylesheet" type="text/css" href="css/main.css" />
<link rel="stylesheet" type="text/css" href="css/iconfont.css" />
<link rel="stylesheet" type="text/css" href="css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="css/purebox.css" />
<link rel="stylesheet" type="text/css" href="css/order-new.css?v=2" />
<link rel="stylesheet" type="text/css" href="../js/jquery-ui/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="../js/spectrum-master/spectrum.css" />
<link rel="stylesheet" type="text/css" href="../js/perfect-scrollbar/perfect-scrollbar.min.css" />
<link rel="stylesheet" type="text/css" href="../js/calendar/calendar.min.css" />
<?php echo $this->smarty_insert_scripts(array('files'=>'../js/jquery-1.9.1.min.js,../js/jquery.json.js,../js/transport_jquery.js,../js/utils.js,../js/perfect-scrollbar/perfect-scrollbar.min.js,../js/calendar/calendar.min.js,../js/jquery.form.js,../js/jquery.nyroModal.js,../js/jquery.validation.min.js,../js/jquery.cookie.js,../js/lib_ecmobanFunc.js,../js/jquery-ui/jquery-ui.min.js,common.js,listtable.js,listtable_pb.js,dsc_admin2.0.js,jquery.bgColorSelector.js')); ?>
<script type="text/javascript">
/*这里把JS用到的所有语言都赋值到这里*/
<?php $_from = $this->_var['lang']['js_languages']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['item']):
?>
var <?php echo $this->_var['key']; ?> = "<?php echo $this->_var['item']; ?>";
<?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
</script>