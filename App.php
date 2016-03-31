<?php

/**
 * NEOS PHP FRAMEWORK
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     NEOS
 * @access      public
 * @since       0.3.0
 *
 */

namespace Neos;

class App 
{    
    private static $config = _PHP.'Config/';

    private static $dock = [];
    private static $vars = [];

    static function config() 
    {
        return static::$config;
    }

    /* Set/Get variables
     * name = value par
     *
     */
    static function val($name, $value = null) 
    {
        if ($value === null)
            return static::$vars[$name];
        static::$vars[$name] = $value;
    }

    /* Parking Objects
     * 
     */
    static function push($name, $object) 
    {
        static::$dock[$name] = $object;
    }

    static function pull($name) 
    {
        return isset(static::$dock[$name]) ? static::$dock[$name] : false;
    }

    /* Jump to...
     *
     */
    static function go($url = '', $type = 'location', $cod = 302) 
    {
        //se tiver 'http' na uri então será externo.
        if (strpos($url, 'http://') === false || strpos($url, 'https://') === false)
            $url = _URL.$url;

        //send header
        if (strtolower($type) == 'refresh')
            header('Refresh:0;url=' . $url);
        else
            header('Location: ' . $url, TRUE, $cod);

        //... and stop
        exit;
    }

    //Download de arquivo em modo PHAR (interno)
    static function download($reqst = '')
    {
        //checando a existencia do arquivo solicitado
        $reqst = static::fileExists($reqst);
        if($reqst == false) return false;

        //gerando header apropriado
        include static::$config.'mimetypes.php';
        $ext = end((explode('.', $reqst)));
        if (!isset($_mimes[$ext])) $mime = 'text/plain';
        else $mime = (is_array($_mimes[$ext])) ? $_mimes[$ext][0] : $_mimes[$ext];

        //get file
        $dt = file_get_contents($reqst);

        //download
        ob_end_clean();
        ob_start('ob_gzhandler');

        header('Vary: Accept-Language, Accept-Encoding');
        header('Content-Type: ' . $mime);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($reqst)) . ' GMT');
        header('Cache-Control: must_revalidate, public, max-age=31536000');
        header('Content-Length: ' . strlen($dt));
        header('Content-Disposition: attachment; filename='.basename($reqst)); 
        header('ETAG: '.md5($reqst));
        exit($dt);
    }

    //Check if file exists - return real path of file or false
    static function fileExists($file){
        if(file_exists($file)) return $file;
        if(file_exists(_PHP.$file)) return _PHP.$file;
        if(file_exists(_WWW.$file)) return _WWW.$file;
        $xfile = str_replace(_WWW, _PHAR, $file);
        if(file_exists($xfile)) return $xfile;
        return false;
    }

    //Print mixed data and exit
    static function e($v) { exit(static::p($v)); }
    static function p($v, $echo = false) {
        $tmp = '<pre>' . print_r($v, true) . '</pre>';
        if ($echo) echo $tmp;
        else return $tmp;
    }

}
