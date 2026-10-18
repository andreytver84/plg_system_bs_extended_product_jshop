<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.BsExtendedProductJshop
 */

namespace Joomla\Plugin\System\BsExtendedProductJshop\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

final class BsExtendedProductJshop extends CMSPlugin implements SubscriberInterface
{
    /**
     * Автоматическая загрузка языковых файлов
     *
     * @var boolean
     */
    protected $autoloadLanguage = true;

    /**
     * Подписка на события JoomShopping
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeDisplayEditProductView' => 'onBeforeDisplayEditProductView',
            'onBeforeSaveProduct'            => 'onBeforeSaveProduct',
            'onBeforeDisplayProductView'     => 'onBeforeDisplayProductView',
        ];
    }

    /**
     * Отображение дополнительных полей в админке карточки товара JoomShopping
     *
     * @param   Event  $event  Объект события
     *
     * @return  void
     */
    public function onBeforeDisplayEditProductView(Event $event): void
    {
        $args = $event->getArguments();
        $view = $args[0] ?? null;

        if (!$view || !isset($view->product)) {
            return;
        }

        $product = $view->product;

        Form::addFormPath(JPATH_PLUGINS . '/system/bs_extended_product_jshop/forms');
        $form = Form::getInstance(
            'com_jshopping.bs_extended_product',
            'project_blocks',
            ['control' => 'project_blocks']
        );

        if (!empty($product->custom_blocks)) {
            $data = json_decode((string) $product->custom_blocks, true);
            if (\is_array($data)) {
                $form->bind($data);
            }
        }

        $html  = '<div class="card mt-4 mb-4 border-primary">';
        $html .= '<div class="card-header bg-primary text-white">';
        $html .= '<h4 class="m-0">' . Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_HEADING') . '</h4>';
        $html .= '</div>';
        $html .= '<div class="card-body">';

        foreach ($form->getFieldset('project_blocks') as $field) {
            $html .= $field->renderField();
        }

        $html .= '</div></div>';

        if (!isset($view->plugin_template_description)) {
            $view->plugin_template_description = '';
        }

        $view->plugin_template_description .= $html;
    }

    /**
     * Сохранение дополнительных блоков в JSON при сохранении товара
     *
     * @param   Event  $event  Объект события
     *
     * @return  void
     */
    public function onBeforeSaveProduct(Event $event): void
    {
        $args = $event->getArguments();
        if (!isset($args[0]) || !\is_array($args[0])) {
            return;
        }

        $post = $args[0];
        $app  = $this->getApplication();

        if (!$app) {
            return;
        }

        $blocksData = $app->getInput()->post->get('project_blocks', [], 'RAW');

        if (!empty($blocksData) && \is_array($blocksData)) {
            $post['custom_blocks'] = json_encode($blocksData, JSON_UNESCAPED_UNICODE);
        } else {
            $post['custom_blocks'] = '';
        }

        $event->setArgument(0, $post);
    }

    /**
     * Передача декодированных данных в шаблон карточки товара на сайте
     *
     * @param   Event  $event  Объект события
     *
     * @return  void
     */
    public function onBeforeDisplayProductView(Event $event): void
    {
        $args = $event->getArguments();
        $view = $args[0] ?? null;

        if (!$view || !isset($view->product)) {
            return;
        }

        $view->project_blocks = !empty($view->product->custom_blocks)
            ? (json_decode((string) $view->product->custom_blocks, true) ?: [])
            : [];
    }
}