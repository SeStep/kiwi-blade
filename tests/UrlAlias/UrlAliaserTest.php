<?php


namespace KiwiBladeTests\UrlAlias;


use KiwiBlade\UrlAlias\AliasException;
use KiwiBlade\UrlAlias\UrlAliaser;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class UrlAliaserTest extends TestCase
{
    /** @var UrlAliaser */
    private $urlAliaser;

    public function setUp()
    {
        $aliases = [
            'nova-registrace' => ['registrace', 'vytvorit']
        ];
        $controllers = [
            'registration' => [
                'name' => 'registrace',
                'actions' => [
                    'new' => 'vytvorit',
                    'finalize' => 'dokoncit',
                ],
            ]
        ];
        $this->urlAliaser = new UrlAliaser($aliases, $controllers);
    }

    public function testDecodeAliasExisting()
    {
        $expected = ['controller' => 'registrace', 'action' => 'vytvorit'];
        $result = $this->urlAliaser->decodeAlias('nova-registrace');

        Assert::assertEquals($expected, $result);
    }

    public function testDecodeAliasNonExisting()
    {
        Assert::assertNull($this->urlAliaser->decodeAlias('foo'));
    }

    public function testAliasMapContains()
    {
        Assert::assertTrue($this->urlAliaser->aliasMapContains('registrace', 'vytvorit'));
        Assert::assertFalse($this->urlAliaser->aliasMapContains('registrace', 'foo'));
    }

    public function testDecodePartsValid()
    {
        $expected = ['controller' => 'registration', 'action' => 'new'];
        $result = $this->urlAliaser->decodeParts('registrace', 'vytvorit');

        Assert::assertEquals($expected, $result);
    }

    public function testDecodePartsInvallidController()
    {
        $this->expectException(AliasException::class);

        $this->urlAliaser->decodeParts('foo', 'vytvorit');
    }

    public function testDecodePartsInvallidAction()
    {
        $this->expectException(AliasException::class);

        $this->urlAliaser->decodeParts('registrace', 'foo');
    }

    public function testEncodeWithAlias()
    {
        $expected = ['controller' => 'nova-registrace', 'action' => ''];
        $actual = $this->urlAliaser->encode('registration', 'new');

        Assert::assertEquals($expected, $actual);
    }

    public function testEncodeWithoutAlias()
    {
        $expected = ['controller' => 'registrace', 'action' => 'dokoncit'];
        $actual = $this->urlAliaser->encode('registration', 'finalize');

        Assert::assertEquals($expected, $actual);
    }

    public function testEncodeInvalid(){
        $this->expectException(AliasException::class);

        $this->urlAliaser->encode('foo', 'bar');
    }
}
