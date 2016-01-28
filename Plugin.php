<?php namespace Vdomah\BlogViews;

use Db;
use Session;
use System\Classes\PluginBase;
use Rainlab\Blog\Components\Post as PostComponent;
use Rainlab\Blog\Models\Post as PostModel;

/**
 * BlogViews Plugin Information File
 */
class Plugin extends PluginBase
{
    /**
     * @var array   Require the RainLab.Blog plugin
     */
    public $require = ['RainLab.Blog'];

    /**
     * @var string   Table to store post views count
     */
    public $table_views = 'vdomah_blogviews_views';

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'Blog Views',
            'description' => 'The plugin counts post views, displays them and adds popular posts widget.',
            'author'      => 'Art Gek',
            'icon'        => 'icon-signal'
        ];
    }

    /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'Vdomah\BlogViews\Components\Views' => 'Views',
            'Vdomah\BlogViews\Components\Popular' => 'popularPosts',
        ];
    }

    public function boot()
    {
        if ($this->app->runningInBackend())
            return;

        PostComponent::extend(function($component) {

            if (!Session::has('postsviewed')) {
                Session::put('postsviewed', []);
            }

            $post = PostModel::where('slug', $component->getController()->getRouter()->getParameters()['slug'])->first();

            if (!is_null($post) && !in_array($post->getKey(), Session::get('postsviewed'))) {
                $obj = Db::table($this->table_views)
                    ->where('post_id', $post->getKey());

                if ($obj->count() > 0) {
                    $row = $obj->first();
                    $obj->update(['views' => ++$row->views]);
                } else {
                    Db::table($this->table_views)->insert([
                        'post_id' => $post->getKey(),
                        'views' => 1,
                    ]);
                }

                Session::push('postsviewed', $post->getKey());
            }

            return true;
        });
    }

}
