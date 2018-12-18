<?php

namespace UrbanIndo\Yii2\QueueTests\Behaviors;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Yii;

class DeferredEventRoutingBehaviorTest extends BaseTestCase
{
    
    public function testEventRouting()
    {
        
        $queue = Yii::$app->queue;
        /* @var $queue \UrbanIndo\Yii2\Queue\Queues\MemoryQueue */
        $this->assertEquals(0, $queue->getSize());
        $model = new DeferredEventRoutingBehaviorTestModel();
        $model->trigger('eventTest');
        $this->assertEquals(1, $queue->getSize());
        $model->id = 5;
        $job = $queue->fetch();
        $this->assertEquals('test/index', $job->route);
        $this->assertFalse($job->isCallable());
        $this->assertEquals(0, $queue->getSize());
        $this->assertEquals([
            'id' => 1,
            'test' => 2,
        ], $job->data);
        $model->trigger('eventTest2');
        $this->assertEquals(1, $queue->getSize());
        $job = $queue->fetch();
        $this->assertEquals('test/halo', $job->route);
        $this->assertFalse($job->isCallable());
        $this->assertEquals(0, $queue->getSize());
        $this->assertEquals([
            'halo' => 5
        ], $job->data);
        
    }
}

class DeferredEventRoutingBehaviorTestModel extends \yii\base\Model {
    
    const EVENT_TEST = 'eventTest';
    const EVENT_TEST2 = 'eventTest2';
    
    public $id;
    
    public function behaviors() {
        return [
            [
                'class' => 'UrbanIndo\Yii2\Queue\Behaviors\DeferredEventRoutingBehavior',
                'events' => [
                    self::EVENT_TEST => ['test/index', 'id' => 1, 'test' => 2],
                    self::EVENT_TEST2 => function($model) {
                        return ['test/halo', 'halo' => $model->id];
                    }
                ]
            ]
        ];
    }
    
    
}