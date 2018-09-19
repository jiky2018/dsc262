<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Modules\Community\Controllers;

class IndexController extends \App\Modules\Base\Controllers\FrontendController
{
	private $size = 10;
	private $page = 1;

	public function __construct()
	{
		parent::__construct();
		$this->page = I('request.page', 1, 'intval');
		L(require LANG_PATH . C('shop.lang') . '/user.php');
		if (strtolower(MODULE_NAME) == 'community' && (ACTION_NAME == 'detail' || ACTION_NAME == 'reply')) {
			$community = 1;
			$this->assign('community', $community);
		}
	}

	public function actionIndex()
	{
		if (IS_AJAX) {
			$list = community_list(0, $this->page, $this->size);
			exit(json_encode($list));
		}

		$tao = array('num' => community_num(1), 'has_new' => community_has_new(1));
		$wen = array('num' => community_num(2), 'has_new' => community_has_new(2));
		$quan = array('num' => community_num(3), 'has_new' => community_has_new(3));
		$sun = array('num' => sd_count(), 'has_new' => community_has_new(4, 1));
		$this->assign('tao', $tao);
		$this->assign('wen', $wen);
		$this->assign('quan', $quan);
		$this->assign('sun', $sun);
		$this->assign('action', strtolower(ACTION_NAME));
		$this->assign('page_title', L('community_group'));
		$this->display();
	}

	public function actionList()
	{
		$dis_type = I('get.type', 1, 'intval');

		if ($dis_type == 1) {
			$page_title = L('discuss_post');
		}
		else if ($dis_type == 2) {
			$page_title = L('answer_post');
		}
		else if ($dis_type == 3) {
			$page_title = L('group_post');
		}
		else if ($dis_type == 4) {
			$page_title = L('sunny_post');

			if (IS_AJAX) {
				if (isset($_COOKIE['community_view_time_' . $dis_type]) && !empty($_COOKIE['community_view_time_' . $dis_type])) {
					cookie('community_view_time_' . $dis_type, gmtime(), 3600 * 24);
				}

				$list = comment_list($this->page, $this->size);
				exit(json_encode($list));
			}
		}

		if (IS_AJAX) {
			if (isset($_COOKIE['community_view_time_' . $dis_type]) && !empty($_COOKIE['community_view_time_' . $dis_type])) {
				cookie('community_view_time_' . $dis_type, gmtime(), 3600 * 24);
			}

			$list = community_list($dis_type, $this->page, $this->size);
			exit(json_encode($list));
		}

		$this->assign('type', $dis_type);
		$this->assign('action', strtolower(ACTION_NAME));
		$this->assign('page_title', $page_title);
		$this->display();
	}

	public function actionDetail()
	{
		$dis_type = I('type', 0, 'intval');
		$dis_id = I('id', 0, 'intval');
		if (empty($dis_id) || empty($dis_type)) {
			$this->redirect('index');
		}

		if (!empty($dis_type) && !empty($dis_id)) {
			$dis_type == 4 ? $table = 'comment' : ($table = 'discuss_circle');
			$dis_type == 4 ? $field = 'comment_id' : ($field = 'dis_id');
			$dis_type == 4 ? $show_type = ' AND status = 1 ' : ($show_type = ' AND review_status = 3 ');
			$sql = 'SELECT * FROM {pre}' . $table . ' WHERE ' . $field . ' =\'' . $dis_id . '\' AND parent_id = 0 ';
			$res = $this->db->getRow($sql);

			if (empty($res)) {
				show_message('帖子不存在');
			}

			if (!empty($res) && $dis_type == 4 && $res['status'] == 0) {
				show_message('抱歉，该帖子禁止显示');
			}

			if (!empty($res) && $dis_type != 4 && $res['review_status'] != 3) {
				show_message('抱歉，该帖子正在被审核');
			}

			if ($res) {
				if ($dis_type == 4) {
					$res['dis_text'] = $res['content'];
					$res['dis_id'] = $res['comment_id'];
					$res['dis_type'] = 4;
				}

				$res['add_time'] = mdate($res['add_time']);
				$users = get_wechat_user_info($res['user_id']);
				$res['user_name'] = encrypt_username($users['nick_name']);
				$res['user_picture'] = get_image_path($users['user_picture'], '', elixir('img/user_default.png'));

				if (empty($_COOKIE[$dis_id . $dis_type . 'islike'])) {
					$sql = 'UPDATE {pre}' . $table . ' SET dis_browse_num=dis_browse_num+1 WHERE ' . $field . ' =\'' . $dis_id . '\' AND parent_id = 0 ' . $show_type;
					$this->db->query($sql);
				}

				if (isset($_COOKIE[$res['dis_id'] . $res['dis_type'] . 'islike']) && $_COOKIE[$res['dis_id'] . $res['dis_type'] . 'islike'] == '1') {
					$res['islike'] = '1';
				}
				else {
					$res['islike'] = '0';
				}

				$this->assign('detail', $res);

				if ($dis_type == 4) {
					$img_list = get_img_list($res['id_value'], $res['comment_id']);

					foreach ($img_list as $key => $list) {
						$img_list[$key]['comment_img'] = get_image_path($list['comment_img']);
					}

					$this->assign('img_list', $img_list);
					$link_good = $this->db->getRow('SELECT goods_id,goods_thumb,goods_name FROM  {pre}goods WHERE goods_id = \'' . $res['id_value'] . '\' ');
				}
				else {
					$link_good = $this->db->getRow('SELECT goods_id,goods_thumb,goods_name FROM  {pre}goods WHERE goods_id = \'' . $res['goods_id'] . '\' ');
				}

				if (!empty($link_good)) {
					$link_good['goods_thumb'] = get_image_path($link_good['goods_thumb']);
					$link_good['url'] = url('goods/index/index', array('id' => $link_good['goods_id']));
				}

				$this->assign('link_good', $link_good);
			}

			if ($dis_type == 4) {
				$sql = 'SELECT count(*) as total FROM {pre}comment WHERE status = 1 AND parent_id = \'' . $dis_id . '\' AND user_id > 0 ';
				$reply_count = $GLOBALS['db']->getOne($sql);
			}
			else {
				$sql = 'SELECT COUNT(*) as total FROM {pre}discuss_circle WHERE parent_id = \'' . $dis_id . '\' AND review_status = 3 ';
				$reply_count = $GLOBALS['db']->getOne($sql);
			}
		}

		$this->assign('reply_count', $reply_count);
		$users = get_wechat_user_info($_SESSION['user_id']);
		$user_picture = get_image_path($users['user_picture'], '', elixir('img/user_default.png'));
		$this->assign('user_picture', $user_picture);
		$this->assign('dis_type', $dis_type);
		$this->assign('dis_id', $dis_id);
		$this->assign('page_title', L('post_detail'));
		$this->display();
	}

	public function actionCommentList()
	{
		if (IS_AJAX) {
			$dis_type = I('request.type');
			$id = I('request.id');
			$goods_id = I('request.goods_id');
			$discuss = dao('discuss_circle')->where(array('dis_id' => $id, 'parent_id' => 0))->find();
			$dis_id = $discuss['dis_id'];

			if ($dis_type == 4) {
				$sql = 'SELECT count(*) FROM {pre}comment WHERE id_value = \'' . $goods_id . '\' AND status = 1 AND parent_id = \'' . $id . '\'  ';
				$total = $GLOBALS['db']->getOne($sql);
				$sql = 'SELECT add_time, user_id, comment_id as dis_id, comment_type, content as dis_text FROM {pre}comment WHERE id_value = \'' . $goods_id . '\' AND status = 1 AND parent_id = \'' . $id . '\'  ORDER BY add_time DESC';
				$dis_comment = $GLOBALS['db']->selectLimit($sql, $this->size, ($this->page - 1) * $this->size);

				foreach ($dis_comment as $k => $v) {
					$dis_comment[$k]['add_time'] = mdate($v['add_time']);
					$usersnick = get_wechat_user_info($v['user_id']);
					$dis_comment[$k]['user_name'] = $v['user_id'] == 0 ? '管理员' : encrypt_username($usersnick['nick_name']);
					$dis_comment[$k]['user_picture'] = get_image_path($usersnick['user_picture'], '', elixir('img/user_default.png'));
					$dis_comment[$k]['quote'] = 0 < $id ? get_comment_reply($v['dis_id'], $id, $goods_id) : array();
					$dis_comment[$k]['quote_count'] = count($dis_comment[$k]['quote']);
				}
			}
			else {
				$sql = 'SELECT COUNT(*) as total FROM {pre}discuss_circle  WHERE parent_id = \'' . $dis_id . '\' AND review_status = 3 ORDER BY add_time DESC';
				$total = $GLOBALS['db']->getOne($sql);
				$sql = 'SELECT * FROM {pre}discuss_circle WHERE parent_id = \'' . $dis_id . '\' ORDER BY add_time DESC';
				$dis_comment = $GLOBALS['db']->selectLimit($sql, $this->size, ($this->page - 1) * $this->size);

				foreach ($dis_comment as $k => $v) {
					$dis_comment[$k]['add_time'] = mdate($v['add_time']);
					$usersnick = get_wechat_user_info($v['user_id']);
					$dis_comment[$k]['user_name'] = encrypt_username($usersnick['nick_name']);
					$dis_comment[$k]['user_picture'] = get_image_path($usersnick['user_picture'], '', elixir('img/user_default.png'));
					$dis_comment[$k]['quote'] = 0 < $dis_id ? get_quote_reply($v['dis_id'], $dis_id) : array();
					$dis_comment[$k]['quote_count'] = count($dis_comment[$k]['quote']);

					if ($v['quote_id']) {
						unset($dis_comment[$k]);
					}
				}
			}

			exit(json_encode(array('commentlist' => $dis_comment, 'totalPage' => ceil($total / $this->size))));
		}
	}

	public function actionCommnet()
	{
		if (IS_POST) {
			$dis_type = I('type');
			$parent_id = I('parent_id', 0, 'intval');
			$quote_id = I('quote_id', 0, 'intval');
			$dis_text = I('dis_text', '', array('htmlspecialchars', 'trim'));
			$user_id = I('user_id', 0, 'intval');
			$goods_id = I('goods_id', 0, 'intval');
			$reply_type = I('reply_type', '', array('htmlspecialchars', 'trim'));

			if (empty($_SESSION['user_id'])) {
				$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
				$url = url('user/login/index', array('back_act' => urlencode($back_act)));
				exit(json_encode(array('error' => 1, 'msg' => '请登录', 'url' => $url)));
			}

			if (checkDistype($dis_type) == false) {
				exit(json_encode(array('error' => 1, 'msg' => '发帖类型不允许！')));
			}

			if (empty($dis_text)) {
				exit(json_encode(array('error' => 1, 'msg' => L('write_answer'))));
			}

			if ($dis_type == 4) {
				if ($reply_type == 'reply_other') {
					$parent_id = $quote_id;
				}
				else if ($user_id == $_SESSION['user_id']) {
					exit(json_encode(array('error' => 1, 'msg' => '不能回复自己的帖子')));
				}

				$data = array('comment_type' => 2, 'id_value' => $goods_id, 'content' => $dis_text, 'parent_id' => $parent_id, 'status' => 1, 'user_id' => $_SESSION['user_id'], 'user_name' => $_SESSION['nickname'] ? $_SESSION['nickname'] : $_SESSION['user_name'], 'add_time' => gmtime());
				$res = dao('comment')->data($data)->add();
			}
			else {
				if ($reply_type == 'reply_other') {
					$data['quote_id'] = $quote_id;
				}
				else if ($user_id == $_SESSION['user_id']) {
					exit(json_encode(array('error' => 1, 'msg' => '不能回复自己的帖子')));
				}

				$data['dis_text'] = $dis_text;
				$data['user_id'] = $_SESSION['user_id'];
				$data['user_name'] = $_SESSION['nickname'] ? $_SESSION['nickname'] : $_SESSION['user_name'];
				$data['parent_id'] = $parent_id;
				$data['add_time'] = gmtime();
				$data['dis_type'] = 0;
				$data['goods_id'] = 0;
				$res = dao('discuss_circle')->data($data)->add();
			}

			if ($res) {
				$json_res = array('error' => 0, 'msg' => '发帖成功！', 'url' => url('community/index/detail', array('id' => $parent_id, 'type' => $dis_type)));
				exit(json_encode($json_res));
			}
		}
	}

	public function actionMy()
	{
		$this->checkLogin();
		$dis_type = I('dis_type', 1, 'intval');

		if (IS_AJAX) {
			if ($dis_type == 4) {
				$list = comment_list($this->page, $this->size, $_SESSION['user_id']);
				exit(json_encode($list));
			}
			else {
				$list = community_list($dis_type, $this->page, $this->size, $_SESSION['user_id']);
				exit(json_encode($list));
			}
		}

		$has_new = reply_has_new();
		$this->assign('has_new', $has_new);
		$info = get_user_default($_SESSION['user_id']);
		$this->assign('type1_num', community_num(1, 0, $_SESSION['user_id']));
		$this->assign('type2_num', community_num(2, 0, $_SESSION['user_id']));
		$this->assign('type3_num', community_num(3, 0, $_SESSION['user_id']));
		$this->assign('type4_num', sd_count($_SESSION['user_id']));
		$this->assign('info', $info);
		$this->assign('action', strtolower(ACTION_NAME));
		$this->assign('dis_type', $dis_type);
		$this->assign('page_title', L('community_my'));
		$this->display();
	}

	public function actionReply()
	{
		$this->checkLogin();

		if (IS_AJAX) {
			if (isset($_COOKIE['community_reply']) && !empty($_COOKIE['community_reply'])) {
				cookie('community_reply', gmtime() + 3600 * 24);
			}

			$sql = "SELECT COUNT(*) as total FROM {pre}discuss_circle dc\r\n                     LEFT JOIN {pre}discuss_circle as dc2 ON dc.parent_id = dc2.dis_id\r\n                     LEFT JOIN {pre}users as u ON dc2.user_id = u.user_id\r\n                     WHERE dc.user_id != " . $_SESSION['user_id'] . ' AND dc.parent_id != 0 AND dc.dis_type = 0 AND dc.review_status = 3';
			$total = $GLOBALS['db']->getOne($sql);
			$sql = "SELECT dc.user_id, dc.dis_text, dc.add_time, dc.parent_id, dc2.dis_type, dc2.dis_title as main_title FROM {pre}discuss_circle dc\r\n                     LEFT JOIN {pre}discuss_circle as dc2 ON dc.parent_id = dc2.dis_id\r\n                     LEFT JOIN {pre}users as u ON dc2.user_id = u.user_id\r\n                     WHERE dc.user_id != " . $_SESSION['user_id'] . " AND dc.parent_id != 0 AND dc.dis_type = 0 AND dc.review_status = 3\r\n                     ORDER BY dc.add_time DESC LIMIT " . ($this->page - 1) * $this->size . (',  ' . $this->size . ' ');
			$list = $GLOBALS['db']->query($sql);

			foreach ($list as $k => $v) {
				$usersnick = get_wechat_user_info($v['user_id']);
				$list[$k]['user_name'] = encrypt_username($usersnick['nick_name']);
				$list[$k]['user_picture'] = get_image_path($usersnick['user_picture'], '', elixir('img/user_default.png'));
				$list[$k]['add_time'] = mdate($v['add_time']);
				$list[$k]['url'] = url('detail', array('id' => $v['parent_id'], 'type' => $v['dis_type']));
			}

			exit(json_encode(array('list' => $list, 'totalPage' => ceil($total / $this->size))));
		}

		$this->assign('page_title', L('reply_me'));
		$this->display();
	}

	public function actionDeleteMycom()
	{
		$this->checkLogin();

		if (IS_AJAX) {
			$result = array('error' => '', 'msg' => '');
			$dis_type = I('dis_type', 0, 'intval');
			$dis_id = I('dis_id', 0, 'intval');
			if (!empty($dis_type) && !empty($dis_id)) {
				if ($dis_type == 4) {
					$data['status'] = 0;
					$condition['comment_id'] = $dis_id;
					$condition['user_id'] = $_SESSION['user_id'];
					$res = $this->model->table('comment')->where($condition)->select();

					if (!$res) {
						$result['error'] = 1;
						$result['msg'] = '不可以删除别人的帖子';
					}
					else {
						$res = $this->model->table('comment')->data($data)->where($condition)->save();
						$result['error'] = 0;
					}
				}
				else {
					$condition['dis_id'] = $dis_id;
					$condition['user_id'] = $_SESSION['user_id'];
					$res = $this->model->table('discuss_circle')->where($condition)->select();

					if (!$res) {
						$result['error'] = 1;
						$result['msg'] = '不可以删除别人的帖子';
					}
					else {
						$condition['dis_id'] = $dis_id;
						$sql = 'DELETE FROM `{pre}discuss_circle` WHERE dis_id = \'' . $dis_id . '\' OR parent_id = \'' . $dis_id . '\' ';
						$res = $GLOBALS['db']->query($sql);

						if ($res) {
							$result['error'] == 0;
						}
					}
				}

				exit(json_encode($result));
			}
		}
	}

	public function actionLike()
	{
		if (IS_AJAX) {
			$dis_type = I('dis_type', 0, 'intval');
			$dis_id = I('dis_id', 0, 'intval');
			if (!empty($dis_type) && !empty($dis_id)) {
				$dis_type == 4 ? $table = 'comment' : ($table = 'discuss_circle');
				$dis_type == 4 ? $field = 'comment_id' : ($field = 'dis_id');

				if ($_COOKIE[$dis_id . $dis_type . 'islike'] == '1') {
					$symbols = '-';
					$islike = '0';
				}
				else {
					$symbols = '+';
					$islike = '1';
				}

				$sql = 'UPDATE {pre}' . $table . ' SET like_num=like_num' . $symbols . ('1 WHERE ' . $field . ' = ' . $dis_id . ' ');
				$GLOBALS['db']->query($sql);
				$like_num = $this->db->getOne('SELECT like_num FROM {pre}' . $table . '  WHERE ' . $field . ' =  ' . $dis_id . '  ');

				if ($like_num === null) {
					$like_num = '0';
				}

				if ($islike == '0') {
					cookie($dis_id . $dis_type . 'islike', $islike, gmtime() - 86400);
				}
				else {
					cookie($dis_id . $dis_type . 'islike', $islike, gmtime() + 86400);
				}

				echo json_encode(array('like_num' => $like_num, 'is_like' => $islike, 'dis_id' => $dis_id));
			}
		}
	}

	private function checkLogin()
	{
		if (!$_SESSION['user_id']) {
			$back_act = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : __HOST__ . $_SERVER['REQUEST_URI'];
			$this->redirect('user/login/index', array('back_act' => urlencode($back_act)));
		}
	}
}

?>
