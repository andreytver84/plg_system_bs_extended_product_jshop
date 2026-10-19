<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.BsExtendedProductJshop
 */

namespace Joomla\Plugin\System\BsExtendedProductJshop\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\BsExtendedProductJshop\Helper\BlocksHelper;

final class BsExtendedProductJshop extends CMSPlugin implements SubscriberInterface
{
    public const VERSION = '1.1.2';

    public const COLUMN = 'custom_blocks';

    protected $autoloadLanguage = true;

    private bool $pendingReady = false;

    private string $pendingJson = '';

    private ?bool $columnExists = null;

    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeDisplayEditProductView' => 'onBeforeDisplayEditProductView',
            'onBeforeDisplaySaveProduct'     => 'onBeforeDisplaySaveProduct',
            'onAfterSaveProduct'             => 'onAfterSaveProduct',
            'onAfterSaveProductEnd'          => 'onAfterSaveProductEnd',
            'onBeforeDisplayProductView'     => 'onBeforeDisplayProductView',
        ];
    }

    /**
     * Поля блоков в админке карточки товара JoomShopping.
     */
    public function onBeforeDisplayEditProductView(Event $event): void
    {
        $view = $this->argumentObject($event, 'product');

        if (!$view || !isset($view->product) || !\is_object($view->product)) {
            return;
        }

        $blocks = $this->readBlocks($view->product, true);
        $form   = $this->makeForm();
        $form->bind($blocks);

        $html  = '<tr><td colspan="2">';
        $html .= '<div class="card mt-3 mb-3 border-primary">';
        $html .= '<div class="card-header bg-primary text-white">';
        $html .= '<h4 class="m-0">' . htmlspecialchars(Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_HEADING'), ENT_QUOTES, 'UTF-8') . '</h4>';
        $html .= '</div><div class="card-body">';

        foreach ($form->getFieldset('project_blocks') as $field) {
            $html .= $field->renderField();
        }

        $html .= '</div></div></td></tr>';

        $langCode = $this->firstLanguageCode($view);
        $key      = $langCode !== '' ? 'plugin_template_description_' . $langCode : 'plugin_template_description';
        $view->{$key} = (string) ($view->{$key} ?? '') . $html;
    }

    /**
     * Сохраняет санитизированный JSON в post и в объект товара.
     * Событие JoomShopping: onBeforeDisplaySaveProduct(&$post, &$product).
     */
    public function onBeforeDisplaySaveProduct(Event $event): void
    {
        $app = $this->getApplication();

        if (!$app || !$app->isClient('administrator')) {
            return;
        }

        $input = $app->getInput()->post;

        if (!$input->exists('project_blocks')) {
            return;
        }

        $raw = $input->get('project_blocks', null, 'raw');

        if (!\is_array($raw)) {
            $all = $input->getArray();
            $raw = $all['project_blocks'] ?? null;
        }

        if (!\is_array($raw)) {
            return;
        }

        $blocks = BlocksHelper::sanitize($raw);
        $json   = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);

        if ($json === false) {
            return;
        }

        $this->pendingReady = true;
        $this->pendingJson  = $json;

        $post = $event->getArgument(0);

        if (\is_array($post)) {
            $post[self::COLUMN] = $json;
            $event->setArgument(0, $post);
        }

        foreach ($event->getArguments() as $arg) {
            if (\is_object($arg)) {
                $arg->{self::COLUMN} = $json;
            }
        }
    }

    /**
     * Пишет колонку после первого store(). pending не сбрасывает:
     * следом uploadImages() может сделать load()+store() и затереть поле.
     */
    public function onAfterSaveProduct(Event $event): void
    {
        $this->writePending($event->getArguments());
    }

    /**
     * Финальная запись после картинок и связанных таблиц.
     */
    public function onAfterSaveProductEnd(Event $event): void
    {
        $this->writePending($event->getArguments());
        $this->pendingReady = false;
        $this->pendingJson  = '';
    }

    private function writePending(array $args): void
    {
        if (!$this->pendingReady) {
            return;
        }

        $app = $this->getApplication();

        if (!$app || !$app->isClient('administrator')) {
            return;
        }

        $productId = $this->findProductId($args);

        if ($productId > 0) {
            $this->storeColumn($productId, $this->pendingJson);
        }
    }

    /**
     * Рендер блоков на витрине без правки шаблона JoomShopping.
     */
    public function onBeforeDisplayProductView(Event $event): void
    {
        $view = $event->getArgument(0);

        if (!\is_object($view) || !isset($view->product) || !\is_object($view->product)) {
            return;
        }

        $blocks = $this->readBlocks($view->product);
        $view->project_blocks = $blocks;

        if (BlocksHelper::isEmpty($blocks)) {
            $view->bs_extended_html = '';

            return;
        }

        $productName = BlocksHelper::text((string) ($view->product->name ?? ''));
        $html        = $this->renderLayout($blocks, $productName);
        $view->bs_extended_html = $html;

        if ($html === '') {
            return;
        }

        $accordions = $blocks['accordions'] ?? [];

        if (\is_array($accordions) && $accordions !== []) {
            $this->loadAssets();
        }

        $this->injectHtml($view, $html);
    }

    /**
     * @param   array<string, mixed>  $blocks
     */
    private function renderLayout(array $blocks, string $productName): string
    {
        $displayData = [
            'blocks'      => $blocks,
            'productName' => $productName,
            'root'        => Uri::root(),
        ];

        $path = PluginHelper::getLayoutPath('system', 'bs_extended_product_jshop', 'default');

        ob_start();
        include $path;

        return trim((string) ob_get_clean());
    }

    private function injectHtml(object $view, string $html): void
    {
        $position = (string) $this->params->get('position', 'description');

        if ($position === 'none') {
            return;
        }

        if ($position === 'description' && isset($view->product) && \is_object($view->product)) {
            $view->product->description = (string) ($view->product->description ?? '') . $html;

            return;
        }

        $property = match ($position) {
            'after_description' => '_tmp_product_html_before_demofiles',
            'after_buttons'     => '_tmp_product_html_after_buttons',
            default             => '_tmp_product_html_end',
        };

        $view->{$property} = (string) ($view->{$property} ?? '') . $html;
    }

    private function loadAssets(): void
    {
        $app = $this->getApplication();

        if (!$app) {
            return;
        }

        $app->getDocument()->getWebAssetManager()->registerAndUseScript(
            'plg_system_bs_extended_product_jshop.product',
            'plg_system_bs_extended_product_jshop/js/product.js',
            ['version' => self::VERSION],
            ['defer' => true]
        );
    }

    private function makeForm(): Form
    {
        Form::addFormPath(JPATH_PLUGINS . '/system/bs_extended_product_jshop/forms');

        return Form::getInstance(
            'com_jshopping.bs_extended_product',
            'project_blocks',
            ['control' => 'project_blocks']
        );
    }

    /**
     * @return  array{features_col1: string, features_col2: string, layouts: array<int, array{image: string, caption: string}>, accordions: array<int, array{title: string, content: string}>}
     */
    private function readBlocks(object $product, bool $fromDb = false): array
    {
        $json      = '';
        $productId = (int) ($product->product_id ?? 0);

        // В админке JoomShopping делает OutputFilter::objectHTMLSafe($product): JSON в свойстве
        // превращается в &quot;…&quot; и не декодируется. Поэтому читаем колонку напрямую из БД.
        if ($fromDb && $productId > 0) {
            $json = $this->fetchColumn($productId);
        }

        if ($json === '' && isset($product->{self::COLUMN}) && \is_string($product->{self::COLUMN})) {
            $json = $product->{self::COLUMN};

            if ($json !== '' && json_decode($json) === null) {
                $json = htmlspecialchars_decode($json, ENT_QUOTES);
            }
        }

        if ($json === '' && !$fromDb && $productId > 0) {
            $json = $this->fetchColumn($productId);
        }

        return BlocksHelper::decode($json);
    }

    private function fetchColumn(int $productId): string
    {
        if ($productId < 1 || !$this->hasColumn()) {
            return '';
        }

        $db    = $this->db();
        $query = $db->getQuery(true)
            ->select($db->quoteName(self::COLUMN))
            ->from($db->quoteName('#__jshopping_products'))
            ->where($db->quoteName('product_id') . ' = :productId')
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);

        return (string) $db->loadResult();
    }

    private function storeColumn(int $productId, string $json): void
    {
        if ($productId < 1 || !$this->ensureColumn()) {
            return;
        }

        $db    = $this->db();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__jshopping_products'))
            ->set($db->quoteName(self::COLUMN) . ' = :json')
            ->where($db->quoteName('product_id') . ' = :productId')
            ->bind(':json', $json, ParameterType::STRING)
            ->bind(':productId', $productId, ParameterType::INTEGER);

        $db->setQuery($query);
        $db->execute();
    }

    private function ensureColumn(): bool
    {
        if ($this->hasColumn()) {
            return true;
        }

        try {
            $db      = $this->db();
            $columns = $db->getTableColumns('#__jshopping_products');

            if (!isset($columns[self::COLUMN])) {
                $db->setQuery(
                    'ALTER TABLE ' . $db->quoteName('#__jshopping_products')
                    . ' ADD ' . $db->quoteName(self::COLUMN) . ' MEDIUMTEXT NULL'
                );
                $db->execute();
            }

            $this->columnExists = true;
        } catch (\Throwable) {
            $this->columnExists = false;
        }

        return $this->columnExists === true;
    }

    private function hasColumn(): bool
    {
        if ($this->columnExists !== null) {
            return $this->columnExists;
        }

        try {
            $columns = $this->db()->getTableColumns('#__jshopping_products');
            $this->columnExists = isset($columns[self::COLUMN]);
        } catch (\Throwable) {
            $this->columnExists = false;
        }

        return $this->columnExists;
    }

    private function argumentObject(Event $event, string $property): ?object
    {
        foreach ($event->getArguments() as $arg) {
            if (\is_object($arg) && isset($arg->{$property})) {
                return $arg;
            }
        }

        return null;
    }

    private function firstLanguageCode(object $view): string
    {
        if (empty($view->languages)) {
            return '';
        }

        foreach ($view->languages as $lang) {
            if (\is_object($lang) && !empty($lang->language)) {
                return (string) $lang->language;
            }
        }

        return '';
    }

    /**
     * @param   array<int|string, mixed>  $args
     */
    private function findProductId(array $args): int
    {
        foreach ($args as $arg) {
            if (\is_int($arg) || (\is_string($arg) && ctype_digit($arg))) {
                $id = (int) $arg;

                if ($id > 0) {
                    return $id;
                }
            }

            if (\is_object($arg)) {
                $id = (int) ($arg->product_id ?? 0);

                if ($id > 0) {
                    return $id;
                }
            }

            if (\is_array($arg) && isset($arg['product_id'])) {
                $id = (int) $arg['product_id'];

                if ($id > 0) {
                    return $id;
                }
            }
        }

        return 0;
    }

    private function db(): DatabaseInterface
    {
        try {
            return $this->getDatabase();
        } catch (\Throwable) {
            return Factory::getContainer()->get(DatabaseInterface::class);
        }
    }
}
