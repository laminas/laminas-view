<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper;

use Laminas\View\Exception;
use Laminas\View\Helper\Gravatar;
use Laminas\View\Renderer\PhpRenderer as View;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

use function method_exists;
use function strtoupper;
use function urlencode;

/** @psalm-suppress DeprecatedClass */
class GravatarTest extends TestCase
{
    /** @var Gravatar */
    protected $helper;

    /** @var View */
    protected $view;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp(): void
    {
        $this->helper = new Gravatar();
        $this->view   = new View();
        $this->view->doctype()->setDoctype(strtoupper("XHTML1_STRICT"));
        $this->helper->setView($this->view);

        if (isset($_SERVER['HTTPS'])) {
            unset($_SERVER['HTTPS']);
        }
    }

    /**
     * Test default options.
     */
    public function testGravatarXhtmlDoctype(): void
    {
        $this->assertMatchesRegularExpression(
            '/\/>$/',
            $this->helper->__invoke('example@example.com')->__toString()
        );
    }

    /**
     * Test if doctype is HTML
     */
    public function testGravatarHtmlDoctype(): void
    {
        $object = new Gravatar();
        $view   = new View();
        $view->doctype()->setDoctype(strtoupper("HTML5"));
        $object->setView($view);

        $this->assertMatchesRegularExpression(
            '/[^\/]>$/',
            $this->helper->__invoke('example@example.com')->__toString()
        );
    }

    /**
     * Test get set methods
     */
    public function testGetAndSetMethods(): void
    {
        $attributes = ['class' => 'gravatar', 'title' => 'avatar', 'id' => 'gravatar-1'];
        $this->helper->setDefaultImg('monsterid')
                     ->setImgSize(150)
                     ->setSecure(true)
                     ->setEmail("example@example.com")
                     ->setAttributes($attributes)
                     ->setRating('pg');
        $this->assertEquals("monsterid", $this->helper->getDefaultImg());
        $this->assertEquals("pg", $this->helper->getRating());
        $this->assertEquals("example@example.com", $this->helper->getEmail());
        $this->assertEquals($attributes, $this->helper->getAttributes());
        $this->assertEquals(150, $this->helper->getImgSize());
        $this->assertTrue($this->helper->getSecure());
    }

    public function tesSetDefaultImg(): void
    {
        $this->helper->gravatar("example@example.com");

        $img = [
            "wavatar",
            "http://www.example.com/images/avatar/example.png",
            Gravatar::DEFAULT_MONSTERID,
        ];

        foreach ($img as $value) {
            $this->helper->setDefaultImg($value);
            $this->assertEquals(urlencode($value), $this->helper->getDefaultImg());
        }
    }

    public function testSetImgSize(): void
    {
        $imgSizesRight = [1, 500, "600"];
        foreach ($imgSizesRight as $value) {
            $this->helper->setImgSize($value);
            $this->assertIsInt($this->helper->getImgSize());
        }
    }

    public function testInvalidRatingParametr(): void
    {
        $ratingsWrong = ['a', 'cs', 456];
        $this->expectException(Exception\ExceptionInterface::class);
        foreach ($ratingsWrong as $value) {
            $this->helper->setRating($value);
        }
    }

    public function testSetRating(): void
    {
        $ratingsRight = ['g', 'pg', 'r', 'x', Gravatar::RATING_R];
        foreach ($ratingsRight as $value) {
            $this->helper->setRating($value);
            $this->assertEquals($value, $this->helper->getRating());
        }
    }

    public function testSetSecure(): void
    {
        $values = ["true", "false", "text", $this->view, 100, true, "", null, 0, false];
        foreach ($values as $value) {
            $this->helper->setSecure($value);
            $this->assertIsBool($this->helper->getSecure());
        }
    }

    /**
     * Test SSL location
     */
    public function testHttpsSource(): void
    {
        $this->assertMatchesRegularExpression(
            '#src="https\&\#x3A\;\&\#x2F\;\&\#x2F\;secure.gravatar.com\&\#x2F\;avatar\&\#x2F\;[a-z0-9]{32}.+"#',
            $this->helper->__invoke("example@example.com", ['secure' => true])->__toString()
        );
    }

    /**
     * Test HTML attributes
     */
    public function testImgAttributes(): void
    {
        $this->assertMatchesRegularExpression(
            '/class="gravatar" title="Gravatar"/',
            $this->helper->__invoke(
                "example@example.com",
                [],
                ['class' => 'gravatar', 'title' => 'Gravatar']
            )->__toString()
        );
    }

    /**
     * Test gravatar's options (rating, size, default image and secure)
     */
    public function testGravatarOptions(): void
    {
        $this->assertMatchesRegularExpression(
            // @codingStandardsIgnoreStart
            '#src="http\&\#x3A\;\&\#x2F\;\&\#x2F\;www.gravatar.com\&\#x2F\;avatar\&\#x2F\;[a-z0-9]{32}&\#x3F;s&\#x3D;125&amp;d&\#x3D;wavatar&amp;r&\#x3D;pg"#',
            $this->helper->__invoke("example@example.com", ['rating' => 'pg', 'imgSize' => 125, 'defaultImg' => 'wavatar', 'secure' => false])->__toString()
            // @codingStandardsIgnoreEnd
        );
    }

    public function testPassingAnMd5HashSkipsMd5Hashing(): void
    {
        $this->assertStringNotContainsString(
            'test@test.com',
            $this->helper->__invoke('test@test.com')->__toString()
        );
        $this->assertStringContainsString(
            'b642b4217b34b1e8d3bd915fc65c4452',
            $this->helper->__invoke('b642b4217b34b1e8d3bd915fc65c4452')->__toString()
        );
    }

    /**
     * Test auto detect location.
     * If request was made through the HTTPS protocol use secure location.
     */
    public function testAutoDetectLocation(): void
    {
        $values = ["on", "", 1, true];

        foreach ($values as $value) {
            $_SERVER['HTTPS'] = $value;
            $this->assertMatchesRegularExpression(
                '#src="https\&\#x3A\;\&\#x2F\;\&\#x2F\;secure.gravatar.com\&\#x2F\;avatar\&\#x2F\;[a-z0-9]{32}.+"#',
                $this->helper->__invoke("example@example.com")->__toString()
            );
        }
    }

    /**
     * @link http://php.net/manual/en/reserved.variables.server.php Section "HTTPS"
     */
    public function testAutoDetectLocationOnIis(): void
    {
        $_SERVER['HTTPS'] = "off";

        $this->assertMatchesRegularExpression(
            '/src="http\&\#x3A\;\&\#x2F\;\&\#x2F\;www.gravatar.com\&\#x2F\;avatar\&\#x2F\;[a-z0-9]{32}.+"/',
            $this->helper->__invoke("example@example.com")->__toString()
        );
    }

    public function testSetAttributesWithSrcKey(): void
    {
        $email = 'example@example.com';
        $this->helper->setEmail($email);
        $this->helper->setAttributes([
            'class' => 'gravatar',
            'src'   => 'http://example.com',
            'id'    => 'gravatarID',
        ]);

        $this->assertMatchesRegularExpression(
            '#src="http\&\#x3A\;\&\#x2F\;\&\#x2F\;www.gravatar.com\&\#x2F\;avatar\&\#x2F\;[a-z0-9]{32}.+"#',
            $this->helper->getImgTag()
        );
    }

    public function testForgottenEmailParameter(): void
    {
        $this->assertMatchesRegularExpression(
            '#(src="http\&\#x3A\;\&\#x2F\;\&\#x2F\;www.gravatar.com\&\#x2F\;avatar\&\#x2F\;[a-z0-9]{32}.+")#',
            $this->helper->getImgTag()
        );
    }

    public function testReturnImgTag(): void
    {
        $this->assertMatchesRegularExpression(
            "/^<img\s.+/",
            $this->helper->__invoke("example@example.com")->__toString()
        );
    }

    public function testInvokeReturnsSelf(): void
    {
        self::assertSame($this->helper, ($this->helper)());
    }

    public function testInvalidKeyWithoutMatchingSetterPassedToSetOptionsMethodIsIgnored(): void
    {
        self::assertFalse(method_exists($this->helper, 'setUnknown'));
        $options = [
            'unknown' => ['val' => 1],
        ];
        $this->helper->__invoke()->setOptions($options);
    }

    public function testEmailIsProperlyNormalized(): void
    {
        $this->assertEquals(
            'example@example.com',
            $this->helper->__invoke('Example@Example.com ')->getEmail()
        );
    }

    public function testSetAttribsIsDeprecated(): void
    {
        $this->expectDeprecation();

        $this->helper->setAttribs([]);
    }

    public function testSetAttribsDocCommentHasDeprecated(): void
    {
        $method  = new ReflectionMethod($this->helper, 'setAttribs');
        $comment = $method->getDocComment();

        $this->assertStringContainsString('@deprecated', $comment);
    }

    public function testGetAttribsIsDeprecated(): void
    {
        $this->expectDeprecation();

        $this->helper->getAttribs();
    }

    public function testGetAttribsDocCommentHasDeprecated(): void
    {
        $method  = new ReflectionMethod($this->helper, 'getAttribs');
        $comment = $method->getDocComment();

        $this->assertStringContainsString('@deprecated', $comment);
    }
}
