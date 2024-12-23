<?php

namespace EvoMark\InertiaWordpress\Data;

class MessageBag
{
    public array $messages = [];
    public string $format = ":message";

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public function messages(): array
    {
        return $this->messages;
    }
}
