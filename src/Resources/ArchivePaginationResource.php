<?php

namespace EvoMark\InertiaWordpress\Resources;

class ArchivePaginationResource
{
    public static $args = [];

    public static function collection(): array
    {
        global $wp_query, $wp_rewrite;

        $baseUrl = html_entity_decode(get_pagenum_link());
        $url_parts = explode('?', $baseUrl);

        $baseUrl = trailingslashit($url_parts[0]) . '%_%';

        $format  = $wp_rewrite->using_index_permalinks() && !strpos($baseUrl, 'index.php') ? 'index.php/' : '';
        $format .= $wp_rewrite->using_permalinks() ? user_trailingslashit($wp_rewrite->pagination_base . '/%#%', 'paged') : '?paged=%#%';


        $args = [
            'base' => $baseUrl,
            'format' => $format,
            'per_page' => get_option('posts_per_page'),
            'add_args' => [],
        ];


        // Merge additional query vars found in the original URL into 'add_args' array.
        if (isset($url_parts[1])) {
            // Find the format argument.
            $format       = explode('?', str_replace('%_%', $args['format'], $baseUrl));

            $format_query = $format[1] ?? '';
            wp_parse_str($format_query, $format_args);


            // Find the query args of the requested URL.
            wp_parse_str($url_parts[1], $url_query_args);

            // Remove the format argument from the array of query arguments, to avoid overwriting custom format.
            foreach ($format_args as $format_arg => $format_arg_value) {
                unset($url_query_args[$format_arg]);
            }


            $args['add_args'] = array_merge($args['add_args'], urlencode_deep($url_query_args));
        }

        self::$args = $args;
        $current = isset($wp_query->query['paged']) ? (int) $wp_query->query['paged'] : 1;
        $total = (int) $wp_query->found_posts;
        $perPage = (int) $args['per_page'];
        $from = (($current - 1) * $perPage) + 1;
        $lastPage = (int) $wp_query->max_num_pages;

        return [
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $current,
            'lastPage' => $lastPage,
            'path' => $url_parts[0],
            'from' => $from,
            'to' => min($total, $from + ($perPage - 1)),
            'firstPageUrl' => self::generatePageLink(1),
            'lastPageUrl' => self::generatePageLink($lastPage),
            'prevPageUrl' => self::generatePageLink($current - 1),
            'nextPageUrl' => self::generatePageLink($current + 1),
        ];
    }

    public static function generatePageLink($page)
    {
        global $wp_query;

        $max = (int) $wp_query->max_num_pages;
        if ($page > $max || $page < 1) {
            return null;
        }

        $link = str_replace('%_%', 1 == $page ? '' : self::$args['format'], self::$args['base']);
        $link = str_replace('%#%', $page, $link);

        return add_query_arg(self::$args['add_args'], $link);
    }
}
