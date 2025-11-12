<?php

// app/Support/XmlPick.php
namespace App\Support;

class XmlPick
{
    public static function one(\SimpleXMLElement $node, string $selector): ?string
    {
        // Attribute على العقدة الحالية
        if (str_starts_with($selector, '@')) {
            $attr = substr($selector, 1);
            return isset($node[$attr]) ? (string)$node[$attr] : null;
        }

        // param[@name=gender]
        if (preg_match('/^param\[@name=(.+?)\]$/', $selector, $m)) {
            $name = $m[1];
            foreach ($node->xpath("param[@name='{$name}']") ?: [] as $p) {
                return trim((string)$p);
            }
            return null;
        }

        // tag عادي
        $el = $node->{$selector} ?? null;
        if ($el === null) return null;
        // لو multi خُد أول واحد
        if (is_array($el) || $el instanceof \Traversable) {
            foreach ($el as $e) return trim((string)$e);
        }
        return trim((string)$el);
    }

    public static function many(\SimpleXMLElement $node, string $selector): array
    {
        // picture[*]
        if (preg_match('/^([a-zA-Z0-9_]+)\[\*\]$/', $selector, $m)) {
            $tag = $m[1];
            $arr = [];
            foreach ($node->{$tag} ?? [] as $e) {
                $v = trim((string)$e);
                if ($v !== '') $arr[] = $v;
            }
            return $arr;
        }

        // fallback: عنصر واحد
        $v = self::one($node, $selector);
        return $v ? [$v] : [];
    }
}
