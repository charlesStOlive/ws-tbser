<?php namespace Waka\Tbser\WakaRules\Asks;

use Waka\Charter\Classes\Rules\ChartBase;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ApplicationException;
use Waka\Tbser\Controllers\Charts;
use Waka\Utils\Interfaces\Ask as AskInterface;

class ChartOpenXml extends ChartBase
{
    public $jsonable = ['datas', 'test'];
    /**
     * Returns information about this event, including name and description.
     */
    public function subFormDetails()
    {
        return [
            'name'        => 'Graphique PowerPoint',
            'description' => 'Un graphique pour powerpoint',
            'icon'        => 'icon-chart',
            'premission'  => 'wcli.utils.ask.edit.admin',
            'show_attributes' => false,
            'outputs' => [
                'word_type' => 'HTM',
            ]
        ]; 
    }

    public function getText()
    {
        //trace_log('getText HTMLASK---');
        $hostObj = $this->host;
        //trace_log($hostObj->config_data);
        $title = $hostObj->config_data['title'] ?? null;
        if($title) {
            return $title;
        }
        return parent::getText();
    }
    
    /**
     * $modelSrc le Model cible
     * $context le type de contenu twig ou word
     * $dataForTwig un modèle en array fournit par le datasource ( avec ces relations parents ) 
     */

    public function resolve($modelSrc, $context = 'twig', $dataForTwig = []) {

        $model = $modelSrc;
        if($childModel = $this->getConfig('relation')) {
            $model = $this->getRelation($model, $childModel);
        }
        //
        $src_calculs = $this->getConfig('src_calculs');
        $series = $this->host->getConfig('datas');
        //trace_log($series);

        $datas = [
            'title' => $this->getConfig('title'),
            'datasets' => [],
        ];

        // $attribute
        $i=1;
        foreach($series as $serie) {
            $serieAttribute = $serie['src_att'];
            // Cas des attributs période
            $attribute = ['periode' => $serieAttribute];
            $serieData = [
                'label' => $serie['src_label'],
                'datas' =>  $model->{$src_calculs}($attribute),
            ];
            $datas['datasets'][$i] = $serieData;
            //Powerpoint n'aime pas les 0 dons je force à 1
            $i++;
        }
        return $datas;
    }
}
