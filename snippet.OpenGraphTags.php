<?php
/**
 * OpenGraphTags
 * Выводит OG-теги только для указанных шаблонов.
 *
 * @param $tplList - список шаблонов через запятую, ОБЯЗАТЕЛЬНЫЙ!!
 * @param $locale , string, по умолчанию "ru_RU"
 * @param $site_name , string, по умолчанию [(site_name)]
 * @param $flag , string, имя ТВ-параметра-флага (чекбокс, им можно включить/отключить вывод OG-тегов для статьи, даже если её шаблон в списке $tplList). По умолчанию 1 (вкл).
 *
 * @param &thumbSnippet, string - сниппет для превьюшек как в SG и FastImage. Например, "sgThumb" или "phpthumb". По умолчанию пусто.
 * @param &thumbOptions, string - опции превьюшки, заданные как в SG или FastImageTV или phpthumb. По умолчанию пусто.
 *
 * @NOTE: Для работы сниппета должны быть созданы следующие ТВ-параметры:
 * og_on_off - ТВ-параметр-флаг (чекбокс, им можно включить/отключить вывод OG-тегов для статьи, даже если её шаблон есть в списке $tplList)
 * og_title - заголовок для соцсетей, если не задан, соцсети чаще всего берут Title.
 * og_description - описание для соцсетей, не более 137 символов по моим экспериментам
 * og_image - картинка для соцсетей, если не задана, соцсети ищут картинки в контенте
 *
 *
 * @category snippet
 * Это должны быть только шаблоны статей (og_type - жестко прописан как article)!!!
 * Выводит непустые значения тегов OpenGraph, указанные в админке для поста
 *
 * @internal @category SMO
 *
 * @version 0.2
 * @author Aharito http://aharito.ru
 *
 *
 * @example [[OpenGraphTags? &tplList=`31,32` &site_name=`[(cfg_company_brand_name)]` &thumbSnippet=`sgThumb` &thumbOptions=`840x420`]]
 **/


/**
Изменения добавил возможность тянуть превю из разных картитнок и типов, картинка,  multitv, simplegallery
Добавил картинку по умолчанию
Добавил возможность указывать тип для конкретного шаблона
Добавил возможность выбирать какие теги нужны
Также можна указать свой title  и description
 *
 **/

//дефолтная картинка если не удалось определить как правило логотип сайта
$defaultImage = isset($defaultImage) ? $defaultImage : '';
$imageStorage = isset($imageStorage) ? str_replace(" ", "", $imageStorage) : '';
$_imageStorage = explode(",", $imageStorage);

//список нужнх тегов
$og_tags = isset($og_tags)?explode(',',str_replace(" ", "", $og_tags)):['site_name','locale','type','title','description','image','url'];

$out = '';
$tplList = isset($tplList) ? str_replace(" ", "", $tplList) : 'all'; //Убираем возможные лишние пробелы между ID шаблонов в списке
$_tplList = explode(",", $tplList);
$docObject = $modx->documentObject;

$og_type= isset($og_type)?$og_type:'website';
if(!empty($params[$docObject['template'].'_og_type'])){
    $og_type = $params[$docObject['template'].'_og_type'];
}


if (in_array($docObject['template'], $_tplList) && $docObject['og_on_off'][1] || $tplList === 'all') { // Если шаблон в списке $tplList, и если флаг "включен"

    // Эти параметры заданы единожды при вызове сниппета OpenGraphTags
    $site_name = isset($site_name) ? $site_name : $modx->getConfig('site_name');
    $site_url = isset($site_url) ? $site_url : $modx->getConfig('site_url');
    $locale = isset($locale) ? $locale : "ru_RU";
    $url = $modx->makeUrl($docObject["id"], '', '', 'full'); // Была возможность редактировать УРЛ, но я убрал за ненужностью


    // Эти параметры задаются при редактировании каждой статьи
    // Если они не заданы, то для них 100% есть дефолтные значения, они и выводятся
    $title = !empty($og_title) ? $og_title : (!empty($docObject["og_title"][1]) ? $docObject["og_title"][1] : $docObject["pagetitle"]);
    // Эти параметры задаются при редактировании каждой статьи
    // Для них нет 100% дефолтных значений, поэтому если они не заданы, то вообще не выводим соответствующий метатег
    $desc = !empty($og_description) ? $og_description : (!empty($docObject["og_description"][1]) ? $docObject["og_description"][1] : $modx->runSnippet('summary', array('text' => $docObject['content'], 'len' => '50')));



    //ищем картинку в тв, multitv, simpleGallery
    $imageSrc = '';
    foreach ($_imageStorage as $storageKey) {
        $tvData = $modx->getTemplateVar($storageKey, '*');
        if ($storageKey === 'SimpleGalleryImage') {
            $image = $modx->runSnippet('sgLister', [
                'display' => 1,
                'api' => 1,
            ]);
            $image = json_decode($image, true);
            $firstImage = array_shift($image);
            $imageSrc = $firstImage['sg_image'];

        } else if ($tvData['type'] === 'image' && !empty($tvData['value'])) {
            $imageSrc = $tvData['value'];
        } else if ($tvData['type'] === 'custom_tv:multitv'  && !empty($tvData['value']) ) {
            $imageList = json_decode($tvData['value'], true)["fieldValue"];
            $imageFieldKey = isset($params[$storageKey.'_fieldKey'])?$params[$storageKey.'_fieldKey']:'image';
            $imageSrc = $imageList[0][$imageFieldKey];
        }
        if(!empty($imageSrc)){
            break;
        }
    }
    //если картинкка пустая и есть дефолтная подставляем
    if(empty($imageSrc) && !empty($defaultImage)){
        $imageSrc = $defaultImage;
    }



    if(in_array('site_name',$og_tags)){
        $out .= '<meta property="og:site_name" content="' . $site_name . '">';
    }
    if(in_array('locale',$og_tags)){
        $out .= PHP_EOL . "\t" . '<meta property="og:locale" content="' . $locale . '">';
    }
    if(in_array('type',$og_tags)){
        $out .= PHP_EOL . "\t" . '<meta property="og:type" content="'.$og_type.'">';
    }
    if(in_array('title',$og_tags)){
        $out .= PHP_EOL . "\t" . '<meta property="og:title" content="' . $title . '">';
    }
    if(in_array('description',$og_tags) && !empty($desc)){
        $out .= PHP_EOL . "\t" .'<meta property="og:description" content="' . $desc . '">';
    }
    if(in_array('image',$og_tags) && !empty($imageSrc)){
        $out .= PHP_EOL . "\t" .'<meta property="og:image" content="' .$site_url. (isset($thumbSnippet) ? $modx->runSnippet($thumbSnippet, array("input" => $imageSrc, "options" => $thumbOptions)) : $imageSrc) . '">';
    }
    if(in_array('url',$og_tags)){
        $out .= PHP_EOL . "\t" . '<meta property="og:url" content="' . $url . '">';
    }








}
return $out;