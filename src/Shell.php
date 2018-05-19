<?php
namespace shellpress\v1_2_2\src;

/**
 * @author jakubkuranda@gmail.com
 * Date: 2017-11-24
 * Time: 22:45
 */

use shellpress\v1_2_2\lib\Psr4Autoloader\Psr4AutoloaderClass;
use shellpress\v1_2_2\ShellPress;
use shellpress\v1_2_2\src\Components\External\AutoloadingHandler;
use shellpress\v1_2_2\src\Components\External\EventHandler;
use shellpress\v1_2_2\src\Components\Internal\ExtractorHandler;
use shellpress\v1_2_2\src\Components\External\LogHandler;
use shellpress\v1_2_2\src\Components\External\MessagesHandler;
use shellpress\v1_2_2\src\Components\External\OptionsHandler;
use shellpress\v1_2_2\src\Components\External\UtilityHandler;

if( ! class_exists( 'shellpress\v1_2_2\src\Shell', false ) ) {

    class Shell {

    	/** @var bool */
    	protected $isInitialized = false;

    	//  ---

        /** @var string */
        protected $mainPluginFile;

        /** @var string */
        protected $pluginPrefix;

        /** @var string */
        protected $pluginVersion;

        //  ---

        /** @var OptionsHandler */
        public $options;

        /** @var UtilityHandler */
        public $utility;

        /** @var Psr4AutoloaderClass */
        public $autoloading;

        /** @var LogHandler */
        public $log;

        /** @var EventHandler */
        public $event;

        /** @var MessagesHandler */
        public $messages;

        /** @var ExtractorHandler */
        protected $extractor;

        /**
         * Shell constructor.
         *
         * @param string        $mainPluginFile
         * @param string        $pluginPrefix
         * @param string        $pluginVersion
         * @param ShellPress    $shellPress
         */
        public function __construct( $mainPluginFile, $pluginPrefix, $pluginVersion ) {

            $this->mainPluginFile = $mainPluginFile;
            $this->pluginPrefix   = $pluginPrefix;
            $this->pluginVersion  = $pluginVersion;

        }

	    /**
	     * Initializes built in components.
	     * Called on ShellPress::initShellPress();
	     *
	     * @param ShellPress $shellPress
	     *
	     * @return void
	     */
        public function init( &$shellPress ) {

        	if( $this->isInitialized ) return;

	        //  ----------------------------------------
	        //  Before auto loading
	        //  ----------------------------------------

	        if( ! class_exists( 'shellpress\v1_2_2\src\Shared\Components\IComponent', false ) )
		        require( __DIR__ . '/Shared/Components/IComponent.php' );
	        if( ! class_exists( 'shellpress\v1_2_2\src\Components\External\AutoloadingHandler', false ) )
		        require( __DIR__ . '/Components/External/AutoloadingHandler.php' );

	        //  -----------------------------------
	        //  Initialize handlers
	        //  -----------------------------------

	        $this->autoloading  = new AutoloadingHandler( $shellPress );
	        $this->utility      = new UtilityHandler( $shellPress );
	        $this->options      = new OptionsHandler( $shellPress );
	        $this->log          = new LogHandler( $shellPress );
	        $this->messages     = new MessagesHandler( $shellPress );
	        $this->event        = new EventHandler( $shellPress );
	        $this->extractor    = new ExtractorHandler( $shellPress );

        }

        //  ================================================================================
        //  GETTERS
        //  ================================================================================

        /**
         * Simple function to get prefix or
         * to prepend given string with prefix.
         *
         * @param string $stringToPrefix
         *
         * @return string
         */
        public function getPrefix( $stringToPrefix = null ) {

            if ( $stringToPrefix === null ) {

                return $this->pluginPrefix;

            } else {

                return $this->pluginPrefix . $stringToPrefix;

            }

        }

        /**
         * Prepands given string with plugin directory url.
         * Example usage: getUrl( '/assets/style.css' );
         *
         * @param string $relativePath
         *
         * @return string - URL
         */
        public function getUrl( $relativePath = null ) {

            $delimeter = 'wp-content';
            $pluginDir = dirname( $this->getMainPluginFile() );

            $pathParts = explode( $delimeter, $pluginDir, 2 );     //  slice path by delimeter string

            $wpContentDirUrl = content_url();                       //  `wp-content` directory url

            $url = $wpContentDirUrl . $pathParts[ 1 ];                //  sum of wp-content url + relative path to plugin dir
            $url = rtrim( $url, '/' );                              //  remove trailing slash

            if ( $relativePath === null ) {

                return $url;

            } else {

                $relativePath = ltrim( $relativePath, '/' );

                return $url . '/' . $relativePath;

            }

        }

        /**
         * Prefixes given string with directory path.
         * Your path must have slash on start.
         * Example usage: getPath( '/dir/another/file.php' );
         *
         * @param string $relativePath
         *
         * @return string - absolute path
         */
        public function getPath( $relativePath = null ) {

            $path = dirname( $this->getMainPluginFile() );  // plugin directory path

            if ( $relativePath === null ) {

                return $path;

            } else {

                $relativePath = ltrim( $relativePath, '/' );

                return $path . '/' . $relativePath;

            }

        }

        /**
         * Requires file by given relative path.
         * If class name is given as a second parameter, it will check, if class already exists.
         *
         * @param string      $path      - Relative file path
         * @param string|null $className - Class name to check against.
         *
         * @return void
         */
        public function requireFile( $path, $className = null ) {

            if ( $className && class_exists( $className, false ) ) {

                return; //  End method. Do not load file.

            }

            require( $this->getPath( $path ) );

        }

        /**
         * It gets main plugin file path.
         *
         * @return string - full path to main plugin file (__FILE__)
         */
        public function getMainPluginFile() {

            return $this->mainPluginFile;

        }

        /**
         * Returns absolute directory path of currently used ShellPress directory.
         *
         * @return string
         */
        public function getShellPressDir() {

            return dirname( __DIR__ );

        }

        /**
         * Gets version of instance.
         *
         * @return string
         */
        public function getPluginVersion() {

            return $this->pluginVersion;

        }

        /**
         * Gets full version of instance.
         * It's like this: `prefix`_`version`.
         *
         * @return string
         */
        public function getFullPluginVersion() {

            return $this->getPrefix() . '_' . $this->getPluginVersion();

        }

        /**
         * Returns main plugin file basename.
         *
         * @since 1.2.1
         *
         * @return string
         */
        public function getPluginBasename() {

            if( function_exists( 'plugin_basename' ) ){
                return plugin_basename( $this->getMainPluginFile() );
            } else {
                return '';
            }

        }

        /**
         * Checks if application is used inside a plugin.
         * It returns false, if directory is not equal ../wp-content/plugins
         *
         * @return bool
         */
        public function isInsidePlugin() {

            if ( strpos( __DIR__, 'wp-content/plugins' ) !== false ) {
                return true;
            } else {
                return false;
            }

        }

        /**
         * Checks if application is used inside a theme.
         * It returns false, if directory is not equal ../wp-content/themes
         *
         * @return bool
         */
        public function isInsideTheme() {

            if ( strpos( __DIR__, 'wp-content/themes' ) !== false ) {
                return true;
            } else {
                return false;
            }

        }

    }

}