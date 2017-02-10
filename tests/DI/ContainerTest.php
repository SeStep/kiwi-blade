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
    public function testGetExistingService()
    {
        $container = new Container();
        $container->registerService(FormFactory::class, function () {
            return new FormFactory();
        });

        $instance = $container->get(FormFactory::class);
        Assert::assertEquals(FormFactory::class, get_class($instance));
    }

    public function testGetUnregisteredService()
    {
        $container = new Container();

        $this->expectException(NotFoundException::class);
        $container->get(\PDO::class);
    }

    public function testGetCyclicDependentService()
    {
        $container = new Container();
        $container->autoregisterService(CyclicDependencyDummy::class);

        $this->expectException(ContainerException::class);
        $container->get(CyclicDependencyDummy::class);
    }

    public function testDynamicParameterAssign()
    {
        $container = new Container();
        $container->setParameters([
            'parameters' => [
                'one' => 1,
                'twelve' => 12,
            ],
            'test' => [
                'ab' => [
                    'a' => '%one%',
                    'b' => '%twelve%',
                ],
            ],
        ]);
        $container->autoregisterService(ABDummy::class, 'test.ab');

        /** @var ABDummy $abDummy */
        $abDummy = $container->get(ABDummy::class);
        if (get_class($abDummy) != ABDummy::class) {
            Assert::markTestSkipped("Did not get ABDummy testing class");

            return;
        }

        Assert::assertEquals($container->getParams()['one'], $abDummy->a);
        Assert::assertEquals($container->getParams()['twelve'], $abDummy->b);
    }

    public function testFactoryParametrized()
    {
        $container = new Container();

        $container->setParameters([
            'parameters' => [],
            'test' => [
                'factoryService' => [
                    'path' => 'desired/path',
                    'b' => 6,
                ],
            ],
        ]);

        $container->registerService(FormFactory::class, function () {
            return new FormFactory();
        });
        $container->registerServiceFactory(ComplexServiceDummy::class, [ComplexServiceDummyFactory::class, 'create'],
            'test.factoryService');

        /** @var ComplexServiceDummy $service */
        $service = $container->get(ComplexServiceDummy::class);
        Assert::assertTrue($service instanceof ComplexServiceDummy, "Complex service dummy should be of correct class");
        Assert::assertTrue($service->formFactory instanceof FormFactory,
            "Complex service dummy form factory should be of correct class");
        Assert::assertEquals($service->path, 'desired/path');
        Assert::assertEquals($service->b, 6);
    }
}
