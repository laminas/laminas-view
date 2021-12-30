<?php

namespace LaminasTest\View\Helper\Navigation;

use DOMDocument;
use Laminas\View;
use Laminas\View\Helper\Navigation\Sitemap;
use Throwable; // phpcs:ignore

use function count;
use function date_default_timezone_get;
use function date_default_timezone_set;
use function sprintf;
use function trim;

/**
 * Tests Laminas\View\Helper\Navigation\Sitemap
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 * @psalm-suppress MissingConstructor
 */
class SitemapTest extends AbstractTest
{
    // @codingStandardsIgnoreStart
    protected $_oldServer = [];

    /**
     * View helper
     *
     * @var Sitemap
     */
    protected $_helper;

    /**
     * Stores the original set timezone
     * @var string
     */
    private $_originaltimezone;
    // @codingStandardsIgnoreEnd

    protected function setUp(): void
    {
        $this->_helper = new Sitemap();
        $this->_originaltimezone = date_default_timezone_get();
        date_default_timezone_set('Europe/Berlin');

        if (isset($_SERVER['SERVER_NAME'])) {
            $this->_oldServer['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
        }

        if (isset($_SERVER['SERVER_PORT'])) {
            $this->_oldServer['SERVER_PORT'] = $_SERVER['SERVER_PORT'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->_oldServer['REQUEST_URI'] = $_SERVER['REQUEST_URI'];
        }

        $_SERVER['SERVER_NAME'] = 'localhost';
        $_SERVER['SERVER_PORT'] = 80;
        $_SERVER['REQUEST_URI'] = '/';

        parent::setUp();

        $this->_helper->setFormatOutput(true);
        $this->_helper->getView()->plugin('basepath')->setBasePath('');
    }

    protected function tearDown(): void
    {
        foreach ($this->_oldServer as $key => $value) {
            $_SERVER[$key] = $value;
        }
        date_default_timezone_set($this->_originaltimezone);
    }

    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->_helper->__invoke();
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->_nav1, $returned->getContainer());
    }

    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->_helper->__invoke($this->_nav2);
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->_nav2, $returned->getContainer());
    }

    public function testNullingOutNavigation(): void
    {
        $this->_helper->setContainer();
        $this->assertEquals(0, count($this->_helper->getContainer()));
    }

    public function testRenderSuppliedContainerWithoutInterfering(): void
    {
        $rendered1 = trim($this->_getExpected('sitemap/default1.xml'));
        $rendered2 = trim($this->_getExpected('sitemap/default2.xml'));

        $expected = [
            'registered'       => $rendered1,
            'supplied'         => $rendered2,
            'registered_again' => $rendered1,
        ];
        $actual = [
            'registered'       => $this->_helper->render(),
            'supplied'         => $this->_helper->render($this->_nav2),
            'registered_again' => $this->_helper->render(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testUseAclRoles(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole($acl['role']);

        $expected = $this->_getExpected('sitemap/acl.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testUseAclButNoRole(): void
    {
        $acl = $this->_getAcl();
        $this->_helper->setAcl($acl['acl']);
        $this->_helper->setRole(null);

        $expected = $this->_getExpected('sitemap/acl2.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testSettingMaxDepth(): void
    {
        $this->_helper->setMaxDepth(0);

        $expected = $this->_getExpected('sitemap/depth1.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testSettingMinDepth(): void
    {
        $this->_helper->setMinDepth(1);

        $expected = $this->_getExpected('sitemap/depth2.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testSettingBothDepths(): void
    {
        $this->_helper->setMinDepth(1)->setMaxDepth(2);

        $expected = $this->_getExpected('sitemap/depth3.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testDropXmlDeclaration(): void
    {
        $this->_helper->setUseXmlDeclaration(false);

        $expected = $this->_getExpected('sitemap/nodecl.xml');
        $this->assertEquals(trim($expected), $this->_helper->render($this->_nav2));
    }

    /**
     * @return never
     */
    public function testThrowExceptionOnInvalidLoc()
    {
        $this->markTestIncomplete('Laminas\URI changes affect this test');
        $nav = clone $this->_nav2;
        $nav->addPage(['label' => 'Invalid', 'uri' => 'http://w.']);

        try {
            $this->_helper->render($nav);
        } catch (View\Exception\ExceptionInterface $e) {
            $expected = sprintf(
                'Encountered an invalid URL for Sitemap XML: "%s"',
                'http://w.'
            );
            $actual = $e->getMessage();
            $this->assertEquals($expected, $actual);
            return;
        }

        $this->fail('A Laminas\View\Exception\InvalidArgumentException was not thrown on invalid <loc />');
    }

    public function testDisablingValidators(): void
    {
        $nav = clone $this->_nav2;
        $nav->addPage(['label' => 'Invalid', 'uri' => 'http://w.']);
        $this->_helper->setUseSitemapValidators(false);

        $expected = $this->_getExpected('sitemap/invalid.xml');

        // using DOMDocument::saveXML() to prevent differences in libxml from invalidating test
        $expectedDom = new DOMDocument();
        $receivedDom = new DOMDocument();
        $expectedDom->loadXML($expected);
        $receivedDom->loadXML($this->_helper->render($nav));
        $this->assertEquals($expectedDom->saveXML(), $receivedDom->saveXML());
    }

    /** @return array<string, array{0:string, 1: class-string<Throwable>, 2:string}> */
    public function invalidServerUrlDataProvider(): array
    {
        return [
            'muppets' => [
                'muppets',
                View\Exception\InvalidArgumentException::class,
                'Invalid server URL: "muppets"',
            ],
        ];
    }

    /**
     * @param class-string<Throwable> $expectedType
     * @dataProvider invalidServerUrlDataProvider
     */
    public function testSetServerUrlRequiresValidUri(
        string $invalidServerUrl,
        string $expectedType,
        string $expectedMessage
    ): void {
        $this->expectException($expectedType);
        $this->expectExceptionMessage($expectedMessage);
        $this->_helper->setServerUrl($invalidServerUrl);
    }

    public function testSetServerUrlWithSchemeAndHost(): void
    {
        $this->_helper->setServerUrl('http://sub.example.org');

        $expected = $this->_getExpected('sitemap/serverurl1.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testSetServerUrlWithSchemeAndPortAndHostAndPath(): void
    {
        $this->_helper->setServerUrl('http://sub.example.org:8080/foo/');

        $expected = $this->_getExpected('sitemap/serverurl2.xml');
        $this->assertEquals(trim($expected), $this->_helper->render());
    }

    public function testGetUserSchemaValidation(): void
    {
        $this->_helper->setUseSchemaValidation(true);
        $this->assertTrue($this->_helper->getUseSchemaValidation());
        $this->_helper->setUseSchemaValidation(false);
        $this->assertFalse($this->_helper->getUseSchemaValidation());
    }

    /**
     * @return never
     */
    public function testUseSchemaValidation()
    {
        $this->markTestSkipped('Skipped because it fetches XSD from web');
        return;
        $nav = clone $this->_nav2;
        $this->_helper->setUseSitemapValidators(false);
        $this->_helper->setUseSchemaValidation(true);
        $nav->addPage(['label' => 'Invalid', 'uri' => 'http://w.']);

        try {
            $this->_helper->render($nav);
        } catch (View\Exception\ExceptionInterface $e) {
            $expected = sprintf(
                'Sitemap is invalid according to XML Schema at "%s"',
                Sitemap::SITEMAP_XSD
            );
            $actual = $e->getMessage();
            $this->assertEquals($expected, $actual);
            return;
        }

        $this->fail('A Laminas\View\Exception\InvalidArgumentException was not thrown when using Schema validation');
    }
}
