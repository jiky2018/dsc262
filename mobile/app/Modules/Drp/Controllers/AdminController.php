<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Drp\Controllers;
class AdminController extends \App\Modules\Base\Controllers\FrontendController
{
	public function __construct()
	{
		parent::__construct();
		L(require MODULE_PATH . 'Language/' . C('shop.lang') . '/drp.php');
		$this->assign('lang', array_change_key_case(L()));
		$files = array('ecmoban');
		$this->load_helper($files);
		$this->init_params();
	}

	private function init_params()
	{
		if (IS_POST) {
			$page_num = I('page_num', 0, 'intval');

			if (0 < $page_num) {
				cookie('ECSCP[page_size]', $page_num);
				exit(json_encode(array('status' => 1)));
			}
		}

		$this->page_num = isset($_COOKIE['ECSCP']['page_size']) && !empty($_COOKIE['ECSCP']['page_size']) ? $_COOKIE['ECSCP']['page_size'] : 10;
		$this->assign('page_num', $this->page_num);
	}

	public function actionConfig()
	{
		$this->admin_priv('drp_config');

		if (IS_POST) {
			$data_list = I('post.data');

			if (empty($data_list)) {
				$this->message(L('request_error'), null, 2);
			}

			foreach ($data_list as $k => $v) {
				$where = array();
				$data = array();
				$where['code'] = $k;
				$data['value'] = $v;
				$this->model->table('drp_config')->data($data)->where($where)->save();
			}

			$this->redirect('config');
		}

		$config = $this->model->table('drp_config')->order('sort_order ASC')->select();

		foreach ($config as $key => $value) {
			if ($value['code'] == 'drp_affiliate') {
				unset($config[$key]);
			}

			if ($value['type'] == 'hidden') {
				unset($config[$key]);
			}
		}

		$this->assign('list', $config);
		$this->display();
	}

	public function actionDrpsetconfig()
	{
		$this->admin_priv('drp_config');

		if (IS_POST) {
			$status = input('status');
			$data = input('post.data');
			$cfg_value = input('post.cfg_value');

			if ($data['day'] < 7) {
				$this->message('佣金可提现时间不能小于7天', null, 2);
			}

			$case_one_1 = $cfg_value['credit_j'][0] + $cfg_value['credit_j'][1] + $cfg_value['credit_j'][2];
			$case_one_2 = $cfg_value['credit_y'][0] + $cfg_value['credit_y'][1] + $cfg_value['credit_y'][2];
			$case_one_3 = $cfg_value['credit_t'][0] + $cfg_value['credit_t'][1] + $cfg_value['credit_t'][2];
			if ((100 < $case_one_1) || (100 < $case_one_2) || (100 < $case_one_3)) {
				$this->message('分销等级佣金比例之和不能大于100%', null, 2);
			}

			$drp_affiliate['config'] = $data;

			foreach ($cfg_value as $key => $val) {
				for ($i = 0; $i < count($val); $i++) {
					if (strpos($val[$i], '%') === false) {
						$val[$i] .= '%';
					}

					$arr[$i][$key] = $val[$i];
				}
			}

			$drp_affiliate['item'] = $arr;
			$drp_affiliate['on'] = $status;
			dao('drp_config')->data(array('value' => serialize($drp_affiliate)))->where(array('code' => 'drp_affiliate'))->save();
		}

		$drp_affiliate = get_drp_affiliate_config();
		$this->assign('drp_a_config', $drp_affiliate);
		$user_credit = dao('drp_user_credit')->field('credit_name')->order('min_money ASC')->limit(0, 3)->select();
		$this->assign('user_credit', $user_credit);
		$this->display();
	}

	public function actionDrpSetQrcode()
	{
		if (IS_POST) {
			$data = input('data', '', 'trim');
			$pic_path = input('file_path', '', 'trim');
			$pic_path = ltrim($pic_path, '/');

			if (empty($data)) {
				exit(json_encode(array('error' => 1, 'msg' => '数据不能为空')));
			}

			if (!empty($data['description']) && (100 < strlen($data['description']))) {
				exit(json_encode(array('error' => 1, 'msg' => '文字内容不能超过100字符！')));
			}

			if (!empty($pic_path)) {
				$img_info = getimagesize(dirname(ROOT_PATH) . '/' . $pic_path);

			}

			if ($_FILES['zip_pic'] && $_FILES['pic']) {
				$_FILES['pic'] = $_FILES['zip_pic'];
				$_FILES['pic']['name'] = 'base64.png';
				unset($_FILES['zip_pic']);
			}

			if ($_FILES['pic']['name']) {

				$type = array('image/jpeg', 'image/png');

				if (!in_array($_FILES['pic']['type'], $type)) {
					exit(json_encode(array('error' => 1, 'msg' => L('not_file_type'))));
				}

				$size = round($_FILES['pic']['size'] / (1024 * 1024), 2);

				if (1 < $size) {
					exit(json_encode(array('error' => 1, 'msg' => L('file_size_limit'))));
				}

				$result = $this->upload('data/attached/qrcode/themes', true, 1,false,2);

				if (0 < $result['error']) {
					exit(json_encode(array('error' => 1, 'msg' => $result['message']))); 
				}

				$data['file'] = $result['url'];
			}
			else {
				$data['file'] = $pic_path;
			}
       

			$config = array(
				'backbround'  => $data['file'],
				'qr_position' => array('left' => $data['qr_left'], 'top' => $data['qr_top']),
				'avatar'      => isset($data['avatar']) ? $data['avatar'] : 1,
				'av_position' => array('left' => $data['av_left'], 'top' => $data['av_top']),
				'text'        => array('description' => $data['description'], 'color' => $data['color'])
				);
			$config = serialize($config);
			$res = dao('drp_config')->where(array('code' => 'qrcode'))->find();

			if (empty($res)) {
				$datas = array('code' => 'qrcode', 'type' => 'hidden', 'value' => $config, 'name' => '名片二维码配置');
				dao('drp_config')->data($datas)->add();
				exit(json_encode(array('error' => 0, 'msg' => L('edit_success'))));
			}
			else {
				dao('drp_config')->data(array('value' => $config))->where(array('code' => 'qrcode'))->save();
				exit(json_encode(array('error' => 0, 'msg' => L('edit_success'))));
			}
		}

		$info = get_qrcode_config();
		$info['avatar'] = !empty($info['avatar']) ? $info['avatar'] : 1;
		$info['backbround'] = '/'.$info['backbround'];

		if (strpos($info['backbround'], 'no_image') == true) {
			$info['backbround'] = '/data/attached/qrcode/drp_bg.png';
		}

		$text_description = nl2br(str_replace(array('\\r\\n', '\\n', '\\r'), '<br />', htmlspecialchars($info['text']['description'])));
		$this->assign('show_text_desc', $text_description);
		$imgList = array();
		$dir = dirname(ROOT_PATH) . '/data/attached/qrcode/themes/';

		$img_num = 0;

		if (is_dir($dir)) {
			if ($dh = opendir($dir)) {
				while (($file = readdir($dh)) != false) {
					list($filesname, $kzm) = explode('.', $file);
					if (($kzm == 'png') || ($kzm == 'jpg') || ($kzm == 'JPG')) {
						if (!is_dir('./' . $file)) {
							$imgList[] = $file;
							$img_num++;
						}
					}
				}

				closedir($dh);
			}
		}

		foreach ($imgList as $key => $val) {
			$imgList[$key] = '/data/attached/qrcode/themes/' . $val;
		}

		$this->assign('imglist', $imgList);
		$this->assign('info', $info);
		$this->assign('time', gmtime());
		$this->display();
	}

	public function actionRemoveBg($path)
	{
		if (IS_AJAX) {
			$path = input('path', '', 'trim');

			if (!empty($path)) {
				$path = (strpos($path, 'drp_bg') == false ? $path : '');
				$this->remove($path);
				$result = array('error' => 0, 'msg' => '删除成功');
			}
			else {
				$result = array('error' => 1, 'msg' => '删除失败');
			}

			exit(json_encode($result));
		}
	}

	public function actionShop()
	{
		$this->admin_priv('drp_shop');
		$user_id = I('get.user_id', 0, 'intval');
		$where = '';

		if (IS_POST) {
			$keyword = I('post.keyword', '', 'trim');
			$user_id = I('post.user_id');

			if (!empty($keyword)) {
				$where = ' and (s.shop_name like "%' . $keyword . '%" or s.real_name like "%' . $keyword . '%" or s.mobile like "%' . $keyword . '%" or u.user_name like "%' . $keyword . '%")';
			}
		}

		$shop_name = '';

		if ($user_id) {
			$shop_name = dao('drp_shop')->where(array('user_id' => $user_id))->getField('shop_name');
			$where .= ' AND drp_parent_id = \'' . $user_id . '\'';
		}

		$filter['user_id'] = $user_id;
		$offset = $this->pageLimit(url('shop', $filter), $this->page_num);
		$sql_count = 'SELECT count(*) as count FROM {pre}drp_shop s LEFT JOIN {pre}users u ON s.user_id = u.user_id WHERE 1 ' . $where . ' ORDER BY create_time DESC';
		$total = $this->model->query($sql_count);
		$this->assign('page', $this->pageShow($total[0]['count']));
		$sql = 'SELECT s.*, u.user_name ,u.drp_parent_id FROM {pre}drp_shop s LEFT JOIN {pre}users u ON s.user_id = u.user_id WHERE 1 ' . $where . ' ORDER BY create_time DESC LIMIT ' . $offset;
		$list = $this->model->query($sql);

		foreach ($list as $key => $val) {
			if (0 < $val['drp_parent_id']) {
				$list[$key]['parent_name'] = parent_name($val['drp_parent_id']);
			}

			$res = drp_rank_info($val['user_id']);
			$list[$key]['credit_name'] = $res['credit_name'];
			$list[$key]['create_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['create_time']);
		}

		$this->assign('list', $list);
		$this->assign('user_id', $user_id);
		$this->assign('shop_name', $shop_name);
		$this->display();
	}

	public function actionDrpAffList()
	{
		$this->admin_priv('drp_shop');
		$up_uid = input('auid', 0, 'intval');
		$level = input('level', 1, 'intval');
		$keyword = input('keyword', '', 'trim');
		$user_list = array();
		$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
		empty($affiliate) && ($affiliate = array());
		$num = count($affiliate['item']);
		$select = array();

		for ($i = 1; $i <= $num; $i++) {
			$select[$i] = $i;
		}

		$this->assign('select', $select);
		$this->assign('current_level', $level);
		$this->assign('auid', $up_uid);
		$filter['auid'] = $up_uid;
		$filter['level'] = $level;
		$offset = $this->pageLimit(url('drp_aff_list', $filter), $this->page_num);
		$user_name = dao('users')->where(array('user_id' => $up_uid))->getField('user_name');
		$this->assign('user_name', $user_name);
		$where = '';

		if (!empty($keyword)) {
			$where = ' and user_name  like "%' . $keyword . '%"';
		}

		$all_count = 0;

		for ($i = 1; $i <= $level; $i++) {
			$count = 0;

			if ($up_uid) {
				$sql = 'SELECT user_id FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE drp_parent_id IN(' . $up_uid . ')';
				$query = $GLOBALS['db']->query($sql);
				$up_uid = '';

				foreach ($query as $key => $value) {
					$up_uid .= ($up_uid ? ',\'' . $value['user_id'] . '\'' : '\'' . $value['user_id'] . '\'');
					$count++;
				}
			}

			$all_count += $count;
			if ($count && ($level == $i)) {
				$sql = 'SELECT count(user_id) as num, \'' . $i . '\' AS level ' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id IN(' . $up_uid . ') ';
				$level_count = $GLOBALS['db']->query($sql);
				$sql = 'SELECT user_id, user_name, \'' . $i . '\' AS level, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ' . ' FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id IN(' . $up_uid . ') ' . $where . ' ORDER by level' . ' LIMIT ' . $offset;
				$user_list = array_merge($user_list, $GLOBALS['db']->getAll($sql));
			}
		}

		$temp_count = count($user_list);

		for ($i = 0; $i < $temp_count; $i++) {
			$user_list[$i]['reg_time'] = local_date(C('shop.date_format'), $user_list[$i]['reg_time']);
			$user_list[$i]['edit_url'] = '../' . ADMIN_PATH . '/users.php?act=edit&id=' . $user_list[$i]['user_id'];
			$user_list[$i]['address_list'] = '../' . ADMIN_PATH . '/users.php?act=address_list&id=' . $user_list[$i]['user_id'];
			$user_list[$i]['order_list'] = '../' . ADMIN_PATH . '/order.php?act=list&user_id=' . $user_list[$i]['user_id'];
			$user_list[$i]['account_log'] = '../' . ADMIN_PATH . '/account_log.php?act=list&user_id=' . $user_list[$i]['user_id'];
		}

		foreach ($user_list as $k => $val) {
			if ($val['level'] != $level) {
				unset($user_list[$k]);
			}
		}

		$this->assign('page', $this->pageShow($level_count[0]['num']));
		$this->assign('user_list', $user_list);
		$this->display();
	}

	public function actionSetShop()
	{
		$this->admin_priv('drp_shop');
		$id = I('get.id', 0, 'intval');

		if (empty($id)) {
			$this->message(L('select_shop'), null, 2);
		}

		if (isset($_GET['audit'])) {
			$data['audit'] = I('get.audit', 1, 'intval');
		}

		if (isset($_GET['status'])) {
			$data['status'] = I('get.status', 0, 'intval');
		}

		if (!empty($data)) {
			dao('drp_shop')->data($data)->where(array('id' => $id))->save();
		}

		$this->redirect('shop');
	}

	public function actionExportShop()
	{
		$this->admin_priv('drp_shop');

		if (IS_POST) {
			$starttime = I('post.starttime', '', 'local_strtotime');
			$endtime = I('post.endtime', '', 'local_strtotime');
			$user_id = I('post.user_id', 0, 'intval');
			if (empty($starttime) || empty($endtime)) {
				$this->message(L('select_start_end_time'), null, 2);
			}

			if ($endtime < $starttime) {
				$this->message(L('start_lt_end_time'), null, 2);
			}

			$where = '';

			if ($user_id) {
				$where .= ' AND u.drp_parent_id = \'' . $user_id . '\'';
			}

			$sql = 'SELECT d.*,u.drp_parent_id FROM {pre}drp_shop as d LEFT JOIN {pre}users as u on d.user_id = u.user_id WHERE 1 ' . $where . ' AND d.create_time >= \'' . $starttime . '\' AND d.create_time <= \'' . $endtime . '\' ORDER BY d.create_time DESC';
			$list = $this->model->query($sql);

			if ($list) {
				$excel = new \PHPExcel();
				$excel->getActiveSheet()->getDefaultColumnDimension()->setAutoSize(true);
				$excel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
				$excel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
				$excel->getActiveSheet()->getColumnDimension('H')->setWidth(25);
				$excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
				$excel->getActiveSheet()->getColumnDimension('J')->setWidth(20);
				$rowVal = array(L('shop_number'), L('shop_name'), L('rely_name'), L('mobile'), L('open_time'), L('shop_audit'), L('shop_state'), L('qq_number'));

				foreach ($rowVal as $k => $r) {
					$excel->getActiveSheet()->getStyleByColumnAndRow($k, 1)->getFont()->setBold(true);
					$excel->getActiveSheet()->getStyleByColumnAndRow($k, 1)->getAlignment();
					$excel->getActiveSheet()->setCellValueByColumnAndRow($k, 1, $r);
				}

				$excel->setActiveSheetIndex(0);
				$objActSheet = $excel->getActiveSheet();
				$title = '分销商信息';
				$objActSheet->setTitle($title);

				foreach ($list as $k => $v) {
					$num = $k + 2;
					$excel->setActiveSheetIndex(0)->setCellValue('A' . $num, $v['id'])->setCellValue('B' . $num, $v['shop_name'])->setCellValue('C' . $num, $v['real_name'])->setCellValue('D' . $num, $v['mobile'])->setCellValue('E' . $num, local_date('Y-m-d H:i:s', $v['create_time']))->setCellValue('F' . $num, $v['audit'])->setCellValue('G' . $num, $v['status'])->setCellValue('H' . $num, $v['qq']);
				}

				$name = date('Y-m-d');
				header('Content-Type: application/force-download');
				header('Content-Type: application/octet-stream');
				header('Content-Type: application/download');
				header('Content-Transfer-Encoding:utf-8');
				header('Pragma: no-cache');
				header('Content-Type: application/vnd.ms-e xcel');
				header('Content-Disposition: attachment;filename="' . $title . '_' . urlencode($name) . '.xls"');
				header('Cache-Control: max-age=0');
				$objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel5');
				$objWriter->save('php://output');
				exit();
			}
		}

		$this->redirect('shop');
	}

	public function actionDrpUserCredit()
	{
		$this->admin_priv('drp_shop');
		$list = drp_credit_info_all();
		$this->assign('list', $list);
		$this->display();
	}

	public function actionDrpUserCreditEdit()
	{
		$this->admin_priv('drp_shop');

		if (IS_AJAX) {
			$id = input('id', 0, 'intval');
			$data = input('data', '', 'trim');

			if ($id) {
				if (empty($data['credit_name'])) {
					exit(json_encode(array('error' => 1, 'msg' => '分销商等级名称不能为空')));
				}

				if (!is_only('credit_name', $data['credit_name'], 0, 'id != \'' . $id . '\'')) {
					exit(json_encode(array('error' => 1, 'msg' => '分销商等级名称已经存在')));
				}

				if ($data['max_money'] <= $data['min_money']) {
					exit(json_encode(array('error' => 1, 'msg' => '佣金上限必须大于佣金下限')));
				}

				if (!is_only('min_money', $data['min_money'], 0, 'id != \'' . $id . '\'')) {
					exit(json_encode(array('error' => 1, 'msg' => '最小佣金值' . $data['min_money'] . '已经存在！请重新输入')));
				}

				if (!is_only('max_money', $data['max_money'], 0, 'id != \'' . $id . '\'')) {
					exit(json_encode(array('error' => 1, 'msg' => '最大佣金值' . $data['max_money'] . '已经存在！请重新输入')));
				}

				dao('drp_user_credit')->data($data)->where(array('id' => $id))->save();
				$result = array('error' => 0, 'msg' => '编辑成功');
				exit(json_encode($result));
			}

			$result = array('error' => 1, 'msg' => '请选择');
			exit(json_encode($result));
		}

		$id = input('id', 0, 'intval');
		$info = dao('drp_user_credit')->where(array('id' => $id))->find();
		$this->assign('info', $info);
		$this->display();
	}

	public function actionDrpList()
	{
		$this->admin_priv('drp_list');
		$act = I('where');

		if (empty($act)) {
			$where = '';
		}
		else if ($act == 1) {
			$where = 'and time >=' . local_strtotime('-1 year');
		}
		else if ($act == 2) {
			$where = 'and time >=' . local_strtotime('-6 month');
		}
		else if ($act == 3) {
			$where = 'and time >=' . local_strtotime('-1 month');
		}

		$filter['where'] = $act;
		$offset = $this->pageLimit(url('drp_list', $filter), $this->page_num);
		$sql = 'SELECT count(id) as num FROM {pre}drp_shop as d LEFT JOIN {pre}users as u ON d.user_id=u.user_id WHERE d.audit=1 and d.status=1 ';
		$count = $this->model->query($sql);
		$this->assign('page', $this->pageShow($count[0]['num']));
		$sql = "SELECT d.id, d.user_id, IFNULL(w.nickname,u.user_name) as  name, d.shop_name, d.mobile, d.credit_id, d.create_time,\r\n                IFNULL((select sum(money) from {pre}drp_affiliate_log where separate_type != -1 and user_id=d.user_id " . $where . "),0) as money\r\n                FROM {pre}drp_shop as d\r\n                LEFT JOIN {pre}users as u ON d.user_id=u.user_id\r\n                LEFT JOIN {pre}wechat_user as w ON d.user_id=w.ect_uid\r\n                LEFT JOIN {pre}drp_affiliate_log as log ON log.user_id=d.user_id\r\n                where d.audit=1 and d.status=1\r\n                GROUP BY d.user_id\r\n                ORDER BY money desc, d.id desc\r\n                LIMIT " . $offset;
		$list = $this->model->query($sql);

		foreach ($list as $key => $val) {
			$res = drp_rank_info($val['user_id']);
			$list[$key]['credit_name'] = $res['credit_name'];
			$list[$key]['create_time'] = local_date($GLOBALS['_CFG']['time_format'], $val['create_time']);
		}

		$this->assign('list', $list);
		$this->assign('act', $act);
		$this->display();
	}

	public function actionDrpOrderList()
	{
		$this->admin_priv('drp_order_list');
		$drp_affiliate = get_drp_affiliate_config();
		$able_day = ($drp_affiliate['config']['day'] ? $drp_affiliate['config']['day'] : 7);
		$sqladd = '';
		$status = I('status', null, 'intval');

		if (isset($status)) {
			$sqladd = ' AND o.drp_is_separate = ' . $status;
			$able = I('able', null, 'intval');
			if (isset($able) && ($able == 1)) {
				$sqladd .= ' AND ' . strtotime((0 - $able_day) . 'day', gmtime()) . '>=o.pay_time';
			}

			if (isset($able) && ($able == 2)) {
				$sqladd .= ' AND ' . strtotime((0 - $able_day) . 'day', gmtime()) . '<o.pay_time';
			}
		}

		$condition['user_id'] = $_SESSION['admin_id'];
		$ru_id = $this->model->table('admin_user')->where($condition)->getField('ru_id');

		if ($ru_id) {
			$sqladd .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = o.order_id LIMIT 1) = ' . $ru_id;
		}

		$sqladd .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
		$sqladd .= ' AND (select sum(drp_money) from ' . $GLOBALS['ecs']->table('order_goods') . ' as og2 where og2.order_id = o.order_id) > 0 ';
		$order_sn = I('order_sn', '');

		if ($order_sn) {
			$sqladd = ' AND o.order_sn like \'%' . $order_sn . '%\' ';
		}

		$list = array();

		if ($drp_affiliate['on'] == 1) {
			$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' a ON o.order_id = a.order_id' . ' WHERE o.user_id > 0 AND (u.drp_parent_id > 0 AND o.drp_is_separate = 0 OR o.drp_is_separate > 0) AND o.pay_status = ' . PS_PAYED . ' ' . $sqladd;
			$record_count = $GLOBALS['db']->getOne($sql);
			$offset = $this->pageLimit(url('drporderlist'), $this->page_num);
			$this->assign('page', $this->pageShow($record_count));
			$sql = "SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type,u.drp_parent_id as up,\r\n                    (select ss.shop_name from {pre}seller_shopinfo as ss left join {pre}order_goods as og3\r\n                     on og3.ru_id=ss.ru_id left join {pre}order_info as o\r\n                     on o.order_id=og3.order_id limit 1) as shop_name FROM " . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' a ON o.order_id = a.order_id' . ' WHERE o.user_id > 0 AND (u.drp_parent_id > 0 AND o.drp_is_separate = 0 OR o.drp_is_separate > 0) AND o.pay_status = ' . PS_PAYED . ' ' . $sqladd . ' ' . ' ORDER BY order_id DESC' . ' LIMIT ' . $offset;
			$list = $this->model->query($sql);
		}

		if (!empty($list)) {
			foreach ($list as $rt) {
				$sql = 'select ru_id from {pre}order_goods  where order_id = \'' . $rt['order_id'] . '\'  limit 1';
				$ru_id = $GLOBALS['db']->getOne($sql);
				$rt['shop_name'] = get_shop_name($ru_id, 1);

				if (0 < $rt['up']) {
					if ($rt['pay_time'] <= strtotime((0 - $able_day) . 'day', gmtime())) {
						$rt['separate_able'] = 1;
					}
				}

				if (!empty($rt['suid'])) {
					$rt['info'] = sprintf(L('drp_separate_info'), $rt['suid'], $rt['auser'], $rt['money']);

					if ($rt['separate_type'] == -1) {
						$rt['drp_is_separate'] = 3;
						$rt['info'] = '<s>' . $rt['info'] . '</s>';
					}
				}

				$order_list[] = $rt;
			}
		}

		$this->assign('status', $status);
		$this->assign('able', $able);
		$this->assign('on', $drp_affiliate['on']);
		$this->assign('able_day', $able_day);
		$this->assign('list', $order_list);
		$this->display();
	}

	public function actionSeparateDrpOrder()
	{
		$this->admin_priv('drp_order_list');
		$drp_affiliate = get_drp_affiliate_config();
		$oid = I('oid');

		if (is_array($oid)) {
			$oid_arr = $oid;
		}
		else {
			$oid_arr[] = $oid;
		}

		if (is_array($oid_arr)) {
			foreach ($oid_arr as $oid) {
				$sql = 'SELECT d.*, o.order_sn FROM ' . $GLOBALS['ecs']->table('drp_log') . ' d ' . ' LEFT JOIN' . $GLOBALS['ecs']->table('order_info') . ' o ON d.order_id = o.order_id' . ' WHERE d.order_id = \'' . $oid . '\' and o.drp_is_separate = 0 and d.is_separate = 0 ';
				$result = $GLOBALS['db']->getAll($sql);

				foreach ($result as $key => $row) {
					if ($row['is_separate'] == 0) {
						$setmoney = $row['money'];
						$setpoint = $row['point'];
						$users = dao('users')->field('user_id, user_name')->where(array('user_id' => $row['user_id']))->find();
						if (empty($users['user_id']) || empty($users['user_name'])) {
							break;
						}
						else {
							$change_desc = sprintf(L('drp_separate_info'), $users['user_name'], $order_sn, $setmoney, $setpoint);
							drp_log_account_change($users['user_id'], $setmoney, 0, 0, $setpoint, $change_desc, ACT_SEPARATE);
							$this->write_drp_affiliate_log($oid, $users['user_id'], $users['user_name'], $setmoney, $setpoint, 0);
							$pushData = array(
								'keyword1' => array('value' => $setmoney),
								'keyword2' => array('value' => local_date('Y-m-d H:i:s', gmtime()))
								);
							$url = __HOST__ . url('drp/user/orderdetail', array('order_id' => $oid));
							push_template('OPENTM201812627', $pushData, $url, $users['user_id']);
						}

						dao('order_info')->data(array('drp_is_separate' => 1))->where(array('order_id' => $oid))->save();
						dao('drp_log')->data(array('is_separate' => 1))->where(array('order_id' => $oid))->save();
					}
				}
			}
		}

		if (IS_POST) {
			$arr = array('url' => url('drporderlist'));
			exit(json_encode($arr));
		}
		else {
			$this->redirect('drporderlist');
		}
	}

	private function write_drp_affiliate_log($oid, $uid, $username, $money, $point, $separate_by)
	{
		$time = gmtime();
		$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('drp_affiliate_log') . '( order_id, `time`, user_id, user_name, money, point, separate_type)' . ' VALUES ( \'' . $oid . '\',\'' . $time . '\', \'' . $uid . '\', \'' . $username . '\', \'' . $money . '\', \'' . $point . '\',' . $separate_by . ')';

		if ($oid) {
			$GLOBALS['db']->query($sql);
		}
	}

	public function actionDelDrpOrder()
	{
		$this->admin_priv('drp_order_list');
		$oid = I('oid', 0, 'intval');

		if ($oid) {
			$stat = $GLOBALS['db']->getOne('SELECT drp_is_separate FROM ' . $GLOBALS['ecs']->table('order_info') . ' WHERE order_id = \'' . $oid . '\'');

			if (empty($stat)) {
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . ' SET drp_is_separate = 2' . ' WHERE order_id = \'' . $oid . '\'';
				$GLOBALS['db']->query($sql);
			}
		}

		$this->redirect('drporderlist');
	}

	public function actionRollbackDrpOrder()
	{
		$this->admin_priv('drp_order_list');
		$logid = I('log_id', 0, 'intval');

		if ($logid) {
			$stat = $GLOBALS['db']->getRow('SELECT * FROM ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' WHERE log_id = \'' . $logid . '\'');

			if (!empty($stat)) {
				$flag = -1;
				drp_log_account_change($stat['user_id'], 0 - $stat['money'], 0, 0, 0 - $stat['point'], L('loginfo_cancel'), ACT_SEPARATE);
				$sql = 'UPDATE ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' SET separate_type = \'' . $flag . '\'' . ' WHERE log_id = \'' . $logid . '\'';
				$GLOBALS['db']->query($sql);
			}
		}
		$this->redirect('drporderlist');
	}

	public function actionDrpCount()
	{
		$this->admin_priv('drp_count');

		if (IS_AJAX) {
			$type = input('type', '', 'trim');
			$date = input('date', '', 'trim');
			$timezone = (isset($_SESSION['timezone']) ? $_SESSION['timezone'] : $GLOBALS['_CFG']['timezone']);
			$time_diff = $timezone * 3600;

			if ($date == 'week') {
				$day_num = 7;
			}

			if ($date == 'month') {
				$day_num = 30;
			}

			if ($date == 'year') {
				$day_num = 180;
			}

			$date_end = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
			$date_start = $date_end - (3600 * 24 * $day_num);
			$data = array();
			$shop_series_data = array();
			$orders_series_data = array();
			$sales_series_data = array();
			$sqladd = '';
			$condition['user_id'] = $_SESSION['admin_id'];
			$ru_id = $this->model->table('admin_user')->where($condition)->getField('ru_id');

			if ($ru_id) {
				$sqladd .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = oi.order_id LIMIT 1) = ' . $ru_id;
			}

			$sqladd .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = oi.order_id) = 0 ';
			$sqladd .= ' AND (select sum(drp_money) from ' . $GLOBALS['ecs']->table('order_goods') . ' as og2 where og2.order_id = oi.order_id) > 0 ';
			$sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(oi.add_time + ' . $time_diff . '),"%y-%m-%d") AS day, COUNT(*) AS count  FROM ' . $GLOBALS['ecs']->table('order_info') . ' oi' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON oi.user_id = u.user_id' . ' WHERE oi.add_time BETWEEN ' . $date_start . ' AND ' . $date_end . ' AND oi.user_id > 0 AND (u.drp_parent_id > 0 AND oi.drp_is_separate = 0 OR oi.drp_is_separate > 0) AND oi.pay_status = ' . PS_PAYED . ' ' . $sqladd . ' GROUP BY day ORDER BY day ASC ';
			$result_order = $this->model->query($sql);

			foreach ($result_order as $row) {
				$orders_series_data[$row['day']] = intval($row['count']);
			}

			$sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(a.time + ' . $time_diff . '),"%y-%m-%d") AS day,SUM(a.money) AS money FROM ' . $GLOBALS['ecs']->table('order_info') . ' oi' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON oi.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' a ON oi.order_id = a.order_id' . ' WHERE a.time BETWEEN ' . $date_start . ' AND ' . $date_end . ' AND oi.user_id > 0 AND (u.drp_parent_id > 0 AND oi.drp_is_separate = 0 OR oi.drp_is_separate > 0) AND oi.pay_status = ' . PS_PAYED . ' ' . $sqladd . ' GROUP BY day ORDER BY day ASC ';
			$result_sale = $this->model->query($sql);

			foreach ($result_sale as $val) {
				$sales_series_data[$val['day']] = floatval($val['money']);
			}

			$sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(d.create_time + ' . $time_diff . '),"%y-%m-%d") AS day, COUNT(*) AS count FROM ' . $GLOBALS['ecs']->table('drp_shop') . ' AS d' . ' WHERE d.create_time BETWEEN ' . $date_start . ' AND ' . $date_end . ' AND d.audit = 1 GROUP BY day ORDER BY day ASC ';
			$result_shop = $GLOBALS['db']->query($sql);

			foreach ($result_shop as $row) {
				$shop_series_data[$row['day']] = intval($row['count']);
			}

			for ($i = 1; $i <= $day_num; $i++) {
				$day = date('y-m-d', strtotime(' - ' . ($day_num - $i) . ' days'));

				if (empty($shop_series_data[$day])) {
					$shop_series_data[$day] = 0;
				}

				if (empty($orders_series_data[$day])) {
					$orders_series_data[$day] = 0;
				}

				if (empty($sales_series_data[$day])) {
					$sales_series_data[$day] = 0;
				}

				$day = date('m-d', strtotime($day));
				$shop_xAxis_data[] = $day;
				$orders_xAxis_data[] = $day;
				$sales_xAxis_data[] = $day;
			}

			$toolbox = array(
				'show'    => true,
				'orient'  => 'vertical',
				'x'       => 'right',
				'y'       => '60',
				'feature' => array(
					'magicType'   => array(
						'show' => true,
						'type' => array('line', 'bar')
						),
					'saveAsImage' => array('show' => true)
					)
				);
			$tooltip = array(
				'trigger'     => 'axis',
				'axisPointer' => array(
					'lineStyle' => array('color' => '#6cbd40')
					)
				);
			$xAxis = array(
				'type'        => 'category',
				'boundaryGap' => false,
				'axisLine'    => array(
					'lineStyle' => array('color' => '#ccc', 'width' => 0)
					),
				'data'        => array()
				);
			$yAxis = array(
				'type'      => 'value',
				'axisLine'  => array(
					'lineStyle' => array('color' => '#ccc', 'width' => 0)
					),
				'axisLabel' => array('formatter' => '')
				);
			$series = array(
				array(
					'name'      => '',
					'type'      => 'line',
					'itemStyle' => array(
						'normal' => array(
							'color'     => '#6cbd40',
							'lineStyle' => array('color' => '#6cbd40')
							)
						),
					'data'      => array(),
					'markPoint' => array(
						'itemStyle' => array(
							'normal' => array('color' => '#6cbd40')
							),
						'data'      => array(
							array('type' => 'max', 'name' => '最大值'),
							array('type' => 'min', 'name' => '最小值')
							)
						)
					)
				);
			$calculable = true;
			$legend = array(
				'data' => array()
				);

			if ($type == 'shop') {
				$xAxis['data'] = $shop_xAxis_data;
				$yAxis['formatter'] = '{value}个';
				ksort($shop_series_data);
				$series[0]['name'] = '分销商人数';
				$series[0]['data'] = array_values($shop_series_data);
				$data['series'] = $series;
			}

			if ($type == 'order') {
				$xAxis['data'] = $orders_xAxis_data;
				$yAxis['formatter'] = '{value}个';
				ksort($orders_series_data);
				$series[0]['name'] = '订单个数';
				$series[0]['data'] = array_values($orders_series_data);
				$data['series'] = $series;
			}

			if ($type == 'sale') {
				$xAxis['data'] = $sales_xAxis_data;
				$yAxis['formatter'] = '{value}元';
				ksort($sales_series_data);
				$series[0]['name'] = '佣金额';
				$series[0]['data'] = array_values($sales_series_data);
				$data['series'] = $series;
			}

			$data['tooltip'] = $tooltip;
			$data['legend'] = $legend;
			$data['toolbox'] = $toolbox;
			$data['calculable'] = $calculable;
			$data['xAxis'] = $xAxis;
			$data['yAxis'] = $yAxis;
			exit(json_encode($data));
		}

		$drp_shop_count = dao('drp_shop')->count();
		$this->assign('drp_shop_trend', 1);
		$this->assign('drp_shop_count', $drp_shop_count);
		$sqladd = '';
		$condition['user_id'] = $_SESSION['admin_id'];
		$ru_id = $this->model->table('admin_user')->where($condition)->getField('ru_id');

		if ($ru_id) {
			$sqladd .= ' AND (SELECT og.ru_id FROM ' . $GLOBALS['ecs']->table('order_goods') . ' AS og WHERE og.order_id = o.order_id LIMIT 1) = ' . $ru_id;
		}

		$sqladd .= ' AND (select count(*) from ' . $GLOBALS['ecs']->table('order_info') . ' as oi2 where oi2.main_order_id = o.order_id) = 0 ';
		$sqladd .= ' AND (select sum(drp_money) from ' . $GLOBALS['ecs']->table('order_goods') . ' as og2 where og2.order_id = o.order_id) > 0 ';
		$sql = 'SELECT COUNT(*) as count FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' WHERE o.user_id > 0 AND (u.drp_parent_id > 0 AND o.drp_is_separate = 0 OR o.drp_is_separate > 0) AND o.pay_status = ' . PS_PAYED . ' ' . $sqladd . ' GROUP BY order_id ';
		$drp_order_count = $this->model->query($sql);
		$this->assign('drp_order_trend', 1);
		$this->assign('drp_order_count', $drp_order_count[0]['count']);
		$sql = 'SELECT SUM(a.money) AS money FROM ' . $GLOBALS['ecs']->table('order_info') . ' o' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' u ON o.user_id = u.user_id' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('drp_affiliate_log') . ' a ON o.order_id = a.order_id' . ' WHERE o.user_id > 0 AND (u.drp_parent_id > 0 AND o.drp_is_separate = 0 OR o.drp_is_separate > 0) AND o.pay_status = ' . PS_PAYED . ' ' . $sqladd . ' GROUP BY a.order_id ';
		$drp_sales_count = $this->model->query($sql);
		$this->assign('drp_sales_trend', 1);
		$this->assign('drp_sales_count', $drp_sales_count[0]['money']);
		$this->display();
	}
}

?>
