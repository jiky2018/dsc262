<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function merge_user($from_user_id = 0, $to_user_id = 0)
{
	if ((0 < $from_user_id) && (0 < $to_user_id) && ($from_user_id != $to_user_id)) {
		$from_user_info = dao('users')->field('*')->where(array('user_id' => $from_user_id))->find();

		if (!empty($from_user_info)) {
			$from_data = array('email' => $from_user_info['email'], 'sex' => $from_user_info['sex'], 'birthday' => $from_user_info['birthday'], 'address_id' => $from_user_info['address_id'], 'user_rank' => $from_user_info['user_rank'], 'is_special' => $from_user_info['is_special'], 'parent_id' => $from_user_info['parent_id'], 'flag' => $from_user_info['flag'], 'alias' => $from_user_info['alias'], 'msn' => $from_user_info['msn'], 'qq' => $from_user_info['qq'], 'office_phone' => $from_user_info['office_phone'], 'home_phone' => $from_user_info['home_phone'], 'mobile_phone' => $from_user_info['mobile_phone'], 'is_validated' => $from_user_info['is_validated']);

			if (empty($from_data['parent_id'])) {
				unset($from_data['parent_id']);
			}

			dao('users')->data($from_data)->where(array('user_id' => $to_user_id))->save();
			$sql = 'UPDATE {pre}users SET user_money = user_money + \'' . $from_user_info['user_money'] . '\', frozen_money = frozen_money + \'' . $from_user_info['frozen_money'] . '\', pay_points = pay_points + \'' . $from_user_info['pay_points'] . '\', rank_points = rank_points + \'' . $from_user_info['rank_points'] . '\', credit_line = credit_line + \'' . $from_user_info['credit_line'] . '\'  WHERE user_id = \'' . $to_user_id . '\'';
			$GLOBALS['db']->query($sql);
			dao('users')->data(array('parent_id' => $to_user_id))->where(array('parent_id' => $from_user_id))->save();
		}

		$from_order_info = dao('order_info')->field('order_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_order_info)) {
			foreach ($from_order_info as $key => $value) {
				dao('order_info')->where('order_id = ' . $value['order_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_booking_goods = dao('booking_goods')->field('rec_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_booking_goods)) {
			foreach ($from_booking_goods as $key => $value) {
				dao('booking_goods')->where('rec_id = ' . $value['rec_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_collect_goods = dao('collect_goods')->field('rec_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_collect_goods)) {
			foreach ($from_collect_goods as $key => $value) {
				dao('collect_goods')->where('rec_id = ' . $value['rec_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_feedback = dao('feedback')->field('msg_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_feedback)) {
			$to_user_info = dao('users')->field('user_name,email')->where(array('user_id' => $to_user_id))->find();

			foreach ($from_feedback as $key => $value) {
				$setdata = array('user_id' => $to_user_id, 'user_name' => $to_user_info['user_name'], 'email' => $to_user_info['email']);
				dao('feedback')->where('msg_id = ' . $value['msg_id'])->setField($setdata);
			}
		}

		$from_user_address = dao('user_address')->field('address_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_user_address)) {
			foreach ($from_user_address as $key => $value) {
				dao('user_address')->where('address_id = ' . $value['address_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_user_bonus = dao('user_bonus')->field('bonus_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_user_bonus)) {
			foreach ($from_user_bonus as $key => $value) {
				dao('user_bonus')->where('bonus_id = ' . $value['bonus_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_user_account = dao('user_account')->field('id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_user_account)) {
			foreach ($from_user_account as $key => $value) {
				dao('user_account')->where('id = ' . $value['id'])->setField('user_id', $to_user_id);
			}
		}

		$from_tag = dao('tag')->field('tag_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_tag)) {
			foreach ($from_tag as $key => $value) {
				dao('tag')->where('tag_id = ' . $value['tag_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_account_log = dao('account_log')->field('log_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_account_log)) {
			foreach ($from_account_log as $key => $value) {
				dao('account_log')->where('log_id = ' . $value['log_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_back_order = dao('back_order')->field('back_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_back_order)) {
			foreach ($from_back_order as $key => $value) {
				dao('back_order')->where('back_id = ' . $value['back_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_comment = dao('comment')->field('comment_id')->where(array('user_id' => $from_user_id))->select();
		if (!empty($from_comment) && !empty($to_user_info)) {
			foreach ($from_comment as $key => $value) {
				$setdata = array('user_id' => $to_user_id, 'user_name' => $to_user_info['user_name'], 'email' => $to_user_info['email']);
				dao('comment')->where('comment_id = ' . $value['comment_id'])->setField($setdata);
			}
		}

		$from_comment_img = dao('comment_img')->field('id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_comment_img)) {
			foreach ($from_comment_img as $key => $value) {
				dao('comment_img')->where('id = ' . $value['id'])->setField('user_id', $to_user_id);
			}
		}

		$from_comment_seller = dao('comment_seller')->field('sid')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_comment_seller)) {
			foreach ($from_comment_seller as $key => $value) {
				dao('comment_seller')->where('sid = ' . $value['sid'])->setField('user_id', $to_user_id);
			}
		}

		$from_collect_brand = dao('collect_brand')->field('rec_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_collect_brand)) {
			foreach ($from_collect_brand as $key => $value) {
				dao('collect_brand')->where('rec_id = ' . $value['rec_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_card = dao('card')->field('card_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_card)) {
			foreach ($from_card as $key => $value) {
				dao('card')->where('card_id = ' . $value['card_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_user_bank = dao('user_bank')->field('id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_user_bank)) {
			foreach ($from_user_bank as $key => $value) {
				dao('user_bank')->where('id = ' . $value['id'])->setField('user_id', $to_user_id);
			}
		}

		$from_baitiao = dao('baitiao')->field('baitiao_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_baitiao)) {
			foreach ($from_baitiao as $key => $value) {
				dao('baitiao')->where('baitiao_id = ' . $value['baitiao_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_baitiao_log = dao('baitiao_log')->field('log_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_baitiao_log)) {
			foreach ($from_baitiao_log as $key => $value) {
				dao('baitiao_log')->where('log_id = ' . $value['log_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_order_return = dao('order_return')->field('ret_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_order_return)) {
			foreach ($from_order_return as $key => $value) {
				dao('order_return')->where('ret_id = ' . $value['ret_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_user_gift_gard = dao('user_gift_gard')->field('gift_gard_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_user_gift_gard)) {
			foreach ($from_user_gift_gard as $key => $value) {
				dao('user_gift_gard')->where('gift_gard_id = ' . $value['gift_gard_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_users_paypwd = dao('users_paypwd')->field('paypwd_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_users_paypwd)) {
			foreach ($from_users_paypwd as $key => $value) {
				dao('users_paypwd')->where('paypwd_id = ' . $value['paypwd_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_users_real = dao('users_real')->field('real_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_users_real)) {
			foreach ($from_users_real as $key => $value) {
				dao('users_real')->where('real_id = ' . $value['real_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_collect_store = dao('collect_store')->field('rec_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_collect_store)) {
			foreach ($from_collect_store as $key => $value) {
				dao('collect_store')->where('rec_id = ' . $value['rec_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_coupons_user = dao('coupons_user')->field('uc_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_coupons_user)) {
			foreach ($from_coupons_user as $key => $value) {
				dao('coupons_user')->where('uc_id = ' . $value['uc_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_value_card = dao('value_card')->field('vid')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_value_card)) {
			foreach ($from_value_card as $key => $value) {
				dao('value_card')->where('vid = ' . $value['vid'])->setField('user_id', $to_user_id);
			}
		}

		$from_pay_card = dao('pay_card')->field('id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_pay_card)) {
			foreach ($from_pay_card as $key => $value) {
				dao('pay_card')->where('id = ' . $value['id'])->setField('user_id', $to_user_id);
			}
		}

		$from_order_invoice = dao('order_invoice')->field('invoice_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_order_invoice)) {
			foreach ($from_order_invoice as $key => $value) {
				dao('order_invoice')->where('invoice_id = ' . $value['invoice_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_zc_focus = dao('zc_focus')->field('rec_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_zc_focus)) {
			foreach ($from_zc_focus as $key => $value) {
				dao('zc_focus')->where('rec_id = ' . $value['rec_id'])->setField('user_id', $to_user_id);
			}
		}

		$from_zc_topic = dao('zc_topic')->field('topic_id')->where(array('user_id' => $from_user_id))->select();

		if (!empty($from_zc_topic)) {
			foreach ($from_zc_topic as $key => $value) {
				dao('zc_topic')->where('topic_id = ' . $value['topic_id'])->setField('user_id', $to_user_id);
			}
		}

		if (!empty($from_user_info['user_name'])) {
			$GLOBALS['user']->remove_user($from_user_info['user_name']);
			return true;
		}
		else {
			return false;
		}
	}
	else {
		return false;
	}
}


?>
