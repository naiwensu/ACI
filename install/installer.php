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
    //默认一个用户协议
    public $rule_file = "./resources/rule.txt";
    //协议的文件类型 默认txt
    public $rule_ext = "txt";

    //构造函数 类初始化
    public function __construct()
    {

    }

    //读取协议，根据文件类型，返回html。
    protected function rule_html($rule_file,$rule_ext)
    {
        $action = 0;
        $rulePath = $rule_file ? $rule_file : $this->rule_file;
        $ruleExt  = $rule_ext ? $ $rule_ext ：$this->rule_ext;
        if( !in_array($ruleExt, ['txt','html','htm']) ){
           return false;
        }
        //文件是否存在
        if ( $this->file_notexist($rulePath); ){
            return false;
        }
        //读取文件html或txt
        switch ( $ruleExt ) {
            case 'txt':
                $textArea = $this->read_txt( $rulePath );
                $action = 1;
                include_once ("./templates/step1.php");
                break;
            case 'html':
                $htmlPath = $rulePath;
                $action = 2;
                include_once ("./templates/step1.php");
                break;
            case 'htm':
                $htmlPath = $rulePath;
                $action = 2;
                include_once ("./templates/step1.php");
                break;
            
            default:
                return false;
                break;
        }

    //读取txt文件
    private function read_txt($Path)
    {
        if( filesize($Path) > 5000)
        {
            return "文件太长"
        }
        else
        {
         return file_get_contents($Path);
        }
    }

    //文件不存在
    private function file_notexist($rulePath)
    {

        }
        if ( file_exists($rulePath) ){
            return true;
        } 
        else{
            return false;
        }
    }
}


