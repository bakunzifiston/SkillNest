<?php

namespace App\Support;

/**
 * Colour theme for course category cards. All categories share the brand blue,
 * so the cards look consistent however many categories an admin adds.
 */
class CategoryTheme
{
    /**
     * base = icon tile / top bar, light = card tint, border = soft outline,
     * text = darker shade for labels (readable on the light tint).
     *
     * @return array{base: string, light: string, border: string, text: string}
     */
    public static function theme(): array
    {
        return ['base' => '#19499B', 'light' => '#E8EEF7', 'border' => '#C9D6EE', 'text' => '#102C5E'];
    }

    /** CSS custom properties for a card, e.g. "--c:#19499B;--bg:#E8EEF7;…". */
    public static function style(): string
    {
        $t = self::theme();

        return "--c:{$t['base']};--bg:{$t['light']};--bd:{$t['border']};--tx:{$t['text']};";
    }
}
