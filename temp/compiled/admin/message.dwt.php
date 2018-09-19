<!doctype html>
<html>
<head><?php echo $this->fetch('library/admin_html_head.lbi'); ?></head>
<body>
<div class="list-div">
    <div class="fh_message">
        <div class="fr_content">
            <div class="success_img">
                <?php if ($this->_var['msg_type'] == 0): ?>
                <img src="images/success.jpg">
                <?php elseif ($this->_var['msg_type'] == 1): ?>
                <img src="images/error.jpg">
                <?php else: ?>
                <img src="images/tooltip.jpg">
                <?php endif; ?>
            </div>
            <div class="success_right">
                <h3 class="title"><?php echo $this->_var['msg_detail']; ?></h3>
                <?php if ($this->_var['auto_redirect']): ?><span class="ts" id="redirectionMsg"><?php echo $this->_var['lang']['auto_redirection']; ?></span><?php endif; ?>
                <ul class="msg-link">
                    <?php $_from = $this->_var['links']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'link');if (count($_from)):
    foreach ($_from AS $this->_var['link']):
?>
                    <li><a href="<?php echo $this->_var['link']['href']; ?>" <?php if ($this->_var['link']['target']): ?>target="<?php echo $this->_var['link']['target']; ?>"<?php endif; ?>><?php echo $this->_var['link']['text']; ?></a></li>
                    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php if ($this->_var['auto_redirect']): ?>
<script type="text/javascript">
var seconds = 3;
var defaultUrl = "<?php echo $this->_var['default_url']; ?>";

$(function(){
	if(document.getElementById('redirectionMsg') && defaultUrl == 'javascript:history.go(-1)' && window.history.length == 0){
		document.getElementById('redirectionMsg').innerHTML = '';
		return;
	}
	
	window.setInterval(redirection, 1000);
});

function redirection(){
	if (seconds <= 0){
		window.clearInterval();
		return;
	}
	
	seconds --;
	document.getElementById('spanSeconds').innerHTML = seconds;
	
	if(seconds == 0){
		location.href = defaultUrl;
	}
}
</script>
<?php endif; ?>
</body>
</html>