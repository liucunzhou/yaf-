<?php
class Model
{

    public $db = null;

    public $tableName = '';

    public $tableFullName = '';

    public $prefix = 'xz_';

    public $presql = '';

    public $preWhereSql = '';

    public $preOrder = '';

    public $preLimit = '';

    public $prexecute = [];

    public $data = [];

    public $lastRowCount = 0;

    public $preWhereIndex = 0;

    public $exceptionMessage = '';

    public function __construct()
    {
        $this->db = DataBase::getInstance();
        $modelName = get_called_class();
        $modelName = strtolower($modelName);
        $this->tableName = substr($modelName, 0, -5);
        $this->tableFullName = $this->prefix . $this->tableName;
    }

    /**
     * 获取数据
     */
    public function find($fields = "*")
    {
        $this->presql = "select {$fields} from {$this->tableFullName} {$this->preWhereSql} {$this->preOrder} limit 1";

        try {
            $stmt = $this->db->prepare($this->presql);
            $stmt->execute($this->prexecute);
            $this->data = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->exceptionMessage = $e->getMessage();
        }
        return $this;
    }

    /**
     * 批量获取数据
     */
    public function select($fields = "*")
    {
        $this->presql = "select {$fields} from {$this->tableFullName} {$this->preWhereSql} {$this->preOrder} {$this->preLimit}";
        $this->query($this->presql, $this->prexecute);

        return $this;
    }

    public function count()
    {
        $this->presql = "select count(*) from {$this->tableFullName} {$this->preWhereSql} {$this->preLimit}";
        try {
            $stmt = $this->db->prepare($this->presql);
            $stmt->execute($this->prexecute);
            $this->data = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->exceptionMessage = $e->getMessage();
        }
        return $this;
    }

    /**
     * 保存数据
     */
    public function save($data)
    {
        $setData = http_build_query($data, '', ',');
        $this->presql = "update {$this->tableFullName} set {$setData}  {$this->preWhereSql}";

        try {
            $stmt = $this->db->prepare($this->presql);
            $stmt->execute($this->prexecute);
            $this->lastRowCount = $stmt->rowCount();
        } catch (PDOException $e) {
            $this->exceptionMessage = $e->getMessage();
        }

        return $this;
    }

    /**
     * 查询
     */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $this->data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->exceptionMessage = $e->getMessage();
        }

        return $this;
    }

    /**
     * 写入数据
     * originData是写入的原始数据
     * 在写于数据库之前，要先进行预处理
     */
    public function insert($originData)
    {
        $data = $this->formatData($originData);
        $tableName = $this->tableName;

        // 组装要写入的字段
        $keys = array_keys($originData);
        $fields = http_build_query($keys, '', ',');

        $prepare = http_build_query($data[0], '', ',');
        $sql = "insert into {$tableName} ({$fields}) values($prepare)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($data[1]);
        } catch (PDOException $e) {
            $this->exceptionMessage = $e->getMessage();
        }

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * 获取最后一次写入的ID
     */
    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function beginTransaction()
    {
        $this->db->beginTransaction();
        return $this;
    }

    public function commit()
    {
        $this->db->commit();
        return $this;
    }

    public function rollBack()
    {
        $this->db->rollBack();
        return $this;
    }

    private function formatData($data)
    {
        // 预处理集合
        $prepare = [];
        // 预处理值
        $execute = [];

        foreach ($data as $key => $value) {
            $fkey = ':' . $key;
            $prepare[$key] = $fkey;
            $execute[$fkey] = $value;
        }

        // prepare 代表预处理得参数
        // execute 代表格式化后的参数
        return [$prepare, $execute];
    }

    /**
     * 排序
     */
    public function order($order)
    {
        $this->preOrder = 'order by ' . $order;
        return $this;
    }

    /**
     * 分页
     */
    public function limit($page = 0, $limit = 1)
    {
        if ($page == 0) {
            $this->preLimit = "limit {$limit}";
        } else {
            $start = $page * $limit;
            $this->preLimit = "limit {$start},{$limit}";
        }
        return $this;
    }

    /**
     * 格式化条件
     */
    public function where($map, $outer = "and", $inner = "and")
    {

        $params = [];
        $where = [];
        foreach ($map as $key => $value) {

            if (!is_array($value)) {
                $type = 'eq';
            } else {
                $type = $value[0];
            }

            $fkey = ':' . $key . $this->preWhereIndex;
            switch ($type) {
                case 'eq': // 相等查询
                    $params[$fkey] = $value;
                    $where[] = "{$key}={$fkey}";
                    break;

                case 'neq': // 不等查询
                    $params[$fkey] = $value[1];
                    $where[] = "{$key}!={$fkey}";
                    break;

                case 'gt': // 大于类型
                    $params[$fkey] = $value[1];
                    $where[] = "{$key} > {$fkey}";
                    break;

                case 'lt': // 小于类型
                    $params[$fkey] = $value[1];
                    $where[] = "{$key} < {$fkey}";
                    break;

                case 'in': // 范围查询
                    $params[$fkey] = implode(',', $value[1]);
                    $where[] = "{$key} in ({$fkey})";
                    break;

                case 'between':

                    $between1 = $fkey . '_between1';
                    $between2 = $fkey . '_between2';
                    $params[$between1] = $value[1][0];
                    $params[$between2] = $value[1][1];
                    $where[] = "{$key} between :{$between1} and :{$between2}";
                    break;

                case 'like':
                    $where[] = "{$key} like {$value[1]}";
                    break;
            }
        }

        $sql = implode(" {$inner} ", $where);

        if ($this->preWhereSql == '') {
            $this->preWhereSql  .= "where ({$sql})";
        } else {
            $this->preWhereSql  .= " {$outer} ({$sql})";
        }
        $this->prexecute = array_merge($this->prexecute, $params);

        $this->preWhereIndex = $this->preWhereIndex + 1;
        return $this;
    }
}
