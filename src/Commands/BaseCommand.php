<?php

namespace EvoMark\InertiaWordpress\Commands;

use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;

abstract class BaseCommand
{
    protected ArgvInput $input;
    protected ConsoleOutput $output;
    protected QuestionHelper $helper;

    public function __construct()
    {
        $this->input = new ArgvInput();
        $this->output = new ConsoleOutput();
        $this->helper = new QuestionHelper();
    }
    /**
     * Ask the user for input and return it
     */
    protected function ask(string $question, ?callable $validationCallback = null): string
    {
        $q = new Question(rtrim($question) . ' ');
        if (!empty($validationCallback)) {
            $q->setValidator(function (string $answer) use ($validationCallback): string {
                $isValid = $validationCallback($answer);
                if ($isValid !== true) {
                    throw new \RuntimeException($isValid);
                }

                return $answer;
            });
        }
        return $this->helper->ask($this->input, $this->output, $q);
    }

    protected function choice(string $question, array $choices, int $default = 0)
    {
        $q = new ChoiceQuestion($question, $choices, $default);

        return $this->helper->ask($this->input, $this->output, $q);
    }
}
