<?php namespace Vdomah\BlogViews;

use Db;
use Cookie;
use Event;
use System\Classes\PluginBase;
use Rainlab\Blog\Components\Post as PostComponent;
use Rainlab\Blog\Models\Post as PostModel;
use Cms\Classes\Controller;
use DeviceDetector\Parser\Bot as BotParser;
use Vdomah\BlogViews\Components\Popular;
use Vdomah\BlogViews\Components\Views;

/**
 * BlogViews Plugin Information File
 */
class Plugin extends PluginBase
{
    const POST_VIEWED = 'vdomah-blog-post-viewed-';
    const EVENT_BEFORE_TRACK = 'blogviews.before.track';
    const PARAM_TRACK_PREVENT = 'trackPrevent';
    const PARAM_TRACK_BOT = 'trackBot';

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
            Views::class   => 'views',
            Popular::class => 'popularPosts'
        ];
    }

    public function boot()
    {
        PostComponent::extend(function($component) {
            if ($this->app->runningInBackend() || !Controller::getController()) {
                return;
            }

            $result = Event::fire(self::EVENT_BEFORE_TRACK, [$this, $component]);

            $preventTrack = isset($result[0][self::PARAM_TRACK_PREVENT]) && $result[0][self::PARAM_TRACK_PREVENT];

            if ($preventTrack)
                return;

            $trackBot = isset($result[0][self::PARAM_TRACK_BOT]) && $result[0][self::PARAM_TRACK_BOT];

            if ($this->isBot() && !$trackBot) {
                // do not do anything if a bot is detected
                return;
            }

            $post = $this->getPost($component);

            if (!is_null($post)) {
                $this->track($post);
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

    private function setViews($post, $views = null)
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

    private function isBot()
    {
        $botParser = new BotParser();
        $botParser->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        $botParser->discardDetails();

        return $botParser->parse();
    }

    /*
     * Getting slug value using logic from Cms\Classes\Controller setComponentPropertiesFromParams
     *
     * @return PostModel
     */
    private function getPost($component)
    {
        $slugValue = $component->property('slug');
        $routerParameters = Controller::getController()->getRouter()->getParameters();

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
            return null;

        $post = PostModel::whereSlug($slugValueFromUrl)->first();

        return $post;
    }

    private function track($post)
    {
        $cookName = self::POST_VIEWED . $post->getKey();

        if (Cookie::get( $cookName, 0 ) == 0) {
            $this->setViews($post);

            Cookie::queue( $cookName, '1', 525000 );
        }
    }
}
