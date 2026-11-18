<?php

namespace EvoMark\InertiaWordpress\Tests\Unit\Props;

use EvoMark\InertiaWordpress\Contracts\ProvidesScrollMetadata;
use EvoMark\InertiaWordpress\Data\ScrollMetadata;
use EvoMark\InertiaWordpress\Tests\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use WP_Query;

/**
 * WordPress adaptation of Laravel's paginator-based metadata: WP_Query is the paginator,
 * "paged" is the page query var and max_num_pages is the last page.
 *
 * @see https://github.com/inertiajs/inertia-laravel/blob/v2.0.28/tests/ScrollMetadataTest.php
 */
class ScrollMetadataTest extends TestCase
{
    public function test_can_be_constructed_directly(): void
    {
        $metadata = new ScrollMetadata('custom', 1, 3, 2);

        $this->assertInstanceOf(ProvidesScrollMetadata::class, $metadata);
        $this->assertSame([
            'pageName' => 'custom',
            'previousPage' => 1,
            'nextPage' => 3,
            'currentPage' => 2,
        ], $metadata->toArray());
    }

    /**
     * @return array<string, array{0: int|null, 1: int, 2: array<string, mixed>}>
     */
    public static function queryPages(): array
    {
        return [
            'first page (paged unset)' => [null, 3, ['previousPage' => null, 'nextPage' => 2, 'currentPage' => 1]],
            'first page (paged = 0)' => [0, 3, ['previousPage' => null, 'nextPage' => 2, 'currentPage' => 1]],
            'middle page' => [2, 3, ['previousPage' => 1, 'nextPage' => 3, 'currentPage' => 2]],
            'last page' => [3, 3, ['previousPage' => 2, 'nextPage' => null, 'currentPage' => 3]],
            'single page' => [null, 1, ['previousPage' => null, 'nextPage' => null, 'currentPage' => 1]],
            'no results' => [null, 0, ['previousPage' => null, 'nextPage' => null, 'currentPage' => 1]],
        ];
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[DataProvider('queryPages')]
    public function test_extract_metadata_from_a_wp_query(?int $paged, int $maxPages, array $expected): void
    {
        $query = new WP_Query($paged === null ? [] : ['paged' => $paged]);
        $query->max_num_pages = $maxPages;

        $this->assertSame(
            ['pageName' => 'paged', ...$expected],
            ScrollMetadata::fromQuery($query)->toArray()
        );
    }

    public function test_throws_exception_if_not_a_query(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The given value is not a WP_Query instance. Use a custom callback to extract pagination metadata.');

        ScrollMetadata::fromQuery(collect());
    }
}
