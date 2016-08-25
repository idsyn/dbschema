<?php 

$config = array(
	'url' => array(
		'host'     => 'http://www.stats.gov.cn',
		'province' => '/tjsj/tjbz/tjyqhdmhcxhfdm/2015/index.html',
		'city'     => '/tjsj/tjbz/tjyqhdmhcxhfdm/2015/{$province}.html',
		'xian'     => '/tjsj/tjbz/tjyqhdmhcxhfdm/2015/{$province}/{$city}.html',
		'xiang'    => '/tjsj/tjbz/tjyqhdmhcxhfdm/2015/{$province}/{$city}/{$xian}.html',
		'cun'      => '/tjsj/tjbz/tjyqhdmhcxhfdm/2015/{$province}/{$city}/{$xian}/{$xiang}.html'
		),
	'db'  => array(
		'host'     => 'localhost',
		'user'     => 'root',
		'password' => 'root',
		'dbname'   => 'test',
		'port'     => '3306'
	),
	'reg_province' => array(
		'step1' => "/<tr class='provincetr'>.+/i",
		'step2' => "/<a href='\d+.html'>\W+/i",
		'step3' => "/(<a href=')|(.html'>)|</i",
		'step4' => "/\d+/i"
	),
	'reg_city' => array(
		'step1' => "/<tr class='citytr'>.+/i", 
		'step2' => "/<a href='\d+\/\d+.html'>\W+/i", 
		'step3' => "/(<a href=')|(.html'>)|(<\/)/i", 
		'step4' => "/\d+/i" 
	),
	'reg_xian' => array(
		'step1' => "/<tr class='countytr'>.+/i", 
		'step2' => "/(<a href='\d+\/\d+.html'>\W+)|(<td>\W+<\/td>)/i", 
		'step3' => "/(<a href=')|(.html'>)|(<\/)/i", 
		'step4' => "/\d+/i",
		'step5' => "/<td>\d+<\/td><td>\W+<\/td>/i"
	),
	'reg_xiang' => array(
		'step1' => "/<tr class='towntr'>.+/i", 
		'step2' => "/<a href='\d+\/\d+.html'>\W+/i", 
		'step3' => "/(<a href=')|(.html'>)|(<\/)/i", 
		'step4' => "/\d+/i"
	),
	'reg_cun' => array(
		'step1' => "/<tr class='villagetr'>.+/i", 
		'step2' => "/<td>\d+<\/td><td>\d+<\/td><td>\W+<\/td>/i", 
	),
	'init_sql' => array(
		'province' => 'CREATE TABLE `province` (`id` int(11) NOT NULL,`name` varchar(45) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
		'city'     => "CREATE TABLE `city` (`id` int(11) NOT NULL,`name` varchar(45) DEFAULT NULL,`pid` int(11) DEFAULT NULL,`type` int(11) DEFAULT NULL COMMENT '0下级是县,1下级是乡镇',PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
		'xian'     => 'CREATE TABLE `xian` (`id` int(11) NOT NULL,`name` varchar(45) DEFAULT NULL,`pid` int(11) DEFAULT NULL,`cid` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
		'xiang'    => 'CREATE TABLE `xiang` (`id` int(11) NOT NULL,`name` varchar(45) DEFAULT NULL,`pid` int(11) DEFAULT NULL,`cid` int(11) DEFAULT NULL,`xid` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;',
		'cun'      => 'CREATE TABLE `cun` (`id` int(11) NOT NULL,`name` varchar(45) DEFAULT NULL,`pid` int(11) DEFAULT NULL,`cid` int(11) DEFAULT NULL,`xid` int(11) DEFAULT NULL,`xgid` int(11) DEFAULT NULL,`type` int(11) DEFAULT NULL,PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;'
	),
	'sleep_time'       => 0,
	'error_sleep_time' => 3,
	# 4419东莞市，4420中山市 下级直接是乡镇，没有县
	'special_city' => array(
		0 => 4419,
		1 => 4420
	),
	# 县 没有下级
	'special_xian' => array(
		0 => '市辖区', // 所有市 下级有市辖区的 都是没有下级的
		1 => '西沙群岛', // 海南省 三沙市
		2 => '南沙群岛', // 海南省 三沙市
		3 => '中沙群岛的岛礁及其海域', // 海南省 三沙市
		4 => '金门县' // 福建省 泉州市 金门县
	),
	# 乡 没有下级
	'special_xiang' => array(
		0 => '大胡同街道',
		1 => '双港新家园街道', // 天津市 市辖区 津南区
		2 => '青源街道', // 天津市 市辖区 红桥区
		3 => '陡电街道办事处', // 河北省 唐山市 开平区
		4 => '荆各庄街道办事处', // 河北省 唐山市 开平区
	),
	# php utf-8不支持的地名
	'special_charset'  => array(
		# xian
		'410304'    => '瀍河回族区', // 河南省 洛阳市
		'420505'    => '猇亭区',
		'341302'    => '埇桥区', // 安徽省 宿州市
		'411502'    => '浉河区', // 河南省 信阳市
		'420104'    => '硚口区',
		# xiang
		'110112106' => '漷县镇', // 北京市 市辖区 通州区
		'120117205' => '俵口乡', // 天津市 市辖区 宁河区
		'130207110' => '柳树酄镇', // 河北省 唐山市 丰南区
		'130208003' => '浭阳街道办事处', // 河北省 唐山市 丰润区
		'130224100' => '倴城镇', // 河北省 唐山市 滦南县
		'130429100' => '临洺关镇', // 河北省 邯郸市 永年县
		'130523204' => '獐獏乡', // 河北省 邢台市 内丘县
		'130533100' => '洺州镇', // 河北省 邢台市 威县
		'130723202' => '哈咇嘎乡', // 河北省 张家口市 康保县
		'130823110' => '桲椤树镇', // 河北省 承德市 平泉县
		'130982104' => '鄚州镇', // 河北省 沧州市 任丘市
		'131082002' => '泃阳西大街街道办事处', // 河北省 廊坊市 三河市
		'131082100' => '泃阳镇', // 河北省 廊坊市 三河市
		'140423104' => '虒亭镇', // 山西省 长治市 襄垣县
		'140621206' => '薛圐圙乡', // 山西省 朔州市 山阴县
		'140823102' => '畖底镇', // 山西省 运城市 闻喜县
		'141034102' => '勍香镇', // 山西省 临汾市 汾西县
		'141123209' => '圪垯上乡', // 山西省 吕梁市 兴县
		'150124202' => '韮菜庄乡', // 内蒙古自治区 呼和浩特市 清水河县
		'152528201' => '宝格达音髙勒苏木', // 内蒙古自治区 锡林郭勒盟 镶黄旗
		'211422201' => '牤牛营子乡', // 辽宁省 葫芦岛市 建昌县
		'320312003' => '垞城街道', // 江苏省 徐州市 铜山区
		'320623100' => '栟茶镇', // 江苏省 南通市 如东县
		'320981121' => '弶港镇', // 江苏省 盐城市 东台市
		'321023101' => '氾水镇', // 江苏省 扬州市 宝应县
		'330206002' => '新碶街道', // 浙江省 宁波市 北仑区
		'330206004' => '大碶街道', // 浙江省 宁波市 北仑区
		'330211100' => '澥浦镇', // 浙江省 宁波市 镇海区
		'330212003' => '石碶街道', // 浙江省 宁波市 鄞州区
		'330304007' => '三垟街道', // 浙江省 温州市 瓯海区
		'330328100' => '大峃镇', // 浙江省 温州市 文成县
		'330328101' => '百丈漈镇', // 浙江省 温州市 文成县
		'330328108' => '峃口镇', // 浙江省 温州市 文成县
		'330382005' => '翁垟街道办事处', // 浙江省 温州市 乐清市
		'330681122' => '浬浦镇', // 浙江省 绍兴市 诸暨市
		'331002003' => '葭沚街道', // 浙江省 台州市 椒江区
		'331003204' => '上垟乡', // 浙江省 台州市 黄岩区
		'331121201' => '黄垟乡', // 浙江省 丽水市 青田县
		'331121213' => '汤垟乡', // 浙江省 丽水市 青田县
		'331123210' => '垵口乡', // 浙江省 丽水市 遂昌县
		'331126002' => '濛洲街道', // 浙江省 丽水市 庆元县
		'331127208' => '大漈乡', // 浙江省 丽水市 景宁畲族自治县
		'331127215' => '毛垟乡', // 浙江省 丽水市 景宁畲族自治县
		'331181101' => '上垟镇', // 浙江省 丽水市 龙泉市
		'331181203' => '竹垟畲族乡', // 浙江省 丽水市 龙泉市
		'340181105' => '中垾镇', // 安徽省 合肥市 巢湖市
		'340181107' => '烔炀镇', // 安徽省 合肥市 巢湖市
		'340203005' => '瀂港街道', // 安徽省 芜湖市 弋江区
		'340221100' => '湾沚镇', // 安徽省 芜湖市 芜湖县
		'341021103' => '富堨镇', // 安徽省 黄山市 歙县
		'341302001' => '埇桥街道', // 安徽省 宿州市 埇桥区
		'341322115' => '永堌镇', // 安徽省 宿州市 萧县
		'350122113' => '苔菉镇', // 福建省 福州市 连江县
		'350125204' => '洑口乡', // 福建省 福州市 永泰县
		'350521113' => '小岞镇', // 福建省 泉州市 惠安县
		'350982105' => '磻溪镇', // 福建省 宁德市 福鼎市
		'360112100' => '石埠镇', // 江西省 南昌市 新建区
		'360430102' => '马垱镇', // 江西省 九江市 彭泽县
		'360430108' => '瀼溪镇', // 江西省 九江市 彭泽县
		'360482201' => '苏家垱乡', // 江西省 九江市 共青城市
		'360821112' => '浬田镇', // 江西省 吉安市 吉安县
		'360830103' => '浬田镇', // 江西省 吉安市 永新县
		'360881201' => '黄垇乡', // 江西省 吉安市 井冈山市
		'360926105' => '大塅镇', // 江西省 宜春市 铜鼓县
		'361029200' => '珀玕乡', // 江西省 抚州市 东乡县
		'361181203' => '昄大乡', // 江西省 上饶市 德兴市
		'370832215' => '赵堌堆乡', // 山东省 济宁市 梁山县
		'370921105' => '堽城镇', // 山东省 泰安市 宁阳县
		'371702103' => '黄堽镇', // 山东省 菏泽市 牡丹区
		'410221106' => '阳堌镇', // 河南省 开封市 杞县
		'410225101' => '堌阳镇', // 河南省 开封市 兰考县
		'410304002' => '瀍西街道办事处', // 河南省 洛阳市 瀍河回族区
		'410304200' => '瀍河回族乡', // 河南省 洛阳市 瀍河回族区
		'410621004' => '伾山街道办事处', // 河南省 鹤壁市 浚县
		'411081103' => '神垕镇', // 河南省 许昌市 禹州市
		'411325110' => '岞岖镇', // 河南省 南阳市 内乡县
		'411421208' => '禇庙乡', // 河南省 商丘市 民权县
		'411502104' => '浉河港镇', // 河南省 信阳市 浉河区
		'411525203' => '马堽集乡', // 河南省 信阳市 固始县
		'411625001' => '洺南办事处', // 河南省 周口市 郸城县
		'411625002' => '洺北办事处', // 河南省 周口市 郸城县
		'420114002' => '奓山街道办事处', // 湖北省 武汉市 蔡甸区
		'420115087' => '豹澥街道办事处', // 湖北省 武汉市 江夏区
		'420682002' => '酂阳街道办事处', // 湖北省 襄阳市 老河口市
		'420984002' => '汈东街道办事处', // 湖北省 孝感市 汉川市
		'420984108' => '垌塚镇', // 湖北省 孝感市 汉川市
		'421022112' => '斑竹垱镇', // 湖北省 荆州市 公安县
		'421125109' => '丁司垱镇', // 湖北省 黄冈市 浠水县
		'422823101' => '东瀼口镇', // 湖北省 恩施土家族苗族自治州 巴东县
		'430223207' => '槚山乡', // 湖南省 株洲市 攸县
		'430224004' => '洣江街道办事处', // 湖南省 株洲市 茶陵县
		'430381203' => '育塅乡', // 湖南省 湘潭市 湘乡市
		'430528102' => '崀山镇', // 湖南省 邵阳市 新宁县
		'430702100' => '河洑镇', // 湖南省 常德市 武陵区
		'430721104' => '官垱镇', // 湖南省 常德市 安乡县
		'430723107' => '大堰垱镇', // 湖南省 常德市 澧县
		'430922208' => '鲊埠回族乡', // 湖南省 益阳市 桃江县
		'431224200' => '洑水湾乡', // 湖南省 怀化市 溆浦县
		'440103018' => '茶滘街道', // 广东省 广州市 荔湾区
		'440103019' => '东漖街道', // 广东省 广州市 荔湾区
		'440606102' => '北滘镇', // 广东省 佛山市 顺德区
		'441502004' => '田墘街道', // 广东省 汕尾市 城区
		'441521105' => '鮜门镇', // 广东省 汕尾市 海丰县
		'441624112' => '浰源镇', // 广东省 河源市 和平县
		'441823110' => '大崀镇', // 广东省 清远市 阳山县
		'441900124' => '道滘镇', // 广东省 东莞市
		'441900129' => '高埗镇', // 广东省 东莞市
		'445122123' => '汫洲镇', // 广东省 潮州市 饶平县
		'445321114' => '簕竹镇', // 广东省 云浮市 新兴县
		'450422104' => '埌南镇', // 广西壮族自治区 梧州市 藤县
		'450603101' => '大菉镇', // 广西壮族自治区 防城港市 防城区
		'450722102' => '石埇镇', // 广西壮族自治区 钦州市 浦北县
		'500101138' => '瀼渡镇', // 重庆市 市辖区 万州区
		'510422204' => '鳡鱼彝族乡', // 四川省 攀枝花市 盐边县
		'511011110' => '椑木镇', // 四川省 内江市 东兴区
		'511011112' => '椑南镇', // 四川省 内江市 东兴区
		'511528101' => '僰王山镇', // 四川省 宜宾市 兴文县
		'513221109' => '绵虒镇', // 四川省 阿坝藏族羌族自治州 汶川县
		'513426100' => '鲹鱼河镇', // 四川省 凉山彝族自治州 会东县
		'520203106' => '牂牁镇', // 贵州省 六盘水市 六枝特区
		'520324206' => '桴㯊乡', // 贵州省 遵义市 正安县
		'520522210' => '永燊乡', // 贵州省 毕节市 黔西县
		'522626100' => '思旸镇', // 贵州省 黔东南苗族侗族自治州 岑巩县
		'522629106' => '磻溪镇', // 贵州省 黔东南苗族侗族自治州 剑河县
		'522731002' => '濛江街道', // 贵州省 黔南布依族苗族自治州 惠水县
		'530181006' => '禄脿街道办事处', // 云南省 昆明市 安宁市
		'532322103' => '法脿镇', // 云南省 楚雄彝族自治州 双柏县
		'610122101' => '洩湖镇', // 陕西省 西安市 蓝田县
		'610304103' => '磻溪镇', // 陕西省 宝鸡市 陈仓区
		'610331106' => '王家堎镇', // 陕西省 宝鸡市 太白县
		'610502103' => '下邽镇', // 陕西省 渭南市 临渭区
		'610631206' => '崾崄乡', // 陕西省 延安市 黄龙县
		'610826103' => '定仙墕镇', // 陕西省 榆林市 绥德县
		'610828108' => '朱家坬镇', // 陕西省 榆林市 佳县
		'620821200' => '汭丰乡', // 甘肃省 平凉市 泾川县
		'622901101' => '枹罕镇', // 甘肃省 临夏回族自治州 临夏市
	)
);


?>