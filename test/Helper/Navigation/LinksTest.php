<?php

declare(strict_types=1);

namespace LaminasTest\View\Helper\Navigation;

use ArrayObject;
use Laminas\Navigation\Page\AbstractPage;
use Laminas\Navigation\Page\Uri as UriPage;
use Laminas\Permissions\Acl;
use Laminas\Permissions\Acl\Resource;
use Laminas\Permissions\Acl\Role;
use Laminas\View;
use Laminas\View\Helper\Doctype;
use Laminas\View\Helper\Navigation;
use RecursiveIteratorIterator;

use function assert;
use function count;
use function get_class;
use function gettype;
use function is_array;
use function str_replace;

use const PHP_EOL;

/**
 * Tests Laminas\View\Helper\Navigation\Links
 *
 * @group      Laminas_View
 * @group      Laminas_View_Helper
 * @psalm-suppress MissingConstructor
 */
class LinksTest extends AbstractTest
{
    /**
     * View helper
     *
     * @var Navigation\Links
     */
    protected $_helper; // phpcs:ignore
    private Doctype $doctypeHelper;
    private string $oldDoctype;

    protected function setUp(): void
    {
        $this->_helper = new Navigation\Links();
        parent::setUp();

        // doctype fix (someone forgot to clean up after their unit tests)
        $helper = $this->_helper->getView()->plugin('doctype');
        assert($helper instanceof Doctype);
        $this->doctypeHelper = $helper;
        $this->oldDoctype    = $helper->getDoctype();
        $this->doctypeHelper->setDoctype(
            Doctype::HTML4_LOOSE
        );

        // disable all active pages
        foreach ($this->_helper->findAllByActive(true) as $page) {
            $page->active = false;
        }
    }

    public function testCanRenderFromServiceAlias(): void
    {
        $this->_helper->setServiceLocator($this->serviceManager);

        $returned = $this->_helper->render('Navigation');
        $this->assertEquals($returned, $this->getExpectedFileContents('links/default.html'));
    }

    public function testHelperEntryPointWithoutAnyParams(): void
    {
        $returned = $this->_helper->__invoke();
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav1, $returned->getContainer());
    }

    public function testHelperEntryPointWithContainerParam(): void
    {
        $returned = $this->_helper->__invoke($this->nav2);
        $this->assertEquals($this->_helper, $returned);
        $this->assertEquals($this->nav2, $returned->getContainer());
    }

    public function testDoNotRenderIfNoPageIsActive(): void
    {
        $this->assertEquals('', $this->_helper->render());
    }

    public function testDetectRelationFromStringPropertyOfActivePage(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');
        $active->addRel('example', 'http://www.example.com/');
        $found = $this->_helper->findRelation($active, 'rel', 'example');

        $expected = [
            'type'  => UriPage::class,
            'href'  => 'http://www.example.com/',
            'label' => null,
        ];

        $actual = [
            'type'  => $found !== null ? get_class($found) : self::class,
            'href'  => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testDetectRelationFromPageInstancePropertyOfActivePage(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');
        $active->addRel('example', AbstractPage::factory([
            'uri'   => 'http://www.example.com/',
            'label' => 'An example page',
        ]));
        $found = $this->_helper->findRelExample($active);

        $expected = [
            'type'  => UriPage::class,
            'href'  => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type'  => get_class($found),
            'href'  => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testDetectRelationFromArrayPropertyOfActivePage(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');
        $active->addRel('example', [
            'uri'   => 'http://www.example.com/',
            'label' => 'An example page',
        ]);
        $found = $this->_helper->findRelExample($active);

        $expected = [
            'type'  => UriPage::class,
            'href'  => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type'  => get_class($found),
            'href'  => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testDetectRelationFromArrayObjectInstancePropertyOfActivePage(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');
        $active->addRel('example', new ArrayObject([
            'uri'   => 'http://www.example.com/',
            'label' => 'An example page',
        ]));
        $found = $this->_helper->findRelExample($active);

        $expected = [
            'type'  => UriPage::class,
            'href'  => 'http://www.example.com/',
            'label' => 'An example page',
        ];

        $actual = [
            'type'  => get_class($found),
            'href'  => $found->getHref(),
            'label' => $found->getLabel(),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function testDetectMultipleRelationsFromArrayPropertyOfActivePage(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');

        $active->addRel('alternate', [
            [
                'label' => 'foo',
                'uri'   => 'bar',
            ],
            [
                'label' => 'baz',
                'uri'   => 'bat',
            ],
        ]);

        $found = $this->_helper->findRelAlternate($active);

        $expected = ['type' => 'array', 'count' => 2];
        $actual   = ['type' => gettype($found), 'count' => count($found)];
        $this->assertEquals($expected, $actual);
    }

    public function testDetectMultipleRelationsFromArrayObjectPropertyOfActivePage(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');

        $active->addRel('alternate', new ArrayObject([
            [
                'label' => 'foo',
                'uri'   => 'bar',
            ],
            [
                'label' => 'baz',
                'uri'   => 'bat',
            ],
        ]));

        $found = $this->_helper->findRelAlternate($active);

        $expected = ['type' => 'array', 'count' => 2];
        $actual   = ['type' => gettype($found), 'count' => count($found)];
        $this->assertEquals($expected, $actual);
    }

    public function testExtractingRelationsFromPageProperties(): void
    {
        $types = [
            'alternate',
            'stylesheet',
            'start',
            'next',
            'prev',
            'contents',
            'index',
            'glossary',
            'copyright',
            'chapter',
            'section',
            'subsection',
            'appendix',
            'help',
            'bookmark',
        ];

        $samplePage = AbstractPage::factory([
            'label' => 'An example page',
            'uri'   => 'http://www.example.com/',
        ]);

        $active   = $this->_helper->findOneByLabel('Page 2');
        $expected = [];
        $actual   = [];

        foreach ($types as $type) {
            $active->addRel($type, $samplePage);
            $found = $this->_helper->findRelation($active, 'rel', $type);

            $expected[$type] = $samplePage->getLabel();
            $actual[$type]   = $found->getLabel();

            $active->removeRel($type);
        }

        $this->assertEquals($expected, $actual);
    }

    public function testFindStartPageByTraversal(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2.1');
        $expected = 'Home';
        $actual   = $this->_helper->findRelStart($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testDoNotFindStartWhenGivenPageIsTheFirstPage(): void
    {
        $active = $this->_helper->findOneByLabel('Home');
        $actual = $this->_helper->findRelStart($active);
        $this->assertNull($actual, 'Should not find any start page');
    }

    public function testFindNextPageByTraversalShouldFindChildPage(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2');
        $expected = 'Page 2.1';
        $actual   = $this->_helper->findRelNext($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testFindNextPageByTraversalShouldFindSiblingPage(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2.1');
        $expected = 'Page 2.2';
        $actual   = $this->_helper->findRelNext($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testFindNextPageByTraversalShouldWrap(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2.2.2');
        $expected = 'Page 2.3';
        $actual   = $this->_helper->findRelNext($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testFindPrevPageByTraversalShouldFindParentPage(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2.1');
        $expected = 'Page 2';
        $actual   = $this->_helper->findRelPrev($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testFindPrevPageByTraversalShouldFindSiblingPage(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2.2');
        $expected = 'Page 2.1';
        $actual   = $this->_helper->findRelPrev($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testFindPrevPageByTraversalShouldWrap(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2.3');
        $expected = 'Page 2.2.2';
        $actual   = $this->_helper->findRelPrev($active)->getLabel();
        $this->assertEquals($expected, $actual);
    }

    public function testShouldFindChaptersFromFirstLevelOfPagesInContainer(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.3');
        $found  = $this->_helper->findRelChapter($active);

        $expected = ['Page 1', 'Page 2', 'Page 3', 'Zym'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }

        $this->assertEquals($expected, $actual);
    }

    public function testFindingChaptersShouldExcludeSelfIfChapter(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');
        $found  = $this->_helper->findRelChapter($active);

        $expected = ['Page 1', 'Page 3', 'Zym'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }

        $this->assertEquals($expected, $actual);
    }

    public function testFindSectionsWhenActiveChapterPage(): void
    {
        $active   = $this->_helper->findOneByLabel('Page 2');
        $found    = $this->_helper->findRelSection($active);
        $expected = ['Page 2.1', 'Page 2.2', 'Page 2.3'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }
        $this->assertEquals($expected, $actual);
    }

    public function testDoNotFindSectionsWhenActivePageIsASection(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.2');
        $found  = $this->_helper->findRelSection($active);
        $this->assertNull($found);
    }

    public function testDoNotFindSectionsWhenActivePageIsASubsection(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.2.1');
        $found  = $this->_helper->findRelation($active, 'rel', 'section');
        $this->assertNull($found);
    }

    public function testFindSubsectionWhenActivePageIsSection(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.2');
        $found  = $this->_helper->findRelSubsection($active);

        $expected = ['Page 2.2.1', 'Page 2.2.2'];
        $actual   = [];
        foreach ($found as $page) {
            $actual[] = $page->getLabel();
        }
        $this->assertEquals($expected, $actual);
    }

    public function testDoNotFindSubsectionsWhenActivePageIsASubSubsection(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.2.1');
        $found  = $this->_helper->findRelSubsection($active);
        $this->assertNull($found);
    }

    public function testDoNotFindSubsectionsWhenActivePageIsAChapter(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2');
        $found  = $this->_helper->findRelSubsection($active);
        $this->assertNull($found);
    }

    public function testFindRevSectionWhenPageIsSection(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.2');
        $found  = $this->_helper->findRevSection($active);
        $this->assertEquals('Page 2', $found->getLabel());
    }

    public function testFindRevSubsectionWhenPageIsSubsection(): void
    {
        $active = $this->_helper->findOneByLabel('Page 2.2.1');
        $found  = $this->_helper->findRevSubsection($active);
        $this->assertEquals('Page 2.2', $found->getLabel());
    }

    public function testAclFiltersAwayPagesFromPageProperty(): void
    {
        $acl = new Acl\Acl();
        $acl->addRole(new Role\GenericRole('member'));
        $acl->addRole(new Role\GenericRole('admin'));
        $acl->addResource(new Resource\GenericResource('protected'));
        $acl->allow('admin', 'protected');
        $this->_helper->setAcl($acl);
        $this->_helper->setRole($acl->getRole('member'));

        $samplePage = AbstractPage::factory([
            'label'    => 'An example page',
            'uri'      => 'http://www.example.com/',
            'resource' => 'protected',
        ]);

        $active   = $this->_helper->findOneByLabel('Home');
        $expected = [
            'alternate'  => false,
            'stylesheet' => false,
            'start'      => false,
            'next'       => 'Page 1',
            'prev'       => false,
            'contents'   => false,
            'index'      => false,
            'glossary'   => false,
            'copyright'  => false,
            'chapter'    => 'array(4)',
            'section'    => false,
            'subsection' => false,
            'appendix'   => false,
            'help'       => false,
            'bookmark'   => false,
        ];
        $actual   = [];

        foreach ($expected as $type => $discard) {
            $active->addRel($type, $samplePage);

            $found = $this->_helper->findRelation($active, 'rel', $type);
            if (null === $found) {
                $actual[$type] = false;
            } elseif (is_array($found)) {
                $actual[$type] = 'array(' . count($found) . ')';
            } else {
                $actual[$type] = $found->getLabel();
            }
        }

        $this->assertEquals($expected, $actual);
    }

    public function testAclFiltersAwayPagesFromContainerSearch(): void
    {
        $acl = new Acl\Acl();
        $acl->addRole(new Role\GenericRole('member'));
        $acl->addRole(new Role\GenericRole('admin'));
        $acl->addResource(new Resource\GenericResource('protected'));
        $acl->allow('admin', 'protected');
        $this->_helper->setAcl($acl);
        $this->_helper->setRole($acl->getRole('member'));

        $this->_helper->getContainer();
        $container = $this->_helper->getContainer();
        $iterator  = new RecursiveIteratorIterator(
            $container,
            RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $page) {
            $page->resource = 'protected';
        }
        $this->_helper->setContainer($container);

        $this->_helper->findOneByLabel('Home');
        $search = [
            'start'      => 'Page 1',
            'next'       => 'Page 1',
            'prev'       => 'Page 1.1',
            'chapter'    => 'Home',
            'section'    => 'Page 1',
            'subsection' => 'Page 2.2',
        ];

        $expected = [];
        $actual   = [];

        foreach ($search as $type => $active) {
            $expected[$type] = false;

            $active = $this->_helper->findOneByLabel($active);
            $found  = $this->_helper->findRelation($active, 'rel', $type);

            if (null === $found) {
                $actual[$type] = false;
            } elseif (is_array($found)) {
                $actual[$type] = 'array(' . count($found) . ')';
            } else {
                $actual[$type] = $found->getLabel();
            }
        }

        $this->assertEquals($expected, $actual);
    }

    public function testFindRelationMustSpecifyRelOrRev(): void
    {
        $active = $this->_helper->findOneByLabel('Home');
        try {
            $this->_helper->findRelation($active, 'foo', 'bar');
            $this->fail('An invalid value was given, but a '
                        . 'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->assertStringContainsString('Invalid argument: $rel', $e->getMessage());
        }
    }

    public function testRenderLinkMustSpecifyRelOrRev(): void
    {
        $active = $this->_helper->findOneByLabel('Home');
        try {
            $this->_helper->renderLink($active, 'foo', 'bar');
            $this->fail('An invalid value was given, but a '
                        . 'Laminas\View\Exception\InvalidArgumentException was not thrown');
        } catch (View\Exception\ExceptionInterface $e) {
            $this->assertStringContainsString('Invalid relation attribute', $e->getMessage());
        }
    }

    public function testFindAllRelations(): void
    {
        $expectedRelations = [
            'alternate'  => ['Forced page'],
            'stylesheet' => ['Forced page'],
            'start'      => ['Forced page'],
            'next'       => ['Forced page'],
            'prev'       => ['Forced page'],
            'contents'   => ['Forced page'],
            'index'      => ['Forced page'],
            'glossary'   => ['Forced page'],
            'copyright'  => ['Forced page'],
            'chapter'    => ['Forced page'],
            'section'    => ['Forced page'],
            'subsection' => ['Forced page'],
            'appendix'   => ['Forced page'],
            'help'       => ['Forced page'],
            'bookmark'   => ['Forced page'],
            'canonical'  => ['Forced page'],
            'home'       => ['Forced page'],
        ];

        // build expected result
        $expected = [
            'rel' => $expectedRelations,
            'rev' => $expectedRelations,
        ];

        // find active page and create page to use for relations
        $active         = $this->_helper->findOneByLabel('Page 1');
        $forcedRelation = new UriPage([
            'label' => 'Forced page',
            'uri'   => '#',
        ]);

        // add relations to active page
        foreach ($expectedRelations as $type => $discard) {
            $active->addRel($type, $forcedRelation);
            $active->addRev($type, $forcedRelation);
        }

        // build actual result
        $actual = $this->_helper->findAllRelations($active);
        foreach ($actual as $attrib => $relations) {
            foreach ($relations as $type => $pages) {
                foreach ($pages as $key => $page) {
                    $actual[$attrib][$type][$key] = $page->getLabel();
                }
            }
        }

        $this->assertEquals($expected, $actual);
    }

    // @codingStandardsIgnoreStart
    /**
     * @return string[]
     *
     * @psalm-return array{1: 'alternate', 2: 'stylesheet', 4: 'start', 8: 'next', 16: 'prev', 32: 'contents', 64: 'index', 128: 'glossary', 512: 'chapter', 1024: 'section', 2048: 'subsection', 4096: 'appendix', 8192: 'help', 16384: 'bookmark', 32768: 'canonical'}
     */
    private function _getFlags(): array
    {
        // @codingStandardsIgnoreEnd
        return [
            Navigation\Links::RENDER_ALTERNATE  => 'alternate',
            Navigation\Links::RENDER_STYLESHEET => 'stylesheet',
            Navigation\Links::RENDER_START      => 'start',
            Navigation\Links::RENDER_NEXT       => 'next',
            Navigation\Links::RENDER_PREV       => 'prev',
            Navigation\Links::RENDER_CONTENTS   => 'contents',
            Navigation\Links::RENDER_INDEX      => 'index',
            Navigation\Links::RENDER_GLOSSARY   => 'glossary',
            Navigation\Links::RENDER_CHAPTER    => 'chapter',
            Navigation\Links::RENDER_SECTION    => 'section',
            Navigation\Links::RENDER_SUBSECTION => 'subsection',
            Navigation\Links::RENDER_APPENDIX   => 'appendix',
            Navigation\Links::RENDER_HELP       => 'help',
            Navigation\Links::RENDER_BOOKMARK   => 'bookmark',
            Navigation\Links::RENDER_CUSTOM     => 'canonical',
        ];
    }

    public function testSingleRenderFlags(): void
    {
        $active         = $this->_helper->findOneByLabel('Home');
        $active->active = true;

        $expected = [];
        $actual   = [];

        // build expected and actual result
        foreach ($this->_getFlags() as $newFlag => $type) {
            // add forced relation
            $active->addRel($type, 'http://www.example.com/');
            $active->addRev($type, 'http://www.example.com/');

            $this->_helper->setRenderFlag($newFlag);
            $expectedOutput = '<link '
                              . 'rel="' . $type . '" '
                              . 'href="http&#x3A;&#x2F;&#x2F;www.example.com&#x2F;">' . PHP_EOL
                            . '<link '
                              . 'rev="' . $type . '" '
                              . 'href="http&#x3A;&#x2F;&#x2F;www.example.com&#x2F;">';
            $actualOutput   = $this->_helper->render();

            $expected[$type] = $expectedOutput;
            $actual[$type]   = $actualOutput;

            // remove forced relation
            $active->removeRel($type);
            $active->removeRev($type);
        }

        $this->assertEquals($expected, $actual);
    }

    public function testRenderFlagBitwiseOr(): void
    {
        $newFlag = Navigation\Links::RENDER_NEXT |
                   Navigation\Links::RENDER_PREV;
        $this->_helper->setRenderFlag($newFlag);
        $active         = $this->_helper->findOneByLabel('Page 1.1');
        $active->active = true;

        // test data
        $expected = '<link rel="next" href="page2" title="Page&#x20;2">'
                  . PHP_EOL
                  . '<link rel="prev" href="page1" title="Page&#x20;1">';
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testIndenting(): void
    {
        $active  = $this->_helper->findOneByLabel('Page 1.1');
        $newFlag = Navigation\Links::RENDER_NEXT |
                   Navigation\Links::RENDER_PREV;
        $this->_helper->setRenderFlag($newFlag);
        $this->_helper->setIndent('  ');
        $active->active = true;

        // build expected and actual result
        $expected = '  <link rel="next" href="page2" title="Page&#x20;2">'
                  . PHP_EOL
                  . '  <link rel="prev" href="page1" title="Page&#x20;1">';
        $actual   = $this->_helper->render();

        $this->assertEquals($expected, $actual);
    }

    public function testSetMaxDepth(): void
    {
        $this->_helper->setMaxDepth(1);
        $this->_helper->findOneByLabel('Page 2.3.3')->setActive(); // level 2
        $flag = Navigation\Links::RENDER_NEXT;

        $expected = '<link rel="next" href="page2&#x2F;page2_3&#x2F;page2_3_1" title="Page&#x20;2.3.1">';
        $actual   = $this->_helper->setRenderFlag($flag)->render();

        $this->assertEquals($expected, $actual);
    }

    public function testSetMinDepth(): void
    {
        $this->_helper->setMinDepth(2);
        $this->_helper->findOneByLabel('Page 2.3')->setActive(); // level 1
        $flag = Navigation\Links::RENDER_NEXT;

        $expected = '';
        $actual   = $this->_helper->setRenderFlag($flag)->render();

        $this->assertEquals($expected, $actual);
    }

    /** @inheritDoc */
    protected function getExpectedFileContents(string $filename): string
    {
        return str_replace("\n", PHP_EOL, parent::getExpectedFileContents($filename));
    }
}
