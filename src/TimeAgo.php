<?php
/**
 * Time Ago plugin for Craft CMS 3.x
 *
 * Time ago twig filter
 *
 * @link      https://github.com/mediabeastnz
 * @copyright Copyright (c) 2019 Myles Derham
 */

namespace mediabeastnz\timeAgo;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;

use mediabeastnz\timediff\twigextensions\TwigExtensionDate;

/**
 * Class TimeAgo
 *
 * @author    Myles Derham
 * @package   TimeAgo
 * @since     1.0.0
 *
 */
class TimeAgo extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var TimeAgo
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        \Craft::$app->view->twig->addExtension(new TwigExtensionDate());

        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                }
            }
        );

        Craft::info(
            Craft::t(
                'time-ago',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function addTwigExtension()
    {
        return new TwigExtensionDate();
    }

}
