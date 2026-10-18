<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.BsExtendedProductJshop
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

return new class () implements InstallerScriptInterface {
    private const COLUMN = 'custom_blocks';

    public function install(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function update(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function uninstall(InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        return true;
    }

    public function postflight(string $type, InstallerAdapter $adapter): bool
    {
        if ($type !== 'install' && $type !== 'update') {
            return true;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        try {
            $columns = $db->getTableColumns('#__jshopping_products');

            if (!isset($columns[self::COLUMN])) {
                $query = 'ALTER TABLE ' . $db->quoteName('#__jshopping_products')
                    . ' ADD ' . $db->quoteName(self::COLUMN) . ' MEDIUMTEXT NULL';
                $db->setQuery($query);
                $db->execute();
            }
        } catch (\Throwable) {
            // JoomShopping ещё не установлен — колонку добавит повторная установка/обновление.
        }

        if ($type === 'install') {
            $plugin = 'plugin';
            $folder = 'system';
            $element = 'bs_extended_product_jshop';
            $enabled = 1;

            $query = $db->getQuery(true)
                ->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = :enabled')
                ->where($db->quoteName('type') . ' = :type')
                ->where($db->quoteName('folder') . ' = :folder')
                ->where($db->quoteName('element') . ' = :element')
                ->bind(':enabled', $enabled, ParameterType::INTEGER)
                ->bind(':type', $plugin)
                ->bind(':folder', $folder)
                ->bind(':element', $element);
            $db->setQuery($query);
            $db->execute();
        }

        return true;
    }
};
