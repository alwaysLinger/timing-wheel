## DESCRIPTION

```
Basically this package provides a timing-wheel process for hyperf
```

## INSTALLATION

```
composer require yylh/timing-wheel
php bin/hyperf.php vendor:publish yylh/timing-wheel
```

## USAGE

```
# method got invoked after the worker.0 start
/**
 * @DelayInvoke()
 */
class FooClass
{
    public function dueProcess()
    {
        var_dump('this dueProcess method will get executed immediately when worker.0 started');
    }
    
    /**
     * @DelayInvoke(delay=5000)
     */
    public function dueProcess()
    {
        var_dump('this dueProcess method got invoked in 5 seconds after worker.0 started');
    }
}

# method got loop invoked after the worker.0 start
/**
 * @LoopInvoke(interval="2000")
 */
class BarClass
{
    public function dueProcess()
    {
        var_dump('dueProcess will auto looped:' . time());
    }
    
    /**
     * @LoopInvoke(interval=2000)
     */
    public function bazMethod()
    {
        var_dump('this method will get invoked every 2 seconds')
    }
    
    /**
     * @LoopInvoke(interval=1000)
     */
    public function addTasks()
    {
        // add two tasks which will get invoked in timing-wheel process
        $msg = [
            [
                'operate' => Task::AddTask_Operation,
                'payload' => [
                    'name' => uniqid(),
                    'delay' => 1,
                    'action' => SomeService::class . '::someAction',
                    'context' => [
                        TickService::class . '::arg1',
                        TickService::class . '::arg2',
                        'uniqid',
                    ]
                ]
            ],
            [
                'operate' => Task::AddTask_Operation,
                'payload' => [
                    'name' => uniqid(),
                    'delay' => 2,
                    'action' => OtherService::class . '::otherAction',
                    'context' => [
                        TickService::class . '::arg1',
                        TickService::class . '::arg2',
                        'uniqid',
                    ]
                ]
            ],
        ];
        var_dump(send_tasks($msg));
    }
    
    /**
     * @DelayInvoke(delay=10000)
     */    
    public function delTasks()
    {
        // delete some tasks
        $ret = TaskEmitter::sendTask([
            [
                'operate' => Task::DelTask_Operation,
                'payload' => [
                    'name' => 'some_task_name',
                ]
            ],
        ]);
        var_dump($ret);
    }
}

```

## TODOS

```
1、annotation timing-wheel task support
2、php8 support
3、task manager implement
4、coroutine server support
5、more thorough tasks exceptions handle
6、more callables type support
7、some task execution events
8、convenient method to inspect the timing-wheel process
9、specific signal handler
```