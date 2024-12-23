<?php

namespace EvoMark\InertiaWordpress\Data;

class Archive
{
    public function __construct(public string $title, public $items, public $pagination)
    {
    }
}
