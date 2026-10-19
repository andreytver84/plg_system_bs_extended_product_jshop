<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.BsExtendedProductJshop
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Plugin\System\BsExtendedProductJshop\Helper\BlocksHelper;

/**
 * @var  array{blocks: array, productName: string, root: string}  $displayData
 */

$blocks      = $displayData['blocks'] ?? [];
$productName = (string) ($displayData['productName'] ?? '');
$root        = (string) ($displayData['root'] ?? '');
$esc         = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

$col1      = BlocksHelper::html((string) ($blocks['features_col1'] ?? ''));
$col2      = BlocksHelper::html((string) ($blocks['features_col2'] ?? ''));
$layouts   = \is_array($blocks['layouts'] ?? null) ? $blocks['layouts'] : [];
$accordion = \is_array($blocks['accordions'] ?? null) ? $blocks['accordions'] : [];

$hasFeatures   = $col1 !== '' || $col2 !== '';
$hasLayouts    = $layouts !== [];
$hasAccordion  = $accordion !== [];
$hasAny        = $hasFeatures || $hasLayouts || $hasAccordion;
$featuresClass = ($col1 !== '' && $col2 !== '') ? 'bsep-features__grid' : 'bsep-features__grid bsep-features__grid--single';
?>
<section class="bsep" data-bsep>
    <?php if ($hasAny) : ?>
        <h2 class="bsep__title bsep__lead"><?php echo $esc(Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_PROJECT_TITLE')); ?></h2>
    <?php endif; ?>
    <?php if ($hasFeatures) : ?>
        <div class="bsep-features">
            <h3 class="bsep__title"><?php echo $esc(Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_FEATURES_TITLE')); ?></h3>
            <div class="<?php echo $esc($featuresClass); ?>">
                <?php if ($col1 !== '') : ?>
                    <div class="bsep-features__col"><?php echo $col1; ?></div>
                <?php endif; ?>
                <?php if ($col2 !== '') : ?>
                    <div class="bsep-features__col"><?php echo $col2; ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hasLayouts) : ?>
        <div class="bsep-layouts">
            <h3 class="bsep__title"><?php echo $esc(Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_LAYOUTS_TITLE')); ?></h3>
            <?php foreach ($layouts as $row) :
                $image = BlocksHelper::mediaPath((string) ($row['image'] ?? ''));

                if ($image === '') {
                    continue;
                }

                $caption = BlocksHelper::text((string) ($row['caption'] ?? ''));
                $src     = $root . $image;
                $alt     = $caption !== '' ? $caption : Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_LAYOUT_IMAGE_LABEL');
                ?>
                <figure class="bsep-layouts__item">
                    <img
                        class="bsep-layouts__img"
                        src="<?php echo $esc($src); ?>"
                        alt="<?php echo $esc($alt); ?>"
                        loading="lazy"
                        decoding="async"
                    >
                    <?php if ($caption !== '') : ?>
                        <figcaption class="bsep-layouts__caption"><?php echo $esc($caption); ?></figcaption>
                    <?php endif; ?>
                </figure>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($hasAccordion) : ?>
        <div class="bsep-accordion">
            <h3 class="bsep__title"><?php echo $esc(Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_ACCORDION_SECTION_TITLE')); ?></h3>
            <div class="bsep-accordion__list">
                <?php foreach ($accordion as $item) :
                    $title = BlocksHelper::text((string) ($item['title'] ?? ''));
                    $body  = BlocksHelper::html((string) ($item['content'] ?? ''));

                    if ($title === '' && $body === '') {
                        continue;
                    }

                    if ($title !== '') {
                        $title = str_replace('{name}', $esc($productName), $esc($title));
                    }

                    if ($body !== '' && preg_match('/<(p|div|br|ul|ol|table|h[1-6])\b/i', $body) !== 1) {
                        $body = nl2br($body, false);
                    }
                    ?>
                    <details class="bsep-accordion__item">
                        <summary class="bsep-accordion__summary">
                            <span class="bsep-accordion__title"><?php echo $title !== '' ? $title : $esc(Text::_('PLG_SYSTEM_BS_EXTENDED_PRODUCT_JSHOP_ACCORDION_TITLE_LABEL')); ?></span>
                        </summary>
                        <?php if ($body !== '') : ?>
                            <div class="bsep-accordion__body"><?php echo $body; ?></div>
                        <?php endif; ?>
                    </details>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
