<?php namespace Waka\Tbser\Classes;

use Waka\Tbser\Models\Presentation;
use Waka\Utils\Classes\DataSource;
use Waka\Utils\Classes\TmpFiles;
use Waka\OpenTBS\MergePpt;

class PresentationCreator extends \Winter\Storm\Extension\Extendable
{
    public static $presentation;
    public $ds;
    public $modelId;
    public $merger;

    public $askResponse = [];

    private $isTwigStarted;

    public static function find($presentation_id)
    {
        $presentation = Presentation::find($presentation_id);
        self::$presentation = $presentation;
        return new self;
    }

    public static function getProductor()
    {
        return self::$presentation;
    }
    

    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
        $dataSourceId = $this->getProductor()->data_source;
        $this->ds =  \DataSources::find($dataSourceId);
        $this->ds->instanciateModel($modelId);
        return $this;
    }

    public function setModelTest()
    {
        $this->modelId = $this->getProductor()->test_id;
        if(!$this->modelId) {
             throw new \ValidationException(['test_id' => \Lang::get('waka.tbser::presentation.e.test_id')]);
        }
        $dataSourceId = $this->getProductor()->data_source;
        $this->ds =  \DataSources::find($dataSourceId);
        $this->ds->instanciateModel($this->modelId);
        return $this;
    }

    public function setRuleAsksResponse($datas = [])
    {
        $askArray = [];
        $srcmodel = $this->ds->getModel($this->modelId);
        $asks = $this->getProductor()->rule_asks()->get();
        foreach($asks as $ask) {
            $key = $ask->getCode();
            //trace_log($key);
            $askResolved = $ask->resolve($srcmodel, 'twig', $datas);
            $askArray[$key] = $askResolved;
        }
        //trace_log($askArray); // les $this->askResponse sont prioritaire
        return array_replace($askArray,$this->askResponse);
        
    }

    //BEBAVIOR AJOUTE LES REPOSES ??
    public function setAsksResponse($datas = [])
    {
        $this->askResponse = $this->ds->getAsksFromData($datas, $this->getProductor()->asks);
        return $this;
    }

    public function setRuleFncsResponse()
    {
        $fncArray = [];
        $srcmodel = $this->ds->getModel($this->modelId);
        $fncs = $this->getProductor()->rule_fncs()->get();
        foreach($fncs as $fnc) {
            $key = $fnc->getCode();
            //trace_log('key of the function');
            $fncResolved = $fnc->resolve($srcmodel,$this->ds->code);
            $fncArray[$key] = $fncResolved;
        }
        //trace_log($fncArray);
        return $fncArray;
        
    }

    public function setdefaultAsks($datas = [])
    {
        if($this->ds) {
             $this->askResponse = $this->ds->getAsksFromData($datas, $this->getProductor()->asks);
        } else {
            $this->askResponse = [];
        }
        return $this;
    }

    public function createTwigStrName($data)
    {
        $nameConstruction = \Twig::parse($this->getProductor()->name_construction, $data);
        return str_slug($nameConstruction);
    }

    public function prepareCreatorVars()
    {
        $this->ds =  \DataSources::find($this->getProductor()->data_source);
        $data = $this->ds->getValues($this->modelId);

        $model = ['ds' => $data];
        
        //Nouveau bloc pour nouveaux asks
        if($this->getProductor()->rule_asks()->count()) {
            $this->askResponse = $this->setRuleAsksResponse($model);
        } else {
            //Injection des asks s'ils existent dans le model;
            if(!$this->askResponse) {
                $this->setAsksResponse($model);
            }
        }

        //Nouveau bloc pour les new Fncs
        if($this->getProductor()->rule_fncs()->count()) {
            $fncs = $this->setRuleFncsResponse($model);
            $model = array_merge($model, [ 'fncs' => $fncs]);
        }
        //trace_log("ASK RESPONSE");
        //trace_log($this->askResponse);
        $model = array_merge($model, [ 'asks' => $this->askResponse]);
        return $model;
    }

    public function getSlides() {
        $slides = \Twig::parse($this->getProductor()->slides);
        //trace_log($slides);
        $slides = \Yaml::parse($slides);
        //trace_log($slides);

        return $slides;
    }

    

    public function checkConditions()//Ancienement checkScopes
    {
        $conditions = new \Waka\Utils\Classes\Conditions($this->getProductor(), $this->ds->model);
        return $conditions->checkConditions();
    }

    public function render($inline = false)
    {
        if (!$this->ds || !$this->modelId) {
            //trace_log("modelId pas instancie");
            throw new \SystemException("Le modelId n a pas ete instancié");
        }
        $data = $this->prepareCreatorVars();
        trace_log($data);
        $this->merger = new MergePpt();
        $this->merger->loadTemplate($this->getProductor()->src->getLocalPath());
        //
        $slides = $this->getSlides($data);
        //
        foreach($slides as $key=>$slide) {
            if($slide['merge'] ?? false) {
                //Pas malin ici mais je suis obligé d'ajouter un prefix a mon tableau. donc je met ds et je pointe la ligne ds. 
                $this->merger->mergeField($key, $data['ds']);
            }
            if($askImage = $slide['change_image'] ?? false) {
                $image = array_get($data, $askImage);
                //trace_log($image['path']);
                $this->merger->changePicture($key, '#merge_me#', $image['path']);
                $this->merger->mergeField($key, $image, 'image');
            } 
            if($slide['delete_slide'] ?? false) {
                $this->merger->deleteSlide($key);
            }
            if($askChart = $slide['change_chart'] ?? false) {
                //trace_log("on change un chart-------------------------------");
                $askCode = $askChart['values'];
                $values = array_get($data, $askCode);
                $chartCode = $askChart['chart'];
                //trace_log($values['datasets']);
                $this->merger->changeChart($chartCode, $values['datasets'], true);
                $this->merger->mergeField($key, $values, 'Chart');
            } 
            if($list = $slide['create_rows'] ?? false) {
                trace_log("on cree des listes-------------------------------");
                $listTemp = explode('.', $list);
                $fncName = $listTemp[1] ?? null;
                if(!$fncName) {
                    throw new \ApplicationException('Il manque le nom des rows lors de la création du chart');
                }
                $values = array_get($data, $list);
                trace_log($values['datas']);
                trace_log($fncName);
                

                $this->merger->MergeBlock($key,$fncName, $values['datas']);
                $this->merger->mergeField($key, $values, $fncName);
                
            } 
        }
        //
        $fileOutputname = $this->createTwigStrName($data);
        return $this->merger->downloadPpt($fileOutputname.'.pptx');
        


        

        
    }

    

    public function renderCloud($lot = false)
    {
        if (!$this->ds || !$this->modelId) {
            //trace_log("modelId pas instancie");
            throw new \SystemException("Le modelId n a pas ete instancié");
        }
        $data = $this->prepareCreatorVars();
        
        

        // if ($lot) {
        //     $path = 'lots';
        // } else {
        //     $folderOrg = new \Waka\Cloud\Classes\FolderOrganisation();
        //     $path = $folderOrg->getPath($this->ds->model);
        // }
        // $cloudSystem->put($path.'/'.$data['fileName'], $pdfContent);
    }

    

    

    

    /**
     * Temporarily registers mail based token parsers with Twig.
     * @return void
     */
    protected function startTwig()
    {
        if ($this->isTwigStarted) {
            return;
        }

        $this->isTwigStarted = true;

        $markupManager = \System\Classes\MarkupManager::instance();
        $markupManager->beginTransaction();
        $markupManager->registerTokenParsers([
            new \System\Twig\MailPartialTokenParser,
        ]);
    }

    /**
     * Indicates that we are finished with Twig.
     * @return void
     */
    protected function stopTwig()
    {
        if (!$this->isTwigStarted) {
            return;
        }

        $markupManager = \System\Classes\MarkupManager::instance();
        $markupManager->endTransaction();
        $this->isTwigStarted = false;
    }
}
