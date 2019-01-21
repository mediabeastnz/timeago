<?php
/**
 * Time Ago plugin for Craft CMS 3.x
 *
 * Time ago twig filter
 *
 * @link      https://github.com/mediabeastnz
 * @copyright Copyright (c) 2019 Myles Derham
 */
namespace mediabeastnz\timeago;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use yii\base\Event;


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

        Craft::$app->view->twig->addExtension(new TwigExtensionDate());

        Craft::info(
            Craft::t(
                'timeago',
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

class TwigExtensionDate extends \Twig_Extension
{
    public $units_translated = array();

    static $units = array(
        'y' => 'year',
        'm' => 'month',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );

    function __construct()
    {
        $this->units_translated = array(
            'y' => Craft::t('app','year'),
            'm' => Craft::t('app','month'),
            'd' => Craft::t('app','day'),
            'h' => Craft::t('app','hour'),
            'i' => Craft::t('app','minute'),
            's' => Craft::t('app','second'),
        );
    }

    /**
     * Returns a list of filters.
     *
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('time_ago', array($this, 'diff'), array(
                'needs_environment' => true
            )),
            new \Twig_SimpleFilter('time_ago_array', array($this, 'diff_array'), array(
                'needs_environment' => true
            )),
        );
    }

    /**
     * Name of this extension
     *
     * @return string
     */
    public function getName()
    {
        return 'date';
    }

    /**
     * Filter for converting dates to a time ago string like Facebook and Twitter has.
     *
     * @param Twig_Environment $env  A Twig_Environment instance.
     * @param string|DateTime  $date A string or DateTime object to convert.
     * @param string|DateTime  $now  A string or DateTime object to compare with. If none given, the current time will be used.
     *
     * @return string The converted time.
     */
    public function diff(\Twig_Environment $env, $date, $now = null)
    {
        $date = twig_date_converter($env, $date);
        $now = twig_date_converter($env, $now);

        // Get the difference between the two DateTime objects.
        $diff = $date->diff($now);
        // Check for each interval if it appears in the $diff object.
        foreach (self::$units as $attribute => $unit) {
            $count = $diff->$attribute;

            if (0 !== $count) {
                return $this->getPluralizedInterval($count, $diff->invert, $unit);
            }
        }

        return '';
    }

    /**
     * Filter for converting dates to a time ago string like Facebook and Twitter has, in a translatable array
     *
     * @param Twig_Environment $env  A Twig_Environment instance.
     * @param string|DateTime  $date A string or DateTime object to convert.
     * @param string|DateTime  $now  A string or DateTime object to compare with. If none given, the current time will be used.
     *
     * @return string The converted time.
     */
    public function diff_array(\Twig_Environment $env, $date, $now = null)
    {
        // Convert both dates to DateTime instances.
        $date = twig_date_converter($env, $date);
        $now = twig_date_converter($env, $now);

        // Get the difference between the two DateTime objects.
        $diff = $date->diff($now);

        // Check for each interval if it appears in the $diff object.
        foreach ($this->units_translated as $attribute => $unit) {
            $count = $diff->$attribute;

            if (0 !== $count) {
                return $this->getPluralizedIntervalArray($count, $diff->invert, $unit);
            }
        }

        return '';
    }

    protected function getPluralizedInterval($count, $invert, $unit)
    {

        if ($count > 1) {
            $unit .= 's';
        }

        return $invert ? "in $count $unit" : "$count $unit ago";
    }

    protected function getPluralizedIntervalArray($count, $invert, $unit)
    {
        $translateable_array = array($invert ? 'future' : 'past', $count, $unit, $count > 1 ? 'plural' : 'single');
        return implode('|', $translateable_array);
    }

}
