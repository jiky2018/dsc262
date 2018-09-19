<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
function steps_users_list()
{
	$result = get_filter();

	if ($result === false) {
		$filter['keywords'] = !isset($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
		if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
			$filter['keywords'] = json_str_iconv($filter['keywords']);
		}

		$filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'mis.shop_id' : trim($_REQUEST['sort_by']);
		$filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
		$filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
		$filter['check'] = isset($_REQUEST['check']) ? intval($_REQUEST['check']) : 0;
		$filter['shopinfo_check'] = isset($_REQUEST['shopinfo_check']) ? intval($_REQUEST['shopinfo_check']) : 0;
		$ex_where = ' WHERE 1 ';
		$filter['store_search'] = empty($_REQUEST['store_search']) ? 0 : intval($_REQUEST['store_search']);
		$filter['merchant_id'] = isset($_REQUEST['merchant_id']) ? intval($_REQUEST['merchant_id']) : 0;
		$filter['store_keyword'] = isset($_REQUEST['store_keyword']) ? trim($_REQUEST['store_keyword']) : '';
		$store_where = '';
		$store_search_where = '';

		if ($filter['store_search'] != 0) {
			if ($ru_id == 0) {
				if ($_REQUEST['store_type']) {
					$store_search_where = 'AND mis.shopNameSuffix = \'' . $_REQUEST['store_type'] . '\'';
				}

				if ($filter['store_search'] == 1) {
					$ex_where .= ' AND mis.user_id = \'' . $filter['merchant_id'] . '\' ';
				}
				else if ($filter['store_search'] == 2) {
					$store_where .= ' AND mis.rz_shopName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\'';
				}
				else if ($filter['store_search'] == 3) {
					$store_where .= ' AND mis.shoprz_brandName LIKE \'%' . mysql_like_quote($filter['store_keyword']) . '%\' ' . $store_search_where;
				}

				if (1 < $filter['store_search']) {
					$ex_where .= ' AND mis.user_id > 0 ' . $store_where . ' ';
				}
			}
		}

		if ($filter['check'] == 1) {
			$ex_where .= ' AND mis.merchants_audit = \'0\'';
		}
		else if ($filter['check'] == 2) {
			$ex_where .= ' AND mis.merchants_audit = \'1\'';
		}
		else if ($filter['check'] == 3) {
			$ex_where .= ' AND mis.merchants_audit = \'2\'';
		}
		else if ($filter['check'] == 0) {
			$ex_where .= '';
		}

		if ($filter['shopinfo_check'] == 1) {
			$ex_where .= ' AND ss.review_status = \'1\'';
		}
		else if ($filter['shopinfo_check'] == 2) {
			$ex_where .= ' AND ss.review_status = \'2\'';
		}
		else if ($filter['shopinfo_check'] == 3) {
			$ex_where .= ' AND ss.review_status = \'3\'';
		}
		else if ($filter['check'] == 0) {
			$ex_where .= '';
		}

		$ex_where .= !empty($filter['user_name']) ? ' AND (u.user_name LIKE \'%' . mysql_like_quote($filter['user_name']) . '%\')' : '';
		$filter['record_count'] = $GLOBALS['db']->getOne('SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as mis ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' as u on mis.user_id = u.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' as ss on mis.user_id = ss.ru_id ' . $ex_where);
		$filter = page_and_size($filter);
		$sql = 'SELECT mis.* ' . ' FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' AS mis ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' as u on mis.user_id = u.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' as ss on mis.user_id = ss.ru_id ' . $ex_where . ' ORDER BY ' . $filter['sort_by'] . ' ' . $filter['sort_order'] . ' LIMIT ' . $filter['start'] . ',' . $filter['page_size'];
		$filter['keywords'] = stripslashes($filter['keywords']);
		set_filter($filter, $sql);
	}
	else {
		$sql = $result['sql'];
		$filter = $result['filter'];
	}

	$users_list = $GLOBALS['db']->getAll($sql);
	$count = count($users_list);

	for ($i = 0; $i < $count; $i++) {
		$users_list[$i]['shop_id'] = $users_list[$i]['shop_id'];
		$users_list[$i]['user_name'] = $GLOBALS['db']->getOne('SELECT user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_id = \'' . $users_list[$i]['user_id'] . '\'', true);
		$users_list[$i]['cat_name'] = $GLOBALS['db']->getOne('SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') . ' WHERE cat_id = \'' . $users_list[$i]['shop_categoryMain'] . '\'', true);
		$users_list[$i]['rz_shopName'] = get_shop_name($users_list[$i]['user_id'], 1);
		$sql = 'SELECT a.grade_img,a.grade_name FROM ' . $GLOBALS['ecs']->table('seller_grade') . ' AS a ' . 'LEFT JOIN ' . $GLOBALS['ecs']->table('merchants_grade') . ' AS b ON a.id = b.grade_id ' . ' WHERE b.ru_id = \'' . $users_list[$i]['user_id'] . '\' LIMIT 1';
		$grade = $GLOBALS['db']->getRow($sql);
		$users_list[$i]['grade_img'] = $grade['grade_img'];
		$users_list[$i]['grade_name'] = $grade['grade_name'];
		$sql = 'SELECT review_status FROM' . $GLOBALS['ecs']->table('seller_shopinfo') . 'WHERE ru_id = \'' . $users_list[$i]['user_id'] . '\'';
		$review_status = $GLOBALS['db']->getOne($sql);
		$users_list[$i]['review_status'] = $GLOBALS['_LANG']['not_audited'];

		if ($review_status == 2) {
			$users_list[$i]['review_status'] = $GLOBALS['_LANG']['audited_not_adopt'];
		}
		else if ($review_status == 3) {
			$users_list[$i]['review_status'] = $GLOBALS['_LANG']['audited_yes_adopt'];
		}

		$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'company_type');

		if ($field['bool'] == 1) {
			$users_list[$i]['company_type'] = $GLOBALS['db']->getOne('SELECT company_type FROM ' . $GLOBALS['ecs']->table('merchants_steps_fields') . ' WHERE user_id = \'' . $users_list[$i]['user_id'] . '\'', true);
		}
	}

	$arr = array('users_list' => $users_list, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}

function get_steps_user_shopInfo_list($user_id = 0, $ec_shop_bid = 0, $action = 'add_shop')
{
	$sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_process') . ' where 1 and process_steps <> 1 AND is_show = 1 AND id <> 10 order by process_steps asc';
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['sp_id'] = $row['id'];
		$arr[$key]['process_title'] = $row['process_title'];
		$arr[$key]['steps_title'] = get_user_steps_title($row['id'], $user_id, $ec_shop_bid, $action);
	}

	return $arr;
}

function get_user_steps_title($id = 0, $user_id, $ec_shop_bid, $action = 'add_shop')
{
	$copy_user_id = $user_id;

	if ($action == 'copy_shop') {
		$copy_user_id = 0;
	}

	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$sql = 'select tid, fields_titles, steps_style, titles_annotation from ' . $GLOBALS['ecs']->table('merchants_steps_title') . (' where fields_steps = \'' . $id . '\'');
	$res = $GLOBALS['db']->getAll($sql);
	$arr = array();

	foreach ($res as $key => $row) {
		$arr[$key]['tid'] = $row['tid'];
		$arr[$key]['fields_titles'] = $row['fields_titles'];
		$arr[$key]['steps_style'] = $row['steps_style'];
		$arr[$key]['titles_annotation'] = $row['titles_annotation'];
		$sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_fields_centent') . ' where tid = \'' . $row['tid'] . '\'';
		$centent = $GLOBALS['db']->getRow($sql);
		$cententFields = get_fields_centent_info($centent['id'], $centent['textFields'], $centent['fieldsDateType'], $centent['fieldsLength'], $centent['fieldsNotnull'], $centent['fieldsFormName'], $centent['fieldsCoding'], $centent['fieldsForm'], $centent['fields_sort'], $centent['will_choose'], 'root', $copy_user_id);
		$arr[$key]['cententFields'] = get_array_sort($cententFields, 'fields_sort');
		$shop_info = get_merchants_septs_custom_userInfo('merchants_shop_information', $user_id);

		if ($action == 'copy_shop') {
			$shop_info['rz_shopName'] = '';
			$shop_info['hopeLoginName'] = '';
		}

		$brand_info = get_merchants_septs_custom_userInfo('merchants_shop_brand', $user_id, 'pingpai', $ec_shop_bid);

		if ($row['steps_style'] == 1) {
			$parent = array('shoprz_type' => $shop_info['shoprz_type'], 'subShoprz_type' => $shop_info['subShoprz_type'], 'shop_expireDateStart' => local_date('Y-m-d H:i', $shop_info['shop_expireDateStart']), 'shop_expireDateEnd' => local_date('Y-m-d H:i', $shop_info['shop_expireDateEnd']), 'shop_permanent' => $shop_info['shop_permanent'], 'authorizeFile' => $shop_info['authorizeFile'], 'shop_hypermarketFile' => $shop_info['shop_hypermarketFile'], 'shop_categoryMain' => $shop_info['shop_categoryMain']);
		}
		else if ($row['steps_style'] == 2) {
			$arr[$key]['first_cate'] = get_first_cate_list('', '', '', $user_id);
			$parent = array('shop_categoryMain' => $shop_info['shop_categoryMain']);
		}
		else if ($row['steps_style'] == 3) {
			if ($action == 'copy_shop') {
				copy_septs_shop_brand_list($user_id);
			}

			$arr[$key]['brand_list'] = get_septs_shop_brand_list($copy_user_id);
			$brandfile_list = get_shop_brandfile_list($ec_shop_bid);
			$arr[$key]['brandfile_list'] = $brandfile_list;

			if (!empty($brand_info['brandEndTime'])) {
				$brand_info['brandEndTime'] = local_date('Y-m-d H:i', $brand_info['brandEndTime']);
			}
			else {
				$brand_info['brandEndTime'] = '';
			}

			$parent = array('bank_name_letter' => $brand_info['bank_name_letter'], 'brandName' => $brand_info['brandName'], 'brandFirstChar' => $brand_info['brandFirstChar'], 'brandLogo' => $brand_info['brandLogo'], 'brandType' => $brand_info['brandType'], 'brand_operateType' => $brand_info['brand_operateType'], 'brandEndTime' => $brand_info['brandEndTime'], 'brandEndTime_permanent' => $brand_info['brandEndTime_permanent']);
		}
		else if ($row['steps_style'] == 4) {
			$sql = 'select bid, brandName from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' where user_id = \'' . $user_id . '\'';
			$brand_list = $GLOBALS['db']->getAll($sql);
			$arr[$key]['brand_list'] = $brand_list;
			$belong_region = array();
			$belong_region['region_id'] = $shop_info['region_id'];
			$belong_region['region_level'] = get_region_level($shop_info['region_id']);
			$belong_region['country_list'] = get_regions_steps();
			$belong_region['province_list'] = get_regions_steps(1, 1);
			$belong_region['city_list'] = get_regions_steps(2, $belong_region['region_level'][1]);
			$arr[$key]['belong_region'] = $belong_region;
			$parent = array('shoprz_brandName' => $shop_info['shoprz_brandName'], 'shop_class_keyWords' => $shop_info['shop_class_keyWords'], 'shopNameSuffix' => $shop_info['shopNameSuffix'], 'rz_shopName' => $shop_info['rz_shopName'], 'hopeLoginName' => $shop_info['hopeLoginName'], 'region_id' => $shop_info['region_id']);

			switch ($shop_info['shoprz_type']) {
			case 1:
				$shop_info['shoprz_type'] = '旗舰店';
				break;

			case 2:
				$shop_info['shoprz_type'] = '专卖店';
				break;

			case 3:
				$shop_info['shoprz_type'] = '专营店';
				break;

			case 4:
				$shop_info['shoprz_type'] = '管';
				break;

			default:
			}

			$parent['shoprz_type'] = $shop_info['shoprz_type'];
		}

		$arr[$key]['parentType'] = $parent;
	}

	return $arr;
}

function get_merchants_septs_custom_userInfo($table = '', $user_id = 0, $type = '', $id = '')
{
	if ($type == 'pingpai') {
		$id = ' and bid = \'' . $id . '\'';
	}

	$sql = 'select * from ' . $GLOBALS['ecs']->table($table) . ' where user_id = \'' . $user_id . '\'' . $id;
	return $GLOBALS['db']->getRow($sql);
}

function get_admin_merchants_steps_title($user_id = 0, $addImg = '')
{
	include_once ROOT_PATH . '/includes/cls_image.php';
	$image = new cls_image($_CFG['bgcolor']);
	$sql = 'SELECT mst.tid, mst.fields_titles, mst.titles_annotation, mst.steps_style, mst.fields_special, mst.special_type FROM ' . $GLOBALS['ecs']->table('merchants_steps_process') . ' AS msp, ' . $GLOBALS['ecs']->table('merchants_steps_title') . ' AS mst WHERE 1 AND msp.is_show = 1 GROUP BY mst.tid ';
	$res = $GLOBALS['db']->getAll($sql);
	$ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? trim($_REQUEST['ec_shop_bid']) : 0;
	$ec_shoprz_type = isset($_POST['ec_shoprz_type']) ? intval($_POST['ec_shoprz_type']) : 0;
	$ec_subShoprz_type = isset($_POST['ec_subShoprz_type']) ? intval($_POST['ec_subShoprz_type']) : 0;
	$ec_shop_expireDateStart = isset($_POST['ec_shop_expireDateStart']) ? trim($_POST['ec_shop_expireDateStart']) : '';
	$ec_shop_expireDateEnd = isset($_POST['ec_shop_expireDateEnd']) ? trim($_POST['ec_shop_expireDateEnd']) : '';
	$ec_shop_permanent = isset($_POST['ec_shop_permanent']) ? intval($_POST['ec_shop_permanent']) : 0;
	$ec_shop_categoryMain = isset($_POST['ec_shop_categoryMain']) ? intval($_POST['ec_shop_categoryMain']) : 0;
	$bank_name_letter = isset($_POST['ec_bank_name_letter']) ? trim($_POST['ec_bank_name_letter']) : '';
	$brandName = isset($_POST['ec_brandName']) ? trim($_POST['ec_brandName']) : '';
	$brandFirstChar = isset($_POST['ec_brandFirstChar']) ? trim($_POST['ec_brandFirstChar']) : '';
	$brandLogo = isset($_FILES['ec_brandLogo']) ? $_FILES['ec_brandLogo'] : '';
	$brandLogo = $image->upload_image($brandLogo, 'septs_Image');
	$brandType = isset($_POST['ec_brandType']) ? intval($_POST['ec_brandType']) : 0;
	$brand_operateType = isset($_POST['ec_brand_operateType']) ? intval($_POST['ec_brand_operateType']) : 0;
	$brandEndTime = isset($_POST['ec_brandEndTime']) ? intval($_POST['ec_brandEndTime']) : '';
	$brandEndTime_permanent = isset($_POST['ec_brandEndTime_permanent']) ? intval($_POST['ec_brandEndTime_permanent']) : 0;
	$qualificationNameInput = isset($_POST['ec_qualificationNameInput']) ? $_POST['ec_qualificationNameInput'] : array();
	$qualificationImg = isset($_FILES['ec_qualificationImg']) ? $_FILES['ec_qualificationImg'] : array();
	$expiredDateInput = isset($_POST['ec_expiredDateInput']) ? $_POST['ec_expiredDateInput'] : array();
	$b_fid = isset($_POST['b_fid']) ? $_POST['b_fid'] : array();
	$ec_shoprz_brandName = isset($_POST['ec_shoprz_brandName']) ? $_POST['ec_shoprz_brandName'] : '';
	$ec_shop_class_keyWords = isset($_POST['ec_shop_class_keyWords']) ? $_POST['ec_shop_class_keyWords'] : '';
	$ec_shopNameSuffix = isset($_POST['ec_shopNameSuffix']) ? $_POST['ec_shopNameSuffix'] : '';
	$ec_rz_shopName = isset($_POST['ec_rz_shopName']) ? $_POST['ec_rz_shopName'] : '';
	$ec_hopeLoginName = isset($_POST['ec_hopeLoginName']) ? $_POST['ec_hopeLoginName'] : '';
	$region_id = isset($_POST['rs_city_id']) ? intval($_POST['rs_city_id']) : 0;
	$arr = array();

	foreach ($res as $key => $row) {
		$sql = 'select shop_id from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where user_id = \'' . $user_id . '\'';
		$shop_id = $GLOBALS['db']->getOne($sql);
		$arr[$key]['tid'] = $row['tid'];
		$sql = 'select * from ' . $GLOBALS['ecs']->table('merchants_steps_fields_centent') . ' where tid = \'' . $row['tid'] . '\'';
		$centent = $GLOBALS['db']->getRow($sql);
		$cententFields = get_fields_centent_info($centent['id'], $centent['textFields'], $centent['fieldsDateType'], $centent['fieldsLength'], $centent['fieldsNotnull'], $centent['fieldsFormName'], $centent['fieldsCoding'], $centent['fieldsForm'], $centent['fields_sort'], $centent['will_choose'], 'root', $user_id);
		$arr[$key]['cententFields'] = get_array_sort($cententFields, 'fields_sort');
		$shop_info = get_merchants_septs_custom_userinfo('merchants_shop_information');
		$brand_info = get_merchants_septs_custom_userinfo('merchants_shop_brand', $user_id, 'pingpai', $ec_shop_bid);

		if ($row['steps_style'] == 1) {
			if (isset($_FILES['ec_authorizeFile'])) {
				$ec_authorizeFile = $image->upload_image($_FILES['ec_authorizeFile'], 'septs_Image');
			}

			$ec_authorizeFile = empty($ec_authorizeFile) ? $shop_info['authorizeFile'] : $ec_authorizeFile;

			if (isset($_FILES['ec_authorizeFile'])) {
				$ec_shop_hypermarketFile = $image->upload_image($_FILES['ec_shop_hypermarketFile'], 'septs_Image');
			}

			$ec_shop_hypermarketFile = empty($ec_shop_hypermarketFile) ? $shop_info['shop_hypermarketFile'] : $ec_shop_hypermarketFile;

			if ($ec_shop_permanent != 1) {
				$ec_shop_expireDateStart = empty($ec_shop_expireDateStart) ? local_date('Y-m-d H:i', $shop_info['shop_expireDateStart']) : $ec_shop_expireDateStart;
				$ec_shop_expireDateEnd = empty($ec_shop_expireDateEnd) ? local_date('Y-m-d H:i', $shop_info['shop_expireDateEnd']) : $ec_shop_expireDateEnd;
				if (!empty($ec_shop_expireDateStart) || !empty($ec_shop_expireDateEnd)) {
					$ec_shop_expireDateStart = local_strtotime($ec_shop_expireDateStart);
					$ec_shop_expireDateEnd = local_strtotime($ec_shop_expireDateEnd);
				}
			}
			else {
				$ec_shop_expireDateStart = '';
				$ec_shop_expireDateEnd = '';
			}

			if ($ec_shoprz_type == 0) {
				$ec_shoprz_type = $shop_info['shoprz_type'];
			}

			if ($ec_subShoprz_type == 0) {
				$ec_subShoprz_type = $shop_info['subShoprz_type'];
			}

			if ($ec_shop_categoryMain == 0) {
				$ec_shop_categoryMain = $shop_info['shop_categoryMain'];
			}

			$parent = array('user_id' => $user_id, 'shoprz_type' => $ec_shoprz_type, 'subShoprz_type' => $ec_subShoprz_type, 'shop_expireDateStart' => $ec_shop_expireDateStart, 'shop_expireDateEnd' => $ec_shop_expireDateEnd, 'shop_permanent' => $ec_shop_permanent, 'authorizeFile' => $ec_authorizeFile, 'shop_hypermarketFile' => $ec_shop_hypermarketFile, 'shop_categoryMain' => $ec_shop_categoryMain);

			if (0 < $user_id) {
				if (0 < $shop_id) {
					if ($parent['shop_expireDateStart'] == '' || $parent['shop_expireDateEnd'] == '') {
						if ($ec_shop_permanent != 1) {
							if ($shop_info['shop_permanent'] == 1) {
								$parent['shop_permanent'] = $shop_info['shop_permanent'];
							}
						}
					}

					if (empty($parent['authorizeFile'])) {
						$parent['shop_permanent'] = 0;
					}
					else {
						if ($parent['shop_expireDateStart'] == '' || $parent['shop_expireDateEnd'] == '') {
							$parent['shop_permanent'] = 1;
							$parent['shop_expireDateStart'] = '';
							$parent['shop_expireDateEnd'] = '';
						}
					}

					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\'');
				}
				else {
					$parent['add_time'] = gmtime();
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'INSERT');
				}
			}

			if ($ec_shop_permanent == 0) {
				if ($parent['shop_expireDateStart'] != '') {
					$parent['shop_expireDateStart'] = local_date('Y-m-d H:i', $shop_info['shop_expireDateStart']);
				}

				if ($parent['shop_expireDateEnd'] != '') {
					$parent['shop_expireDateEnd'] = local_date('Y-m-d H:i', $shop_info['shop_expireDateEnd']);
				}
			}
		}
		else if ($row['steps_style'] == 2) {
			if (0 < $user_id) {
				if ($shop_id < 1) {
					$parent['user_id'] = $user_id;
					$parent['shop_categoryMain'] = $ec_shop_categoryMain;
					$parent['add_time'] = gmtime();
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'INSERT');
				}
			}

			$arr[$key]['first_cate'] = get_first_cate_list('', '', '', $user_id);
			$catId_array = get_catId_array($user_id);
			$parent['user_shopMain_category'] = implode('-', $catId_array);

			if ($ec_shop_categoryMain == 0) {
				$ec_shop_categoryMain = $shop_info['shop_categoryMain'];
				$parent['shop_categoryMain'] = $ec_shop_categoryMain;
			}

			$parent['shop_categoryMain'] = $ec_shop_categoryMain;
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\'');

			if (!empty($parent['user_shopMain_category'])) {
				get_update_temporarydate_isAdd($catId_array, $user_id);
			}

			get_update_temporarydate_isAdd($catId_array, $user_id, 1);
		}
		else if ($row['steps_style'] == 3) {
			$arr[$key]['brand_list'] = get_septs_shop_brand_list($user_id);

			if (0 < $ec_shop_bid) {
				$bank_name_letter = empty($bank_name_letter) ? $brand_info['bank_name_letter'] : $bank_name_letter;
				$brandName = empty($brandName) ? $brand_info['brandName'] : $brandName;
				$brandFirstChar = empty($brandFirstChar) ? $brand_info['brandFirstChar'] : $brandFirstChar;
				$brandLogo = empty($brandLogo) ? $brand_info['brandLogo'] : $brandLogo;
				$brandType = empty($brandType) ? $brand_info['brandType'] : $brandType;
				$brand_operateType = empty($brand_operateType) ? $brand_info['brand_operateType'] : $brand_operateType;
				$brandEndTime = empty($brandEndTime) ? $brand_info['brandEndTime'] : local_strtotime($brandEndTime);
				$brandEndTime_permanent = empty($brandEndTime_permanent) ? $brand_info['brandEndTime_permanent'] : $brandEndTime_permanent;
				$brandfile_list = get_shop_brandfile_list($ec_shop_bid);
				$arr[$key]['brandfile_list'] = $brandfile_list;
				$parent = array('user_id' => $user_id, 'bank_name_letter' => $bank_name_letter, 'brandName' => $brandName, 'brandFirstChar' => $brandFirstChar, 'brandLogo' => $brandLogo, 'brandType' => $brandType, 'brand_operateType' => $brand_operateType, 'brandEndTime' => $brandEndTime, 'brandEndTime_permanent' => $brandEndTime_permanent);

				if (!empty($parent['brandEndTime'])) {
					$arr[$key]['parentType']['brandEndTime'] = local_date('Y-m-d H:i', $parent['brandEndTime']);
				}

				if (0 < $user_id || $addImg == 'addImg') {
					if ($parent['brandEndTime_permanent'] == 1) {
						$parent['brandEndTime'] = '';
					}

					$sql = 'select bid from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . (' where brandName = \'' . $brandName . '\' and bid <> \'' . $ec_shop_bid . '\' and user_id = \'') . $user_id . '\'';
					$bRes = $GLOBALS['db']->getOne($sql);
					$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brand'), $parent, 'UPDATE', 'user_id = \'' . $user_id . ('\' and bid = \'' . $ec_shop_bid . '\''));
					get_shop_brand_file($qualificationNameInput, $qualificationImg, $expiredDateInput, $b_fid, $ec_shop_bid);
				}
			}
			else {
				if (0 < $user_id || $addImg == 'addImg') {
					$sql = 'select bid from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . (' where brandName = \'' . $brandName . '\' and user_id = \'') . $user_id . '\'';
					$bRes = $GLOBALS['db']->getOne($sql);

					if (!$bRes) {
						$parent = array('user_id' => $user_id, 'bank_name_letter' => $bank_name_letter, 'brandName' => $brandName, 'brandFirstChar' => $brandFirstChar, 'brandLogo' => $brandLogo, 'brandType' => $brandType, 'brand_operateType' => $brand_operateType, 'brandEndTime' => $brandEndTime, 'brandEndTime_permanent' => $brandEndTime_permanent);
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_brand'), $parent, 'INSERT');
						$bid = $GLOBALS['db']->insert_id();
						get_shop_brand_file($qualificationNameInput, $qualificationImg, $expiredDateInput, $b_fid, $bid);
					}
				}
			}
		}
		else if ($row['steps_style'] == 4) {
			$sql = 'select bid, brandName from ' . $GLOBALS['ecs']->table('merchants_shop_brand') . ' where user_id = \'' . $user_id . '\'';
			$brand_list = $GLOBALS['db']->getAll($sql);
			$arr[$key]['brand_list'] = $brand_list;
			$ec_shoprz_brandName = empty($ec_shoprz_brandName) ? $shop_info['shoprz_brandName'] : $ec_shoprz_brandName;
			$ec_shop_class_keyWords = empty($ec_shop_class_keyWords) ? $shop_info['shop_class_keyWords'] : $ec_shop_class_keyWords;
			$ec_shopNameSuffix = empty($ec_shopNameSuffix) ? $shop_info['shopNameSuffix'] : $ec_shopNameSuffix;
			$ec_rz_shopName = empty($ec_rz_shopName) ? $shop_info['rz_shopName'] : $ec_rz_shopName;
			$ec_hopeLoginName = empty($ec_hopeLoginName) ? $shop_info['hopeLoginName'] : $ec_hopeLoginName;
			$region_id = empty($region_id) ? $shop_info['region_id'] : $region_id;
			$belong_region = array();
			$belong_region['region_id'] = $region_id;
			$belong_region['region_level'] = get_region_level($region_id);
			$belong_region['country_list'] = get_regions_steps();
			$belong_region['province_list'] = get_regions_steps(1, 1);
			$belong_region['city_list'] = get_regions_steps(2, $belong_region['region_level'][1]);
			$arr[$key]['belong_region'] = $belong_region;

			if (!empty($ec_rz_shopName)) {
				$parent = array('shoprz_brandName' => $ec_shoprz_brandName, 'shop_class_keyWords' => $ec_shop_class_keyWords, 'shopNameSuffix' => $ec_shopNameSuffix, 'rz_shopName' => $ec_rz_shopName, 'hopeLoginName' => $ec_hopeLoginName, 'region_id' => $region_id);

				if (0 < $user_id) {
					if (0 < $shop_id) {
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\'');
					}
					else {
						$parent['add_time'] = gmtime();
						$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $parent, 'INSERT');
					}
				}
			}
		}
	}

	return $arr;
}

function get_admin_steps_title_insert_form($user_id)
{
	$steps_title = get_admin_merchants_steps_title($user_id);

	for ($i = 0; $i < count($steps_title); $i++) {
		if (is_array($steps_title[$i]['cententFields'])) {
			$cententFields = $steps_title[$i]['cententFields'];

			for ($j = 1; $j <= count($cententFields); $j++) {
				$arr['formName'] .= $cententFields[$j]['textFields'] . ',';
			}
		}
	}

	$arr['formName'] = substr($arr['formName'], 0, -1);
	return $arr;
}

function get_search_user_list($user_list)
{
	$html = '';

	if ($user_list) {
		$html .= '<ul>';

		foreach ($user_list as $key => $user) {
			$html .= '<li data-name=\'' . $user['user_name'] . '\' data-id=\'' . $user['user_id'] . '\'>' . $user['user_name'] . '</li>';
		}

		$html .= '</ul>';
	}
	else {
		$html = '<span class="red">查无该会员</span><input name="user_id" value="0" type="hidden" />';
	}

	return $html;
}

define('IN_ECS', true);
require dirname(__FILE__) . '/includes/init.php';
$exc = new exchange($ecs->table('merchants_shop_information'), $db, 'shop_id', 'shoprz_brandName');
$adminru = get_admin_ru_id();

if ($adminru['ru_id'] == 0) {
	$smarty->assign('priv_ru', 1);
}
else {
	$smarty->assign('priv_ru', 0);
}

if ($_REQUEST['act'] == 'list') {
	admin_priv('users_merchants');
	$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '02_merchants_users_list'));
	$smarty->assign('ur_here', $_LANG['02_merchants_users_list']);
	$smarty->assign('action_link', array('text' => $_LANG['01_merchants_user_add'], 'href' => 'merchants_users_list.php?act=add_shop'));
	$smarty->assign('action_link2', array('text' => $_LANG['02_initialize_seller_rank'], 'href' => 'merchants_users_list.php?act=create_initialize_rank'));
	$users_list = steps_users_list();
	$smarty->assign('users_list', $users_list['users_list']);
	$smarty->assign('filter', $users_list['filter']);
	$smarty->assign('record_count', $users_list['record_count']);
	$smarty->assign('page_count', $users_list['page_count']);
	$smarty->assign('full_page', 1);
	$smarty->assign('sort_user_id', '<img src="images/sort_desc.gif">');
	$sql = 'SELECT count(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' WHERE merchants_audit = 0';
	$shop_account = $db->getOne($sql);
	$smarty->assign('shop_account', $shop_account);
	$sql = 'SELECT COUNT(*) FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' as mis ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('users') . ' as u on mis.user_id = u.user_id ' . ' LEFT JOIN ' . $GLOBALS['ecs']->table('seller_shopinfo') . ' as ss on mis.user_id = ss.ru_id ' . ' WHERE ss.review_status = 1';
	$shopinfo_account = $db->getOne($sql);
	$smarty->assign('shopinfo_account', $shopinfo_account);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	assign_query_info();
	$smarty->display('merchants_users_list.dwt');
}
else if ($_REQUEST['act'] == 'check_shop_name') {
	$shop_name = empty($_REQUEST['shop_name']) ? '' : $_REQUEST['shop_name'];
	$adminru = empty($_REQUEST['user_id']) ? '' : $_REQUEST['user_id'];
	$sql = ' select * from ' . $GLOBALS['ecs']->table('merchants_shop_information') . ' where rz_shopName=\'' . $shop_name . '\' limit 1';
	$shop_info = $GLOBALS['db']->getRow($sql);
	$data = array();
	if (!empty($shop_info) && $shop_info['user_id'] != $adminru) {
		$data['error'] = 1;
	}
	else {
		$data['error'] = 2;
	}

	exit(json_encode($data));
}
else if ($_REQUEST['act'] == 'query') {
	$users_list = steps_users_list();
	$smarty->assign('users_list', $users_list['users_list']);
	$smarty->assign('filter', $users_list['filter']);
	$smarty->assign('record_count', $users_list['record_count']);
	$smarty->assign('page_count', $users_list['page_count']);
	$store_list = get_common_store_list();
	$smarty->assign('store_list', $store_list);
	$sort_flag = sort_flag($users_list['filter']);
	$smarty->assign($sort_flag['tag'], $sort_flag['img']);
	make_json_result($smarty->fetch('merchants_users_list.dwt'), '', array('filter' => $users_list['filter'], 'page_count' => $users_list['page_count']));
}

if ($_REQUEST['act'] == 'add_shop' || $_REQUEST['act'] == 'edit_shop' || $_REQUEST['act'] == 'copy_shop') {
	admin_priv('users_merchants');
	$db->query('DELETE FROM' . $ecs->table('merchants_shop_brand') . ' WHERE (user_id = 0 or user_id = \'\')');
	$user_id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
	$shopInfo_list = get_steps_user_shopInfo_list($user_id, 0, $_REQUEST['act']);
	$smarty->assign('shopInfo_list', $shopInfo_list);
	$smarty->assign('action_link', array('text' => $_LANG['02_merchants_users_list'], 'href' => 'merchants_users_list.php?act=list'));
	$seller_grade_list = $db->getAll(' SELECT * FROM' . $ecs->table('seller_grade'));
	$smarty->assign('seller_grade_list', $seller_grade_list);
	$grade = $db->getRow('SELECT a.grade_id ,a.year_num, b.grade_name FROM' . $ecs->table('merchants_grade') . ' as a LEFT JOIN ' . $ecs->table('seller_grade') . (' as b ON a.grade_id = b.id  WHERE ru_id = \'' . $user_id . '\''));
	$smarty->assign('grade', $grade);
	$category_info = get_fine_category_info(0, $user_id);
	$smarty->assign('category_info', $category_info);
	$permanent_list = get_category_permanent_list($user_id);
	$smarty->assign('permanent_list', $permanent_list);
	$country_list = get_regions_steps();
	$province_list = get_regions_steps(1, 1);
	$city_list = get_regions_steps(2, $consignee['province']);
	$district_list = get_regions_steps(3, $consignee['city']);
	$sql = ' SELECT region_id, region_name FROM ' . $ecs->table('region');
	$region = $db->getAll($sql);

	foreach ($region as $v) {
		$regions[$v['region_id']] = $v['region_name'];
	}

	$smarty->assign('regions', $regions);
	$sql = 'select steps_audit, merchants_audit, merchants_message, review_goods, self_run, shop_close from ' . $ecs->table('merchants_shop_information') . (' where user_id = \'' . $user_id . '\'');
	$merchants = $db->getRow($sql);
	$smarty->assign('merchants', $merchants);
	$sn = 0;
	$smarty->assign('country_list', $country_list);
	$smarty->assign('province_list', $province_list);
	$smarty->assign('city_list', $city_list);
	$smarty->assign('district_list', $district_list);
	$smarty->assign('consignee', $consignee);
	$smarty->assign('sn', $sn);

	if ($_REQUEST['act'] == 'copy_shop') {
		$user_id = 0;
		$smarty->assign('copy_action', $_REQUEST['act']);
	}

	$smarty->assign('user_id', $user_id);

	if ($_REQUEST['act'] == 'edit_shop') {
		$seller_shopinfo = get_shop_name($user_id, 2);
		$smarty->assign('seller_shopinfo', $seller_shopinfo);
		$smarty->assign('form_action', 'update_shop');
	}
	else {
		$sql = 'select user_id, user_name from' . $ecs->table('users') . ' where 1';
		$user_list = $db->getAll($sql);
		$smarty->assign('user_list', $user_list);
		$smarty->assign('form_action', 'insert_shop');
	}

	$smarty->assign('brand_ajax', 1);
	assign_query_info();
	$smarty->display('merchants_users_shopInfo.dwt');
}
else if ($_REQUEST['act'] == 'toggle_is_street') {
	check_authz_json('goods_manage');
	$shop_id = intval($_POST['id']);
	$is_street = intval($_POST['val']);

	if ($exc->edit('is_street = \'' . $is_street . '\'', $shop_id)) {
		clear_cache_files();
		make_json_result($is_street);
	}
}
else if ($_REQUEST['act'] == 'toggle_is_IM') {
	check_authz_json('goods_manage');
	$shop_id = intval($_POST['id']);
	$is_IM = intval($_POST['val']);

	if ($exc->edit('is_IM = \'' . $is_IM . '\'', $shop_id)) {
		clear_cache_files();
		make_json_result($is_IM);
	}
}
else {
	if ($_REQUEST['act'] == 'insert_shop' || $_REQUEST['act'] == 'update_shop') {
		admin_priv('users_merchants');
		$copy_action = isset($_REQUEST['copy_action']) ? trim($_REQUEST['copy_action']) : 'update_shop';
		$brand_copy_id = isset($_REQUEST['brand_copy_id']) ? $_REQUEST['brand_copy_id'] : array();
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$merchants_audit = isset($_REQUEST['merchants_audit']) ? intval($_REQUEST['merchants_audit']) : 0;
		$merchants_allow = isset($_REQUEST['merchants_allow']) ? intval($_REQUEST['merchants_allow']) : 0;
		$merchants_message = isset($_REQUEST['merchants_message']) ? trim($_REQUEST['merchants_message']) : '';
		$review_goods = isset($_REQUEST['review_goods']) ? intval($_REQUEST['review_goods']) : 0;
		$shopname_audit = isset($_REQUEST['shopname_audit']) ? intval($_REQUEST['shopname_audit']) : 1;
		$old_merchants_audit = isset($_REQUEST['old_merchants_audit']) ? intval($_REQUEST['old_merchants_audit']) : 0;
		$default_grade = $db->getOne('SELECT id FROM' . $ecs->table('seller_grade') . ' WHERE is_default = 1');
		$grade_id = isset($_REQUEST['grade_id']) ? intval($_REQUEST['grade_id']) : $grade_id;
		$year_num = isset($_REQUEST['year_num']) ? intval($_REQUEST['year_num']) : 1;
		$self_run = isset($_REQUEST['self_run']) ? intval($_REQUEST['self_run']) : 0;
		$shop_close = isset($_REQUEST['shop_close']) ? intval($_REQUEST['shop_close']) : 1;

		if ($user_id == 0) {
			$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_users_list.php?act=add_shop');
			$centent = $_LANG['user_select_please'];
			sys_msg($centent, 0, $link);
		}

		$form = get_admin_steps_title_insert_form($user_id);
		$parent = get_setps_form_insert_date($form['formName']);

		if ($old_merchants_audit != $merchants_audit) {
			$sql = 'SELECT COUNT(id) FROM ' . $ecs->table('merchants_grade') . (' WHERE ru_id = ' . $user_id);
			$grade = $db->getOne($sql);

			if ($merchants_audit == 1) {
				if (0 < $grade) {
					$db->query('UPDATE' . $ecs->table('merchants_grade') . (' SET grade_id = \'' . $grade_id . '\' ,year_num=\'' . $year_num . '\'  WHERE ru_id = \'' . $user_id . '\' '));
				}
				else {
					$add_time = gmtime();
					$db->query('INSERT INTO ' . $ecs->table('merchants_grade') . (' (`ru_id`,`grade_id`,`add_time`,`year_num`) VALUES (\'' . $user_id . '\',\'' . $grade_id . '\',\'' . $add_time . '\',\'' . $year_num . '\')'));
				}

				$sql = 'SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE ru_id = \'' . $user_id . '\'');
				$action_list = $db->getOne($sql);

				if (empty($action_list)) {
					$sql = ' SELECT action_list FROM ' . $ecs->table('merchants_privilege') . (' WHERE grade_id = \'' . $grade_id . '\' ');
					$action = array('action_list' => $db->getOne($sql));
					$db->autoExecute($ecs->table('admin_user'), $action, 'UPDATE', 'ru_id = \'' . $user_id . '\'');
				}
			}
			else if (0 < $grade) {
				$db->query('DELETE FROM ' . $ecs->table('merchants_grade') . (' WHERE ru_id = \'' . $user_id . '\''));
			}
		}

		$sql = 'SELECT allow_number, review_goods FROM ' . $GLOBALS['ecs']->table('merchants_shop_information') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
		$shop_info = $GLOBALS['db']->getRow($sql);
		$allow_number = $shop_info['allow_number'];

		if ($_REQUEST['act'] == 'update_shop') {
			if ($merchants_audit != 1) {
				$db->query('UPDATE ' . $ecs->table('goods') . (' SET is_on_sale = 0 WHERE user_id = \'' . $user_id . '\''));
			}
			else {
				$goods_where = ' AND (SELECT COUNT(*) FROM ' . $ecs->table('presale_activity') . ' AS pa WHERE g.goods_id = pa.goods_id LIMIT 1) = 0 ';
			}

			if ($shop_close != 1) {
				$db->query('UPDATE ' . $ecs->table('presale_activity') . (' SET review_status = 1 WHERE user_id = \'' . $user_id . '\''));
				$db->query('UPDATE ' . $ecs->table('goods') . (' SET review_status = 1 WHERE user_id = \'' . $user_id . '\''));
			}
			else {
				if ($GLOBALS['_CFG']['review_goods'] == 0 || $shop_info['review_goods'] == 0) {
					$db->query('UPDATE ' . $ecs->table('presale_activity') . (' SET review_status = 3 WHERE user_id = \'' . $user_id . '\''));
					$db->query('UPDATE ' . $ecs->table('goods') . (' SET review_status = 3 WHERE user_id = \'' . $user_id . '\''));
				}
			}

			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'UPDATE', 'user_id = \'' . $user_id . '\'');
		}
		else {
			$parent['user_id'] = $user_id;
			$parent['agreement'] = 1;
			$sql = 'SELECT fid FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\'');
			$fid = $db->getOne($sql, true);

			if (0 < $fid) {
				$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_users_list.php?act=add_shop');
				$centent = $_LANG['insert_fail'];
				sys_msg($centent, 0, $link);
				exit();
			}
			else {
				$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_steps_fields'), $parent, 'INSERT');
			}
		}

		$info['merchants_audit'] = $merchants_audit;
		$info['review_goods'] = $review_goods;
		$info['self_run'] = $self_run;

		if ($merchants_allow == 1) {
			$info['steps_audit'] = 0;
			$info['allow_number'] = $allow_number + 1;
		}
		else {
			$ec_hopeLoginName = isset($_REQUEST['ec_hopeLoginName']) ? trim($_REQUEST['ec_hopeLoginName']) : '';
			$sql = 'select user_id from ' . $ecs->table('admin_user') . (' where user_name = \'' . $ec_hopeLoginName . '\' AND ru_id <> \'' . $user_id . '\'');
			$adminId = $db->getOne($sql);

			if (0 < $adminId) {
				if ($_REQUEST['act'] == 'update_shop') {
					$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_users_list.php?act=edit_shop&id=' . $user_id);
				}
				else {
					$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_users_list.php?act=add_shop');
				}

				sys_msg($_LANG['adminId_have'], 0, $link);
				exit();
			}

			$info['steps_audit'] = 1;
		}

		$info['merchants_message'] = $merchants_message;
		$info['shop_close'] = $shop_close;
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('merchants_shop_information'), $info, 'UPDATE', 'user_id = \'' . $user_id . '\'');
		$seller_shopinfo = array('shopname_audit' => $shopname_audit, 'shop_close' => $shop_close);
		$shopinfo = get_seller_shopinfo($user_id);

		if ($shopinfo) {
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_shopinfo'), $seller_shopinfo, 'UPDATE', 'ru_id = \'' . $user_id . '\'');
		}
		else if ($merchants_audit == 1) {
			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactPhone');

			if ($field['bool']) {
				$steps_fields = get_merchants_steps_fields($user_id, array('contactPhone'));
				$seller_shopinfo['mobile'] = $steps_fields['contactPhone'];
			}

			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactEmail');

			if ($field['bool']) {
				$steps_fields = get_merchants_steps_fields($user_id, array('contactEmail'));
				$seller_shopinfo['seller_email'] = $steps_fields['contactEmail'];
			}

			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'company_adress');

			if ($field['bool']) {
				$steps_fields = get_merchants_steps_fields($user_id, array('company_adress'));
				$seller_shopinfo['shop_address'] = $steps_fields['company_adress'];
			}

			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'company_located');

			if ($field['bool']) {
				$steps_fields = get_merchants_steps_fields($user_id, array('company_located'));

				if ($steps_fields['company_located']) {
					$region = explode(',', $steps_fields['company_located']);
					$seller_shopinfo['country'] = $region[0];
					$seller_shopinfo['province'] = $region[1];
					$seller_shopinfo['city'] = $region[2];
					$seller_shopinfo['district'] = $region[3];
				}
			}

			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'companyName');

			if ($field['bool']) {
				$steps_fields = get_merchants_steps_fields($user_id, array('companyName'));
				$seller_shopinfo['shop_name'] = $steps_fields['companyName'];
			}

			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'company_contactTel');

			if ($field['bool']) {
				$steps_fields = get_merchants_steps_fields($user_id, array('company_contactTel'));
				$seller_shopinfo['kf_tel'] = $steps_fields['company_contactTel'];
			}

			$seller_shopinfo['ru_id'] = $user_id;
			$seller_shopinfo['templates_mode'] = 1;
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('seller_shopinfo'), $seller_shopinfo);
		}

		if ($merchants_audit == 1) {
			$tpl_dir = ROOT_PATH . 'data/seller_templates/seller_tem_' . $user_id;
			$tpl_arr = get_dir_file_list($tpl_dir);

			if (empty($tpl_arr)) {
				require ROOT_PATH . '/includes/lib_visual.php';
				$new_suffix = get_new_dirName($user_id);
				$dir = ROOT_PATH . 'data/seller_templates/seller_tem/Bucket_tpl';
				$file = $tpl_dir . '/' . $new_suffix;

				if (!empty($new_suffix)) {
					if (!is_dir($file)) {
						make_dir($file);
					}

					recurse_copy($dir, $file, 1);
					$result['error'] = 0;
				}

				$sql = ' UPDATE' . $ecs->table('seller_shopinfo') . ('SET seller_templates = \'' . $new_suffix . '\' WHERE ru_id=\'' . $user_id . '\'');
				$db->query($sql);
			}

			$href = 'merchants_users_list.php?act=allot&user_id=' . $user_id;
		}
		else {
			$href = 'merchants_users_list.php?act=list';
		}

		if ($review_goods == 0 && $shop_close == 1) {
			$goods_date['review_status'] = 3;
			$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('goods'), $goods_date, 'UPDATE', 'user_id = \'' . $user_id . '\'');
		}

		if ($copy_action == 'copy_shop') {
			$sql = 'UPDATE' . $ecs->table('merchants_shop_brand') . ('SET user_id = \'' . $user_id . '\' WHERE bid ') . db_create_in($brand_copy_id);
			$db->query($sql);
		}

		if ($_REQUEST['act'] == 'update_shop') {
			$centent = $_LANG['update_success'];
		}
		else {
			$centent = $_LANG['insert_success'];
		}

		$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
		sys_msg($centent, 0, $link);
	}
	else if ($_REQUEST['act'] == 'allot') {
		admin_priv('users_merchants');
		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

		if ($id == 0) {
			$user_id = $user_id;
		}
		else {
			$user_id = $id;
		}

		$smarty->assign('ur_here', $_LANG['allot_priv']);
		$smarty->assign('action_link', array('text' => $_LANG['restore_default_priv'], 'href' => 'merchants_users_list.php?act=restore_default_priv&user_id=' . $user_id));
		$sql = 'select steps_audit, merchants_audit, hopeLoginName from ' . $ecs->table('merchants_shop_information') . (' where user_id = \'' . $user_id . '\'');
		$merchants = $db->getRow($sql);
		$sql = 'select user_name, email, user_id from ' . $ecs->table('users') . (' where user_id = \'' . $user_id . '\'');
		$user = $db->getRow($sql);

		if (empty($merchants['hopeLoginName'])) {
			$user_name = $user['user_name'];
		}
		else {
			$user_name = $merchants['hopeLoginName'];
		}

		$ec_salt = rand(1, 9999);
		$pwd = $GLOBALS['_CFG']['merchants_prefix'] . $user_id;
		$password = md5(md5($pwd) . $ec_salt);
		$sql = 'SELECT grade_id FROM' . $ecs->table('merchants_grade') . (' WHERE ru_id = \'' . $user_id . '\' LIMIT 1 ');
		$merchants_grade = $db->getRow($sql);
		$grade_id = 0 < $merchants_grade['grade_id'] ? $merchants_grade['grade_id'] : 0;
		$action_list = $db->getOne('SELECT action_list FROM ' . $ecs->table('merchants_privilege') . (' WHERE  grade_id = \'' . $grade_id . '\''));
		$sql = 'SELECT nav_list FROM ' . $ecs->table('admin_user') . ' WHERE action_list = \'all\'';
		$row = $db->getRow($sql);
		$sql = 'SELECT action_list FROM ' . $ecs->table('admin_user') . (' WHERE ru_id = \'' . $user_id . '\' AND parent_id =0');
		$rows = $db->getRow($sql);

		if (isset($rows['action_list'])) {
			$action_list = $rows['action_list'];
		}

		$sql = 'select user_id from ' . $ecs->table('admin_user') . (' where ru_id = \'' . $user_id . '\'');
		$adminId = $db->getOne($sql);

		if (0 < $adminId) {
			$sql = 'update ' . $ecs->table('admin_user') . (' set user_name = \'' . $user_name . '\', email = \'' . $email . '\', ') . ('nav_list = \'' . $row['nav_list'] . '\', action_list = \'' . $action_list . '\' where ru_id = \'' . $user_id . '\' AND parent_id = 0 AND suppliers_id = 0 ');
		}
		else {
			$sql = 'INSERT INTO ' . $ecs->table('admin_user') . ' (user_name, email, password, ec_salt, nav_list, action_list, ru_id) ' . ('VALUES (\'' . $user_name . '\', \'') . $email . ('\', \'' . $password . '\', \'' . $ec_salt . '\', \'' . $row['nav_list'] . '\', \'' . $action_list . '\', \'' . $user_id . '\')');
		}

		$db->query($sql);
		$user_priv = $db->getRow('SELECT user_id, user_name, action_list FROM ' . $ecs->table('admin_user') . (' WHERE user_name = \'' . $user_name . '\''));
		$admin_id = $user_priv['user_id'];
		$priv_str = $user_priv['action_list'];

		if ($id == 0) {
			if ($adminId < 1) {
				$current_admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
				$shop_name = get_shop_name($user_id, 1);
				$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactPhone');

				if ($field['bool']) {
					$sql = ' SELECT contactPhone AS mobile FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
					$shopinfo = $db->getRow($sql);
					$smsParams = array('seller_name' => $shop_name, 'sellername' => $shop_name, 'login_name' => $user_name ? htmlspecialchars($user_name) : '', 'loginname' => $user_name ? htmlspecialchars($user_name) : '', 'password' => $pwd ? htmlspecialchars($pwd) : '', 'admin_name' => $current_admin_name ? $current_admin_name : '', 'adminname' => $current_admin_name ? $current_admin_name : '', 'edit_time' => local_date('Y-m-d H:i:s', gmtime()), 'edittime' => local_date('Y-m-d H:i:s', gmtime()), 'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '', 'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '');
					if ($adminru['ru_id'] == 0 && $GLOBALS['_CFG']['sms_seller_signin'] == '1' && $shopinfo['mobile'] != '') {
						if ($GLOBALS['_CFG']['sms_type'] == 0) {
							huyi_sms($smsParams, 'sms_seller_signin');
						}
						else if (1 <= $GLOBALS['_CFG']['sms_type']) {
							$result = sms_ali($smsParams, 'sms_seller_signin');

							if ($result) {
								$resp = $GLOBALS['ecs']->ali_yu($result);
							}
							else {
								sys_msg('阿里大鱼短信配置异常', 1);
							}
						}
					}
				}

				$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactEmail');

				if ($field['bool']) {
					$sql = ' SELECT contactEmail AS seller_email FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
					$shopinfo = $db->getRow($sql);
					$template = get_mail_template('seller_signin');
					if ($adminru['ru_id'] == 0 && $template['template_content'] != '') {
						if ($shopinfo['seller_email']) {
							$smarty->assign('shop_name', $shop_name);
							$smarty->assign('seller_name', $user_name);
							$smarty->assign('seller_psw', $pwd);
							$smarty->assign('site_name', $_CFG['shop_name']);
							$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
							$content = $smarty->fetch('str:' . $template['template_content']);
							send_mail($user_name, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
						}
					}
				}
			}
		}

		$sql_query = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id = 0 AND seller_show = 1';
		$res = $db->query($sql_query);

		while ($rows = $db->FetchRow($res)) {
			$priv_arr[$rows['action_id']] = $rows;
		}

		if ($priv_arr) {
			$sql = 'SELECT action_id, parent_id, action_code,relevance FROM ' . $ecs->table('admin_action') . ' WHERE parent_id ' . db_create_in(array_keys($priv_arr)) . ' AND seller_show = 1';
			$result = $db->query($sql);

			while ($priv = $db->FetchRow($result)) {
				$priv_arr[$priv['parent_id']]['priv'][$priv['action_code']] = $priv;
			}

			foreach ($priv_arr as $action_id => $action_group) {
				if (isset($action_group['priv']) && $action_group['priv']) {
					$priv_arr[$action_id]['priv_list'] = join(',', @array_keys($action_group['priv']));

					foreach ($action_group['priv'] as $key => $val) {
						$priv_arr[$action_id]['priv'][$key]['cando'] = strpos($priv_str, $val['action_code']) !== false || $priv_str == 'all' ? 1 : 0;
					}
				}
			}
		}

		$smarty->assign('priv_arr', $priv_arr);
		$smarty->assign('form_action', 'update_allot');
		$smarty->assign('admin_id', $admin_id);
		$smarty->assign('user_id', $user_id);

		if (!empty($user_priv['user_name'])) {
			$user_name = $user_priv['user_name'];
		}

		$smarty->assign('user_name', $user_name);
		$smarty->assign('users', get_table_date('merchants_shop_information', 'user_id=\'' . $user_id . '\'', array('user_id', 'hopeLoginName', 'merchants_audit')));
		$smarty->assign('menu_select', array('action' => 'seller_shopinfo', 'action' => 'templates', 'current' => 'allot'));
		assign_query_info();
		$smarty->display('merchants_user_allot.dwt');
	}
	else if ($_REQUEST['act'] == 'restore_default_priv') {
		admin_priv('users_merchants');
		$user_id = !empty($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;

		if (0 < $user_id) {
			$sql = 'select user_id from ' . $ecs->table('admin_user') . (' where ru_id = \'' . $user_id . '\' AND parent_id =0 ');
			$adminId = $db->getOne($sql);
			$sql = 'SELECT grade_id FROM' . $ecs->table('merchants_grade') . (' WHERE ru_id = \'' . $user_id . '\' LIMIT 1 ');
			$merchants_grade = $db->getRow($sql);
			$grade_id = 0 < $merchants_grade['grade_id'] ? $merchants_grade['grade_id'] : 0;
			$action_list = $db->getOne('SELECT action_list FROM ' . $ecs->table('merchants_privilege') . (' WHERE grade_id = \'' . $grade_id . '\''));
			$sql = ' update ' . $ecs->table('admin_user') . (' set action_list = \'' . $action_list . '\' where user_id = \'' . $adminId . '\' ');
			$db->query($sql);
			$update_success = $_LANG['update_success'];
		}
		else {
			$update_success = $_LANG['update_fail'];
		}

		$href = 'merchants_users_list.php?act=list';
		$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
		sys_msg($update_success, 0, $link);
	}
	else if ($_REQUEST['act'] == 'update_allot') {
		admin_priv('users_merchants');
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$login_name = isset($_REQUEST['login_name']) ? trim($_REQUEST['login_name']) : '';
		$ec_salt = rand(1, 9999);
		$login_password = !empty($_REQUEST['login_password']) ? trim($_REQUEST['login_password']) : '';

		if (!empty($login_password)) {
			$seller_psw = $login_password;
			$login_password = ', password = \'' . md5(md5($login_password) . $ec_salt) . '\'';
			$ec_salt = ', ec_salt = \'' . $ec_salt . '\'';
		}
		else {
			$login_password = '';
			$ec_salt = '';
		}

		if (!empty($login_name)) {
			$sql = 'SELECT COUNT(*) FROM ' . $ecs->table('admin_user') . (' WHERE user_name = \'' . $login_name . '\' AND ru_id <> \'' . $user_id . '\'');

			if ($db->getOne($sql) == 0) {
				$sql = 'update ' . $ecs->table('merchants_shop_information') . (' set hopeLoginName = \'' . $login_name . '\' where user_id = \'' . $user_id . '\'');
				$db->query($sql);
				$seller_name = $login_name;
				$login_name = ' ,user_name = \'' . $login_name . '\'';
			}
			else {
				sys_msg('登录名称已存在！');
				exit();
			}
		}
		else {
			sys_msg('登录名称不为空！');
		}

		$act_list = @join(',', $_POST['action_code']);
		$sql = 'UPDATE ' . $ecs->table('admin_user') . (' SET action_list = \'' . $act_list . '\', role_id = \'\' ') . $login_name . $login_password . $ec_salt . (' WHERE ru_id = \'' . $user_id . '\' AND parent_id = 0 AND suppliers_id = 0 ');
		$db->query($sql);
		$current_admin_name = $db->getOne('SELECT user_name FROM ' . $ecs->table('admin_user') . ' WHERE user_id = \'' . $_SESSION['admin_id'] . '\'');
		$shop_name = get_shop_name($user_id, 1);
		$sql = ' SELECT mobile, seller_email FROM ' . $ecs->table('seller_shopinfo') . (' WHERE ru_id = \'' . $user_id . '\' LIMIT 1');
		$shopinfo = $db->getRow($sql);
		$err_code = true;

		if (empty($shopinfo['mobile'])) {
			$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactPhone');

			if ($field['bool']) {
				$sql = ' SELECT contactPhone AS mobile FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
				$shopinfo = $db->getRow($sql);
			}
		}

		if ($adminru['ru_id'] == 0 && $GLOBALS['_CFG']['sms_seller_signin'] == '1' && $shopinfo['mobile'] != '') {
			$smsParams = array('seller_name' => $shop_name, 'sellername' => $shop_name, 'login_name' => $seller_name ? htmlspecialchars($seller_name) : '', 'loginname' => $seller_name ? htmlspecialchars($seller_name) : '', 'password' => $seller_psw ? htmlspecialchars($seller_psw) : '', 'admin_name' => $current_admin_name ? $current_admin_name : '', 'adminname' => $current_admin_name ? $current_admin_name : '', 'edit_time' => local_date('Y-m-d H:i:s', gmtime()), 'edittime' => local_date('Y-m-d H:i:s', gmtime()), 'mobile_phone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '', 'mobilephone' => $shopinfo['mobile'] ? $shopinfo['mobile'] : '');

			if ($GLOBALS['_CFG']['sms_type'] == 0) {
				$send_result = huyi_sms($smsParams, 'sms_seller_signin');
				if (isset($send_result) && $send_result) {
					$err_code = true;
				}
				else {
					$err_code = false;
				}
			}
			else if (1 <= $GLOBALS['_CFG']['sms_type']) {
				if ($seller_name && $seller_psw) {
					$result = sms_ali($smsParams, 'sms_seller_signin');

					if ($result) {
						$resp = $GLOBALS['ecs']->ali_yu($result);

						if ($resp->code == 0) {
							$err_code = true;
						}
						else {
							$err_code = false;
						}
					}
					else {
						sys_msg('阿里大鱼短信配置异常', 1);
					}
				}
			}
		}

		if ($seller_name && $seller_psw) {
			if ($err_code) {
				$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactEmail');

				if ($field['bool']) {
					if (empty($shopinfo['seller_email'])) {
						$sql = ' SELECT contactEmail AS seller_email FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\' LIMIT 1');
						$shopinfo = $db->getRow($sql);
					}
				}

				admin_log(addslashes($current_admin_name), 'edit', 'merchants_users_list');
				$template = get_mail_template('seller_signin');
				if ($adminru['ru_id'] == 0 && $template['template_content'] != '') {
					if (empty($shopinfo['seller_email'])) {
						$field = get_table_file_name($GLOBALS['ecs']->table('merchants_steps_fields'), 'contactEmail');

						if ($field['bool']) {
							$sql = ' SELECT contactEmail AS seller_email FROM ' . $ecs->table('merchants_steps_fields') . (' WHERE user_id = \'' . $user_id . '\'');
							$seller_email = $db->getOne($sql);
							$shopinfo['seller_email'] = $seller_email;
						}
					}

					if ($shopinfo['seller_email'] && ($seller_name != '' || $seller_psw != '')) {
						$smarty->assign('shop_name', $shop_name);
						$smarty->assign('seller_name', $seller_name);
						$smarty->assign('seller_psw', $seller_psw);
						$smarty->assign('site_name', $_CFG['shop_name']);
						$smarty->assign('send_date', local_date($GLOBALS['_CFG']['time_format'], gmtime()));
						$content = $smarty->fetch('str:' . $template['template_content']);
						send_mail($seller_name, $shopinfo['seller_email'], $template['template_subject'], $content, $template['is_html']);
					}
				}

				$update_success = $_LANG['update_success'];
			}
			else {
				$update_success = $_LANG['update_fail'];
			}
		}
		else {
			$update_success = $_LANG['update_success'];
		}

		$href = 'merchants_users_list.php?act=list';
		$link[] = array('text' => $_LANG['go_back'], 'href' => $href);
		sys_msg($update_success, 0, $link);
	}
	else if ($_REQUEST['act'] == 'remove') {
		admin_priv('users_merchants_drop');
		$id = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
		$sql = 'delete from ' . $ecs->table('merchants_shop_information') . (' where user_id = \'' . $id . '\'');
		$db->query($sql);
		$sql = 'delete from ' . $ecs->table('merchants_steps_fields') . (' where user_id = \'' . $id . '\'');
		$db->query($sql);
		if ($GLOBALS['_CFG']['delete_seller'] && $id) {
			get_delete_seller_info('seller_shopbg', 'ru_id = \'' . $id . '\'');
			get_delete_seller_info('seller_shopwindow', 'ru_id = \'' . $id . '\'');
			get_delete_seller_info('seller_shopheader', 'ru_id = \'' . $id . '\'');
			get_delete_seller_info('seller_shopslide', 'ru_id = \'' . $id . '\'');
			get_delete_seller_info('seller_shopinfo', 'ru_id = \'' . $id . '\'');
			get_delete_seller_info('seller_domain', 'ru_id = \'' . $id . '\'');
			get_delete_seller_info('admin_user', 'ru_id = \'' . $id . '\'');
			get_seller_delete_order_list($id);
			get_seller_delete_goods_list($id);
			get_delete_seller_info('merchants_category', 'user_id = \'' . $id . '\'');
		}

		$link[] = array('text' => $_LANG['go_back'], 'href' => 'merchants_users_list.php?act=list');
		sys_msg('删除成功', 0, $link);
	}
	else if ($_REQUEST['act'] == 'addChildCate') {
		check_authz_json('users_merchants');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$filter = $json->decode($_GET['JSON']);

		if ($type == 1) {
			$catarr = $filter->cat_id;
		}

		$cate_list = get_first_cate_list($filter->cat_id, $filter->type, $catarr, $filter->cat_id);
		$smarty->assign('cate_list', $cate_list);
		$smarty->assign('cat_id', $filter->cat_id);
		make_json_result($smarty->fetch('merchants_cate_list.dwt'));
	}
	else if ($_REQUEST['act'] == 'addChildCate_checked') {
		check_authz_json('users_merchants');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$result = array('error' => 0, 'message' => '', 'content' => '', 'cat_id' => '');
		$json = new JSON();
		$_POST['cat_id'] = strip_tags(urldecode($_POST['cat_id']));
		$_POST['cat_id'] = json_str_iconv($_POST['cat_id']);
		$cat = $json->decode($_POST['cat_id']);
		$child_category = get_child_category($cat->cat_id);
		$category_info = get_fine_category_info($child_category['cat_id'], $cat->user_id);
		$smarty->assign('category_info', $category_info);
		make_json_result($smarty->fetch('merchants_cate_checked_list.dwt'));
		$permanent_list = get_category_permanent_list($cat->user_id);
		$smarty->assign('permanent_list', $permanent_list);
		make_json_result($smarty->fetch('merchants_steps_catePermanent.dwt'));
	}
	else if ($_REQUEST['act'] == 'deleteChildCate_checked') {
		check_authz_json('users_merchants');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$ct_id = isset($_REQUEST['ct_id']) ? intval($_REQUEST['ct_id']) : '';
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$result = array('error' => 0, 'message' => '', 'content' => '', 'cat_id' => '');
		$json = new JSON();
		$catParent = get_temporarydate_ctId_catParent($ct_id);

		if ($catParent['num'] == 1) {
			$sql = 'delete from ' . $ecs->table('merchants_dt_file') . ' where cat_id = \'' . $catParent['parent_id'] . '\'';
			$db->query($sql);
		}

		$sql = 'delete from ' . $ecs->table('merchants_category_temporarydate') . (' where ct_id = \'' . $ct_id . '\'');
		$db->query($sql);
		$category_info = get_fine_category_info(0, $user_id);
		$smarty->assign('category_info', $category_info);
		make_json_result($smarty->fetch('merchants_cate_checked_list.dwt'));
		$permanent_list = get_category_permanent_list($user_id);
		$smarty->assign('permanent_list', $permanent_list);
		make_json_result($smarty->fetch('merchants_steps_catePermanent.dwt'));
	}
	else if ($_REQUEST['act'] == 'deleteBrand') {
		check_authz_json('users_merchants');
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$filter = $json->decode($_GET['JSON']);
		$sql = 'delete from ' . $ecs->table('merchants_shop_brand') . ' where bid = \'' . $filter->ct_id . '\'';
		$db->query($sql);
		$brand_list = get_septs_shop_brand_list($filter->user_id);
		$smarty->assign('brand_list', $brand_list);
		make_json_result($smarty->fetch('merchants_steps_brank_list.dwt'));
	}
	else if ($_REQUEST['act'] == 'brand_edit') {
		$b_fid = isset($_REQUEST['del_bFid']) ? intval($_REQUEST['del_bFid']) : 0;

		if (0 < $b_fid) {
			$sql = 'delete from ' . $ecs->table('merchants_shop_brandfile') . (' where b_fid = \'' . $b_fid . '\'');
			$db->query($sql);
		}

		$ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? intval($_REQUEST['ec_shop_bid']) : 0;
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$brandView = isset($_REQUEST['brandView']) ? $_REQUEST['brandView'] : '';
		$shopInfo_list = get_steps_user_shopInfo_list($user_id, $ec_shop_bid);
		$smarty->assign('shopInfo_list', $shopInfo_list);
		$category_info = get_fine_category_info(0, $user_id);
		$smarty->assign('category_info', $category_info);
		$permanent_list = get_category_permanent_list($user_id);
		$smarty->assign('permanent_list', $permanent_list);
		$country_list = get_regions_steps();
		$province_list = get_regions_steps(1, $consignee['country']);
		$city_list = get_regions_steps(2, $consignee['province']);
		$district_list = get_regions_steps(3, $consignee['city']);
		$sn = 0;
		$smarty->assign('country_list', $country_list);
		$smarty->assign('province_list', $province_list);
		$smarty->assign('city_list', $city_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('consignee', $consignee);
		$smarty->assign('sn', $sn);
		$smarty->assign('user_id', $user_id);
		$smarty->assign('brandView', $brandView);
		$smarty->assign('ec_shop_bid', $ec_shop_bid);
		$smarty->assign('form_action', 'update_shop');
		assign_query_info();
		$smarty->display('merchants_users_shopInfo.dwt');
	}
	else if ($_REQUEST['act'] == 'addBrand') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$result = array('content' => '');
		$b_fid = isset($_REQUEST['del_bFid']) ? intval($_REQUEST['del_bFid']) : 0;

		if (0 < $b_fid) {
			$sql = 'delete from ' . $ecs->table('merchants_shop_brandfile') . (' where b_fid = \'' . $b_fid . '\'');
			$db->query($sql);
		}

		$ec_shop_bid = isset($_REQUEST['ec_shop_bid']) ? intval($_REQUEST['ec_shop_bid']) : 0;
		$user_id = isset($_REQUEST['user_id']) ? intval($_REQUEST['user_id']) : 0;
		$brandView = isset($_REQUEST['brandView']) ? $_REQUEST['brandView'] : '';
		$shopInfo_list = get_steps_user_shopInfo_list($user_id, $ec_shop_bid);

		foreach ($shopInfo_list as $k => $v) {
			foreach ($v['steps_title'] as $key => $val) {
				if ($val['steps_style'] == 3 && $val['fields_titles'] == '新品牌信息') {
					$title = $val;
				}
			}
		}

		$smarty->assign('title', $title);
		$smarty->assign('shopInfo_list', $shopInfo_list);
		$category_info = get_fine_category_info(0, $user_id);
		$smarty->assign('category_info', $category_info);
		$permanent_list = get_category_permanent_list($user_id);
		$smarty->assign('permanent_list', $permanent_list);
		$country_list = get_regions_steps();
		$province_list = get_regions_steps(1, $consignee['country']);
		$city_list = get_regions_steps(2, $consignee['province']);
		$district_list = get_regions_steps(3, $consignee['city']);
		$sn = 0;
		$smarty->assign('country_list', $country_list);
		$smarty->assign('province_list', $province_list);
		$smarty->assign('city_list', $city_list);
		$smarty->assign('district_list', $district_list);
		$smarty->assign('consignee', $consignee);
		$smarty->assign('sn', $sn);
		$smarty->assign('user_id', $user_id);
		$smarty->assign('brandView', $brandView);
		$smarty->assign('ec_shop_bid', $ec_shop_bid);
		$smarty->assign('form_action', 'update_shop');
		$result['content'] = $GLOBALS['smarty']->fetch('merchants_bank_dialog.dwt');
		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'get_user_name') {
		check_authz_json('goods_manage');
		$user_name = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
		$sql = 'select user_id, user_name FROM ' . $GLOBALS['ecs']->table('users') . ' WHERE user_name like \'%' . $user_name . '%\'';
		$user_list = $GLOBALS['db']->getAll($sql);
		$res = get_search_user_list($user_list);
		clear_cache_files();
		make_json_result($res);
	}
	else if ($_REQUEST['act'] == 'addImg') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$result = array('content' => '', 'error' => 0, 'massege' => '');
		$user_id = !empty($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
		$steps_title = get_admin_merchants_steps_title($user_id, 'addImg');

		if (!empty($steps_title)) {
			$result['error'] = '2';

			if (0 < $user_id) {
				$title['brand_list'] = $db->getAll(' SELECT * FROM' . $ecs->table('merchants_shop_brand') . ' WHERE  user_id = \'' . $_REQUEST['user_id'] . '\'');
			}
			else {
				$title['brand_list'] = $db->getAll(' SELECT * FROM' . $ecs->table('merchants_shop_brand') . ' WHERE  user_id = 0 ');
			}

			if (!empty($title['brand_list'])) {
				foreach ($title['brand_list'] as $k => $v) {
					$brand_id .= $v['bid'] . ',';
				}
			}

			$brand_id = substr($brand_id, 0, strlen($brand_id) - 1);
			$smarty->assign('brand_id', $brand_id);
			$smarty->assign('title', $title);
			$result['content'] = $GLOBALS['smarty']->fetch('merchants_steps_brankType.dwt');
		}
		else {
			$result['error'] = '1';
			$result['massege'] = '添加失败';
		}

		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'edit_sort_order') {
		check_authz_json('users_merchants');
		$shop_id = intval($_POST['id']);
		$sort_order = intval($_POST['val']);

		if ($exc->edit('sort_order = \'' . $sort_order . '\'', $shop_id)) {
			clear_cache_files();
			make_json_result($sort_order);
		}
	}
	else if ($_REQUEST['act'] == 'create_initialize_rank') {
		admin_priv('users_merchants');
		$smarty->assign('ur_here', $_LANG['create_seller_grade']);
		$seller_grade_list = seller_grade_list();
		$record_count = count($seller_grade_list);
		$smarty->assign('record_count', $record_count);
		$smarty->assign('page', 1);
		assign_query_info();
		$smarty->display('merchants_initialize_rank.dwt');
	}
	else if ($_REQUEST['act'] == 'ajax_initialize_rank') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
		$seller_grade_list = seller_grade_list();
		$grade_list = $ecs->page_array($page_size, $page, $seller_grade_list);
		$arr = array();

		foreach ($grade_list['list'] as $key => $row) {
			$sql = 'SELECT id, grade_id, ru_id FROM ' . $GLOBALS['ecs']->table('merchants_grade') . ' WHERE ru_id = \'' . $row['user_id'] . '\' LIMIT 1';
			$grade_row = $GLOBALS['db']->getRow($sql);

			if ($grade_row) {
				$sql = 'SELECT id, grade_name FROM ' . $GLOBALS['ecs']->table('seller_grade') . ' WHERE id = \'' . $grade_row['grade_id'] . '\' LIMIT 1';
				$seller_grade = $GLOBALS['db']->getRow($sql);
			}
			else {
				$sql = 'SELECT id, grade_name FROM ' . $GLOBALS['ecs']->table('seller_grade') . ' WHERE 1' . ' AND seller_temp = (SELECT MIN(seller_temp) FROM ' . $GLOBALS['ecs']->table('seller_grade') . ') LIMIT 1';
				$seller_grade = $GLOBALS['db']->getRow($sql);
				$add_time = gmtime();
				$sql = 'INSERT INTO ' . $GLOBALS['ecs']->table('merchants_grade') . ' (`ru_id`, `grade_id`, `add_time`, `year_num`) VALUES (\'' . $row['user_id'] . '\', \'' . $seller_grade['id'] . ('\', \'' . $add_time . '\', \'1\')');
				$GLOBALS['db']->query($sql);
			}

			$seller_list[$key]['shop_name'] = get_shop_name($row['user_id'], 1);
			$arr = array('user_id' => $row['user_id'], 'shop_name' => $seller_list[$key]['shop_name'], 'grade_name' => $seller_grade['grade_name']);
		}

		$result['list'] = $arr;
		$result['page'] = $grade_list['filter']['page'] + 1;
		$result['page_size'] = $grade_list['filter']['page_size'];
		$result['record_count'] = $grade_list['filter']['record_count'];
		$result['page_count'] = $grade_list['filter']['page_count'];
		$result['is_stop'] = 1;

		if ($grade_list['filter']['page_count'] < $page) {
			$result['is_stop'] = 0;
		}
		else {
			$result['filter_page'] = $grade_list['filter']['page'];
		}

		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'create_seller_grade') {
		admin_priv('users_merchants');
		$smarty->assign('menu_select', array('action' => '17_merchants', 'current' => '04_create_seller_grade'));
		$smarty->assign('ur_here', $_LANG['create_seller_grade']);
		$seller_grade_list = seller_grade_list();
		$record_count = count($seller_grade_list);
		$smarty->assign('record_count', $record_count);
		$smarty->assign('page', 1);
		assign_query_info();
		$smarty->display('merchants_grade.dwt');
	}
	else if ($_REQUEST['act'] == 'ajax_seller_grade') {
		include_once ROOT_PATH . 'includes/cls_json.php';
		$json = new JSON();
		$page = !empty($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
		$page_size = isset($_REQUEST['page_size']) ? intval($_REQUEST['page_size']) : 1;
		$seller_grade_list = seller_grade_list();
		$grade_list = $ecs->page_array($page_size, $page, $seller_grade_list);
		$arr = array();

		if ($grade_list['list']) {
			foreach ($grade_list['list'] as $key => $row) {
				@unlink(ROOT_PATH . DATA_DIR . '/sc_file/seller_comment_' . $row['user_id'] . '.php');
				$seller_list[$key]['user_id'] = $row['user_id'];
				$seller_list[$key]['shop_name'] = get_shop_name($row['user_id'], 1);
				$seller_list[$key]['seller_comment'] = get_merchants_goods_comment($row['user_id']);
				$mc_all = isset($seller_list[$key]['seller_comment']['commentRank']['mc_all']) ? $seller_list[$key]['seller_comment']['commentRank']['mc_all'] : 0;
				$desc = isset($seller_list[$key]['seller_comment']['cmt']['commentRank']['zconments']['score']) ? $seller_list[$key]['seller_comment']['cmt']['commentRank']['zconments']['score'] : 0;
				$service = isset($seller_list[$key]['seller_comment']['cmt']['commentServer']['zconments']['score']) ? $seller_list[$key]['seller_comment']['cmt']['commentServer']['zconments']['score'] : 0;
				$delivery = isset($seller_list[$key]['seller_comment']['cmt']['commentDelivery']['zconments']['score']) ? $seller_list[$key]['seller_comment']['cmt']['commentDelivery']['zconments']['score'] : 0;
				write_static_cache('seller_comment_' . $row['user_id'], $seller_list[$key], '/data/sc_file/');
				$arr = array('user_id' => $row['user_id'], 'shop_name' => $seller_list[$key]['shop_name'], 'desc' => $desc, 'service' => $service, 'delivery' => $delivery, 'mc_all' => $mc_all);
			}
		}

		$result['list'] = $arr;
		$result['page'] = $grade_list['filter']['page'] + 1;
		$result['page_size'] = $grade_list['filter']['page_size'];
		$result['record_count'] = $grade_list['filter']['record_count'];
		$result['page_count'] = $grade_list['filter']['page_count'];
		$result['is_stop'] = 1;

		if ($grade_list['filter']['page_count'] < $page) {
			$result['is_stop'] = 0;
		}
		else {
			$result['filter_page'] = $grade_list['filter']['page'];
		}

		exit($json->encode($result));
	}
	else if ($_REQUEST['act'] == 'seller_shopinfo') {
		admin_priv('users_merchants');
		$seller_shop_info = array('shop_logo' => '', 'logo_thumb' => '', 'street_thumb' => '', 'brand_thumb' => '');
		include_once ROOT_PATH . 'languages/' . $_CFG['lang'] . '/' . ADMIN_PATH . '/index.php';
		$smarty->assign('lang', $_LANG);
		$user_id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
		$smarty->assign('users', get_table_date('merchants_shop_information', 'user_id=\'' . $user_id . '\'', array('user_id', 'hopeLoginName', 'merchants_audit')));
		$smarty->assign('menu_select', array('current' => 'seller_shopinfo', 'action' => 'templates', 'action' => 'allot'));
		$adminru['ru_id'] = $user_id;

		if ($adminru['ru_id'] == 0) {
			$smarty->assign('priv_ru', 1);
		}
		else {
			$smarty->assign('priv_ru', 0);
		}

		$smarty->assign('action_link', array('text' => '返回', 'href' => 'merchants_users_list.php?act=list'));
		$smarty->assign('ru_id', $adminru['ru_id']);
		$smarty->assign('countries', get_regions());
		$smarty->assign('provinces', get_regions(1, 1));
		$sql = 'SELECT ss.*,sq.* FROM ' . $ecs->table('seller_shopinfo') . ' AS ss ' . ' LEFT JOIN ' . $ecs->table('seller_qrcode') . ' AS sq ON sq.ru_id = ss.ru_id ' . ' WHERE ss.ru_id = \'' . $adminru['ru_id'] . '\' LIMIT 1';
		$seller_shop_info = $db->getRow($sql);
		$action = 'add';

		if ($seller_shop_info) {
			$action = 'update';
		}

		$smarty->assign('seller_notice', $seller_shop_info['notice']);
		$shipping_list = warehouse_shipping_list();
		$smarty->assign('shipping_list', $shipping_list);
		$domain_name = $db->getOne(' SELECT domain_name FROM' . $ecs->table('seller_domain') . ' WHERE ru_id=\'' . $adminru['ru_id'] . '\'');
		$seller_shop_info['domain_name'] = $domain_name;
		$diff_data = get_seller_shopinfo_changelog($adminru['ru_id']);
		$seller_shop_info = array_replace($seller_shop_info, $diff_data);

		if ($seller_shop_info['shop_logo']) {
			$seller_shop_info['shop_logo'] = str_replace('../', '', $seller_shop_info['shop_logo']);
			$seller_shop_info['shop_logo'] = get_image_path(0, $seller_shop_info['shop_logo']);
		}

		if ($seller_shop_info['logo_thumb']) {
			$seller_shop_info['logo_thumb'] = str_replace('../', '', $seller_shop_info['logo_thumb']);
			$seller_shop_info['logo_thumb'] = get_image_path(0, $seller_shop_info['logo_thumb']);
		}

		if ($seller_shop_info['street_thumb']) {
			$seller_shop_info['street_thumb'] = str_replace('../', '', $seller_shop_info['street_thumb']);
			$seller_shop_info['street_thumb'] = get_image_path(0, $seller_shop_info['street_thumb']);
		}

		if ($seller_shop_info['brand_thumb']) {
			$seller_shop_info['brand_thumb'] = str_replace('../', '', $seller_shop_info['brand_thumb']);
			$seller_shop_info['brand_thumb'] = get_image_path(0, $seller_shop_info['brand_thumb']);
		}

		$smarty->assign('shop_info', $seller_shop_info);
		$shop_information = get_shop_name($adminru['ru_id']);
		$adminru['ru_id'] == 0 ? $shop_information['is_dsc'] = true : $shop_information['is_dsc'] = false;
		$smarty->assign('shop_information', $shop_information);
		$smarty->assign('cities', get_regions(2, $seller_shop_info['province']));
		$smarty->assign('districts', get_regions(3, $seller_shop_info['city']));
		$smarty->assign('http', $ecs->http());
		$smarty->assign('data_op', $action);
		assign_query_info();
		$smarty->assign('ur_here', $_LANG['04_self_basic_info']);
		$smarty->display('seller_shopinfo.dwt');
	}
	else if ($_REQUEST['act'] == 'save_seller_shopinfo') {
		$ru_id = empty($_REQUEST['ru_id']) ? 0 : intval($_REQUEST['ru_id']);
		$adminru['ru_id'] = $ru_id;

		if (empty($adminru['ru_id'])) {
			$lnk[] = array('text' => '返回上一步', 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $adminru['ru_id']);
			sys_msg('无效数据', 0, $lnk);
		}

		include_once ROOT_PATH . '/includes/cls_image.php';
		$image = new cls_image($_CFG['bgcolor']);
		$shop_name = empty($_POST['shop_name']) ? '' : addslashes(trim($_POST['shop_name']));
		$shop_title = empty($_POST['shop_title']) ? '' : addslashes(trim($_POST['shop_title']));
		$shop_keyword = empty($_POST['shop_keyword']) ? '' : addslashes(trim($_POST['shop_keyword']));
		$shop_country = empty($_POST['shop_country']) ? 0 : intval($_POST['shop_country']);
		$shop_province = empty($_POST['shop_province']) ? 0 : intval($_POST['shop_province']);
		$shop_city = empty($_POST['shop_city']) ? 0 : intval($_POST['shop_city']);
		$shop_district = empty($_POST['shop_district']) ? 0 : intval($_POST['shop_district']);
		$shipping_id = empty($_POST['shipping_id']) ? 0 : intval($_POST['shipping_id']);
		$shop_address = empty($_POST['shop_address']) ? '' : addslashes(trim($_POST['shop_address']));
		$mobile = empty($_POST['mobile']) ? '' : trim($_POST['mobile']);
		$seller_email = empty($_POST['seller_email']) ? '' : addslashes(trim($_POST['seller_email']));
		$street_desc = empty($_POST['street_desc']) ? '' : addslashes(trim($_POST['street_desc']));
		$kf_qq = empty($_POST['kf_qq']) ? '' : $_POST['kf_qq'];
		$kf_ww = empty($_POST['kf_ww']) ? '' : $_POST['kf_ww'];
		$kf_touid = empty($_POST['kf_touid']) ? '' : addslashes(trim($_POST['kf_touid']));
		$kf_appkey = empty($_POST['kf_appkey']) ? 0 : addslashes(trim($_POST['kf_appkey']));
		$kf_secretkey = empty($_POST['kf_secretkey']) ? 0 : addslashes(trim($_POST['kf_secretkey']));
		$kf_logo = empty($_POST['kf_logo']) ? 'http://' : addslashes(trim($_POST['kf_logo']));
		$kf_welcomeMsg = empty($_POST['kf_welcomeMsg']) ? '' : addslashes(trim($_POST['kf_welcomeMsg']));
		$meiqia = empty($_POST['meiqia']) ? '' : addslashes(trim($_POST['meiqia']));
		$kf_type = empty($_POST['kf_type']) ? 0 : intval($_POST['kf_type']);
		$kf_tel = empty($_POST['kf_tel']) ? '' : addslashes(trim($_POST['kf_tel']));
		$notice = empty($_POST['notice']) ? '' : addslashes(trim($_POST['notice']));
		$data_op = empty($_POST['data_op']) ? '' : $_POST['data_op'];
		$check_sellername = empty($_POST['check_sellername']) ? 0 : intval($_POST['check_sellername']);
		$shop_style = intval($_POST['shop_style']);
		$domain_name = empty($_POST['domain_name']) ? '' : trim($_POST['domain_name']);
		$templates_mode = empty($_REQUEST['templates_mode']) ? 0 : intval($_REQUEST['templates_mode']);
		$tengxun_key = empty($_POST['tengxun_key']) ? '' : addslashes(trim($_POST['tengxun_key']));
		$longitude = empty($_POST['longitude']) ? '' : addslashes(trim($_POST['longitude']));
		$latitude = empty($_POST['latitude']) ? '' : addslashes(trim($_POST['latitude']));
		$js_appkey = empty($_POST['js_appkey']) ? '' : $_POST['js_appkey'];
		$js_appsecret = empty($_POST['js_appsecret']) ? '' : $_POST['js_appsecret'];
		$print_type = empty($_POST['print_type']) ? 0 : intval($_POST['print_type']);
		$kdniao_printer = empty($_POST['kdniao_printer']) ? '' : $_POST['kdniao_printer'];

		if (!empty($domain_name)) {
			$sql = ' SELECT count(id) FROM ' . $ecs->table('seller_domain') . ' WHERE domain_name = \'' . $domain_name . '\' AND ru_id !=\'' . $adminru['ru_id'] . '\'';

			if (0 < $db->getOne($sql)) {
				$lnk[] = array('text' => '返回上一步', 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $adminru['ru_id']);
				sys_msg('域名已存在', 0, $lnk);
			}
		}

		$seller_domain = array('ru_id' => $adminru['ru_id'], 'domain_name' => $domain_name);
		$shop_info = array('ru_id' => $adminru['ru_id'], 'shop_name' => $shop_name, 'shop_title' => $shop_title, 'shop_keyword' => $shop_keyword, 'country' => $shop_country, 'province' => $shop_province, 'city' => $shop_city, 'district' => $shop_district, 'shipping_id' => $shipping_id, 'shop_address' => $shop_address, 'mobile' => $mobile, 'seller_email' => $seller_email, 'kf_qq' => $kf_qq, 'kf_ww' => $kf_ww, 'kf_appkey' => $kf_appkey, 'kf_secretkey' => $kf_secretkey, 'kf_touid' => $kf_touid, 'kf_logo' => $kf_logo, 'kf_welcomeMsg' => $kf_welcomeMsg, 'meiqia' => $meiqia, 'kf_type' => $kf_type, 'kf_tel' => $kf_tel, 'notice' => $notice, 'street_desc' => $street_desc, 'shop_style' => $shop_style, 'check_sellername' => $check_sellername, 'templates_mode' => $templates_mode, 'tengxun_key' => $tengxun_key, 'longitude' => $longitude, 'latitude' => $latitude, 'js_appkey' => $js_appkey, 'js_appsecret' => $js_appsecret, 'print_type' => $print_type, 'kdniao_printer' => $kdniao_printer);
		$sql = 'SELECT ss.shop_logo, ss.logo_thumb, ss.street_thumb, ss.brand_thumb, sq.qrcode_thumb FROM ' . $ecs->table('seller_shopinfo') . ' as ss ' . ' left join ' . $ecs->table('seller_qrcode') . ' as sq on sq.ru_id=ss.ru_id ' . ' WHERE ss.ru_id=\'' . $adminru['ru_id'] . '\'';
		$store = $db->getRow($sql);
		$allow_file_types = '|GIF|JPG|PNG|BMP|';

		if ($_FILES['shop_logo']) {
			$file = $_FILES['shop_logo'];
			if (isset($file['error']) && $file['error'] == 0 || !isset($file['error']) && $file['tmp_name'] != 'none') {
				if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
					sys_msg(sprintf($_LANG['msg_invalid_file'], $file['name']));
				}
				else {
					if ($file['name']) {
						$ext = explode('.', $file['name']);
						$ext = array_pop($ext);
					}
					else {
						$ext = '';
					}

					$file_name = '../seller_imgs/seller_logo/seller_logo' . $adminru['ru_id'] . '.' . $ext;

					if (move_upload_file($file['tmp_name'], $file_name)) {
						$shop_info['shop_logo'] = $file_name;
					}
					else {
						sys_msg(sprintf($_LANG['msg_upload_failed'], $file['name'], '../seller_imgs/seller_' . $adminru['ru_id']));
					}
				}
			}
		}

		$del_logo_thumb = '';

		if ($_FILES['logo_thumb']) {
			$file = $_FILES['logo_thumb'];
			if (isset($file['error']) && $file['error'] == 0 || !isset($file['error']) && $file['tmp_name'] != 'none') {
				if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
					sys_msg(sprintf($_LANG['msg_invalid_file'], $file['name']));
				}
				else {
					if ($file['name']) {
						$ext = explode('.', $file['name']);
						$ext = array_pop($ext);
					}
					else {
						$ext = '';
					}

					$file_name = '../seller_imgs/seller_logo/logo_thumb/logo_thumb' . $adminru['ru_id'] . '.' . $ext;

					if (move_upload_file($file['tmp_name'], $file_name)) {
						include_once ROOT_PATH . '/includes/cls_image.php';
						$image = new cls_image($_CFG['bgcolor']);
						$goods_thumb = $image->make_thumb($file_name, 120, 120, '../seller_imgs/seller_logo/logo_thumb/');
						$shop_info['logo_thumb'] = $goods_thumb;

						if (!empty($goods_thumb)) {
							if ($store['logo_thumb']) {
								$store['logo_thumb'] = str_replace('../', '', $store['logo_thumb']);
								$del_logo_thumb = $store['logo_thumb'];
							}

							@unlink(ROOT_PATH . $del_logo_thumb);
						}
					}
					else {
						sys_msg(sprintf($_LANG['msg_upload_failed'], $file['name'], 'seller_imgs/logo_thumb_' . $adminru['ru_id']));
					}
				}
			}
		}

		$street_thumb = $image->upload_image($_FILES['street_thumb'], 'store_street/street_thumb');
		$brand_thumb = $image->upload_image($_FILES['brand_thumb'], 'store_street/brand_thumb');
		$domain_id = $db->getOne('SELECT id FROM ' . $ecs->table('seller_domain') . ' WHERE ru_id =\'' . $adminru['ru_id'] . '\'');

		if (0 < $domain_id) {
			$db->autoExecute($ecs->table('seller_domain'), $seller_domain, 'UPDATE', 'ru_id=\'' . $adminru['ru_id'] . '\'');
		}
		else {
			$db->autoExecute($ecs->table('seller_domain'), $seller_domain, 'INSERT');
		}

		if ($_FILES['qrcode_thumb']) {
			$file = $_FILES['qrcode_thumb'];
			if (isset($file['error']) && $file['error'] == 0 || !isset($file['error']) && $file['tmp_name'] != 'none') {
				if (!check_file_type($file['tmp_name'], $file['name'], $allow_file_types)) {
					sys_msg(sprintf($_LANG['msg_invalid_file'], $file['name']));
				}
				else {
					$ext = array_pop(explode('.', $file['name']));
					$file_name = '../seller_imgs/seller_qrcode/qrcode_thumb/qrcode_thumb' . $adminru['ru_id'] . '.' . $ext;

					if (move_upload_file($file['tmp_name'], $file_name)) {
						include_once ROOT_PATH . '/includes/cls_image.php';
						$image = new cls_image($_CFG['bgcolor']);
						$qrcode_thumb = $image->make_thumb($file_name, 120, 120, '../seller_imgs/seller_qrcode/qrcode_thumb/');

						if (!empty($qrcode_thumb)) {
							if ($store['qrcode_thumb']) {
								$store['qrcode_thumb'] = str_replace('../', '', $store['qrcode_thumb']);
								$del_logo_thumb = $store['qrcode_thumb'];
							}

							@unlink(ROOT_PATH . $del_logo_thumb);
						}

						$sql = ' select * from ' . $GLOBALS['ecs']->table('seller_qrcode') . ' where ru_id=\'' . $adminru['ru_id'] . '\' limit 1';
						$qrinfo = $GLOBALS['db']->getRow($sql);

						if (empty($qrinfo)) {
							$sql = ' insert into ' . $GLOBALS['ecs']->table('seller_qrcode') . ' (ru_id,qrcode_thumb) ' . ' values ' . '(\'' . $adminru['ru_id'] . '\',\'' . $qrcode_thumb . '\')';
							$GLOBALS['db']->query($sql);
						}
						else {
							$sql = ' update ' . $GLOBALS['ecs']->table('seller_qrcode') . ' set ru_id=\'' . $adminru['ru_id'] . '\', ' . ' qrcode_thumb=\'' . $qrcode_thumb . '\' ';
							$GLOBALS['db']->query($sql);
						}
					}
					else {
						sys_msg(sprintf($_LANG['msg_upload_failed'], $file['name'], 'seller_imgs/qrcode_thumb_' . $adminru['ru_id']));
					}
				}
			}
		}

		$shop_logo = '';

		if ($shop_info['shop_logo']) {
			$shop_logo = str_replace('../', '', $shop_info['shop_logo']);
		}

		$add_logo_thumb = '';

		if ($shop_info['logo_thumb']) {
			$add_logo_thumb = str_replace('../', '', $shop_info['logo_thumb']);
		}

		get_oss_add_file(array($street_thumb, $brand_thumb, $shop_logo, $add_logo_thumb));
		$admin_user = array('email' => $seller_email);
		$db->autoExecute($ecs->table('admin_user'), $admin_user, 'UPDATE', 'user_id = \'' . $_SESSION['seller_id'] . '\'');

		if ($data_op == 'add') {
			$shop_info['street_thumb'] = $street_thumb;
			$shop_info['brand_thumb'] = $brand_thumb;

			if (!$store) {
				$review_status = empty($_REQUEST['review_status']) ? 1 : intval($_REQUEST['review_status']);
				$review_content = empty($_REQUEST['review_content']) ? '' : trim($_REQUEST['review_content']);
				$review_data = array('review_status' => $review_status, 'review_content' => $review_content);

				if ($review_status == 3) {
					$diff_data = get_seller_shopinfo_changelog($adminru['ru_id']);
					$shop_info = array_replace($shop_info, $diff_data);
					$db->autoExecute($ecs->table('seller_shopinfo'), $shop_info, 'INSERT');
					$sql = ' DELETE FROM ' . $GLOBALS['ecs']->table('seller_shopinfo_changelog') . (' WHERE ru_id = \'' . $adminru['ru_id'] . '\' ');
					$GLOBALS['db']->query($sql);
				}
				else {
					$db->autoExecute($ecs->table('seller_shopinfo'), array('id' => NULL, 'ru_id' => $adminru['ru_id']), 'INSERT');
				}

				$db->autoExecute($ecs->table('seller_shopinfo'), $review_data, 'UPDATE', 'ru_id=\'' . $adminru['ru_id'] . '\'');
			}

			$lnk[] = array('text' => '返回上一步', 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $adminru['ru_id']);
			sys_msg('添加店铺信息成功', 0, $lnk);
		}
		else {
			$sql = 'select check_sellername from ' . $ecs->table('seller_shopinfo') . ' where ru_id=\'' . $adminru['ru_id'] . '\'';
			$seller_shop_info = $db->getRow($sql);

			if ($seller_shop_info['check_sellername'] != $check_sellername) {
				$shop_info['shopname_audit'] = 0;
			}

			$oss_street_thumb = '';

			if (!empty($street_thumb)) {
				$oss_street_thumb = $store['street_thumb'];
				$shop_info['street_thumb'] = $street_thumb;
				@unlink(ROOT_PATH . $oss_street_thumb);
			}

			$oss_brand_thumb = '';

			if (!empty($brand_thumb)) {
				$oss_brand_thumb = $store['brand_thumb'];
				$shop_info['brand_thumb'] = $brand_thumb;
				@unlink(ROOT_PATH . $oss_brand_thumb);
			}

			if ($GLOBALS['_CFG']['open_oss'] == 1) {
				$bucket_info = get_bucket_info();
				$url = $GLOBALS['ecs']->seller_url();
				$self = explode('/', substr(PHP_SELF, 1));
				$count = count($self);

				if (1 < $count) {
					$real_path = $self[$count - 2];

					if ($real_path == SELLER_PATH) {
						$str_len = 0 - (str_len(SELLER_PATH) + 1);
						$url = substr($GLOBALS['ecs']->seller_url(), 0, $str_len);
					}
				}

				$urlip = get_ip_url($url);
				$url = $urlip . 'oss.php?act=del_file';
				$Http = new Http();
				$post_data = array(
					'bucket'    => $bucket_info['bucket'],
					'keyid'     => $bucket_info['keyid'],
					'keysecret' => $bucket_info['keysecret'],
					'is_cname'  => $bucket_info['is_cname'],
					'endpoint'  => $bucket_info['outside_site'],
					'object'    => array($oss_street_thumb, $oss_brand_thumb, $del_logo_thumb)
					);
				$Http->doPost($url, $post_data);
			}

			$review_status = empty($_REQUEST['review_status']) ? 1 : intval($_REQUEST['review_status']);
			$review_content = empty($_REQUEST['review_content']) ? '' : trim($_REQUEST['review_content']);
			$review_data = array('review_status' => $review_status, 'review_content' => $review_content);

			if ($review_status == 3) {
				$diff_data = get_seller_shopinfo_changelog($adminru['ru_id']);
				$shop_info = array_replace($shop_info, $diff_data);
				$db->autoExecute($ecs->table('seller_shopinfo'), $shop_info, 'UPDATE', 'ru_id=\'' . $adminru['ru_id'] . '\'');
				$sql = ' DELETE FROM ' . $GLOBALS['ecs']->table('seller_shopinfo_changelog') . (' WHERE ru_id = \'' . $adminru['ru_id'] . '\' ');
				$GLOBALS['db']->query($sql);
			}

			$db->autoExecute($ecs->table('seller_shopinfo'), $review_data, 'UPDATE', 'ru_id=\'' . $adminru['ru_id'] . '\'');
			$lnk[] = array('text' => '返回上一步', 'href' => 'merchants_users_list.php?act=seller_shopinfo&id=' . $adminru['ru_id']);
			sys_msg('更新店铺信息成功', 0, $lnk);
		}
	}
}

?>
