<?php

require_once '../vendor/autoload.php';

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class MyClient
{
    private $oauth;

    private Client $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client();
        $parameters = include(__DIR__.'/oauth.php');
        $this->oauth = $parameters['oauth'];
    }

    function add_video(string $playlist_id, string $video_id): ResponseInterface
    {
        return $this->client->request(
            'POST',
            'https://youtube.googleapis.com/youtube/v3/playlistItems?part=snippet',
            [
                'json' => [
                    'snippet' =>
                        [
                            'playlistId' => $playlist_id,
                            "resourceId" => [
                                "kind" => "youtube#video",
                                "videoId" => $video_id
                            ]
                        ]
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->oauth,
                    'Content-Type' => 'application/json'
                ]
            ]
        );
    }

    function create_playlist(string $tag_name, string $tag_slug): ResponseInterface
    {
        $date = date('Y-m-d');
        $description = "This playlist have music videos that have tag: $tag_name in it. The tag may be an action, a scene, a person, a word or phrase, an object, a gesture, or a cliche or trope.
Music videos are order by descending duration of a tag. In. ex. first video have more of $tag_name than the second.
This playlist was autogenerated on ($date) from https://culturevein.com/tags/$tag_slug
If you want to add video to this playlist please do it at https://culturevein.com";

        return $this->client->request(
            'POST',
            'https://youtube.googleapis.com/youtube/v3/playlists?part=snippet,status',
            [
                'json' => [
                    'snippet' =>
                        [
                            'title' => "$tag_name in music video",
                            'description' => $description
                        ],
                    'status' => [
                        'privacyStatus' => 'public'
                    ]
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->oauth,
                    'Content-Type' => 'application/json'
                ]
            ]
        );
    }
}