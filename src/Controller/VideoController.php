<?php

declare(strict_types=1);

namespace Controller;

use Normalizer\TagsForVideo;
use Repository\ArtistRepository;
use Repository\TagRepository;
use Repository\VideoRepository;

class VideoController extends BaseController
{
    private $videoRepository;

    private $tagRepository;

    private $artistRepository;

    public function __construct()
    {
        $this->videoRepository = new VideoRepository();
        $this->tagRepository = new TagRepository();
        $this->artistRepository = new ArtistRepository();
    }

    public function create()
    {
        $body = file_get_contents('php://input');
        $data = json_decode($body);

        $videoId = $this->videoRepository->create($data);

        $artistId = $this->artistRepository->find($data->artist);

        if(!$artistId){
            $artistId = $this->artistRepository->create($data->artist);
        }

        $this->videoRepository->assignToArtist($artistId, $videoId);

        $data->artist_id = $artistId;
        $data->video_id = $videoId;

        $this->responseCreated($data);
    }

    public function list()
    {
        $videos = $this->videoRepository->findAll();

        $this->response($videos);
    }

    public function show(string $youtubeId)
    {
        $tags = $this->videoRepository->find($youtubeId);

        $this->response(reset($tags));
    }

    public function highestNumberOfTags()
    {
        $videos = $this->videoRepository->withHighestNumberOfTags();

        $this->response($videos);
    }

    public function newestTen()
    {
        $videos = $this->videoRepository->newestTen();

        $this->response($videos);
    }

    public function tags(string $youtubeId)
    {
        $tags = $this->tagRepository->findByVideo($youtubeId);
        $tags = (new TagsForVideo())->normalize($tags);

        $this->response($tags);
    }
}