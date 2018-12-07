<?php
/**
 * Created by PhpStorm.
 * User: 吴宏佳
 * Date: 2018/8/27
 * Time: 下午3:52
 */

//积分系统的积分类型
define('COIN_CODE', 'XZ');

//勋章管理员账户 在积分系统中的acctid
define('COIN_ADMIN_ACCTID_XZ', DEBUG?'8890000000154406549251462':'');

//勋章 磨损 账户 在积分系统中的acctid
define('COIN_ADMIN_ACCTID_MS', DEBUG?'8890000000154406552939161':'');


//警示管理员账户 在积分系统中的acctid
define('COIN_ADMIN_ACCTID_SJ', 'IN_XZ_8890000000154406549251462');


//所有账户 在积分系统中的密码
define('COIN_PWD_XZ', '20181128105358');

//用户之间转让勋章的磨损系数
define('COIN_TRANSFER_WEAR', 0.1);



//勋章管理 平台名称
define('COIN_PLATFORM', '沐新平台');
