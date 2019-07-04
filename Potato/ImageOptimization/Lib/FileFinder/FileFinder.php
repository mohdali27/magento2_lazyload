<?php
namespace Potato\ImageOptimization\Lib\FileFinder;

/**
 * Class FileFinder
 */
class FileFinder
{
    const ITERATION_LIMIT = 1000;

    protected $_dir = null;
    protected $_callback = null;
    protected $_is_folder_path_excluded_callback = null;
    protected $_startPath = null;

    /**
     * @param array $config
     */
    public function __construct($config)
    {
        $this->_dir = $config['dir'];
        $this->_callback = $config['callback'];
        $this->_is_folder_path_excluded_callback = $config['is_folder_path_excluded_callback'];
        if (array_key_exists('start_path', $config)) {
            $this->_startPath = $config['start_path'];
        }
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function find()
    {
        $originalIni = ini_get('xdebug.max_nesting_level');
        ini_set('xdebug.max_nesting_level', self::ITERATION_LIMIT * 10);

        $startPath = $this->_startPath;
        while(true) {
            $result = $this->_runWorker(self::ITERATION_LIMIT, $startPath);
            if (null === $result) {
                break;
            }
            $startPath = $result;
        }

        ini_set('xdebug.max_nesting_level',$originalIni);
        return $this;
    }

    /**
     * @param int $iterationLimit
     * @param string $fromPath
     *
     * @return string|null
     * @throws \Exception
     */
    protected function _runWorker($iterationLimit, $fromPath)
    {
        $worker = new FileFinderWorker($this, $iterationLimit, $fromPath);
        $worker->find();
        return $worker->getLastPath();
    }

    public function getDir()
    {
        return $this->_dir;
    }

    public function getCallback()
    {
        return $this->_callback;
    }

    public function isFolderPathExcluded($dir)
    {
        return call_user_func($this->_is_folder_path_excluded_callback, $dir);
    }
}
