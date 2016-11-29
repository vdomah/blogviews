<?php namespace Vdomah\BlogViews\Components;

use Cms\Classes\ComponentBase;
use Rainlab\Blog\Models\Post as BlogPost;
use Cms\Classes\Page;

class Popular extends ComponentBase
{
    /**
     * @var Rainlab\Blog\Models\Post The post model used for display.
     */
    public $posts;

    /**
     * Message to display when there are no posts.
     * @var string
     */
    public $noPostsMessage;

    /**
     * Reference to the page name for linking to posts.
     * @var string
     */
    public $postPage;

    /**
     * The max number of posts to show.
     * @var string
     */
    public $postsLimit;

    public function componentDetails()
    {
        return [
            'name'        => 'vdomah.blogviews::lang.component.popular_name',
            'description' => 'vdomah.blogviews::lang.component.popular_description'
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
            'noPostsMessage' => [
                'title'        => 'vdomah.blogviews::lang.settings.posts_no_posts',
                'description'  => 'vdomah.blogviews::lang.settings.posts_no_posts_description',
                'type'         => 'string',
                'default'      => 'No posts found',
                'showExternalParam' => false
            ],
            'postPage' => [
                'title'       => 'vdomah.blogviews::lang.settings.posts_post',
                'description' => 'vdomah.blogviews::lang.settings.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
                'group'       => 'Links',
            ]
        ];
    }

    public function onRun()
    {
        $this->prepareVars();

        $this->posts = $this->page['posts'] = $this->listPosts();
    }

    protected function listPosts()
    {
        /*
         * List all the posts
         */
        $posts = BlogPost::leftJoin('vdomah_blogviews_views as pv', 'pv.post_id', '=', 'rainlab_blog_posts.id')
            ->orderBy('views', 'DESC')
            ->limit($this->postsLimit)
            ->get()
        ;

        $posts->each(function($post) {
            $post->setUrl($this->postPage, $this->controller);
        });

        return $posts;
    }

    protected function prepareVars()
    {
        $this->postsLimit = $this->page['postsLimit'] = $this->property('postsLimit');
        $this->noPostsMessage = $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        /*
         * Page links
         */
        $this->postPage = $this->page['postPage'] = $this->property('postPage');
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }
}
