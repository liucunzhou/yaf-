<?php
/**
 * @name IndexController
 * @author xiaozhu
 * @desc 默认控制器
 * @see http://www.php.net/manual/en/class.yaf-controller-abstract.php
 */
class IndexController extends Yaf_Controller_Abstract {

	/**
     * 默认动作
     * Yaf支持直接把Yaf_Request_Abstract::getParam()得到的同名参数作为Action的形参
     * 对于如下的例子, 当访问http://yourhost/NetSchool/index/index/index/name/xincun 的时候, 你就会发现不同
     */
	public function indexAction() {

        $redis = CRedis::getInstance();
        $redis->get("");

        return false;

        $model = new AdminModel;
        echo "<pre>";
        echo "<h2>测试等号或in的操作</h2>";
        $map = [];
        $map['id'] = 1;
        $map['status'] = ['in', [1,2,3,4,5,6]];
        $model->where($map)->find();


        echo "SQL:".$model->presql;
        echo "<br>";
        print_r($model->getData());
        echo "<br>";

		return false;
	}

    public function HelloAction($name = "Stranger") {
        echo 'HelloAction';
        return false;
    }
}
