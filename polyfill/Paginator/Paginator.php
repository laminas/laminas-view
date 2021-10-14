<?php

namespace Laminas\Paginator;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Laminas\Cache\Storage\IteratorInterface as CacheIterator;
use Laminas\Cache\Storage\StorageInterface as CacheStorage;
use Laminas\Db\ResultSet\AbstractResultSet;
use Laminas\Filter\FilterInterface;
use Laminas\Paginator\Adapter\AdapterInterface;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Laminas\View;
use Laminas\View\Renderer\RendererInterface;
use ReturnTypeWillChange;
use stdClass;
use Throwable;
use Traversable;

use function ceil;
use function class_exists;
use function count;
use function get_class;
use function gettype;
use function is_array;
use function is_object;
use function is_string;
use function iterator_count;
use function json_encode;
use function max;
use function md5;
use function min;
use function sprintf;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trigger_error;

use const E_USER_WARNING;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * Test shim for PHP 8.1 compatibility
 *
 * This class replaces the laminas-paginator equivalent during development only, for
 * purposes of testing against PHP 8.1.
 *
 * @todo Remove when laminas-paginator has a release targeting PHP 8.1.
 */
class Paginator implements Countable, IteratorAggregate
{
    /**
     * The cache tag prefix used to namespace Paginator results in the cache
     */
    public const CACHE_TAG_PREFIX = 'Laminas_Paginator_';

    /**
     * Adapter plugin manager
     *
     * @var AdapterPluginManager
     */
    protected static $adapters;

    /**
     * Configuration file
     *
     * @var array|null
     */
    protected static $config;

    /**
     * Default scrolling style
     *
     * @var string
     */
    protected static $defaultScrollingStyle = 'Sliding';

    /**
     * Default item count per page
     *
     * @var int
     */
    protected static $defaultItemCountPerPage = 10;

    /**
     * Scrolling style plugin manager
     *
     * @var ScrollingStylePluginManager
     */
    protected static $scrollingStyles;

    /**
     * Cache object
     *
     * @var CacheStorage
     */
    protected static $cache;

    /**
     * Enable or disable the cache by Laminas\Paginator\Paginator instance
     *
     * @var bool
     */
    protected $cacheEnabled = true;

    /**
     * Adapter
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Number of items in the current page
     *
     * @var int
     */
    protected $currentItemCount;

    /**
     * Current page items
     *
     * @var Traversable
     */
    protected $currentItems;

    /**
     * Current page number (starting from 1)
     *
     * @var int
     */
    protected $currentPageNumber = 1;

    /**
     * Result filter
     *
     * @var FilterInterface
     */
    protected $filter;

    /**
     * Number of items per page
     *
     * @var int
     */
    protected $itemCountPerPage;

    /**
     * Number of pages
     *
     * @var int
     */
    protected $pageCount;

    /**
     * Number of local pages (i.e., the number of discrete page numbers
     * that will be displayed, including the current page number)
     *
     * @var int
     */
    protected $pageRange = 10;

    /**
     * Pages
     *
     * @var stdClass
     */
    protected $pages;

    /**
     * View instance used for self rendering
     *
     * @var RendererInterface
     */
    protected $view;

    /**
     * Set a global config
     *
     * @param array|Traversable $config
     * @throws Exception\InvalidArgumentException
     * @return void
     */
    public static function setGlobalConfig($config)
    {
        if ($config instanceof Traversable) {
            $config = ArrayUtils::iteratorToArray($config);
        }
        if (! is_array($config)) {
            throw new Exception\InvalidArgumentException(__METHOD__ . ' expects an array or Traversable');
        }

        static::$config = $config;

        if (
            isset($config['scrolling_style_plugins'])
            && null !== ($adapters = $config['scrolling_style_plugins'])
        ) {
            static::setScrollingStylePluginManager($adapters);
        }

        $scrollingStyle = $config['scrolling_style'] ?? null;

        if ($scrollingStyle !== null) {
            static::setDefaultScrollingStyle($scrollingStyle);
        }
    }

    /**
     * Returns the default scrolling style.
     *
     * @return  string
     */
    public static function getDefaultScrollingStyle()
    {
        return static::$defaultScrollingStyle;
    }

    /**
     * Get the default item count per page
     *
     * @return int
     */
    public static function getDefaultItemCountPerPage()
    {
        return static::$defaultItemCountPerPage;
    }

    /**
     * Set the default item count per page
     *
     * @param int $count
     * @return void
     */
    public static function setDefaultItemCountPerPage($count)
    {
        static::$defaultItemCountPerPage = (int) $count;
    }

    /**
     * Sets a cache object
     *
     * @return void
     */
    public static function setCache(CacheStorage $cache)
    {
        static::$cache = $cache;
    }

    /**
     * Sets the default scrolling style.
     *
     * @param string $scrollingStyle
     * @return void
     */
    public static function setDefaultScrollingStyle($scrollingStyle = 'Sliding')
    {
        static::$defaultScrollingStyle = $scrollingStyle;
    }

    /**
     * @param string|ScrollingStylePluginManager $scrollingAdapters
     * @return void
     */
    public static function setScrollingStylePluginManager($scrollingAdapters)
    {
        if (is_string($scrollingAdapters)) {
            if (! class_exists($scrollingAdapters)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unable to locate scrolling style plugin manager with class "%s"; class not found',
                    $scrollingAdapters
                ));
            }
            $scrollingAdapters = new $scrollingAdapters(new ServiceManager());
        }
        if (! $scrollingAdapters instanceof ScrollingStylePluginManager) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Pagination scrolling-style manager must extend ScrollingStylePluginManager; received "%s"',
                is_object($scrollingAdapters) ? get_class($scrollingAdapters) : gettype($scrollingAdapters)
            ));
        }
        static::$scrollingStyles = $scrollingAdapters;
    }

    /**
     * Returns the scrolling style manager.  If it doesn't exist it's
     * created.
     *
     * @return ScrollingStylePluginManager
     */
    public static function getScrollingStylePluginManager()
    {
        if (static::$scrollingStyles === null) {
            static::$scrollingStyles = new ScrollingStylePluginManager(new ServiceManager());
        }

        return static::$scrollingStyles;
    }

    /**
     * @param AdapterInterface|AdapterAggregateInterface $adapter
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($adapter)
    {
        if ($adapter instanceof AdapterInterface) {
            $this->adapter = $adapter;
        } elseif ($adapter instanceof AdapterAggregateInterface) {
            $this->adapter = $adapter->getPaginatorAdapter();
        } else {
            throw new Exception\InvalidArgumentException(
                'Laminas\Paginator only accepts instances of the type '
                . 'Laminas\Paginator\Adapter\AdapterInterface or Laminas\Paginator\AdapterAggregateInterface.'
            );
        }

        $config = static::$config;

        if (! empty($config)) {
            $setupMethods = ['ItemCountPerPage', 'PageRange'];

            foreach ($setupMethods as $setupMethod) {
                $key   = strtolower($setupMethod);
                $value = $config[$key] ?? null;

                if ($value !== null) {
                    $setupMethod = 'set' . $setupMethod;
                    $this->$setupMethod($value);
                }
            }
        }
    }

    /**
     * Serializes the object as a string.  Proxies to {@link render()}.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->render();
        } catch (Throwable $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return '';
    }

    /**
     * Enables/Disables the cache for this instance
     *
     * @param bool $enable
     * @return Paginator
     */
    public function setCacheEnabled($enable)
    {
        $this->cacheEnabled = (bool) $enable;
        return $this;
    }

    /**
     * Returns the number of pages.
     *
     * @return int
     */
    #[ReturnTypeWillChange]
    public function count()
    {
        if ($this->pageCount === null) {
            $this->pageCount = $this->_calculatePageCount();
        }

        return $this->pageCount;
    }

    /**
     * Returns the total number of items available.
     *
     * @return int
     */
    public function getTotalItemCount()
    {
        return count($this->getAdapter());
    }

    /**
     * Clear the page item cache.
     *
     * @param int $pageNumber
     * @return Paginator
     */
    public function clearPageItemCache($pageNumber = null)
    {
        if (! $this->cacheEnabled()) {
            return $this;
        }

        if (null === $pageNumber) {
            $prefixLength  = strlen(self::CACHE_TAG_PREFIX);
            $cacheIterator = static::$cache->getIterator();
            $cacheIterator->setMode(CacheIterator::CURRENT_AS_KEY);
            foreach ($cacheIterator as $key) {
                if (0 === strpos($key, self::CACHE_TAG_PREFIX)) {
                    static::$cache->removeItem($this->_getCacheId((int) substr($key, $prefixLength)));
                }
            }
        } else {
            $cleanId = $this->_getCacheId($pageNumber);
            static::$cache->removeItem($cleanId);
        }
        return $this;
    }

    /**
     * Returns the absolute item number for the specified item.
     *
     * @param  int $relativeItemNumber Relative item number
     * @param  int $pageNumber Page number
     * @return int
     */
    public function getAbsoluteItemNumber($relativeItemNumber, $pageNumber = null)
    {
        $relativeItemNumber = $this->normalizeItemNumber($relativeItemNumber);

        if ($pageNumber === null) {
            $pageNumber = $this->getCurrentPageNumber();
        }

        $pageNumber = $this->normalizePageNumber($pageNumber);

        return (($pageNumber - 1) * $this->getItemCountPerPage()) + $relativeItemNumber;
    }

    /**
     * Returns the adapter.
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Returns the number of items for the current page.
     *
     * @return int
     */
    public function getCurrentItemCount()
    {
        if ($this->currentItemCount === null) {
            $this->currentItemCount = $this->getItemCount($this->getCurrentItems());
        }

        return $this->currentItemCount;
    }

    /**
     * Returns the items for the current page.
     *
     * @return Traversable
     */
    public function getCurrentItems()
    {
        if ($this->currentItems === null) {
            $this->currentItems = $this->getItemsByPage($this->getCurrentPageNumber());
        }

        return $this->currentItems;
    }

    /**
     * Returns the current page number.
     *
     * @return int
     */
    public function getCurrentPageNumber()
    {
        return $this->normalizePageNumber($this->currentPageNumber);
    }

    /**
     * Sets the current page number.
     *
     * @param  int $pageNumber Page number
     * @return Paginator $this
     */
    public function setCurrentPageNumber($pageNumber)
    {
        $this->currentPageNumber = (int) $pageNumber;
        $this->currentItems      = null;
        $this->currentItemCount  = null;

        return $this;
    }

    /**
     * Get the filter
     *
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set a filter chain
     *
     * @return Paginator
     */
    public function setFilter(FilterInterface $filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Returns an item from a page.  The current page is used if there's no
     * page specified.
     *
     * @param  int $itemNumber Item number (1 to itemCountPerPage)
     * @param  int $pageNumber
     * @throws Exception\InvalidArgumentException
     * @return mixed
     */
    public function getItem($itemNumber, $pageNumber = null)
    {
        if ($pageNumber === null) {
            $pageNumber = $this->getCurrentPageNumber();
        } elseif ($pageNumber < 0) {
            $pageNumber = ($this->count() + 1) + $pageNumber;
        }

        $page      = $this->getItemsByPage($pageNumber);
        $itemCount = $this->getItemCount($page);

        if ($itemCount === 0) {
            throw new Exception\InvalidArgumentException('Page ' . $pageNumber . ' does not exist');
        }

        if ($itemNumber < 0) {
            $itemNumber = ($itemCount + 1) + $itemNumber;
        }

        $itemNumber = $this->normalizeItemNumber($itemNumber);

        if ($itemNumber > $itemCount) {
            throw new Exception\InvalidArgumentException(
                "Page {$pageNumber} does not contain item number {$itemNumber}"
            );
        }

        return $page[$itemNumber - 1];
    }

    /**
     * Returns the number of items per page.
     *
     * @return int
     */
    public function getItemCountPerPage()
    {
        if (empty($this->itemCountPerPage)) {
            $this->itemCountPerPage = static::getDefaultItemCountPerPage();
        }

        return $this->itemCountPerPage;
    }

    /**
     * Sets the number of items per page.
     *
     * @param  int $itemCountPerPage
     * @return Paginator $this
     */
    public function setItemCountPerPage($itemCountPerPage = -1)
    {
        $this->itemCountPerPage = (int) $itemCountPerPage;
        if ($this->itemCountPerPage < 1) {
            $this->itemCountPerPage = $this->getTotalItemCount();
        }
        $this->pageCount        = $this->_calculatePageCount();
        $this->currentItems     = null;
        $this->currentItemCount = null;

        return $this;
    }

    /**
     * Returns the number of items in a collection.
     *
     * @param  mixed $items Items
     * @return int
     */
    public function getItemCount($items)
    {
        $itemCount = 0;

        if (is_array($items) || $items instanceof Countable) {
            $itemCount = count($items);
        } elseif ($items instanceof Traversable) { // $items is something like LimitIterator
            $itemCount = iterator_count($items);
        }

        return $itemCount;
    }

    /**
     * Returns the items for a given page.
     *
     * @param int $pageNumber
     * @return mixed
     */
    public function getItemsByPage($pageNumber)
    {
        $pageNumber = $this->normalizePageNumber($pageNumber);

        if ($this->cacheEnabled()) {
            $data = static::$cache->getItem($this->_getCacheId($pageNumber));
            if ($data) {
                return $data;
            }
        }

        $offset = ($pageNumber - 1) * $this->getItemCountPerPage();

        $items = $this->adapter->getItems($offset, $this->getItemCountPerPage());

        $filter = $this->getFilter();

        if ($filter !== null) {
            $items = $filter->filter($items);
        }

        if (! $items instanceof Traversable) {
            $items = new ArrayIterator($items);
        }

        if ($this->cacheEnabled()) {
            $cacheId = $this->_getCacheId($pageNumber);
            static::$cache->setItem($cacheId, $items);
        }

        return $items;
    }

    /**
     * Returns a foreach-compatible iterator.
     *
     * @throws Exception\RuntimeException
     * @return Traversable
     */
    #[ReturnTypeWillChange]
    public function getIterator()
    {
        try {
            return $this->getCurrentItems();
        } catch (Throwable $e) {
            throw new Exception\RuntimeException('Error producing an iterator', null, $e);
        }
    }

    /**
     * Returns the page range (see property declaration above).
     *
     * @return int
     */
    public function getPageRange()
    {
        return $this->pageRange;
    }

    /**
     * Sets the page range (see property declaration above).
     *
     * @param  int $pageRange
     * @return Paginator $this
     */
    public function setPageRange($pageRange)
    {
        $this->pageRange = (int) $pageRange;

        return $this;
    }

    /**
     * Returns the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return stdClass
     */
    public function getPages($scrollingStyle = null)
    {
        if ($this->pages === null) {
            $this->pages = $this->_createPages($scrollingStyle);
        }

        return $this->pages;
    }

    /**
     * Returns a subset of pages within a given range.
     *
     * @param  int $lowerBound Lower bound of the range
     * @param  int $upperBound Upper bound of the range
     * @return array
     */
    public function getPagesInRange($lowerBound, $upperBound)
    {
        $lowerBound = $this->normalizePageNumber($lowerBound);
        $upperBound = $this->normalizePageNumber($upperBound);

        $pages = [];

        for ($pageNumber = $lowerBound; $pageNumber <= $upperBound; $pageNumber++) {
            $pages[$pageNumber] = $pageNumber;
        }

        return $pages;
    }

    /**
     * Returns the page item cache.
     *
     * @return array
     */
    public function getPageItemCache()
    {
        $data = [];
        if ($this->cacheEnabled()) {
            $prefixLength  = strlen(self::CACHE_TAG_PREFIX);
            $cacheIterator = static::$cache->getIterator();
            $cacheIterator->setMode(CacheIterator::CURRENT_AS_VALUE);
            foreach ($cacheIterator as $key => $value) {
                if (0 === strpos($key, self::CACHE_TAG_PREFIX)) {
                    $data[(int) substr($key, $prefixLength)] = $value;
                }
            }
        }
        return $data;
    }

    /**
     * Retrieves the view instance.
     *
     * If none registered, instantiates a PhpRenderer instance.
     *
     * @return RendererInterface|null
     */
    public function getView()
    {
        if ($this->view === null) {
            $this->setView(new View\Renderer\PhpRenderer());
        }

        return $this->view;
    }

    /**
     * Sets the view object.
     *
     * @return Paginator
     */
    public function setView(?View\Renderer\RendererInterface $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * Brings the item number in range of the page.
     *
     * @param  int $itemNumber
     * @return int
     */
    public function normalizeItemNumber($itemNumber)
    {
        $itemNumber = (int) $itemNumber;

        if ($itemNumber < 1) {
            $itemNumber = 1;
        }

        if ($itemNumber > $this->getItemCountPerPage()) {
            $itemNumber = $this->getItemCountPerPage();
        }

        return $itemNumber;
    }

    /**
     * Brings the page number in range of the paginator.
     *
     * @param  int $pageNumber
     * @return int
     */
    public function normalizePageNumber($pageNumber)
    {
        $pageNumber = (int) $pageNumber;

        if ($pageNumber < 1) {
            $pageNumber = 1;
        }

        $pageCount = $this->count();

        if ($pageCount > 0 && $pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        return $pageNumber;
    }

    /**
     * Renders the paginator.
     *
     * @return string
     */
    public function render(?View\Renderer\RendererInterface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }

        $view = $this->getView();

        return $view->paginationControl($this);
    }

    /**
     * Returns the items of the current page as JSON.
     *
     * @return string
     */
    public function toJson()
    {
        $currentItems  = $this->getCurrentItems();
        $encodeOptions = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP;

        /** @psalm-suppress UndefinedClass */
        if ($currentItems instanceof AbstractResultSet) {
            /** @psalm-suppress UndefinedInterfaceMethod */
            return json_encode($currentItems->toArray(), $encodeOptions);
        }

        return json_encode($currentItems, $encodeOptions);
    }

    /**
     * Tells if there is an active cache object
     * and if the cache has not been disabled
     *
     * @return bool
     */
    protected function cacheEnabled()
    {
        return (static::$cache !== null) && $this->cacheEnabled;
    }

    /**
     * Makes an Id for the cache
     * Depends on the adapter object and the page number
     *
     * Used to store item in cache from that Paginator instance
     *  and that current page
     *
     * @param int $page
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getCacheId($page = null)
    {
        // @codingStandardsIgnoreEnd
        if ($page === null) {
            $page = $this->getCurrentPageNumber();
        }
        return self::CACHE_TAG_PREFIX . $page . '_' . $this->_getCacheInternalId();
    }

    /**
     * Get the internal cache id
     * Depends on the adapter and the item count per page
     *
     * Used to tag that unique Paginator instance in cache
     *
     * @return string
     */
    // @codingStandardsIgnoreStart
    protected function _getCacheInternalId()
    {
        $adapter            = $this->getAdapter();
        $adapterToSerialize = $adapter instanceof DbSelect
            ? $adapter->getArrayCopy()
            : $adapter;

        // @codingStandardsIgnoreEnd
        return md5(
            get_class($adapter)
            . json_encode($adapterToSerialize)
            . $this->getItemCountPerPage()
        );
    }

    /**
     * Calculates the page count.
     *
     * @return int
     */
    // @codingStandardsIgnoreStart
    protected function _calculatePageCount()
    {
        // @codingStandardsIgnoreEnd
        return (int) ceil($this->getAdapter()->count() / $this->getItemCountPerPage());
    }

    /**
     * Creates the page collection.
     *
     * @param  string $scrollingStyle Scrolling style
     * @return stdClass
     */
    // @codingStandardsIgnoreStart
    protected function _createPages($scrollingStyle = null)
    {
        // @codingStandardsIgnoreEnd
        $pageCount         = $this->count();
        $currentPageNumber = $this->getCurrentPageNumber();

        $pages                   = new stdClass();
        $pages->pageCount        = $pageCount;
        $pages->itemCountPerPage = $this->getItemCountPerPage();
        $pages->first            = 1;
        $pages->current          = $currentPageNumber;
        $pages->last             = $pageCount;

        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $pages->previous = $currentPageNumber - 1;
        }

        if ($currentPageNumber + 1 <= $pageCount) {
            $pages->next = $currentPageNumber + 1;
        }

        // Pages in range
        $scrollingStyle          = $this->_loadScrollingStyle($scrollingStyle);
        $pages->pagesInRange     = $scrollingStyle->getPages($this);
        $pages->firstPageInRange = min($pages->pagesInRange);
        $pages->lastPageInRange  = max($pages->pagesInRange);

        // Item numbers
        if ($this->getCurrentItems() !== null) {
            $pages->currentItemCount = $this->getCurrentItemCount();
            $pages->totalItemCount   = $this->getTotalItemCount();
            $pages->firstItemNumber  = $pages->totalItemCount
                ? (($currentPageNumber - 1) * $pages->itemCountPerPage) + 1
                : 0;
            $pages->lastItemNumber   = $pages->totalItemCount
                ? $pages->firstItemNumber + $pages->currentItemCount - 1
                : 0;
        }

        return $pages;
    }

    /**
     * Loads a scrolling style.
     *
     * @param string $scrollingStyle
     * @return ScrollingStyleInterface
     * @throws Exception\InvalidArgumentException
     */
    // @codingStandardsIgnoreStart
    protected function _loadScrollingStyle($scrollingStyle = null)
    {
        // @codingStandardsIgnoreEnd
        if ($scrollingStyle === null) {
            $scrollingStyle = static::$defaultScrollingStyle;
        }

        switch (strtolower(gettype($scrollingStyle))) {
            case 'object':
                if (! $scrollingStyle instanceof ScrollingStyleInterface) {
                    throw new Exception\InvalidArgumentException(
                        'Scrolling style must implement Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface'
                    );
                }

                return $scrollingStyle;

            case 'string':
                return static::getScrollingStylePluginManager()->get($scrollingStyle);

            case 'null':
                // Fall through to default case

            default:
                throw new Exception\InvalidArgumentException(
                    'Scrolling style must be a class '
                    . 'name or object implementing Laminas\Paginator\ScrollingStyle\ScrollingStyleInterface'
                );
        }
    }
}
