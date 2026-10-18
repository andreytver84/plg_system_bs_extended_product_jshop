<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.BsExtendedProductJshop
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\System\BsExtendedProductJshop\Extension\BsExtendedProductJshop;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $plugin = new BsExtendedProductJshop(
                    $container->get(DispatcherInterface::class),
                    (array) PluginHelper::getPlugin('system', 'bs_extended_product_jshop')
                );
                $plugin->setApplication(Factory::getApplication());

                if (method_exists($plugin, 'setDatabase')) {
                    $plugin->setDatabase($container->get(DatabaseInterface::class));
                }

                return $plugin;
            }
        );
    }
};
