<?php
// vim: foldmethod=marker tabstop=4 shiftwidth=4 autoindent
/**
 *  Cachemanager.php
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @license    http://www.opensource.org/licenses/bsd-license.php The BSD License
 *  @package    Ethna
 *  @version    $Id$
 */

/**
 *  キャッシュマネージャプラグインクラス
 *
 *  @author     Masaki Fujimoto <fujimoto@php.net>
 *  @access     public
 *  @package    Ethna
 */
class Ethna_Plugin_Cachemanager
{
    /**#@+  @access private */

    /** @protected    string  現在のネームスペース */
    protected $namespace = '';

    /** @protected    array  plugin options */
    protected $opt;

    /** @protected    object  Ethna_Backend       backendオブジェクト */
    protected $backend;

    /** @protected    object  Ethna_Config        設定オブジェクト    */
    protected $config;

    /** $plugin->getPlugin('Cachemanager', 'Memcache'); の呼び出しに対応する */
    protected $type;
    protected $name;

    /**
     *  コンストラクタ
     *
     *  @access public
     */
    public function __construct($controller, $type = null, $name = null)
    {
        $this->controller = $controller;
        $this->backend = $this->controller->getBackend();
        $this->config = $this->controller->getConfig();

        $this->type = $type;
        $this->name = $name;

	// load config
	$this->_loadConfig();

        if (isset($this->opt['namespace'])) {
            $this->namespace = $this->opt['namespace'];
        }
    }

    /**
     *  _loadConfig
     *
     *  @access protected
     */
    protected function _loadConfig()
    {
        $config = $this->config;
        $plugin_config = $config->get('plugin');

        if ($plugin_config === null || !isset($plugin_config[$this->type])
            || ($this->name !== null && !isset($plugin_config[$this->type][$this->name]))) {
            $this->opt = $this->config_default;
        }
        else {
            if ($this->name === null) {
                $this->opt = array_merge($this->config_default, $plugin_config[$this->type]);
            }
            else {

                $this->opt = array_merge($this->config_default, $plugin_config[$this->type][$this->name]);
            }
        }

        return true;
    }

    /**
     *  キャッシュネームスペースを取得する
     *
     *  @access public
     *  @return string  現在のキャッシュネームスペース
     */
    public function getNamespace($namespace = null)
    {
        if ($namespace === null) {
            return $this->namespace;
        }
        else {
            return $namespace;
        }
    }

    /**
     *  キャッシュネームスペースを設定する
     *
     *  @access public
     *  @param  string  $namespace  ネームスペース
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     *  キャッシュに設定された値を取得する
     *
     *  キャッシュに値が設定されている場合はキャッシュ値
     *  が戻り値となる。キャッシュに値が無い場合やlifetime
     *  を過ぎている場合、エラーが発生した場合はEthna_Error
     *  オブジェクトが戻り値となる。
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  int     $lifetime   キャッシュ有効期間
     *  @param  string  $namespace  キャッシュネームスペース
     *  @return mixed   キャッシュ値
     */
    public function get($key, $lifetime = null, $namespace = null)
    {
    }

    /**
     *  キャッシュの最終更新日時を取得する
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  string  $namespace  キャッシュネームスペース
     *  @return int     最終更新日時(unixtime)
     */
    public function getLastModified($key, $namespace = null)
    {
    }

    /**
     *  キャッシュに値を設定する
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  mixed   $value      キャッシュ値
     *  @param  int     $timestamp  キャッシュ最終更新時刻(unixtime)
     *  @param  string  $namespace  キャッシュネームスペース
     */
    public function set($key, $value, $timestamp = null, $namespace = null)
    {
    }

    /**
     *  値がキャッシュされているかどうかを取得する
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  int     $lifetime   キャッシュ有効期間
     *  @param  string  $namespace  キャッシュネームスペース
     */
    public function isCached($key, $timestamp = null, $namespace = null)
    {
    }

    /**
     *  キャッシュから値を削除する
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  string  $namespace  キャッシュネームスペース
     */
    public function clear($key, $namespace = null)
    {
    }

    /**
     *  キャッシュデータをロックする
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  int     $timeout    ロックタイムアウト
     *  @param  string  $namespace  キャッシュネームスペース
     *  @return bool    true:成功 false:失敗
     */
    public function lock($key, $timeout = 5, $namespace = null)
    {
        return false;
    }

    /**
     *  キャッシュデータのロックを解除する
     *
     *  @access public
     *  @param  string  $key        キャッシュキー
     *  @param  string  $namespace  キャッシュネームスペース
     *  @return bool    true:成功 false:失敗
     */
    public function unlock($key, $namespace = null)
    {
        return false;
    }

    /**
     * 圧縮フラグを立てる
     *
     * MySQLなどいくつかの子クラスで有効
     * 
     * @access public
     * @param bool $flag フラグ
     */
    public function setCompress($flag) {
        return false;
    }
}
