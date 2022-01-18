<?php namespace Waka\Tbser\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;

/**
 * Presentation Back-end Controller
 */
class Presentations extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Waka.Utils.Behaviors.BtnsBehavior',
        'Waka.Utils.Behaviors.SideBarUpdate',
        'Backend.Behaviors.ReorderController',
        'Waka.Utils.Behaviors.DuplicateModel',
        'Waka.Tbser.Behaviors.PresentationBehavior',
    ];
    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $btnsConfig = 'config_btns.yaml';
    public $duplicateConfig = 'config_duplicate.yaml';
    public $reorderConfig = 'config_reorder.yaml';
    public $sideBarUpdateConfig = 'config_side_bar_update.yaml';
    //FIN DE LA CONFIG AUTO
    //startKeep/
    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Waka.Tbser', 'Presentations');
    }

    public function update($id)
    {
        $this->bodyClass = 'compact-container';
        return $this->asExtension('FormController')->update($id);
    }

    //endKeep/
}

