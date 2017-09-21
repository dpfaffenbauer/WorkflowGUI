<?php

namespace WorkflowGuiBundle;

use Pimcore\Extension\Bundle\AbstractPimcoreBundle;
use Pimcore\Extension\Bundle\Traits\PackageVersionTrait;

class WorkflowGuiBundle extends AbstractPimcoreBundle
{
    use PackageVersionTrait;

    protected function getComposerPackageName(): string
    {
        return 'dpfaffenbauer/workflow-gui';
    }

    public function getJsPaths()
    {
        return [
            '/bundles/workflowgui/js/startup.js',
            '/bundles/workflowgui/js/workflow/panel.js',
            '/bundles/workflowgui/js/workflow/item.js'
        ];
    }

    public function getCssPaths()
    {
        return [
            '/bundles/workflowgui/css/workflow.css'
        ];
    }
}
