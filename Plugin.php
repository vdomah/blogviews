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
            'name'        => 'vdomah.blogviews::lang.plugin.name',
            'description' => 'vdomah.blogviews::lang.plugin.description',
            'author'      => 'Art Gek',
            'icon'        => 'icon-signal',
            'homepage'    => 'https://github.com/vdomah/blogviews'
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
            'Vdomah\BlogViews\Components\Views'   => 'views',
            'Vdomah\BlogViews\Components\Popular' => 'popularPosts'
        ];
    }

    public function boot()
    {
        PostComponent::extend(function($component) {
            if ($this->app->runningInBackend()) {
                return;
            }
            // getting slug value using logic from Cms\Classes\Controller setComponentPropertiesFromParams
            $routerParameters = $component->getController()->getRouter()->getParameters();
            $slugValue = $component->property('slug');
            $slugValueFromUrl = null;

            if (preg_match('/^\{\{([^\}]+)\}\}$/', $slugValue, $matches)) {
                $paramName = trim($matches[1]);

                if (substr($paramName, 0, 1) == ':') {
                    $routeParamName = substr($paramName, 1);
                    $slugValueFromUrl = array_key_exists($routeParamName, $routerParameters)
                        ? $routerParameters[$routeParamName]
                        : null;

                }
            }

            if (!$slugValueFromUrl)
                return;

            if (!Session::has('postsviewed')) {
                Session::put('postsviewed', []);
            }

            $post = PostModel::where('slug', $slugValueFromUrl)->first();

            if (!is_null($post) && !in_array($post->getKey(), Session::get('postsviewed'))) {
                $this->setViews($post);

                Session::push('postsviewed', $post->getKey());
            }

            return true;
        });

        PostModel::extend(function($model) {
            $model->addDynamicMethod('getViewsAttribute', function() use ($model) {
                $obj = Db::table('vdomah_blogviews_views')
                    ->where('post_id', $model->getKey());

                $out = 0;
                if ($obj->count() > 0) {
                    $out = $obj->first()->views;
                }

                return $out;
            });
        });
    }

    public function setViews($post, $views = null)
    {
        $obj = Db::table($this->table_views)
            ->where('post_id', $post->getKey());

        if ($obj->count() > 0) {
            $row = $obj->first();
            if (!$views) {
                $views = ++$row->views;
            }
            $obj->update(['views' => $views]);
        } else {
            if (!$views) {
                $views = 1;
            }
            Db::table($this->table_views)->insert([
                'post_id' => $post->getKey(),
                'views'   => $views
            ]);
        }
    }
}
