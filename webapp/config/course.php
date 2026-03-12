<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Course Content Path
    |--------------------------------------------------------------------------
    | Absolute path to the root of the course content directory.
    | Each Module_XX_* or Workshop_XX_* sub-directory is a section.
    */
    'content_path' => env('COURSE_CONTENT_PATH', base_path('../../mcp_course/corse')),

    /*
    |--------------------------------------------------------------------------
    | Course Title
    |--------------------------------------------------------------------------
    */
    'title' => env('COURSE_TITLE', 'MCP Cyber Defense Academy'),

    /*
    |--------------------------------------------------------------------------
    | Known lesson section folders (in display order)
    |--------------------------------------------------------------------------
    */
    'sections' => [
        'theoretical' => 'Theoretical',
        'practical'   => 'Practical',
        'examples'    => 'Examples',
        'slides_prompt' => 'Slides & Prompts',
    ],

];
