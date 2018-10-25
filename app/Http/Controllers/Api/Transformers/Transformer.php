<?php

namespace App\Http\Controllers\Api\Transformers;

abstract class Transformer
{
    public static $lang;

    public function transformCollection(array $items, $systemLang=['en'], $method = 'transform')
    {
        if (is_array($systemLang))
            self::$lang = ((end($systemLang) == 'ar') ? 'ar' : 'en');
        else
            self::$lang = $systemLang;
        if (isset($items['current_page'])) {
            return $this->transformPaginate($items, $systemLang, $method);
        }

        return array_map([$this, $method], $items, $systemLang);
    }


    public function transformPaginate($item, $opt, $method)
    {
        return array_merge([
            'current_page' => $item['current_page'],
            'from' => ((isset($item['from'])) ? $item['from'] : ""),
            'last_page' => $item['last_page'],
            'next_page_url' => ((!$item['next_page_url']) ? '' : self::NextPageUrl($item['next_page_url'])),
            'path' => self::NextPageUrl($item['path']),
            'per_page' => $item['per_page'],
            'prev_page_url' => (isset($item['prev_page_url']) ? self::NextPageUrl($item['prev_page_url']) : ""),
            'to' => ((isset($item['to'])) ? $item['to'] : ""),
            'total' => $item['total'],
        ], ['items' => $this->transformCollection($item['data'], $opt, $method)]);
    }

    public abstract function transform($item, $opt);

    private static function NextPageUrl($url)
    {
       return trim(substr($url, strpos($url, '/', 45)), '/');
    }


    public static function trans($item, $column, $lang)
    {
        if (!isset(self::$lang)) {
            if (isset($lang))
                self::$lang = ((is_array($lang)) ? end($lang) : $lang);
        }

        if (isset($item[$column]))
            return $item[$column];
        else if (isset($item[$column . '_' . self::$lang])) {
            return $item[$column . '_' . self::$lang];
        } else
            return null;
    }

    public static function Link($item, $column)
    {
        if (isset($item[$column])) {
            return $item[$column];
            //return getenv('APP_URL') . '/' . $item[$column];
        } else
            return "";
    }

}