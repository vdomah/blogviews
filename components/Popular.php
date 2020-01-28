<?php namespace Vdomah\BlogViews\Components;

use Cms\Classes\ComponentBase;
use Rainlab\Blog\Models\Post as BlogPost;
use Rainlab\Blog\Models\Category as BlogCategory;
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
            'category' => [
                'title' => 'vdomah.blogviews::lang.properties.category',
                'type' => 'dropdown',
                'default' => '{{ :category }}',
            ],
            'postsLimit' => [
                'title'             => 'vdomah.blogviews::lang.properties.posts_limit',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'vdomah.blogviews::lang.properties.posts_limit_validation',
                'default'           => '3',
            ],
            'noPostsMessage' => [
                'title'        => 'vdomah.blogviews::lang.properties.posts_no_posts',
                'description'  => 'vdomah.blogviews::lang.properties.posts_no_posts_description',
                'type'         => 'string',
                'default'      => 'No posts found',
                'showExternalParam' => false
            ],
            'postPage' => [
                'title'       => 'vdomah.blogviews::lang.properties.posts_post',
                'description' => 'vdomah.blogviews::lang.properties.posts_post_description',
                'type'        => 'dropdown',
                'default'     => 'blog/post',
                'group'       => 'Links',
            ],
        ];
    }

    public function getCategoryOptions()
    {
        return array_merge(
            [
                null => e(trans('vdomah.blogviews::lang.properties.all_option')),
                0 => e(trans('vdomah.blogviews::lang.properties.no_option'))
            ],
            BlogCategory::lists('name', 'slug')
        );
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
        $query = BlogPost::isPublished()
            ->leftJoin('vdomah_blogviews_views as pv', 'pv.post_id', '=', 'rainlab_blog_posts.id')
        ;

        $category_slug = $this->property('category');
        if ((is_string($category_slug) && strlen($category_slug) == 0) || $category_slug === false)
            $category_slug = null;

        if ($category_slug !== null) {
            if ($category_slug == 0)
                $query = $query->has('categories', '=', 0);
            elseif ($category_slug > 0)
                $query->whereHas('categories', function($q) use ($category_slug) {
                    $q->where('slug', $category_slug);
                });
        }

        $query = $query->orderBy('views', 'DESC')
            ->limit($this->postsLimit)
        ;

        $posts = $query->get();

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
