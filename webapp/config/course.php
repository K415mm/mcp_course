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
        'theoretical'   => 'Theoretical',
        'practical'     => 'Practical',
        'examples'      => 'Examples',
        'slides_prompt' => 'Slides & Prompts',
        'diagrams'      => 'Diagrams',   // virtual — resolved from DB
    ],

    /*
    |--------------------------------------------------------------------------
    | GitHub Repository for Workshops
    |--------------------------------------------------------------------------
    | The base URL connecting the Course App to the Colab Notebooks.
    */
    'workshop_github_base_url' => env('WORKSHOP_GITHUB_BASE_URL', 'https://colab.research.google.com/github/K415mm/mcp_course_workshops/blob/main/'),

];
