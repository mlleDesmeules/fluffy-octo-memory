<?php
namespace app\modules\v1\admin\models\category;

use app\helpers\ArrayHelperEx;
use app\models\app\Lang;
use app\models\category\CategoryLang;

/**
 * Class CategoryLangEx
 * @package app\modules\v1\admin\models\category
 */
class CategoryLangEx extends CategoryLang
{
	
	/** @inheritdoc */
	public function fields ()
	{
		return [
			"language" => function ( self $model ) { return $model->lang->icu; },
			"name",
			"slug",
		];
	}
	
	/** @inheritdoc */
	public function rules ()
	{
		return [
			[ "lang_id", "required" ],
			[ "lang_id", "exist", 'targetClass' => Lang::className(), "targetAttribute" => [ "lang_id" => "id" ] ],
			
			[ "name", "required" ],
			[ "name", "string", "max" => 255 ],
			[ "name", "unique" ],
			
			[ "slug", "required" ],
			[ "slug", "string", "max" => 255 ],
			[ "slug", "unique" ],
		];
	}
	
	/**
	 * @param int   $categoryId
	 * @param array $translations
	 *
	 * @return array
	 */
	public static function manageTranslations ( $categoryId, $translations )
	{
		//  if category doesn't exists, then throw an error
		if (!CategoryEx::idExists($categoryId)) {
			return self::buildError(self::ERR_CATEGORY_NOT_FOUND);
		}
		
		//  define result as success, will be overwritten by an error when necessary
		$result = self::buildSuccess([]);
		
		//  for each possible translation, define if it needs to be created or updated
		foreach ( $translations as $translation ) {
			$langId = ArrayHelperEx::getValue($translation, "lang_id");
			
			//  verify if language in data exists
			if (!Lang::idExists($langId)) {
				$result = self::buildError(self::ERR_LANG_NOT_FOUND);
				break;
			}
			
			//  if the translation exists, then update it, otherwise create it
			if (self::translationExists($categoryId, $langId)) {
				$result = self::updateTranslation($categoryId, $langId, $translation);
			} else {
				$result = self::createTranslation($categoryId, $translation);
			}
			
			//  if there was an error, then stop here
			if ( $result["status"] === self::ERROR ) {
				break;
			}
		}
		
		//  return the result
		return $result;
	}
}