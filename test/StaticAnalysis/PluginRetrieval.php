<?php

declare(strict_types=1);

namespace LaminasTest\View\StaticAnalysis;

use Laminas\View\Helper\GravatarImage;
use Laminas\View\Helper\Layout;
use Laminas\View\HelperPluginManager;

final class PluginRetrieval
{
    private HelperPluginManager $pluginManager;

    public function __construct(HelperPluginManager $pluginManager)
    {
        $this->pluginManager = $pluginManager;
    }

    /** @param non-empty-string $email */
    public function retrieveHelperByClassName(string $email): string
    {
        return ($this->pluginManager->get(GravatarImage::class))($email);
    }

    public function retrievalByClassNameInfersKnownMethods(): string
    {
        $helper = $this->pluginManager->get(Layout::class);

        return $helper->getLayout();
    }
}
