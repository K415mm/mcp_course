<?php

$dir = 'd:\mcp_course\corse';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

$count = 0;
foreach ($iterator as $file) {
    if ($file->isFile() && strtolower($file->getExtension()) === 'md') {
        $path = $file->getRealPath();
        $content = file_get_contents($path);

        $status = 'draft';

        if (str_starts_with(ltrim($content), '---')) {
            $trimmed = ltrim($content);
            $end = strpos($trimmed, '---', 3);
            if ($end !== false) {
                $yaml = substr($trimmed, 3, $end - 3);
                $body = substr($trimmed, $end + 3);

                // Check if status line already exists
                if (!preg_match('/^status\s*:.+$/mi', $yaml)) {
                    $yaml = rtrim($yaml) . "\nstatus: {$status}\n";
                    $newContent = "---{$yaml}---{$body}";
                    file_put_contents($path, $newContent);
                    $count++;
                }
            }
        } else {
            // No front-matter exists — add it
            $newContent = "---\nstatus: {$status}\n---\n\n{$content}";
            file_put_contents($path, $newContent);
            $count++;
        }
    }
}

echo "Updated $count files.\n";
