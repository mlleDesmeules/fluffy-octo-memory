<?php

namespace app\modules\v1\models\post;

use app\helpers\DateHelper;
use app\models\post\PostComment;
use app\modules\v1\models\LangEx;

/**
 * Class PostCommentEx
 *
 * @package app\modules\v1\models\post
 */
class PostCommentEx extends PostComment
{
	/** @inheritdoc */
	public function getReplies ()
	{
		return $this->hasMany(self::className(), [ "reply_comment_id" => "id" ])
		            ->approved();
	}

	/** @inheritdoc */
	public function fields ()
	{
		return [
			"id",
			"author" => function ( self $model ) {
				if (!is_null($model->user_id)) {
					return $model->user->profile->getFullname();
				} else {
					return $model->author;
				}
			},
			"comment",
			"replies",
			"created_on" => function ( self $model ) { return DateHelper::formatDate($model->created_on); },
		];
	}

	/** @inheritdoc */
	public function rules ()
	{
		return [
			[ "post_id", "required", "message" => self::ERR_FIELD_REQUIRED ],
			[
				[ "post_id" ], "exist",
				"skipOnError"     => true,
				"targetClass"     => PostEx::className(),
				"targetAttribute" => [ "post_id" => "id" ],
				"message"         => self::ERR_FIELD_NOT_FOUND,
			],

			[
				"reply_comment_id", "exist",
				"skipOnEmpty"     => true,
				"targetClass"     => self::className(),
				"targetAttribute" => [ "reply_comment_id" => "id" ],
				"message"         => self::ERR_FIELD_NOT_FOUND,
			],

			[ "author", "required", "message" => self::ERR_FIELD_REQUIRED ],
			[ "author", "string", "max" => 140, "tooLong" => self::ERR_FIELD_TOO_LONG ],

			[ "email", "required", "message" => self::ERR_FIELD_REQUIRED ],
			[ "email", "string", "max" => 255, "tooLong" => self::ERR_FIELD_TOO_LONG ],

			[ "comment", "required", "message" => self::ERR_FIELD_REQUIRED ],
		];
	}

	/**
	 * @param int           $postId
	 * @param PostCommentEx $data
	 *
	 * @return array
	 */
	public static function createForPost ( $postId, $data )
	{
		$langId = LangEx::getIdFromIcu(\Yii::$app->language);

		return self::createByVisitor($postId, $langId, $data);
	}

	/**
	 * This method will get all post comments that a approved for a single Post translation and organize it in a tree
	 * so that all replies to a comment are linked to the comment itself.
	 *
	 * @param int $postId
	 *
	 * @return self[]
	 */
	public static function getCommentsForPost ( $postId )
	{
		$langId = LangEx::getIdFromIcu(\Yii::$app->language);

		return self::find()
		           ->byPost($postId)
		           ->byLang($langId)
		           ->firstComment()
		           ->approved()
		           ->all();
	}

	/**
	 * This method will count the number of approved post comments for a single Post translation.
	 *
	 * @param int $postId
	 *
	 * @return int|string
	 */
	public static function getCommentCountForPost ( $postId )
	{
		$langId = LangEx::getIdFromIcu(\Yii::$app->language);

		return self::find()
		           ->byPost($postId)
		           ->byLang($langId)
		           ->approved()
		           ->count();
	}
}
