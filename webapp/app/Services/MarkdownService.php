<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;

class MarkdownService
{
    protected MarkdownConverter $converter;

    public function __construct()
    {
        $environment = new Environment([
            'html_input' => 'allow',   // allow HTML inside .md (for iframes/images)
            'allow_unsafe_links' => false,
            'max_nesting_level' => 100,
        ]);
        $environment->addExtension(new \League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * Parse YAML front-matter from markdown content.
     * Returns ['meta' => [...], 'body' => '...raw markdown body...']
     */
    public function parseFrontMatter(string $content): array
    {
        $meta = ['type' => 'lesson'];
        $body = $content;

        if (str_starts_with(ltrim($content), '---')) {
            $content = ltrim($content);
            $end = strpos($content, '---', 3);
            if ($end !== false) {
                $yaml = substr($content, 3, $end - 3);
                $body = ltrim(substr($content, $end + 3));
                // Parse simple key: value YAML (avoid needing symfony/yaml)
                foreach (explode("\n", trim($yaml)) as $line) {
                    $line = trim($line);
                    if (empty($line))
                        continue;
                    $colonPos = strpos($line, ':');
                    if ($colonPos === false)
                        continue;
                    $key = trim(substr($line, 0, $colonPos));
                    $value = trim(substr($line, $colonPos + 1));
                    // Strip surrounding quotes
                    $value = trim($value, '"\'');
                    $meta[$key] = $value;
                }
            }
        }

        return ['meta' => $meta, 'body' => $body];
    }

    /**
     * Render a markdown file to HTML, parsing front-matter.
     * Returns ['type', 'meta', 'html', 'raw']
     */
    public function renderFile(string $absolutePath): string
    {
        if (!File::exists($absolutePath)) {
            return '<p class="text-danger">Content file not found.</p>';
        }
        $content = File::get($absolutePath);
        ['body' => $body] = $this->parseFrontMatter($content);
        return (string) $this->converter->convert($body);
    }

    /**
     * Full parse: returns type + meta + rendered HTML + raw body for admin.
     */
    public function parseFile(string $absolutePath): array
    {
        if (!File::exists($absolutePath)) {
            return ['type' => 'lesson', 'meta' => [], 'html' => '', 'raw' => ''];
        }
        $content = File::get($absolutePath);
        ['meta' => $meta, 'body' => $body] = $this->parseFrontMatter($content);
        $html = (string) $this->converter->convert($body);
        return [
            'type' => $meta['type'] ?? 'lesson',
            'meta' => $meta,
            'html' => $html,
            'raw' => $content,
            'body' => $body,
        ];
    }

    /**
     * Render a markdown string to HTML.
     */
    public function renderString(string $markdown): string
    {
        return (string) $this->converter->convert($markdown);
    }
}
