<?php

namespace Waka\Tbser\Behaviors;

use Backend\Classes\ControllerBehavior;
use Redirect;
use Waka\Utils\Classes\DataSource;
use Waka\Tbser\Classes\PresentationCreator;
use Waka\Tbser\Models\Presentation;
use Waka\OpenTBS\MergePpt;
use Session;

class PresentationBehavior extends ControllerBehavior
{
    protected $presentationBehaviorWidget;
    protected $askDataWidget;
    public $errors;

    public function __construct($controller)
    {
        parent::__construct($controller);
        $this->presentationBehaviorWidget = $this->createPresentationBehaviorWidget();
        $this->errors = [];
        \Event::listen('waka.utils::conditions.error', function ($error) {
            array_push($this->errors, $error);
        });
    }

    /**
     * METHODES
     */

    /**
     * LOAD DES POPUPS
     */
    public function onLoadPresentationBehaviorPopupForm()
    {
        $modelClass = post('modelClass');
        $modelId = post('modelId');

        $ds = \DataSources::findByClass($modelClass);
        $options = $ds->getProductorOptions('Waka\Tbser\Models\Presentation', $modelId);

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        $this->vars['errors'] = $this->errors;
        $this->vars['modelClass'] = $modelClass;

        if($options) {
            return $this->makePartial('$/waka/tbser/behaviors/presentationbehavior/_popup.htm');
        } else {
            return $this->makePartial('$/waka/utils/views/_popup_no_model.htm');
        }

        
    }
    public function onLoadPresentationBehaviorContentForm()
    {
        $modelClass = post('modelClass');
        $modelId = post('modelId');
        

        $ds = \DataSources::findByClass($modelClass);
        $options = $ds->getProductorOptions('Waka\Tbser\Models\Presentation', $modelId);

        $this->vars['options'] = $options;
        $this->vars['modelId'] = $modelId;
        $this->vars['errors'] = $this->errors;
        $this->vars['modelClass'] = $modelClass;

        if($options) {
            return ['#popupActionContent' => $this->makePartial('$/waka/tbser/behaviors/presentationbehavior/_content.htm')];
        } else {
            return ['#popupActionContent' => $this->makePartial('$/waka/utils/views/_content_no_model.htm')];
        }

        
    }

    public function onSelectPresentation() {
        $productorId = post('productorId');
        $productor = PresentationCreator::find($productorId);
        $askDataWidget = $this->createAskDataWidget();
        $asks = $productor->getProductorAsks();
        $askDataWidget->addFields($asks);
        $this->vars['askDataWidget'] = $askDataWidget;
        
        return [
            '#askDataWidget' => $this->makePartial('$/waka/utils/models/ask/_widget_ask_data.htm')
        ];
    }

    public function onPresentationBehaviorPopupValidation()
    {
        $datas = post();
        $errors = $this->CheckValidation(\Input::all());
        if ($errors) {
            throw new \ValidationException(['error' => $errors]);
        }
        $productorId = post('productorId');
        $modelId = post('modelId');
        Session::put('presentation_asks_'.$modelId, $datas['asks_array'] ?? []);

        return Redirect::to('/backend/waka/tbser/presentations/makepresentation/?productorId=' . $productorId . '&modelId=' . $modelId);
    }

    /**
     * Validations
     */
    public function CheckValidation($inputs)
    {
        $rules = [
            'modelId' => 'required',
            'productorId' => 'required',
        ];

        $validator = \Validator::make($inputs, $rules);

        if ($validator->fails()) {
            return $validator->messages()->first();
        } else {
            return false;
        }
    }
    /**
     * Cette fonction est utilisé lors du test depuis le controller presentation.
     */
    public function onLoadPresentationTest()
    {
        $productorId = post('productorId');
        $productor = PresentationCreator::find($productorId)->setModelTest();
        return Redirect::to('/backend/waka/tbser/presentations/makepresentation/?productorId=' . $productorId . '&modelId=' . $productor->modelId);
    }
    public function makepresentation()
    {
        $productorId = \Input::get('productorId');
        $modelId = \Input::get('modelId');
        $asks = Session::pull('presentation_asks_'.$modelId);
        return PresentationCreator::find($productorId)->setModelId($modelId)->setAsksResponse($asks)->render();
    }
    //
    public function onLoadPresentationCheck()
    {
        $productorId = post('productorId');
        $productor = PresentationCreator::find($productorId);
        $modelTest = $productor->getProductor()->test_id;
        if(!$modelTest) {
            throw new \ValidationException(['test_id' => "Le modèle de test n'est pas renseigné ou n'existe plus"]);
        }
        return $productor->setModelId($modelTest)->checkPresentation();
    }
    //
    public function createPresentationBehaviorWidget()
    {
        $config = $this->makeConfig('$/waka/tbser/models/presentation/fields_for_test.yaml');
        $config->alias = 'presentationBehaviorformWidget';
        $config->arrayName = 'presentationBehavior_array';
        $config->model = new Presentation();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
    //
    public function createAskDataWidget()
    {
        $config = $this->makeConfig('$/waka/utils/models/ask/empty_fields.yaml');
        $config->alias = 'askDataformWidget';
        $config->arrayName = 'asks_array';
        $config->model = new \Waka\Utils\Models\RuleAsk();
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
}
