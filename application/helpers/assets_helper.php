<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Cette fonction renvoie l'URL d'un fichier CSS
if(! function_exists('css_url'))
{
    function css_url($cssName)
    {
        return base_url() . 'assets/css/' . $cssName . '.css';
    }
}
// Elle renvoie l'URL d'un fichier JS
if(! function_exists('js_url'))
{
    function js_url($jsName)
    {
        return base_url() . 'assets/js/' . $jsName . '.js';
    }
}
// Elle renvoie l'URL d'une image
if(! function_exists('img_url'))
{
    function img_url($imgName)
    {
        return base_url() . 'assets/images/' . $imgName;
    }
}
// Elle permet de générer le code HTML qui insère une image
if(! function_exists('img'))
{
    function img($imgName, $alt, $target = null)
    {
        return '<img src = "' . img_url($imgName) . '" alt = "' . $alt . '" target = "' . $target . '"/>';
    }
}
