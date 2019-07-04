<?php
namespace Potato\ImageOptimization\Lib\FileFinder;


class FileFinderWorker
{
    protected $_fileFinder = null;
    protected $_iterationLimit = null;
    protected $_startPath = null;

    protected $_iterationCount = 0;
    protected $_callbackCount = 0;
    protected $_lastPath;
    protected $_cacheDir = array();


    /**
     * FileFinderWorker constructor.
     * @param FileFinder $finder
     * @param $iterationLimit
     * @param null $startPath
     */
    public function __construct(FileFinder $finder, $iterationLimit, $startPath = null)
    {
        $this->_fileFinder = $finder;
        $this->_iterationLimit = $iterationLimit;
        $this->_startPath = $startPath;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function find()
    {
        if (!is_dir($this->_fileFinder->getDir())) {
            throw new \Exception('This directory is not exists or not directory: ' . $this->_fileFinder->getDir());
        }
        if (null !== $this->_startPath) {
            $this->_goUp($this->_startPath);
            return $this;
        }
        $this->_readDir($this->_fileFinder->getDir());
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastPath()
    {
        return $this->_lastPath;
    }

    /**
     * @param string $dirPath
     * @param string $after = null
     *
     * @return $this
     * @throws \Exception
     */
    protected function _readDir($dirPath, $after = null)
    {
        if ($this->_fileFinder->isFolderPathExcluded($dirPath . DIRECTORY_SEPARATOR)) {
            $this->_goUp($dirPath);
            return $this;
        }
        if (!$this->_checkIteration($dirPath)) {
            return $this;
        }
        if (!array_key_exists(md5($dirPath), $this->_cacheDir)) {
            $list = scandir($dirPath);
            if (count($list) > 1000) {
                $this->_cacheDir[md5($dirPath)] = $list;
            }
        } else {
            $list = $this->_cacheDir[md5($dirPath)];
        }

        if (FALSE === $list) {
            throw new \Exception('scandir return FALSE for this directory: ' . $dirPath);
        }
        $list = array_diff($list, array('..', '.'));
        $list = array_values($list);
        for ($i=0; $i< count($list); $i++) {
            if (null !== $after && strcmp($list[$i], $after) <= 0) {//if $filename less or equal $after then
                continue;
            }
            $path = $dirPath . DIRECTORY_SEPARATOR . $list[$i];
            if (is_dir($path)) {
                unset($list, $dirPath, $after);
                return $this->_readDir($path);
            }
            $result = $this->_callForFile($path);
            if (!$result) {
                unset($list);
                return $this;
            }
        }
        unset($list, $path, $filename, $result, $after);
        $this->_goUp($dirPath);
        return $this;
    }

    /**
     * @param string $dirPath
     *
     * @return $this
     */
    protected function _goUp($dirPath)
    {
        if (!$this->_checkIteration($dirPath)) {
            return $this;
        }
        if ($dirPath === $this->_fileFinder->getDir()) {
            return $this;
        }
        $list = explode(DIRECTORY_SEPARATOR, $dirPath);
        $filename = array_pop($list);
        $path = join(DIRECTORY_SEPARATOR, $list);
        unset($list, $dirPath);
        $this->_readDir($path, $filename);
        return $this;
    }

    /**
     * @param string $filePath
     *
     * @return $this
     */
    protected function _callForFile($filePath)
    {
        call_user_func($this->_fileFinder->getCallback(), $filePath);
        $this->_callbackCount++;
        return $this;
    }

    /**
     * @param string
     *
     * @return bool
     */
    protected function _checkIteration($path)
    {
        if ($this->_iterationLimit <= $this->_iterationCount) {
            $this->_lastPath = $path;
            return false;
        }
        $this->_iterationCount++;
        return true;
    }
}