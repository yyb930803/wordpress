<?php
class Nasa_Caching {
    
    public static $_live_time = 3600;
    
    /**
     * Set Cache
     * 
     * @param type $key
     * @param type $content
     * @param type $folder
     */
    public static function set_content($key, $content, $folder = 'nasa-core') {
        global $nasa_opt;
        
        if(!isset($nasa_opt['enable_nasa_cache']) || $nasa_opt['enable_nasa_cache']) {
            global $wp_filesystem, $nasa_cache_dir;

            if(!isset($nasa_cache_dir) || !$nasa_cache_dir) {
                $upload_dir = wp_upload_dir();
                $nasa_cache_dir = $upload_dir['basedir'] . '/nasa-cache';

                $GLOBALS['nasa_cache_dir'] = $nasa_cache_dir;
            }

            // Initialize the WP filesystem, no more using 'file-put-contents' function
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }
            
            if (!defined('FS_CHMOD_FILE')) {
                define('FS_CHMOD_FILE', (fileperms(ABSPATH . 'index.php') & 0777 | 0644));
            }

            /**
             * Create new root cache
             */
            if(!$wp_filesystem->is_dir($nasa_cache_dir)) {
                if (!wp_mkdir_p($nasa_cache_dir)){
                    return false;
                }
            }
            
            $folder_cache = $nasa_cache_dir . '/' . $folder;
            if(!$wp_filesystem->is_dir($folder_cache)) {   
                /**
                 * Create folder cache products
                 */
                if (!wp_mkdir_p($folder_cache)){
                    return false;
                }
            }
            
            /**
             * Create htaccess file
             */
            $htaccess = $folder_cache . '/.htaccess';
            if(!is_file($htaccess)) {
                if (!$wp_filesystem->put_contents($htaccess, 'Deny from all', FS_CHMOD_FILE)) {
                    return false;
                }
            }

            /**
             * Set cache file
             */
            $filename = $folder_cache . '/' . md5($key) . '.html';
            if (!$wp_filesystem->put_contents($filename, $content, FS_CHMOD_FILE)) {
                return false;
            }

            return true;
        }
        
        return false;
    }

    /**
     * Get Cache
     * 
     * @param type $key
     * @param type $folder
     */
    public static function get_content($key, $folder = 'nasa-core') {
        global $nasa_opt;
        
        if (!isset($nasa_opt['enable_nasa_cache']) || $nasa_opt['enable_nasa_cache']) {
            global $wp_filesystem, $nasa_cache_dir;

            if(!isset($nasa_cache_dir) || !$nasa_cache_dir) {
                $upload_dir = wp_upload_dir();
                $nasa_cache_dir = $upload_dir['basedir'] . '/nasa-cache';

                $GLOBALS['nasa_cache_dir'] = $nasa_cache_dir;
            }

            // Initialize the WP filesystem, no more using 'file-put-contents' function
            if (empty($wp_filesystem)) {
                require_once ABSPATH . '/wp-admin/includes/file.php';
                WP_Filesystem();
            }

            $filename = $nasa_cache_dir . '/' . $folder . '/' . md5($key) . '.html';
            if(!is_file($filename)) {
                return false;
            }

            $time = filemtime($filename);
            if (isset($nasa_opt['nasa_cache_expire']) && (int) $nasa_opt['nasa_cache_expire']) {
                self::$_live_time = (int) $nasa_opt['nasa_cache_expire'];
            }
            if($time + self::$_live_time < NASA_TIME_NOW) {
                return false;
            }

            return $wp_filesystem->get_contents($filename);
        }

        return false;
    }
    
    /**
     * Delete cache by key
     * 
     * @global string $nasa_cache_dir
     * @param type $key
     * @param type $folder
     * @return boolean
     */
    public static function delete_cache_by_key($key, $folder = 'nasa-core') {
        global $nasa_cache_dir;

        if(!isset($nasa_cache_dir) || !$nasa_cache_dir) {
            $upload_dir = wp_upload_dir();
            $nasa_cache_dir = $upload_dir['basedir'] . '/nasa-cache';

            $GLOBALS['nasa_cache_dir'] = $nasa_cache_dir;
        }

        $file = $nasa_cache_dir . '/' . $folder . '/' . md5($key) . '.html';
        if(is_file($file)) {
            wp_delete_file($file);
            
            return true;
        }

        return false;
    }

    /**
     * Delete all cache in any folder
     * 
     * @param type $folder
     */
    public static function delete_cache($folder = 'nasa-core') {
        global $wp_filesystem, $nasa_cache_dir;

        if(!isset($nasa_cache_dir) || !$nasa_cache_dir) {
            $upload_dir = wp_upload_dir();
            $nasa_cache_dir = $upload_dir['basedir'] . '/nasa-cache';

            $GLOBALS['nasa_cache_dir'] = $nasa_cache_dir;
        }

        // Initialize the WP filesystem, no more using 'file-put-contents' function
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $folder_cache = $nasa_cache_dir . '/' . $folder;
        if(is_dir($folder_cache)) {
            return $wp_filesystem->rmdir($folder_cache, true);
        }

        return false;
    }
}
