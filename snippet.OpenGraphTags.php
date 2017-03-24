<?php
/**
 * OpenGraphTags
 * Выводит OG-теги только для указанных шаблонов.
 * 
 * @category snippet
 * Это должны быть только шаблоны статей (og_type - жестко прописан как article)!!!
 * Выводит непустые значения тегов OpenGraph, указанные в админке для поста
 *
 * @param $tplList - список шаблонов через запятую, ОБЯЗАТЕЛЬНЫЙ!!
 * @param $locale, string, по умолчанию "ru_RU"
 * @param $site_name, string, по умолчанию [(site_name)]
 * @param $flag, string, имя ТВ-параметра-флага (чекбокс, им можно включить/отключить вывод OG-тегов для статьи, даже если её шаблон в списке $tplList). По умолчанию 1 (вкл).
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
 * @internal category SMO
 *
 * @version 0.2
 * @author Aharito http://aharito.ru
 * 
 * 
 * @example [[OpenGraphTags? &tplList=`31,32` &site_name=`[(cfg_company_brand_name)]` &thumbSnippet=`sgThumb` &thumbOptions=`840x420`]]
**/

$out = '';

$tplList = str_replace(" ", "", $tplList); //Убираем возможные лишние пробелы между ID шаблонов в списке
$_tplList = explode(",", $tplList);

$docObject = $modx->documentObject;

if ( in_array($docObject['template'], $_tplList) && $docObject['og_on_off'][1] ) { // Если шаблон в списке $tplList, и если флаг "включен"

	// Эти параметры заданы единожды при вызове сниппета OpenGraphTags
	$site_name = isset($site_name) ? $site_name : $modx->getConfig('site_name');
	$locale = isset($locale) ? $locale : "ru_RU";
	
	// Эти параметры задаются при редактировании каждой статьи
	// Если они не заданы, то для них 100% есть дефолтные значения, они и выводятся
	$title = !empty($docObject["og_title"][1]) ? $docObject["og_title"][1] : $docObject["pagetitle"];
	$url = $modx->makeUrl($docObject["id"], '', '', 'full');
	
	// Эти параметры задаются при редактировании каждой статьи
	// Для них нет 100% дефолтных значений, поэтому если они не заданы, то вообще не выводим соответствующий метатег
	$desc = !empty($docObject["og_description"][1]) ? $docObject["og_description"][1] : $modx->runSnippet('summary', array('text' => $docObject['content'], 'len' => '50'));
	$descTPL = !empty($desc) ? PHP_EOL."\t".'<meta property="og:description" content="' .$desc. '">' : '';
	
	$imgTPL = !empty($docObject["og_image"][1]) ? PHP_EOL."\t".'<meta property="og:image" content="' .(isset($thumbSnippet) ? $modx->runSnippet($thumbSnippet, array("input"=>$docObject["og_image"][1], "options"=>$thumbOptions)) : $docObject["og_image"][1]). '">'.PHP_EOL."\t" : '';


	$out =  '<meta property="og:site_name" content="' . $site_name . '">
	<meta property="og:locale" content="' . $locale . '">
	<meta property="og:type" content="article">
	<meta property="og:title" content="' .$title. '">'
	.$descTPL
	.$imgTPL.
	'<meta property="og:url" content="' . $url . '">';
}

return $out;