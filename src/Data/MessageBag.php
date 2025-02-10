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

    /**
     * Update a message inside the bag
     */
    public function update(string $key, string $value): static
    {
        $this->messages[$key] = [$value];

        return $this;
    }

    public function remove(string $key): static
    {
        unset($this->messages[$key]);

        return $this;
    }
}
