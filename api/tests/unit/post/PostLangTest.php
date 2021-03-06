<?php

namespace tests\unit\post;

use app\models\app\Lang;
use app\models\post\PostLang;
use app\tests\fixtures;
use Faker\Factory as Faker;

/**
 * Class PosLangTest
 *
 * @package app\tests\unit\post
 *
 * @group   post
 */
class PostLangTest extends \Codeception\Test\Unit
{
	use \Codeception\Specify;

	/** @var \UnitTester */
	protected $tester;

	/** @var \Faker\Generator */
	protected $faker;

	/**
	 * @var PostLang
	 * @specify
	 */
	protected $model;

	/** @inheritdoc */
	protected function _before ()
	{
		$this->faker = Faker::create();
	}

	/** @inheritdoc */
	protected function _after () { }

	public function _fixtures ()
	{
		return [
			"file"     => fixtures\FileFixture::class,
			"postLang" => fixtures\PostLangFixture::class,
		];
	}

	/**
	 * Validate model rules
	 */
	public function testValidation ()
	{
		$this->model = new PostLang();

		$this->specify("post_id is required", function () {
				$this->_fieldsError($this->model, "post_id", PostLang::ERR_FIELD_REQUIRED);
			});
		$this->specify("post_id is excepted to be an integer", function () {
				$this->model->post_id = "invalid";

				$this->_fieldsError($this->model, "post_id", PostLang::ERR_FIELD_TYPE);
			});
		$this->specify("post_id is expected to exists in post table", function () {
				$this->model->post_id = 1000;

				$this->_fieldsError($this->model, "post_id", PostLang::ERR_FIELD_NOT_FOUND);
			});

		$this->specify("lang_id is required", function () {
				$this->_fieldsError($this->model, "lang_id", PostLang::ERR_FIELD_REQUIRED);
			});
		$this->specify("lang_id is excepted to be an integer", function () {
				$this->model->lang_id = "invalid";

				$this->_fieldsError($this->model, "lang_id", PostLang::ERR_FIELD_TYPE);
			});
		$this->specify("lang_id is expected to exists in lang table", function () {
				$this->model->lang_id = 1000;

				$this->_fieldsError($this->model, "lang_id", PostLang::ERR_FIELD_NOT_FOUND);
			});

		$this->specify("post_id and lang_id are expected to be a unique combo", function () {
				$this->model->post_id = 1;
				$this->model->lang_id = Lang::FR;

				$this->_fieldsError($this->model, "post_id", PostLang::ERR_FIELD_NOT_UNIQUE);

				$this->model->post_id = 1;
				$this->model->lang_id = Lang::EN;

				$this->tester->assertTrue($this->model->validate([ "post_id", "lang_id" ]));
			});

		$this->specify("title is required", function () {
				$this->_fieldsError($this->model, "title", PostLang::ERR_FIELD_REQUIRED);
			});
		$this->specify("title is excepted to be a string of less than 255 characters", function () {
				$this->model->title = \Yii::$app->getSecurity()->generateRandomString(256);

				$this->_fieldsError($this->model, "title", PostLang::ERR_FIELD_TOO_LONG);
			});

		$this->specify("slug is excepted to be a string of less than 255 characters", function () {
				$this->model->slug = \Yii::$app->getSecurity()->generateRandomString(256);

				$this->_fieldsError($this->model, "slug", PostLang::ERR_FIELD_TOO_LONG);
			});
		$this->specify("slug is excepted to be unique by language", function () {
				$temp = $this->tester->grabFixture("postLang", "post_lang8");

				$this->model->lang_id = $temp->lang_id;
				$this->model->slug    = $temp->slug;

				$this->_fieldsError($this->model, "slug", PostLang::ERR_FIELD_NOT_UNIQUE);

				$this->model->lang_id = Lang::EN;

				$this->assertTrue($this->model->validate([ "slug" ]));
			});

		$this->specify("summary is excepted to be a string of less than 180 characters", function () {
			$this->model->summary = \Yii::$app->getSecurity()->generateRandomString(181);

			$this->_fieldsError($this->model, "summary", PostLang::ERR_FIELD_TOO_LONG);
		});

		$this->specify("content is expected to be a string", function () {
				$this->model->content = [ "invalid" ];

				$this->_fieldsError($this->model, "content", PostLang::ERR_FIELD_TYPE);
			});

		$this->specify("file_id is expected to be an integer", function () {
				$this->model->file_id = "file-url";

				$this->_fieldsError($this->model, "file_id", PostLang::ERR_FIELD_TYPE);
			});
		$this->specify("file_id is expected to exists in file table", function () {
				$this->model->file_id = 1000;

				$this->_fieldsError($this->model, "file_id", PostLang::ERR_FIELD_NOT_FOUND);
			});

		$this->specify("file_alt is expected to be a string", function () {
				$this->model->file_alt = [ "invalid" ];

				$this->_fieldsError($this->model, "file_alt", PostLang::ERR_FIELD_TYPE);
			});

		$this->specify("valid model", function () {
			$this->model->post_id = 1;
			$this->model->lang_id = Lang::EN;

			$this->model->title   = $this->faker->realText();
			$this->model->slug    = $this->faker->slug();
			$this->model->summary = $this->faker->realText(180);
			$this->model->content = $this->faker->sentences($this->faker->numberBetween(1, 8), true);

			$this->model->file_id  = $this->tester->grabFixture("file", "file2")->id;
			$this->model->file_alt = $this->faker->realText();

			$this->assertTrue($this->model->validate());
		});
	}

	/**
	 * Test creation of a single translation
	 */
	public function testCreate ()
	{
		$this->model = new PostLang();

		$this->specify("not create with invalid post id", function () {
				$result = PostLang::createTranslation(1000, $this->model);

				$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
				$this->tester->assertEquals(PostLang::ERR_POST_NOT_FOUND, $result[ "error" ]);
			});
		$this->specify("not create with invalid lang id", function () {
				$this->model->lang_id = 1000;

				$result = PostLang::createTranslation(1, $this->model);

				$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
				$this->tester->assertEquals(PostLang::ERR_LANG_NOT_FOUND, $result[ "error" ]);
			});
		$this->specify("not create an existing translation", function () {
			$this->model->lang_id = Lang::FR;
			$this->model->title   = $this->faker->text();
			$this->model->content = $this->faker->paragraphs(3, true);

			$result = PostLang::createTranslation(1, $this->model);

			$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
			$this->tester->assertEquals(PostLang::ERR_TRANSLATION_EXISTS, $result[ "error" ]);
		});
		$this->specify("not create with invalid model", function () {
				$this->model->lang_id = Lang::EN;
				$this->model->content = $this->faker->paragraphs(3, true);

				$result = PostLang::createTranslation(5, $this->model);

				$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
				$this->tester->assertTrue(is_array($result[ "error" ]));
				$this->tester->assertArrayHasKey("title", $result[ "error" ]);

			});
		$this->specify("create valid translation", function () {
				$this->model->lang_id = Lang::FR;
				$this->model->title   = $this->faker->text();
				$this->model->content = $this->faker->paragraphs(2, true);
				$this->model->slug    = $this->faker->slug();

				$result = PostLang::createTranslation(5, $this->model);

				$this->tester->assertEquals(PostLang::SUCCESS, $result[ "status" ]);
			});
	}

	/**
	 * Test that all translation of a post are deleted
	 */
	public function testDeleteAll ()
	{
		$this->specify("not delete translations of a published post", function () {
			$model  = $this->tester->grabFixture("postLang", "post_lang12");
			$result = PostLang::deleteTranslations($model->post_id);

			$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
			$this->tester->assertEquals(PostLang::ERR_POST_PUBLISHED, $result[ "error" ]);

			$this->tester->canSeeNumRecords(2, PostLang::tableName(), [ "post_id" => $model->post_id ]);
		});
		$this->specify("delete translations of a post",function () {
			$model  = $this->tester->grabFixture("postLang", "post_lang0");
			$result = PostLang::deleteTranslations($model->post_id);

			$this->tester->assertEquals(PostLang::SUCCESS, $result[ "status" ]);

			$this->tester->cantSeeRecord(PostLang::class, [ "post_id" => $model->post_id ]);
		});
	}

	/**
	 * Test that a single translation can be updated
	 */
	public function testUpdate ()
	{
		$this->model = $this->tester->grabFixture("postLang", "post_lang6");

		$this->specify("not update an invalid translation id", function () {
				$result = PostLang::updateTranslation(1000, Lang::EN, $this->model);

				$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
				$this->tester->assertEquals(PostLang::ERR_NOT_FOUND, $result[ "error" ]);
			});
		$this->specify("not update with invalid model", function () {
				$this->model->slug = $this->tester->grabFixture("postLang", "post_lang12")->slug;

				$result = PostLang::updateTranslation($this->model->post_id, $this->model->lang_id, $this->model);

				$this->tester->assertEquals(PostLang::ERROR, $result[ "status" ]);
				$this->tester->assertArrayHasKey("slug", $result[ "error" ]);
			});
		$this->specify("update valid translation", function () {
				$this->model->slug = $this->faker->slug();

				$result = PostLang::updateTranslation($this->model->post_id, $this->model->lang_id, $this->model);

				$this->tester->assertEquals(PostLang::SUCCESS, $result[ "status" ]);
				$this->tester->canSeeRecord(PostLang::class,
					[
						"post_id" => $this->model->post_id,
						"lang_id" => $this->model->lang_id,
						"slug"    => $this->model->slug,
					]);
			});
	}

	/**
	 * @param PostLang $model
	 * @param string   $field
	 * @param string   $error
	 */
	protected function _fieldsError ( $model, $field, $error )
	{
		$this->tester->assertFalse($model->validate([ $field ]));
		$this->tester->assertContains($error, $model->getErrors($field));
	}
}