<?php
return array(
    'URL_MODEL' => '2',
    'URL_PARAMS_BIND_TYPE' => 1,
    /* 数据库设置 */
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'lrvp_live', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'spsqlmima', // 密码
    'DB_PREFIX' => 'lrvp_', // 数据库表前缀
    'DB_CONFIG1' => array(
        'db_type' => 'mysql',
        'db_user' => 'root',
        'db_pwd' => '',
        'db_name' => 'center',
        'db_host' => '127.0.0.1',
        'db_port' => '3306',
//        PDO::ATTR_TIMEOUT=>3
        'db_params' =>
            array(
                12 => true,
                1002 => 'SET NAMES \'UTF8\'',
                2 => 1,
            ),
    ),
    'URL_HTML_SUFFIX' => false,
    'LANG_SWITCH_ON' => true, // 开启语言包功能
    'LANG_AUTO_DETECT' => true, // 自动侦测语言 开启多语言功能后有效
    'LANG_LIST' => 'zh-cn,en-us,ja-jp', // 允许切换的语言列表 用逗号分隔
    'VAR_LANGUAGE' => '2', // 默认语言切换变量
);
