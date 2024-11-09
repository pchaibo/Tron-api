<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-27
 * Time: 21:15
 */

use \Workerman\Worker;
use \Workerman\Lib\Timer;
require_once __DIR__ . '/../vendor/Workerman/Autoloader.php';
require_once __DIR__ . '/../vendor/autoload.php';

$task = new Worker();
// 开启多少个进程运行定时任务，注意业务是否在多进程有并发问题
$task->count = 1;
$task->onWorkerStart = function($task)
{
    $hour = intval(date('H'));
    if( $hour>5){
		// 将db实例存储在全局变量中(也可以存储在某类的静态成员中)
		global $db;
		$db = new Workerman\MySQL\Connection('127.0.0.1', '3306', 'pay', 'pay2017+','pay');
		// 每2.5秒执行一次
		$time_interval = 10;
		Timer::add($time_interval, function()
		{
			global $db;
            $data =  $db->select('*')->from('pay_blockedlog')
               ->where('thawtime > :thawtime and createtime > :ytime and createtime < :curtime and status = :status')
               ->bindValues(array('thawtime' => strtotime('today'),'ytime'=>strtotime("yesterday"),
                   'curtime'=>strtotime('today'),'status'=>0))
               ->groupBy(array('thawtime'))
               ->limit(0)
               ->offset(50)
               ->query();
			if(count($data)){
				foreach ($data as $item){
					//解冻金额
					$row_count = $db->query("Update pay_member set balance=balance+{$item['amount']} ,blockedbalance=blockedbalance-{$item['amount']} WHERE `id`={$item['userid']}");
					if($row_count){
						$db->query("UPDATE `pay_blockedlog` SET `status` = 1 WHERE `id`=".$item['id']);
						$db->query("INSERT INTO `pay_moneychange` ( `userid`,`money`,`datetime`,`tongdao`,`transid`,`orderid`,`lx`,`contentstr`)
VALUES ( '".$item['userid']."', '".$item['amount']."', '".date("Y-m-d H:i:s")."', '".$item['pid']."','','".$item['orderid']."',8,'订单金额解冻') ");
					}
				}
			}
			//echo "task run\n";
		});
    }
};

// 运行worker
Worker::runAll();