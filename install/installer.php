<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/23
 * Time: 10:53
 */
namespace mjwlking\installer;
/**
 * Class installer
 * @package mjwlking\installer
 */
class installer
{
    //是否允许用户协议
    public $set_rule = False;
    //安装类文件的绝对路径
    public $class_dir = './install/';
    //默认一个用户协议
    public $rule_file = '/resources/rule.txt';
    //版权期限
    public $copyright = "";
    //版权txt文件的长度
    public $rult_max_size = 30960;
    //开发者及网站信息
    public $site_info = array(
        'Title' => '安装向导',
        'Powered' => 'Powered by mjwlking',
        'Inc' => '香港奈特伍森网络有限公司'
    );
    //推荐安装参数
    public $install_info = array(
        'phpversion' => '>5.4X',
        'server' => '',
        'session' => '需开启',
        'safe_mode' => '基础配置',
        'gd' => '必须开启', //是否需要开启GD库
        'mysqli' => '必须开启',
        'uploadSize' => '>2M',
        'apache' => '',
        'nginx' => ''
    );

    //最低要求
    public $min_req = array(
        'phpversion' => '5',
        'server' => 'apache2.0以上/nginx1.6以上',
        'session' => true,
        'safe_mode' => true,
        'gd' => true,
        'mysqli' => true,
        'uploadSize' => true,
        'apache' => '2.4',
        'nginx' => '1'
    );

    //检查函数
    public $functions = array(
        'curl_init',
        'get_put_contents',
        'array_merge',
        'strstr',
        'apache_get_version',
        'preg_match'
    );

    //目录、文件权限
    public $folder = array(
        'install',
        'public/upload',
        'application/admin/conf',
        'runtime',
        'runtime/cache',
        'runtime/temp',
        'runtime/log',
        'step2.html'
    );

    //构造函数 类初始化
    public function __construct()
    {
        //检测必要的目录权限虾米的
        $this->rule_file = $this->class_dir.$this->rule_file;
        $this->copyright = date('Y',time());
    }

    //获取没有定义的属性
    function __get($attr)
    {
        echo "{$attr} 属性未定义或者未声明。";
    }
    //设置不存在的属性
    function __set($attr,$val)
    {
        echo "{$attr} 属性没有权限访问，或者该属性不存在。";
    }
    //判断没有定义的属性
    function __isset($attr)
    {
        echo "{$attr} 属性未定义或者未声明。";
    }
    //判断没有定义的属性
    function __unset($attr)
    {
        echo "{$attr} 属性未定义或者未声明。";
    }

    //读取协议，根据文件类型，返回html。
    public function rules_html()
    {
        $rules_html = "";
        $host = $_SERVER['HTTP_HOST'];
        $copyright = empty($this->copyright)?date('Y',time()):$this->copyright;
        //获取规则的扩展名
        if (!isset($_SERVER['PATH_INFO']))
        {
            $file_ext = substr($this->rule_file, strrpos($this->rule_file, '.')+1);
        }
        else
        {
            $file_path = pathinfo($this->rule_file);
            $file_ext = @$file_path ['extension'];
        }

        if($file_ext == 'txt')
        {

            $rules_html = $this->valid_rule_file($this->rule_file);
            //加上排版标识
            $rules_html = '<pre class="pact" readonly="readonly">'.$rules_html.'</pre>';
        }
        else
        {
            $rules_html = '<section class="section"><iframe width="100%" height="500" src="'.$this->rule_file.'"></iframe></section>';
        }

        $html = <<<EOF
<!doctype html>
<html>
<head>
<meta charset="UTF-8" />
<title>{$this->site_info['Title']} - {$this->site_info['Powered']}</title>
<link rel="stylesheet" href="{$this->class_dir}css/install.css?v=9.0" />
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1 class="logo">logo</h1>
    <div class="icon_install">安装向导——用户协议</div>
    <div class="version"></div>
  </div>
  <div class="section">
    <div class="main cc">
    {$rules_html}
    </div>
    <div class="bottom tac"> <a href="{$_SERVER['PHP_SELF']}?step=2" class="btn">接 受</a> </div>
  </div>
</div>
<div class="footer"> &copy; {$copyright}  <a href="http://{$host}" target="_blank">{$host}</a> {$this->site_info['Inc']}</div>
</body>
</html>
EOF;
        return $html;
    }

    //判断协议文件是否有效 ，返回协议文件内容。
    private function valid_rule_file()
    {
        //返回信息
        $info = "用户协议{{$this->rule_file}}不存在或者不可读，请使用绝对路径，例如‘./install/rule.txt’。";
        //文件目录
        $dir = dirname($this->rule_file);
        //文件名
        $file = basename($this->rule_file);
        //如果txt文件可读，则判断长度是否超过最大限制
        $enable_file = $this->file_mode_info($this->rule_file);
        if($enable_file > 0)
        {
            $info = file_get_contents($this->rule_file);
            //是否超过了配置中允许的协议文件长度
            $info = isset($info{$this->rult_max_size+1})?'协议文件太长，请适当精简一点。':$info;
            //上面玩了个花活，下面是标准写法
            if(filesize($this->rule_file) > $this->rult_max_size)
            {
                $info = '协议文件太长，请适当精简一点。';
            }
        }
        return $info;
    }
    /**
     * 文件或目录权限检查函数 摘自网络，修改了其中的bug。
     *
     * @access          public
     * @param           string  $file_path   文件路径
     * @param           bool    $rename_prv  是否在检查修改权限时检查执行rename()函数的权限
     *
     * @return          int     返回值的取值范围为{0 <= x <= 15}，每个值表示的含义可由四位二进制数组合推出。
     *                          返回值在二进制计数法中，四位由高到低分别代表
     *                          可执行rename()函数权限、可对文件追加内容权限、可写入文件权限、可读取文件权限。
     */
    private function file_mode_info($file_path)
    {
        /* 如果不存在，则不可读、不可写、不可改 */
        if (!file_exists($file_path))
        {
            return 0;
        }
        $mark = 0;
        /* 测试文件定义 */
        if (is_dir($file_path))
        {
            $dir = $file_path;
        }
        else
        {
            //文件目录
            $dir = dirname($file_path);
            //文件名
            $file = basename($file_path);
        }
        $test_file = $dir . '/test.txt';
        $test_file_write = $dir . '/writetest.txt';
        //判断操作系统
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
        {
            /* 如果是目录 */
            if (is_dir($file_path))
            {
                /* 检查目录是否可读 */
                $dir = @opendir($file_path);
                if ($dir === false)
                {
                    return $mark; //如果目录打开失败，直接返回目录不可修改、不可写、不可读
                }
                if (@readdir($dir) !== false)
                {
                    $mark ^= 1; //目录可读 001，目录不可读 000
                }
                @closedir($dir);
                /* 检查目录是否可写 */
                $fp = @fopen($test_file, 'wb');
                if ($fp === false)
                {
                    return $mark; //如果目录中的文件创建失败，返回不可写。
                }
                if (@fwrite($fp, 'directory access testing.') !== false)
                {
                    $mark ^= 2; //目录可写可读011，目录可写不可读 010
                }
                @fclose($fp);
                @unlink($test_file);
                /* 检查目录是否可修改 */
                $fp = @fopen($test_file, 'ab+');
                if ($fp === false)
                {
                    return $mark;
                }
                if (@fwrite($fp, "modify test.\r\n") !== false)
                {
                    $mark ^= 4;
                }
                @fclose($fp);
                /* 检查目录下是否有执行rename()函数的权限 */
                if (@rename($test_file, $test_file_write) !== false)
                {
                    $mark ^= 8;
                }
                @unlink($test_file_write);
            }
            /* 如果是文件 */
            elseif (is_file($file_path))
            {
                /* 以读方式打开 */
                $fp = @fopen($file_path, 'rb');
                if ($fp)
                {
                    $mark ^= 1; //可读 001
                }
                @fclose($fp);
                /* 试着修改文件 */
                $fp = @fopen($file_path, 'ab+');
                if ($fp && @fwrite($fp, '') !== false)
                {
                    $mark ^= 6; //可修改可写可读 111，不可修改可写可读011...
                }
                @fclose($fp);
                /* 检查目录下是否有执行rename()函数的权限 */
                if (rename($file_path, $test_file_write) !== false)
                {
                    $mark ^= 8;
                    //换成源文件名
                    rename($test_file_write, $file_path);
                }
            }
        }
        else
        {
            if (@is_readable($file_path))
            {
                $mark ^= 1;
            }
            if (@is_writable($file_path))
            {
                $mark ^= 14;
            }
        }
        return $mark;
    }

    public function check_environment()
    {
        $os = PHP_OS;
        //$os = php_uname();
        $tmp = function_exists('gd_info') ? gd_info() : array();
        $server_type = $_SERVER["SERVER_SOFTWARE"];
        $host = (empty($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_HOST"] : $_SERVER["SERVER_ADDR"]);
        $name = $_SERVER["SERVER_NAME"];
        $max_execution_time = ini_get('max_execution_time');
        $allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[√]On</font>' : '<font color=red>[×]Off</font>');
        $err = 0;
        $serverv = '';

        if(strpos($server_type,'Apache') !== false){
            $patten = '/\b(Apache\/){1}[12]{1}[.]+[\d.]+/';
            preg_match($patten,$server_type,$arr);
            $serverv = explode('/',$arr['0'])['1'];
            //$serverv = apache_get_version();
            if($serverv >= $this->min_req['apache']){
                $server = '<span class="correct_span">&radic;</span>'.$server_type;
            }else{
                $server = '<span class="correct_span error_span">&radic;</span>'.$server_type;
                $err++;
            }
        }elseif(strpos($server_type,'nginx') !== false){
            $patten = '/\b(nginx\/){1}[12]{1}[.]+[\d.]+/';
            preg_match($patten,$server_type,$arr);
            $serverv = explode('/',$arr['0'])['1'];
            if($serverv >= $this->min_req['nginx']){
                $server = '<span class="correct_span">&radic;</span>'.$server_type;
            }else{
                $server = '<span class="correct_span error_span">&radic;</span>'.$server_type;
                $err++;
            }
        }else{
            $server = '<span class="correct_span error_span">&radic;</span>您尚未安装apache和nginx';
            $err++;
        }
        if($this->min_req['safe_mode']){
            $min_safe_mode = '必需开启';
            if(ini_get('safe_mode')){
                $safe_mode = '<span class="correct_span">&radic;</span>支持';
            }else{
                $safe_mode = '<span class="correct_span error_span">&radic;</span>不支持';
                $err++;
            }
        }else{
            $min_safe_mode = '不需开启';
            if(ini_get('safe_mode')){
                $safe_mode = '<span class="correct_span">&radic;</span>支持';
            }else{
                $safe_mode = '<span class="correct_span error_span">&radic;</span>不支持';
            }
        }
        if (phpversion() < $this->min_req['phpversion']) {
            $phpv =  '<span class="correct_span error_span">&radic;</span>'.phpversion();
            $err++;
        }else{
            $phpv = '<span class="correct_span">&radic;</span> '.phpversion();
        }
        if($this->min_req['gd']){
            $recommend_gd = '开启';
            if (empty($tmp['GD Version'])) {
                $gd = '<span class="correct_span error_span">&radic;</span>';
                $err++;
            } else {
                $gd = '<span class="correct_span">&radic;</span> ' . $tmp['GD Version'];
            }
        }else{
            $recommend_gd = '非必需开启';
            if (empty($tmp['GD Version'])) {
                $gd = '<span class="correct_span error_span">&radic;</span>';
            } else {
                $gd = '<span class="correct_span">&radic;</span> ' . $tmp['GD Version'];
            }
        }
        if($this->min_req['mysqli']){
            $recommend_mysqli = '开启';
            if (function_exists('mysqli_connect')) {
                $mysql = '<span class="correct_span">&radic;</span> 已安装';
            } else {
                $mysql = '<span class="correct_span error_span">&radic;</span> 请安装mysqli扩展';
                $err++;
            }
        }else{
            $recommend_mysqli = '非必需开启';
            if (function_exists('mysqli_connect')) {
                $mysql = '<span class="correct_span">&radic;</span> 已安装';
            } else {
                $mysql = '<span class="correct_span error_span">&radic;</span> 未安装mysqli扩展';
            }
        }
        if($this->min_req['session']){
            $recommend_session = '必须开启';
            if (function_exists('session_start')) {
                $session = '<span class="correct_span">&radic;</span> 支持';
            } else {
                $session = '<span class="correct_span error_span">&radic;</span> 不支持';
                $err++;
            }
        } else{
            $recommend_session = '非必须开启';
            if (function_exists('session_start')) {
                $session = '<span class="correct_span">&radic;</span> 支持';
            } else {
                $session = '<span class="correct_span error_span">&radic;</span> 不支持';
            }
        }
        if($this->min_req['uploadSize']){
            $recommend_uploadSize = '必须开启';
            if (ini_get('file_uploads')) {
                $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
            } else {
                $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
                $err++;
            }
        } else{
            $recommend_uploadSize = '非必须开启';
            if (ini_get('file_uploads')) {
                $uploadSize = '<span class="correct_span">&radic;</span> ' . ini_get('upload_max_filesize');
            } else {
                $uploadSize = '<span class="correct_span error_span">&radic;</span>禁止上传';
            }
        }

        $res = $this->check_function( $this->functions,$err );
        $fun_name = $res['0'];
        $err = $res['1'];
        $folder = $this->folder;
        return include_once('./step2.html');

    }

    public function check_function( $functions ,$err)
    {
        foreach($functions as $fun_name){
            if(function_exists($fun_name)){
                $fun_arr[$fun_name] = '<font color=green>[√]支持</font> ';
            }else{
                $fun_arr[$fun_name] = '<font color=red>[×]不支持</font>';
                $err++;
            }
        }

        return array($fun_arr,$err);
    }
}
$install = new installer;
$install->check_environment();


