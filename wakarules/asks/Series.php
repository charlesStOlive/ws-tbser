<?php namespace Waka\Tbser\WakaRules\Asks;

use Waka\Tbser\Classes\Rules\ChartBase;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use ApplicationException;
use Waka\Tbser\Controllers\Charts;

class Series extends ChartBase
{
    public $jsonable = [];
    /**
     * Returns information about this event, including name and description.
     */
    public function askDetails()
    {
        return [
            'name'        => 'Graphiques PowerPoint',
            'description' => 'Remplace les données d\'un graphique powerPoint',
            'icon'        => 'icon-pie-chart',
            'premission'  => 'wcli.utils.ask.edit.admin',
            'show_attributes' => true,
            'productor_type' => 'chart',
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
        $srcLabels = $this->getConfig('src_labels');
        $src_calculs = $this->getConfig('src_calculs');

        $series = $this->host->datas;

        $datas = [
            'labels' => $srcLabels,
            'datasets' => [
                [
                    'data' => $dataSet1,
                    'label' => $src_1_label,
                ],
                [
                    'data' => $dataSet2,
                    'label' => $src_2_label,
                ],
            ],
        ];

        // $attribute

        foreach($series as $serie) {
            $serieAttribute = $serie['src_att'];
            $attribute = ['periode' => $serieAttribute];
            $serieData = [
                'label' => $serie['src_label'],
                'data' =>  $model->{$src_calculs}($attribute),
            ];
            array_push($datas['datasets'], $serieData);
        }

        
        $dataSet1 = $model->{$src_calculs}($attributes1);
        $dataSet2 = $model->{$src_calculs}($attributes2);
        $labels = $model->{$srcLabels}($attributes1);


        $datas = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $dataSet1,
                    'label' => $src_1_label,
                ],
                [
                    'data' => $dataSet2,
                    'label' => $src_2_label,
                ],
            ],
        ];
        trace_log($datas);
        return $datas;
    }
}
