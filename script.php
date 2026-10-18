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

return new class () implements InstallerScriptInterface {
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
        if ($type === 'install' || $type === 'update') {
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // Добавление колонки custom_blocks в таблицу товаров JoomShopping
            try {
                $columns = $db->getTableColumns('#__jshopping_products');
                if (!isset($columns['custom_blocks'])) {
                    $query = 'ALTER TABLE ' . $db->quoteName('#__jshopping_products') 
                           . ' ADD ' . $db->quoteName('custom_blocks') . ' MEDIUMTEXT NULL';
                    $db->setQuery($query);
                    $db->execute();
                }
            } catch (\Throwable $e) {
                // Если JoomShopping еще не установлен, пропускаем без фатальной ошибки
            }

            // Автоматическое включение плагина при чистой установке
            if ($type === 'install') {
                $query = $db->getQuery(true)
                    ->update($db->quoteName('#__extensions'))
                    ->set($db->quoteName('enabled') . ' = 1')
                    ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                    ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
                    ->where($db->quoteName('element') . ' = ' . $db->quote('bs_extended_product_jshop'));
                $db->setQuery($query);
                $db->execute();
            }
        }

        return true;
    }
};