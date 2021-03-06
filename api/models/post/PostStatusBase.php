<?php

namespace app\models\post;

use Yii;

/**
 * This is the model class for table "post_status".
 *
 * @property int    $id
 * @property string $name
 *
 * Relations :
 * @property Post[] $posts
 */
abstract class PostStatusBase extends \yii\db\ActiveRecord
{
	const DRAFT       = 1;
	const UNPUBLISHED = 2;
	const PUBLISHED   = 3;
	const ARCHIVED    = 4;

	/** @inheritdoc */
	public static function tableName () { return 'post_status'; }
	
	/** @inheritdoc */
	public function rules ()
	{
		return [
			[ "name", "required" ],
			[ "name", "string", "max" => 255 ],
			[ "name", "unique" ],
		];
	}
	
	/** @inheritdoc */
	public function attributeLabels ()
	{
		return [
			'id'   => Yii::t('app.post', 'ID'),
			'name' => Yii::t('app.post', 'Name'),
		];
	}
	
	/** @return \yii\db\ActiveQuery */
	public function getPosts ()
	{
		return $this->hasMany(Post::className(), [ 'post_status_id' => 'id' ]);
	}
	
	/**
	 * @inheritdoc
	 * @return PostStatusQuery the active query used by this AR class.
	 */
	public static function find ()
	{
		return new PostStatusQuery(get_called_class());
	}

	/**
	 * This method will verify if a specific project status ID exists in the database.
	 *
	 * @param integer $statusId
	 *
	 * @return bool
	 */
	public static function idExists ( $statusId )
	{
		return self::find()->andWhere([ "id" => $statusId ])->exists();
	}
}
