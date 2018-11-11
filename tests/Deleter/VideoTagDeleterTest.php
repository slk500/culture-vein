<?php

declare(strict_types=1);

namespace Tests\Deleter;

use Factory\VideoFactory;
use Factory\VideoTagFactory;
use Model\Tag;
use Repository\TagRepository;
use Repository\VideoTagRepository;
use Tests\Builder\VideoCreateBuilder;
use Tests\Builder\VideoTagCreateBuilder;
use Deleter\VideoTagDeleter;
use PHPUnit\Framework\TestCase;
use Service\DatabaseHelper;

class VideoTagDeleterTest extends TestCase
{
    /**
     * @var VideoTagRepository
     */
    private $video_tag_repository;

    /**
     * @var VideoTagDeleter
     */
    private $video_tag_deleter;

    public static function setUpBeforeClass()
    {
        (new DatabaseHelper())->truncate_all_tables();
    }

    public function setUp()
    {
        $this->video_tag_repository = new VideoTagRepository();
        $this->video_tag_deleter = new VideoTagDeleter();

        (new DatabaseHelper())->truncate_all_tables();
    }

    /**
     * @test
     * @covers \Deleter\VideoTagDeleter::delete()
     */
    public function delete_ARCHIVE_one_video_tag_IF_two_with_time_range_exist()
    {
        $tag = new Tag('tag name');

        (new TagRepository())->create($tag);

        $video_create = (new VideoCreateBuilder())->build();
        (new VideoFactory())->create($video_create);

        $video_tag_create = (new VideoTagCreateBuilder())->build();
        (new VideoTagFactory())->create($video_tag_create);

        $video_tag_create = (new VideoTagCreateBuilder())->build();
        (new VideoTagFactory())->create($video_tag_create);

        $video_tags = $this->video_tag_repository->find_all_for_video($video_create->youtube_id);
        $this->assertCount(2, $video_tags);

        $this->video_tag_deleter->delete(1);

        $video_tags = $this->video_tag_repository->find_all_for_video($video_create->youtube_id);
        $this->assertCount(1, $video_tags);


    }

    /**
     * @test
     * @covers \Deleter\VideoTagDeleter::delete()
     */
    public function delete_ARCHIVE_video_tag_IF_only_one_video_tag_exist_and_time_range_is_null()
    {
        $tag = new Tag('tag name');

        (new TagRepository())->create($tag);

        $video_create = (new VideoCreateBuilder())->build();
        (new VideoFactory())->create($video_create);

        $video_tag_create = (new VideoTagCreateBuilder())
            ->start(null)
            ->stop(null)
            ->build();
        (new VideoTagFactory())->create($video_tag_create);

        $this->video_tag_deleter->delete(1);

        $video_tags = $this->video_tag_repository->find_all_for_video($video_create->youtube_id);

        $this->assertCount(0, $video_tags);
    }

    /**
     * @test
     * @covers \Deleter\VideoTagDeleter::delete()
     */
    public function delete_SET_time_range_null_IF_only_one_video_tag_exist_and_time_range_is_not_null()
    {
        $tag = new Tag('tag name');

        (new TagRepository())->create($tag);

        $video_create = (new VideoCreateBuilder())->build();
        (new VideoFactory())->create($video_create);

        $video_tag_create = (new VideoTagCreateBuilder())->build();
        (new VideoTagFactory())->create($video_tag_create);

        $result = $this->video_tag_repository->find_all_for_video($video_create->youtube_id);
        $video_tag = end($result);

        $this->assertSame(0, $video_tag->start);
        $this->assertSame(20, $video_tag->stop);

        $this->video_tag_deleter->delete($video_tag->video_tag_id);

        $result = $this->video_tag_repository->find_all_for_video($video_create->youtube_id);
        $video_tag_after_delete = end($result);

        $this->assertNull($video_tag_after_delete->start);
        $this->assertNull($video_tag_after_delete->stop);
    }
}