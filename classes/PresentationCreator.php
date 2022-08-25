<?php namespace Waka\Tbser\Classes;

use Waka\Tbser\Models\Presentation;
use Waka\Utils\Classes\DataSource;
use Waka\Utils\Classes\TmpFiles;
use Waka\OpenTBS\MergePpt;
use Waka\Utils\Classes\ProductorCreator;

class PresentationCreator extends ProductorCreator
{
    public $merger;

    public static function find($presentation_id)
    {
        $presentation = Presentation::find($presentation_id);
        self::$productor = $presentation;
        return new self;
    }

    public function getSlides($data) {
        $slides = \Twig::parse($this->getProductor()->slides, $data);
        //trace_log($slides);
        $slides = \Yaml::parse($slides);
        //trace_log($slides);
        return $slides;
    }

    

    public function checkConditions()//Ancienement checkScopes
    {
        $conditions = new \Waka\Utils\Classes\Conditions($this->getProductor(), self::$ds->model);
        return $conditions->checkConditions();
    }

    public function render($inline = false)
    {
        if (!self::$ds || !$this->modelId) {
            //trace_log("modelId pas instancie");
            throw new \SystemException("Le modelId n a pas ete instancié");
        }
        $data = $this->getProductorVars();
        //trace_log($data);
        $this->merger = new MergePpt();
        $this->merger->loadTemplate($this->getProductor()->src->getLocalPath());
        //
        $slides = $this->getSlides($data);
        //
        foreach($slides as $key=>$slide) {
            if($slide['merge'] ?? false) {
                //Pas malin ici mais je suis obligé d'ajouter un prefix a mon tableau. donc je met ds et je pointe la ligne ds. 
                $this->merger->mergeField($key, $data['ds']->toArray());
            }
            if($askImage = $slide['change_image'] ?? false) {
                $image = array_get($data, $askImage);
                $temp = new TmpFiles();
               
                $temp->putUrlFile($image['path']);
                $this->merger->changePicture($key, '#'.$askImage.'#', $temp->getFilePath());
                $this->merger->mergeField($key, $image, 'image');
                $temp->delete();
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
                //trace_log("on cree des listes-------------------------------");
                $listTemp = explode('.', $list);
                $fncName = $listTemp[1] ?? null;
                if(!$fncName) {
                    throw new \ApplicationException('Il manque le nom des rows lors de la création du chart');
                }
                $values = array_get($data, $list);
                //trace_log($values['datas']);
                //trace_log($fncName);
                

                $this->merger->MergeBlock($key,$fncName, $values['datas']);
                $this->merger->mergeField($key, $values, $fncName);
                
            } 
        }
        //
        $fileOutputname = $this->createTwigStrName();
        return $this->merger->downloadPpt($fileOutputname.'.pptx');
        


        

        
    }

    

    public function renderCloud($lot = false)
    {
        if (!self::$ds || !$this->modelId) {
            //trace_log("modelId pas instancie");
            throw new \SystemException("Le modelId n a pas ete instancié");
        }
        $data = $this->prepareCreatorVars();
        
        

        // if ($lot) {
        //     $path = 'lots';
        // } else {
        //     $folderOrg = new \Waka\Cloud\Classes\FolderOrganisation();
        //     $path = $folderOrg->getPath(self::$ds->model);
        // }
        // $cloudSystem->put($path.'/'.$data['fileName'], $pdfContent);
    }

    

    

    

    

}
