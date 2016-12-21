<?php

namespace KiwiBladeTests\DI;

use KiwiBlade\DI\Container;
use KiwiBlade\DI\ContainerException;
use KiwiBlade\DI\NotFoundException;
use KiwiBlade\Forms\FormFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    /** @var Container */
    private $container;

    public function setUp()
    {
        $this->container = new Container();
        $this->container->autoregisterService(CyclicDependencyDummy::class);
        $this->container->registerService(FormFactory::class, function () {
            return new FormFactory();
        });
        $this->container->setParameters([
            'parameters' => [
                'one' => 1,
                'twelve' => 12,
            ],
            'ab' => [
                'a' => '%one%',
                'b' => '%twelve%',
            ],
        ]);
        $this->container->autoregisterService(ABDummy::class, 'ab');
    }

    public function testGetExistingService()
    {
        $instance = $this->container->get(FormFactory::class);
        Assert::assertEquals(FormFactory::class, get_class($instance));
    }

    public function testGetUnregisteredService()
    {
        $this->expectException(NotFoundException::class);
        $this->container->get(\PDO::class);
    }

    public function testGetCyclicDependentService()
    {
        $this->expectException(ContainerException::class);
        $this->container->get(CyclicDependencyDummy::class);
    }

    public function testDynamicParameterAssign(){
        /** @var ABDummy $abDummy */
        $abDummy = $this->container->get(ABDummy::class);
        if(get_class($abDummy) != ABDummy::class){
            Assert::markTestSkipped("Did not get ABDummy testing class");
            return;
        }

        Assert::assertEquals($this->container->getParams()['one'], $abDummy->a);
        Assert::assertEquals($this->container->getParams()['twelve'], $abDummy->b);
    }
}
