<?php 

# 临时变量
$data = array(
	'province'     => array(),
	'city'         => array(),
	'xian'         => array(),
	'xiang'        => array(),
	'special_city' => array()
);

# begin run

require_once('config.php');
require_once('mysql.php');

# utf-8字符集不支持的中文地名
$arr_special_charset = array_keys($config['special_charset']);

init_db($config['db']['dbname'], $config['init_sql']);

# 0全部重新抓取, 1(市)忽略省, 2(县)忽略省市, 3(乡)忽略省市县, 4(村)仅抓取村的数据
run(0);

# end

function run($type) {
	global $config;
	global $data;
	global $arr_special_charset;

	# 省 province
	if ($type < 1) {
		$url_province = $config['url']['host'].$config['url']['province'];
		$data['province'] = get_province($url_province);

		$sql = '';
		foreach ($data['province'] as $province) {
			$name_province_temp = mb_convert_encoding($province['name'], 'utf-8', 'gb2312');
			$sql .= "insert into `{$config['db']['dbname']}`.`province` (`id`, `name`) values ({$province['id']},'{$name_province_temp}');";
		}
		insert_db($sql);

		echo "province success:".count($data['province'])."\n";
	} else if ($type == 1) {
		$sql_province = "select * from `{$config['db']['dbname']}`.`province`;";
		$data['province'] = select_db($sql_province);
		if (count($data['province']) == 0) {
			die('no province data');
		}
	}

	# 市 city
	if ($type < 2) {
		foreach ($data['province'] as $province) {
			$url_city = $config['url']['host'].str_replace('{$province}', $province['id'], $config['url']['city']);
			$data['city'] = array_merge($data['city'], get_city($url_city, $province));
		}

		$sql = '';
		foreach ($data['city'] as $city) {
			$name_city_temp = mb_convert_encoding($city['name'], 'utf-8', 'gb2312');
			$sql .= "insert into `{$config['db']['dbname']}`.`city` (`id`, `name`, `pid`, `type`) values ({$city['id']},'{$name_city_temp}',{$city['pid']},{$city['type']});";
		}
		insert_db($sql);

		echo "city success:".count($data['city'])."\n";
	} else if ($type == 2) {
		$sql_city = "select * from `{$config['db']['dbname']}`.`city`;";
		$data['city'] = select_db($sql_city);
		if (count($data['city']) == 0) {
			die('no city data');
		}

		foreach ($data['city'] as $city_temp) {
			if (in_array($city_temp['id'], $config['special_city'])) {
				$data['special_city'][] = $city_temp;
			}
		}
	}

	# 县/区 xian 
	if ($type < 3) {
		foreach ($data['city'] as $city) {
			if (in_array($city['id'], $config['special_city'])) {
				continue;
			}

			$url_xian = $config['url']['host'].str_replace('{$city}', $city['id'], str_replace('{$province}', $city['pid'], $config['url']['xian']));
			$data['xian'] = array_merge($data['xian'], get_xian($url_xian, $city));
		}

		$sql = '';
		foreach ($data['xian'] as $xian) {
			$name_xian_temp = '';
			if (in_array($xian['id'], $arr_special_charset)) {
				$name_xian_temp = $config['special_charset'][$xian['id']];
			} else {
				$name_xian_temp = mb_convert_encoding($xian['name'], 'utf-8', 'gb2312');
			}
			
			$sql .= "insert into `{$config['db']['dbname']}`.`xian` (`id`, `name`, `pid`, `cid`) values ('{$xian['id']}','{$name_xian_temp}','{$xian['pid']}','{$xian['cid']}');";
		}
		insert_db($sql);

		echo "xian success:".count($data['xian'])."\n";
	} else if ($type == 3) {
		$sql_xian = "select * from `{$config['db']['dbname']}`.`xian` where `name` not in ('".implode("','", $config['special_xian'])."') union select * from `{$config['db']['dbname']}`.`xian` where `id` in (".implode("','", $config['special_xian1']).");";
		$data['xian'] = select_db($sql_xian);
		if (count($data['xian']) == 0) {
			die('no xian data');
		}

		$sql_city = "select * from `{$config['db']['dbname']}`.`city` where `type` = 1;";
		$data['special_city'] = select_db($sql_city);
	}

	# 乡/镇 xiang
	if ($type < 4) {
		foreach ($data['xian'] as $xian) {
			if (in_array($xian['name'], $config['special_xian'])) {
				continue;
			}

			$url_xiang = $config['url']['host'].str_replace('{$xian}', $xian['id'], str_replace('{$city}', str_replace($xian['pid'], '', $xian['cid']), str_replace('{$province}', $xian['pid'], $config['url']['xiang'])));
			$data['xiang'] = array_merge($data['xiang'], get_xiang($url_xiang, $xian));
		}

		foreach ($data['special_city'] as $special_city) {
			$url_xiang1 = $config['url']['host'].str_replace('{$city}', $special_city['id'], str_replace('{$province}', $special_city['pid'], $config['url']['xian']));
			$xian = array('id' => 0, 'name' => $special_city['name'], 'pid' => $special_city['pid'], 'cid' => $special_city['id']);
			$data['xiang'] = array_merge($data['xiang'], get_xiang($url_xiang1, $xian));
		}

		$sql = '';
		foreach ($data['xiang'] as $xiang) {
			$name_xiang_temp = '';
			if (in_array($xiang['id'], $arr_special_charset)) {
				$name_xiang_temp = $config['special_charset'][$xiang['id']];
			} else {
				$name_xiang_temp = mb_convert_encoding($xiang['name'], 'utf-8', 'gb2312');
			}

			$sql .= "insert into `{$config['db']['dbname']}`.`xiang` (`id`, `name`, `pid`, `cid`, `xid`) values ('{$xiang['id']}','{$name_xiang_temp}','{$xiang['pid']}','{$xiang['cid']}','{$xiang['xid']}');";
		}
		insert_db($sql);

		echo "xiang success:".count($data['xiang'])."\n";
	} else if ($type == 4) {
		$sql_xiang = "select * from `{$config['db']['dbname']}`.`xiang`;";
		$data['xiang'] = select_db($sql_xiang);
		if (count($data['xiang']) == 0) {
			die('no xiang data');
		}
	}

	# 村/社区 cun
	if ($type < 5) {
		$count = 0;
		$arr_cun = array();
		foreach ($data['xiang'] as $xiang) {
			$url_cun = '';
			if ($xiang['xid'] == 0) {
				$url_cun = $config['url']['host'].str_replace('{$xian}', $xiang['id'], str_replace('{$city}', str_replace($xiang['pid'], '', $xiang['cid']), str_replace('{$province}', $xiang['pid'], $config['url']['xiang'])));
			} else {
				$url_cun = $config['url']['host'].str_replace('{$xiang}', $xiang['id'], str_replace('{$xian}', str_replace($xiang['cid'], '', $xiang['xid']), str_replace('{$city}', str_replace($xiang['pid'], '', $xiang['cid']), str_replace('{$province}', $xiang['pid'], $config['url']['cun']))));	
			}
			
			$arr_cun = array_merge($arr_cun, get_cun($url_cun, $xiang));

			if (count($arr_cun) >= $config['max_cun_data']) {
				insert_cun($arr_cun);

				$arr_cun = array();
				$count++;
			}
		}

		$cnt = count($arr_cun);
		if ($cnt > 0) {
			insert_cun($arr_cun);
			$count++;
		}

		echo "cun success:".$count*$config['max_cun_data'] + $cnt."\n";
	}
}

function insert_cun($arr_cun) {
	global $config;

	$sql = '';
	foreach ($arr_cun as $cun) {
		$name_cun_temp = mb_convert_encoding($cun['name'], 'utf-8', 'gb2312');
		$sql .= "insert into `{$config['db']['dbname']}`.`cun` (`id`, `name`, `pid`, `cid`, `xid`, `xgid`, `type`) values ({$cun['id']},'{$name_cun_temp}',{$cun['pid']},{$cun['cid']},{$cun['xid']},{$cun['xgid']},{$cun['type']});";
	}
	insert_db($sql);
}

function get_province($url) {
	global $config;
	
	if ($config['sleep_time']) {
		sleep($config['sleep_time']);
	}
	echo "get_province().........begin...........";

	$result = array();
	try {
		$html_province = @file_get_contents($url);
		if (!$html_province) {
			throw new Exception('http_304');
		}		
	} catch (Exception $e) {
		echo ".........error...........\n";
		if ($config['error_sleep_time']) {
			sleep($config['error_sleep_time']);
		}
		return get_province($url);
	}
	
	preg_match_all($config['reg_province']['step1'], $html_province, $match_province1);
	if (count($match_province1[0]) == 0) {
		return $result;
	}

	$str_province1 = $match_province1[0][0];
	preg_match_all($config['reg_province']['step2'], $str_province1, $match_province2);
	if (count($match_province2[0]) == 0) {
		die('match2 error');
	}

	foreach ($match_province2[0] as $str_province_temp) {
		$str_province3 = preg_replace($config['reg_province']['step3'], '', $str_province_temp);

		preg_match_all($config['reg_province']['step4'], $str_province3, $match_province4);
		if (count($match_province4) == 0) {
			die('match4 error:'.$str_province_temp);
		}
		foreach ($match_province4[0] as $str_province_temp) {
			$str_province5 = str_replace($str_province_temp, '', $str_province3);
			$result[] = array('id' => $str_province_temp, 'name' => $str_province5);
		}
	}

	echo ".........end.............".count($result).".....\n";
	return $result;
}

function get_city($url, $province) {
	global $config;
	if ($config['sleep_time']) {
		sleep($config['sleep_time']);
	}
	echo "get_city({$province['id']}).........begin...........";

	$result = array();
	try {
		$html_city = @file_get_contents($url);
		if (!$html_city) {
			throw new Exception('http_304');
		}
	} catch (Exception $e) {
		echo ".........error...........\n";
		if ($config['error_sleep_time']) {
			sleep($config['error_sleep_time']);
		}
		return get_city($province['id']);
	}

	preg_match_all($config['reg_city']['step1'], $html_city, $match_city1);
	if (count($match_city1[0]) == 0) {
		echo ".........error2...........\n";
		if ($config['error_sleep_time']) {
			sleep($config['error_sleep_time']);
		}
		return get_city($province['id']);
	}

	$str_city1 = $match_city1[0][0];
	preg_match_all($config['reg_city']['step2'], $str_city1, $match_city2);
	if (count($match_city2[0]) == 0) {
		return $result;
	}

	foreach ($match_city2[0] as $str_city_temp) {
		$str_city3 = str_replace($province['id'].'/', '', preg_replace($config['reg_city']['step3'], '', $str_city_temp));

		preg_match_all($config['reg_city']['step4'], $str_city3, $match_city4);
		if (count($match_city4) == 0) {
			die('city match4 error:'.$str_city_temp);
		}
		foreach ($match_city4[0] as $str_city_temp) {
			$str_city5 = str_replace($str_city_temp, '', $str_city3);

			$type = 0;
			if (in_array($str_city_temp, $config['special_city'])) {
				$type = 1;
				$data['special_city'][] = array('pid' => $province['id'], 'id' => $str_city_temp, 'name' => $str_city5, 'type' => $type);
			}

			$result[] = array('pid' => $province['id'], 'id' => $str_city_temp, 'name' => $str_city5, 'type' => $type);
		}
	}

	echo ".........end.............".count($result).".....\n";
	return $result;
}

function get_xian($url, $city) {
	global $config;
	if ($config['sleep_time']) {
		sleep($config['sleep_time']);
	}
	echo "get_xian({$city['id']}).........begin...........";

	$result = array();
	try {
		$html_xian = @file_get_contents($url);
		if (!$html_xian) {
			throw new Exception('http_304');
		}
	} catch (Exception $e) {
		echo ".........error1...........\n";
		if ($config['error_sleep_time']) {
			sleep($config['error_sleep_time']);
		}
		return get_xian($url, $city);
	}

	preg_match_all($config['reg_xian']['step1'], $html_xian, $match_xian1);
	if (count($match_xian1[0]) == 0) {
		echo "...no xian...\n";
		return $result;
	}

	$str_xian1 = $match_xian1[0][0];
	preg_match_all($config['reg_xian']['step2'], $str_xian1, $match_xian2);
	if (count($match_xian2[0]) == 0) {
		die('xian match2 error');
	}

	foreach ($match_xian2[0] as $str_xian_temp) {
		$str_xian3 = '';
		if (strripos($str_xian_temp, 'td') > 0) {
			$str_xian_temp1 = str_replace('</td>','', $str_xian_temp);
			$reg_xian5 = "/<td>\d+<\/td>".$str_xian_temp1."<\/td>/i";

			preg_match_all($reg_xian5, $str_xian1, $match_xian5);
			if (count($match_xian5[0]) == 0) {
				die('xian match5 error');
			}

			$str_xian3 = str_replace('000000', '', str_replace('<td>', '', str_replace('</td>', '', $match_xian5[0][0])));
		} else {
			$str_xian3 = str_replace(str_replace($city['pid'] ,'', $city['id']).'/', '', preg_replace($config['reg_xian']['step3'], '', $str_xian_temp));
		}

		preg_match_all($config['reg_xian']['step4'], $str_xian3, $match_xian4);
		if (count($match_xian4[0]) == 0) {
			die('xian match4 error:'.$str_xian_temp);
		}
		foreach ($match_xian4[0] as $str_xian_temp) {
			$str_xian5 = str_replace($str_xian_temp, '', $str_xian3);
			$result[] = array('pid' => $city['pid'], 'id' => $str_xian_temp, 'name' => $str_xian5, 'cid' => $city['id']);
		}
	}

	echo ".........end.............".count($result).".....\n";
	return $result;
}

function get_xiang($url, $xian) {
	global $config;
	if ($config['sleep_time']) {
		sleep($config['sleep_time']);
	}
	echo "get_xiang({$xian['id']}).........begin...........";

	$result = array();
	try {
		$html_xiang = @file_get_contents($url);
		if (!$html_xiang) {
			throw new Exception('http_304');
		}
	} catch (Exception $e) {
		echo ".........error...........\n";
		if ($config['error_sleep_time']) {
			sleep($config['error_sleep_time']);
		}
		return get_xiang($url, $xian);
	}

	preg_match_all($config['reg_xiang']['step1'], $html_xiang, $match_xiang1);
	if (count($match_xiang1[0]) == 0) {
		echo "...no xiang...\n";
		return $result;
	}

	$str_xiang1 = $match_xiang1[0][0];
	preg_match_all($config['reg_xiang']['step2'], $str_xiang1, $match_xiang2);
	if (count($match_xiang2[0]) == 0) {
		die('xiang match2 error');
	}

	foreach ($match_xiang2[0] as $str_xiang_temp) {
		$str_xiang3_temp = str_replace($xian['cid'],'', $xian['id']).'/';
		if ($xian['id'] == 0) {
			$str_xiang3_temp = str_replace($xian['pid'],'', $xian['cid']).'/';
		}

		$str_xiang3 = str_replace($str_xiang3_temp, '', preg_replace($config['reg_xiang']['step3'], '', $str_xiang_temp));

		preg_match_all($config['reg_xiang']['step4'], $str_xiang3, $match_xiang4);
		if (count($match_xiang4[0]) == 0) {
			die('xiang matchg4 error:'.$str_xiang_temp);
		}
		foreach ($match_xiang4[0] as $str_xiang_temp) {
			$str_xiang5 = str_replace($str_xiang_temp, '', $str_xiang3);
			$result[] = array('pid' => $xian['pid'], 'id' => $str_xiang_temp, 'name' => $str_xiang5, 'cid' => $xian['cid'], 'xid' => $xian['id']);
		}
	}

	echo ".........end.............".count($result).".....\n";
	return $result;
}

function get_cun($url, $xiang) {
	global $config;
	if ($config['sleep_time']) {
		sleep($config['sleep_time']);
	}
	echo "get_cun({$xiang['id']}).........begin...........";

	$result = array();
	try {
		$html_cun = @file_get_contents($url);
		if (!$html_cun) {
			throw new Exception('http_304');
		}
	} catch (Exception $e) {
		echo ".........error...........\n";
		if ($config['error_sleep_time']) {
			sleep($config['error_sleep_time']);
		}
		return get_cun($url, $xiang);
	}

	preg_match_all($config['reg_cun']['step1'], $html_cun, $match_cun1);
	if (count($match_cun1[0]) == 0) {
		echo "...no cun...\n";
		return $result;
	}

	$str_cun1 = $match_cun1[0][0];
	preg_match_all($config['reg_cun']['step2'], $str_cun1, $match_cun2);
	if (count($match_cun2[0]) == 0) {
		die('cun match2 error');
	}

	foreach ($match_cun2[0] as $str_cun_temp) {
		$str_cun3 = '</td>'.$str_cun_temp.'<td>';
		$arr_cun3 = explode('</td><td>', $str_cun3);

		if (count($arr_cun3) != 5) {
			die($str_cun_temp.'^^^^^^ error3 ^^^^^^');
		}

		$result[] = array('pid' => $xiang['pid'], 'id' => $arr_cun3[1], 'name' => $arr_cun3[3], 'cid' => $xiang['cid'], 'xid' => $xiang['xid'], 'xgid' => $xiang['id'], 'type' => $arr_cun3[2]);
	}

	echo ".........end.............".count($result).".....\n";
	return $result;
}

?>