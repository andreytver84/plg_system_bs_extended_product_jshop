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
    public const VERSION = '1.0.1';

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

        $blocks = $this->readBlocks($view->product);
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

        $raw = $app->getInput()->post->get('project_blocks', [], 'RAW');
        $raw = \is_array($raw) ? $raw : [];

        $form     = $this->makeForm();
        $filtered = $form->filter($raw);
        $blocks   = BlocksHelper::sanitize(\is_array($filtered) ? $filtered : $raw);
        $json     = json_encode($blocks, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            $json = '{}';
        }

        $this->pendingReady = true;
        $this->pendingJson  = $json;

        $post = $event->getArgument(0);

        if (\is_array($post)) {
            $post[self::COLUMN] = $json;
            $event->setArgument(0, $post);
        }

        $product = $event->getArgument(1);

        if (\is_object($product)) {
            $product->{self::COLUMN} = $json;
        }
    }

    /**
     * Дублирует запись колонки после store(), если bind отбросил неизвестное поле.
     */
    public function onAfterSaveProduct(Event $event): void
    {
        if (!$this->pendingReady) {
            return;
        }

        $app = $this->getApplication();

        if (!$app || !$app->isClient('administrator')) {
            return;
        }

        $productId = $this->findProductId($event->getArguments());

        if ($productId > 0) {
            $this->storeColumn($productId, $this->pendingJson);
        }

        $this->pendingReady = false;
        $this->pendingJson  = '';
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

        $this->loadAssets();
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

        $wa = $app->getDocument()->getWebAssetManager();
        $wa->registerAndUseStyle(
            'plg_system_bs_extended_product_jshop.product',
            'plg_system_bs_extended_product_jshop/css/product.css',
            ['version' => self::VERSION]
        );
        $wa->registerAndUseScript(
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
    private function readBlocks(object $product): array
    {
        $json = '';

        if (isset($product->{self::COLUMN}) && \is_string($product->{self::COLUMN})) {
            $json = $product->{self::COLUMN};
        } else {
            $productId = (int) ($product->product_id ?? 0);

            if ($productId > 0) {
                $json = $this->fetchColumn($productId);
            }
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
        if ($productId < 1 || !$this->hasColumn()) {
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
            if (\is_object($arg) && isset($arg->product_id)) {
                $id = (int) $arg->product_id;

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
