<?php
/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author xiaozhu
 */
class AllocateModel extends Model {
    
    public function index()
    {
        $map['id'] = 100;
        $model = new Model();
        echo $model->formatCondition($map);

        return false;
    }
}
