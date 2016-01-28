<?php namespace Vdomah\BlogViews\Components;

use Cms\Classes\ComponentBase;
use Rainlab\Blog\Models\Post as BlogPost;

class Popular extends ComponentBase
{
    /**
     * @var Rainlab\Blog\Models\Post The post model used for display.
     */
    public $posts;

    public function componentDetails()
    {
        return [
            'name'        => 'Popular Posts',
            'description' => 'Most viewed posts list'
        ];
    }

    public function defineProperties()
    {
        return [
            'postsLimit' => [
                'title'             => 'vdomah.blogviews::lang.settings.posts_limit',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'vdomah.blogviews::lang.settings.posts_limit_validation',
                'default'           => '3',
            ],
        ];
    }

    public function onRun()
    {
        $this->posts = $this->page['posts'] = $this->listPosts();
    }

    protected function listPosts()
    {
        /*
         * List all the posts
         */
        $posts = BlogPost::leftJoin('vdomah_blogviews_views as pv', 'pv.post_id', '=', 'rainlab_blog_posts.id')
            ->orderBy('views', 'DESC')
            ->get()
        ;

        return $posts;
    }

}