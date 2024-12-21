<?php

namespace EvoMark\InertiaWordpress\Resources;

class ImageResource
{
    public static function single(?int $attachment_id = null, array $args = [])
    {
        $fallbackToPostImage = !isset($args['fallback']) || $args['fallback'] !== false;

        $sizes = get_intermediate_image_sizes();
        $sizes[] = 'full';
        if (!$attachment_id && $fallbackToPostImage) $attachment_id = get_post_thumbnail_id();

        $images = array();
        foreach ($sizes as $size) {
            $src = wp_get_attachment_image_src($attachment_id, $size);
            if (empty($src)) return null;

            $images[$size] = [
                'url'        => $src[0] ?? null,
                'width'      => $src[1] ?? null,
                'height'     => $src[2] ?? null,
                'is_resized' => $src[3] ?? false
            ];
        }
        $imageObject =  (object) [
            'sizes' => $images,
            'metadata' => self::getImageMetadata($attachment_id),
            'exists' => !empty($attachment_id)
        ];

        return $imageObject;
    }

    public static function collection(array $attachment_ids = [], array $args = [])
    {
        return array_map(fn($id) => self::single($id, $args), $attachment_ids);
    }

    public static function getImageMetadata($id)
    {
        $meta = [];
        $meta['alt'] = get_post_meta($id, '_wp_attachment_image_alt', true);
        $meta['title'] = get_post_field('post_title', $id);
        $meta['caption'] = get_post_field('post_excerpt', $id);
        $meta['description'] = get_post_field('post_content', $id);
        $meta['name'] = get_post_field('post_name', $id);
        $meta['type'] = get_post_field('post_type', $id);
        $meta['mime'] = get_post_field('post_mime_type', $id);

        return $meta;
    }
}
