<?php

namespace EvoMark\InertiaWordpress\Commands;

use WP_CLI;
use Symfony\Component\Process\Process;
use EvoMark\InertiaWordpress\Container;
use EvoMark\InertiaWordpress\Helpers\Path;
use EvoMark\InertiaWordpress\Helpers\Strings;
use Symfony\Component\Process\Exception\ProcessFailedException;

defined('\\ABSPATH') || exit;

class CreateThemeCommand extends BaseCommand
{
    public array $replacements;

    /**
     * Create an Inertia-based theme
     *
     * ## OPTIONS
     *
     * [<name>]
     * : The name of the theme folder to create
     *
     * @when after_wp_load
     */
    public function __invoke($args, $assocArgs)
    {
        $name = $args[0] ?? $this->ask(
            'What should the theme be called?',
        );
        $template = $this->choice("Which template should be used?", ['Vue'], 0);
        $template = strtolower($template);

        $slug = sanitize_title($name);
        $this->replacements = [
            '##NAMESPACE##' => Strings::toPascalCase($name),
            '##SLUG##' => $slug,
            '##THEME_TITLE##' => $name
        ];

        $themesDir = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'themes';
        $themeDir = Path::join($themesDir, $slug);
        wp_mkdir_p($themeDir);

        $container = Container::getInstance();
        $stubsPath = Path::join($container->get('env.root'), 'stubs', 'theme');

        // STYLE.CSS
        $this->copyStub(Path::join($stubsPath, 'style.css.stub'), Path::join($themeDir, 'style.css'));

        // INDEX.PHP
        $this->copyStub(Path::join($stubsPath, 'index.php.stub'), Path::join($themeDir, 'index.php'));

        // VITE.CONFIG
        $this->copyStub(Path::join($stubsPath, 'vite.config.' . $template . '.stub'), Path::join($themeDir, 'vite.config.js'));

        // PM2 ECOSYSTEM
        $this->copyStub(Path::join($stubsPath, 'ecosystem.config.cjs.stub'), Path::join($themeDir, 'ecosystem.config.cjs'));

        // PACKAGE.JSON
        $this->copyStub(Path::join($stubsPath, 'package.json.' . $template . '.stub'), Path::join($themeDir, 'package.json'));

        // COMPOSER.JSON
        $this->copyStub(Path::join($stubsPath, 'composer.json.stub'), Path::join($themeDir, 'composer.json'));

        // CONTROLLERS
        wp_mkdir_p(Path::join($themeDir, 'controllers'));
        $controllers = glob(Path::join($stubsPath, 'controllers', '*.php.stub'));
        foreach ($controllers as $controller) {
            $filename = basename($controller, '.stub'); // Remove the .stub extension
            $targetFile = Path::join($themeDir, 'controllers', $filename);

            $content = file_get_contents($controller);

            if ($content === false) {
                throw new \RuntimeException("Failed to copy file: $controller to $targetFile");
            }

            $content = $this->applyReplacements($content);

            if (file_put_contents($targetFile, $content) === false) {
                throw new \RuntimeException("Failed to write to file: $targetFile");
            }
        }

        // APP PHP FILE
        $this->copyStub(Path::join($stubsPath, 'app.php.stub'), Path::join($themeDir, 'app.php'));

        // FUNCTIONS PHP FILE
        $this->copyStub(Path::join($stubsPath, 'functions.php.stub'), Path::join($themeDir, 'functions.php'));

        // REST API EXAMPLE
        wp_mkdir_p(Path::join($themeDir, 'rest-api'));
        $this->copyStub(Path::join($stubsPath, 'rest-api', 'ExamplePost.php.stub'), Path::join($themeDir, 'rest-api', 'ExamplePost.php'));

        // MAIN JS
        wp_mkdir_p(Path::join($themeDir, 'resources', 'js'));
        wp_mkdir_p(Path::join($themeDir, 'resources', 'js', 'pages'));

        $this->copyStub(Path::join($stubsPath, 'resources', 'js', 'main.js.' . $template . '.stub'), Path::join($themeDir, 'resources', 'js', 'main.js'));
        $this->copyStub(Path::join($stubsPath, 'resources', 'js', 'ssr.js.' . $template . '.stub'), Path::join($themeDir, 'resources', 'js', 'ssr.js'));

        // COMPOSER INSTALL
        $process = new Process(['composer', 'install'], $themeDir);
        $process->setTimeout(300);
        try {
            $process->mustRun();
            echo $process->getOutput();
        } catch (ProcessFailedException $exception) {
            WP_CLI::log($exception->getMessage());
            WP_CLI::log("Unable to initiate composer in theme folder, please run `composer install` before use.");
        }

        WP_CLI::runcommand('theme activate ' . $slug);
        return true;
    }

    private function applyReplacements(string $content): string
    {
        foreach ($this->replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        return $content;
    }

    private function copyStub(string $from, string $to): void
    {
        $content = file_get_contents($from);
        $content = $this->applyReplacements($content);
        file_put_contents($to, $content);
    }
}
