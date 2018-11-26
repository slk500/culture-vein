<?php

declare(strict_types=1);

namespace Normalizer;

use DTO\VideoTagRaw;

final class VideoTagNormalizer
{

    //todo to complex -> make it simple
    public function normalize(array $array): array
    {
        $previous_tag_slug_id = '';
        $lastKey = null;
        $result = [];

        /**
         * @var $video_tag VideoTagRaw
         */
        foreach ($array as $key => $video_tag) {

            if($previous_tag_slug_id === $video_tag->tag_slug_id){

                $result[$lastKey]['video_tags_time'][]=[
                    'video_tag_time_id' => $video_tag->video_tag_time_id,
                    'start' => $video_tag->start,
                    'stop' => $video_tag->stop
                ];

                $previous_tag_slug_id = $video_tag->tag_slug_id;

            }

            else {
                $video_tag_normalize = [
                    'video_tag_id' => $video_tag->video_tag_id,
                    'video_youtube_id' => $video_tag->video_youtube_id,
                    'tag_name' => $video_tag->tag_name,
                    'tag_slug_id' => $video_tag->tag_slug_id,
                    'is_complete' => $video_tag->is_complete,
                    'video_tags_time' => []
                ];

                if($video_tag->video_tag_time_id) {
                    $video_tag_normalize['video_tags_time'][] = [
                        'video_tag_time_id' => $video_tag->video_tag_time_id,
                        'start' => $video_tag->start,
                        'stop' => $video_tag->stop
                    ];
                }

                $previous_tag_slug_id = $video_tag->tag_slug_id;
                $result [] = $video_tag_normalize;
            }
            $lastKey = count($result)-1; //array_key_last -> waiting for PHP 7.3 :D
        }
        return array_values($result); //have to do this -> angular will throw error otherwise
    }


}