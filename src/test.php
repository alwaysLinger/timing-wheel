<?php

// $server = new \Swoole\Http\Server('127.0.0.1', 9501);
// // var_dump($server->ports);
// var_dump(get_class_methods($server->ports[0]));
// var_dump(get_class($server->ports[0]));


use Al\TimingWheel\TimingWheelManager;

include '../vendor/autoload.php';

// $a = new \Al\TimingWheel\Utils\CircularArray(2);
// $a[0] = 1;
// $a[1] = 2;
// foreach ($a as $item) {
//     var_dump($item);
// }

// $a = \Al\TimingWheel\Utils\CircularArray::fromArray(range(1, 10));
// $b = new ArrayObject($a->toArray());
// var_dump($b->getArrayCopy());
// $a = \Al\TimingWheel\Utils\CircularArray::fromArray(123);
// $a = \Al\TimingWheel\Utils\CircularArray::fromArray(array(1, 2, 3, 4));
// var_dump(count($a));


// foreach ($a as $v) {
//     var_dump($v);
// }

// $a = \Al\TimingWheel\TimingWheel::fromArray(range(1, 5));
// var_dump(get_class($a));

$a = collect();

$server = new \Swoole\Http\Server('127.0.0.1', 9501);
$p1 = new \Swoole\Process(function ($p1) use ($a) {
    // var_dump($a->toArray());
    $p2 = $a->get(2);
    $s2 = $p2->exportSocket();
    go(function () use ($s2) {
        while (true) {
            $s2->send('from p1');
            co::sleep(1);
        }
    });
    go(function () use ($p1) {
        $s1 = $p1->exportSocket();
        while (true) {
            $data = $s1->recv();
            var_dump($data);
        }
    });
}, false, 2, 1);
$a->put(1, $p1);

$p2 = new \Swoole\Process(function ($p2) use ($a) {
    $p1 = $a->get(1);
    $s1 = $p1->exportSocket();
    go(function () use ($s1) {
        while (true) {
            $s1->send('from p2');
            co::sleep(1);
        }
    });
    go(function () use ($p2) {
        $s2 = $p2->exportSocket();
        while (true) {
            $data = $s2->recv();
            var_dump($data);
        }
    });
}, false, 2, 1);
$a->put(2, $p2);

$server->addProcess($p1);
$server->addProcess($p2);

$server->on('workerStart', function () use ($server) {
    // go(function () use ($server) {
    $chan = new \Swoole\Coroutine\Channel();
    $server->defer(function () use ($chan) {
        var_dump('immediately');
        $chan->push(1);
    });
    $chan->pop();
    // });
    // go(function () use ($server) {
    $server->after(2 * 1000, function () {
        var_dump('delay invocation');
    });
    // });
});

$server->on('request', function () {

});
$server->start();

// TimingWheelManager::Process_Name