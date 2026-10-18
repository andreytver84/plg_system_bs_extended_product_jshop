<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.BsExtendedProductJshop
 */

namespace Joomla\Plugin\System\BsExtendedProductJshop\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;

/**
 * Санитизация JSON-блоков карточки товара.
 */
final class BlocksHelper
{
    /**
     * Очищает POST/JSON до безопасной структуры для записи в БД.
     *
     * @param   array<string, mixed>  $raw
     *
     * @return  array{features_col1: string, features_col2: string, layouts: array<int, array{image: string, caption: string}>, accordions: array<int, array{title: string, content: string}>}
     */
    public static function sanitize(array $raw): array
    {
        $layouts = [];

        foreach (self::rows($raw['layouts'] ?? null) as $row) {
            $image = self::mediaPath((string) ($row['image'] ?? ''));

            if ($image === '') {
                continue;
            }

            $layouts[] = [
                'image'   => $image,
                'caption' => self::text((string) ($row['caption'] ?? '')),
            ];
        }

        $accordions = [];

        foreach (self::rows($raw['accordions'] ?? null) as $row) {
            $title   = self::text((string) ($row['title'] ?? ''));
            $content = self::html((string) ($row['content'] ?? ''));

            if ($title === '' && $content === '') {
                continue;
            }

            $accordions[] = [
                'title'   => $title,
                'content' => $content,
            ];
        }

        return [
            'features_col1' => self::html((string) ($raw['features_col1'] ?? '')),
            'features_col2' => self::html((string) ($raw['features_col2'] ?? '')),
            'layouts'       => $layouts,
            'accordions'    => $accordions,
        ];
    }

    /**
     * Декодирует JSON из колонки товара и прогоняет через sanitize.
     *
     * @return  array{features_col1: string, features_col2: string, layouts: array<int, array{image: string, caption: string}>, accordions: array<int, array{title: string, content: string}>}
     */
    public static function decode(string $json): array
    {
        if ($json === '') {
            return self::sanitize([]);
        }

        $data = json_decode($json, true);

        return self::sanitize(\is_array($data) ? $data : []);
    }

    public static function isEmpty(array $blocks): bool
    {
        return ($blocks['features_col1'] ?? '') === ''
            && ($blocks['features_col2'] ?? '') === ''
            && empty($blocks['layouts'])
            && empty($blocks['accordions']);
    }

    public static function html(string $html): string
    {
        $html = str_replace("\0", '', $html);

        if (trim($html) === '') {
            return '';
        }

        $filter = InputFilter::getInstance([], [], 1, 1);

        return trim($filter->clean($html, 'HTML'));
    }

    public static function text(string $text): string
    {
        $text = str_replace("\0", '', $text);
        $filter = InputFilter::getInstance();

        return trim($filter->clean($text, 'STRING'));
    }

    /**
     * Только относительный путь к файлу медиа. Без схем, //, .. и абсолютных путей.
     */
    public static function mediaPath(string $path): string
    {
        $path = str_replace(["\0", '\\'], ['', '/'], trim($path));

        if ($path === '') {
            return '';
        }

        $hashPos = strpos($path, '#');

        if ($hashPos !== false) {
            $path = substr($path, 0, $hashPos);
        }

        $path = ltrim($path, '/');

        if ($path === '' || str_contains($path, '..')) {
            return '';
        }

        if (preg_match('#^(?:[a-z][a-z0-9+.-]*:)?//#i', $path) === 1) {
            return '';
        }

        if (preg_match('#^[a-zA-Z]:/#', $path) === 1) {
            return '';
        }

        if (preg_match('#^(javascript|data|vbscript):#i', $path) === 1) {
            return '';
        }

        if (preg_match('#^(images|media)/[A-Za-z0-9_./-]+$#', $path) !== 1) {
            return '';
        }

        if (preg_match('#\.(?:jpe?g|png|gif|webp)$#i', $path) !== 1) {
            return '';
        }

        return $path;
    }

    /**
     * @param   mixed  $rows
     *
     * @return  array<int, array<string, mixed>>
     */
    private static function rows(mixed $rows): array
    {
        if (!\is_array($rows) || $rows === []) {
            return [];
        }

        $out = [];

        foreach ($rows as $row) {
            if (\is_array($row)) {
                $out[] = $row;
            }
        }

        return $out;
    }
}
