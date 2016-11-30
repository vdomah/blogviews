<?php

return [
    'plugin' => [
        'name' => 'Blog Views',
        'description' => 'The plugin enables blog posts views tracking and displaying popular articles.'
    ],
    'settings' => [
        'posts_limit' => 'Limit',
        'posts_limit_validation' => 'Invalid format of the limit value',
        'posts_no_posts' => 'No posts message',
        'posts_no_posts_description' => 'Message to display in the blog post popular list in case if there are no posts. This property is used by the default component partial.',
        'posts_post' => 'Post page',
        'posts_post_description' => 'Name of the blog post page file for the "Learn more" links. This property is used by the default component partial.'
    ],
    'post' => [
        'tab_views' => 'Views'
    ],
    'component' => [
        'popular_name' => 'Popular Posts',
        'popular_description' => 'Most viewed posts list',
        'views_name' => 'Post Views',
        'views_description' => 'Show post views'
    ]
];
