<?php


namespace Chatwing\IntegrationPlugins\WordPress;


class Asset
{
    /**
     * @param $file
     * @return string
     */
    public static function link($file)
    {
        return CHATWING_PLG_URL . 'assets/' . $file;
    }
}