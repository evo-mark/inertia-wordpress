<?php

namespace EvoMark\InertiaWordpress\Resources;

use EvoMark\InertiaWordpress\Contracts\InertiaResource;
use stdClass;

class CommentResource implements InertiaResource
{
    public static function collection(?array $comments): array
    {
        if (empty($comments) || !$comments) {
            return [];
        } else {
            return array_map(fn ($c) => self::single($c), array_values($comments));
        }
    }

    public static function single($comment): stdClass
    {
        if (is_int($comment)) {
            $comment = get_comment($comment);
        }

        return (object) [
            'id'        => intval($comment->comment_ID),
            'author'    => $comment->comment_author,
            'authorUrl' => $comment->comment_author_url,
            'authorAvatar' => get_avatar_url($comment->comment_author_email),
            'karma'     => intval($comment->comment_karma),
            'content'   => htmlspecialchars($comment->comment_content, ENT_QUOTES, 'UTF-8'),
            'date'      => $comment->comment_date_gmt,
            'children'  => self::collection($comment->get_children()),
        ];
    }
}
