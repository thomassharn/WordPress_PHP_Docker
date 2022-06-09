<?php

include_once 'Utilities.php';
require_once dirname(__FILE__) . '/includes/lib/mo-saml-options-enum.php';

class MoSAMLLogger
{
    const INFO = 'INFO';
    const DEBUG = 'DEBUG';
    const ERROR = 'ERROR';
    const Critical = "CRITICAL";
    private $log_file_writable = FALSE;

    /**
     * @return bool
     */
    public function is_log_file_writable()
    {
        return $this->log_file_writable;
    }
    private $plugin_directory;
    private $wp_config_editor;
    protected $cached_logs = array();


    public function __construct()
    {

        //For setting up debug directory for log files
        add_action('plugins_loaded', array($this, 'write_cached_logs'));
        $upload_dir = wp_upload_dir(null, false);
        $this->define('MO_SAML_DIRC', $upload_dir['basedir'] . '/mo-saml-logs/');

        // Debug directory for log files
        //if directory doesn't exist then create
        if (is_writable($upload_dir['basedir'])) {
            $this->log_file_writable = TRUE;
            if (!is_dir(MO_SAML_DIRC))
                self::create_files();
        }
    }

    /**
     * Add a log entry along with the log level
     *
     * @param string $log_message
     * @param string $log_level
     */

    public function add_log($log_message = "", $log_level = self::INFO)
    {
        $e = new Exception();
        $trace = $e->getTrace();
        //if log is off then we will not add log messages
        $message = "";
        // for adding date and time of log message

        $message = $message . date("Y-m-d") . "" . date("h:i:sa") . " UTC " . "{$log_level}";
        $message = $message . ' : ' . $trace[0]['file'] . ' : ' . $trace[1]['function'] . ' : ' . $trace[0]['line'];
        $message =  $message . ' ' . str_replace(array("\r", "\n", "\t"), '', rtrim($log_message))  . PHP_EOL;
        $message = PHP_EOL . preg_replace("/[,]/", "\n", $message);

        if (!MoSAMLLogger::is_debugging_enabled())
            return;
        if ($this->log_file_writable) {
            $log_file = @fopen(self::get_log_file_path('mo_saml'), "a+");
            if ($log_file) {
                @fwrite($log_file, $message);
            }
            fclose($log_file);
        }
    }

    /**
     * Cache log to write later.
     *
     * @param string $entry Log entry text.
     * @param string $handle Log entry handle.
     */
    protected function cache_log($entry)
    {
        $this->cached_logs[] = array(
            'entry'  => $entry,
            'handle' => 'mo_saml',
        );
    }

    /**
     * Write cached logs.
     */
    public function write_cached_logs()
    {
        foreach ($this->cached_logs as $log) {
            $this->add($log['entry'], $log['handle']);
        }
    }

    /**
     *  Logs critical errors
     */
    function log_critical_errors()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR), true)) {
            $this->add_log(
            /* translators: 1: error message 2: file name and path 3: line number */
                sprintf(__('%1$s in %2$s on line %3$s', 'mo'), $error['message'], $error['file'], $error['line']) . PHP_EOL,
                self::Critical
            );
            do_action('miniOrange-down-error', $error);
        }
    }

    /**
     * Get all log files in the log directory.
     *
     * @since 4.9.0
     * @return array
     */
    public static function get_log_files()
    {
        $files  = @scandir(MO_SAML_DIRC);
        $result = array();
        if (!empty($files)) {
            foreach ($files as $key => $value) {
                if (!in_array($value, array('.', '..'), true)) {
                    if (!is_dir($value) && strstr($value, '.log')) {
                        $result[sanitize_title($value)] = $value;
                    }
                }
            }
        }

        return $result;
    }
    /**
     * Deletes all the files in the Log directory older than 7 Days
     */

    public static function delete_logs_before_timestamp($timestamp = 0)
    {
        if (!$timestamp) {
            return;
        }
        $log_files = self::get_log_files();
        foreach ($log_files as $log_file) {
            $last_modified = filemtime(trailingslashit(MO_SAML_DIRC) . $log_file);
            if ($last_modified < $timestamp) {
                @unlink(trailingslashit(MO_SAML_DIRC) . $log_file); // @codingStandardsIgnoreLine.
            }
        }
    }
    /**
     * Get the file path of current log file used by plugins
     */
    public static function get_log_file_path($handle)
    {
        if (function_exists('wp_hash')) {
            return trailingslashit(MO_SAML_DIRC) . self::get_log_file_name($handle);
        } else {
            return false;
        }
    }
    /**
     * To get the log for based on the time
     */

    public static function get_log_file_name($handle)
    {
        if (function_exists('wp_hash')) {
            $date_suffix = date('Y-m-d', time());
            $hash_suffix = wp_hash($handle);
            return sanitize_file_name(implode('-', array($handle, $date_suffix, $hash_suffix)) . '.log');
        } else {
            _doing_it_wrong(__METHOD__, __('This method should not be called before plugins_loaded.', 'miniorange'), mo_saml_options_plugin_constants::Version);
            return false;
        }
    }


    /**
     * Used to show the UI part of the log feature to user screen.
     */
    static function mo_saml_log_page()
    {
        mo_saml_display_log_page();
    }
    /**
     * Creates files Index.html for directory listing
     * and local .htaccess rule to avoid hotlinking
     */
    private static function create_files()
    {

        $upload_dir      = wp_get_upload_dir();

        $files = array(

            array(
                'base'    => MO_SAML_DIRC,
                'file'    => '.htaccess',
                'content' => 'deny from all',
            ),
            array(
                'base'    => MO_SAML_DIRC,
                'file'    => 'index.html',
                'content' => '',
            ),
        );

        foreach ($files as $file) {
            if (wp_mkdir_p($file['base']) && !file_exists(trailingslashit($file['base']) . $file['file'])) {
                $file_handle = @fopen(trailingslashit($file['base']) . $file['file'], 'wb'); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
                if ($file_handle) {
                    fwrite($file_handle, $file['content']);
                    fclose($file_handle);
                }
            }
        }
    }
    /**
     * Check if a constant is defined if not define a cosnt
     */
    private function define($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }

    /**
     * To check if Debug constant is defined and logs are enabled
     * @return bool
     */
    static function is_debugging_enabled()
    {
        if (!defined('MO_SAML_LOGGING')) {
            return false;
        } else {
            return MO_SAML_LOGGING;
        }
    }
}
