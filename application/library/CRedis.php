<?php
class CRedis
{
    public static $exceptionMessage = '';

    #避免在类外部被实例化
    final function __construct()
    { }

    #不允许对象实例被克隆
    final function __clone()
    { }

    #类唯一实例的全局访问点
    public static function getInstance(string $redis_name = 'master')
    {
        header("Content-type: text/html;charset=utf-8");
        $config = Yaf_Registry::get("config");
        $redisConfig = $config->redis->$redis_name;

        $redis = new \Redis();
        try {
            $redis->connect($redisConfig->host, (int) $redisConfig->port, 1);
            $redis->auth($redisConfig->password);
        } catch (\RedisException $e) {
            self::$exceptionMessage = $e->getMessage();
        } catch (\Exception $e) {
            self::$exceptionMessage = $e->getMessage();
        }

        self::$exceptionMessage = '';      

        return $redis;
    }


}
