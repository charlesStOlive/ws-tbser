ws-tbser

# Attention :
* version plus qu'alpha !!!
* plugin pour winter.cms
*  ne fonctionne qu'avec le reste des outils notilac : waka.session & waka.utils

# Configuration:
Dans les champs html reprendre les codes de chaque slide et indiquer : 
* **merge: true** une ou plusieurs injection de champs dynamyque en utilisant merge: true
* **change_image** un remplacement d'image. 
* Un remplacement de chart

rendu 
```language yaml
1: # le numéro de la slide
    merge: true # indique qu'on va injecter les valeurs
    change_image: asks.logo  # indique qu'on va changer la photo qui a comment texte de remplacement #asks.logo#
2:
    change_image: asks.logo
3:
    change_image: asks.logo
4:
    merge: true
    change_image: asks.logo
5:
    change_chart: 
        values: asks.chart
        chart: chart1

```

## Pour que ça fonctionne : 
les champs dynamyques [ds.{nom d'une var}] dans le PPT ne doivent pas avoir de controlleur d'orthographe
```
Think to set all texts to "Tools > Language > No check" when you edit the PowerPoint presentation, otherwise some TBS fields can be split by XML tags about the language and spell checking
If some TBS fields are not merged or raise an error, then you can cut the field and paste it back using Paste / Keep Text Only.

```
les champs disponible  : 
* **[ds.{var}]** une variable du modèle cible
* **[asks.{codeAsk}]** une valeur issue du champs ask
