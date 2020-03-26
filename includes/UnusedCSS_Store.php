<?php

/**
 * Class UnusedCSS
 */
class UnusedCSS_Store {

    public $base = 'cache/uucss';
    public $provider = null;

    public $url = null;
    public $purged_files = [];


    /**
     * UnusedCSS constructor.
     * @param $provider
     * @param $url
     */
    public function __construct($provider, $url)
    {
        $this->provider = $provider;
        $this->url = $url;

        // load wp filesystem related files;
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        WP_Filesystem();

        $this->purge_css();

    }

    
    protected function purge_css(){

        $uucss_api = new UnusedCSS_Api();
        $this->purged_files = $uucss_api->get($this->url);

        if($this->purged_files && count($this->purged_files) > 0) {
            $this->cache_files();
        }
        
    }

    public function get_base_dir($url = false){
        global $wp_filesystem;

        $root = ($url) ? $url : $wp_filesystem->wp_content_dir()  . $this->base;
        $root_with_provider = $root . '/' . $this->provider;

        if(!$wp_filesystem->exists($root)) {
            $wp_filesystem->mkdir($root);
        }

        if(!$wp_filesystem->exists($root_with_provider)) {
            $wp_filesystem->mkdir($root_with_provider);
        }

        return $root_with_provider;
    }

    protected function get_cache_source_dir($url = false)
    {
        global $wp_filesystem;
        
        $hash = $this->encode($this->url);

        $source_dir = $this->get_base_dir($url) . '/' . $hash;

        if(!$wp_filesystem->exists($source_dir)) {
            $wp_filesystem->mkdir($source_dir);
        }

        return $source_dir;
    }


    protected function encode($data)
    {
        return rtrim(md5($data));
    }


    protected function cache_files() {
        global $wp_filesystem;

        foreach($this->purged_files as $file) {
            
            $file_location = $this->cache_file_location($file->file);
            
            if(!$wp_filesystem->exists($file_location)) {
                $wp_filesystem->put_contents($file_location, $file->css, FS_CHMOD_FILE);
            }
        }
        
    }

    protected function cache_file_location($file, $link = false){
        return $this->get_cache_source_dir($link) . '/' . $this->get_file_name($file);
    }

    protected function get_file_name($file){
        return explode("?", basename($file))[0];
    }


}