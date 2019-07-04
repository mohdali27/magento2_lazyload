<?php
namespace Potato\Compressor\Model\Optimisation\Processor\Merger;

use Potato\Compressor\Model\Optimisation\Processor\Finder\Result\Tag;
use Potato\Compressor\Helper\File as FileHelper;
use Potato\Compressor\Helper\Log as LogHelper;

abstract class AbstractMerger
{
    /** @var FileHelper  */
    protected $fileHelper;

    /** @var LogHelper  */
    protected $logHelper;

    /**
     * AbstractMerger constructor.
     * @param FileHelper $fileHelper
     * @param LogHelper $logHelper
     */
    public function __construct(FileHelper $fileHelper, LogHelper $logHelper)
    {
        $this->fileHelper = $fileHelper;
        $this->logHelper = $logHelper;
    }

    /**
     * @param Tag[] $list
     *
     * @return null|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function merge($list)
    {
        $localPathList = [];
        foreach ($list as $key => $tag) {
            $localPathList[] = $this->getLocalPath(
                $this->getPathFromTag($tag)
            );
        }
        return $this->mergeFileList($localPathList);
    }

    /**
     * @param string[] $files
     *
     * @return string|null
     */
    abstract public function mergeFileList($files);

    /**
     * @param Tag $tag
     *
     * @return string
     */
    abstract protected function getPathFromTag($tag);

    /**
     * @param string $url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getLocalPath($url)
    {
        return $this->fileHelper->getLocalPathFromUrl($url);
    }

    /**
     * @param array $fileList
     *
     * @return string
     */
    protected function getMergeFilename($fileList)
    {
        $result = [];
        foreach ($fileList as $filename) {
            $timestamp = filemtime(realpath($filename));
            $result[] = $filename . '+' . $timestamp;
        }
        return implode(',', $result);
    }

    /**
     * @param array $srcFiles
     * @param bool  $targetFile
     * @param bool  $mustMerge
     * @param null  $beforeMergeCallback
     * @param array $extensionsFilter
     *
     * @return bool|string
     */
    protected function mergeFiles(array $srcFiles, $targetFile = false, $mustMerge = false,
        $beforeMergeCallback = null, $extensionsFilter = [])
    {
        try {
            // check whether merger is required
            $shouldMerge = $mustMerge || !$targetFile;
            if (!$shouldMerge) {
                if (!file_exists($targetFile)) {
                    $shouldMerge = true;
                } else {
                    $targetMtime = filemtime($targetFile);
                    foreach ($srcFiles as $file) {
                        if (!file_exists($file) || @filemtime($file) > $targetMtime) {
                            $shouldMerge = true;
                            break;
                        }
                    }
                }
            }

            // merge contents into the file
            if ($shouldMerge) {
                if ($targetFile && !is_writeable(dirname($targetFile))) {
                    // no translation intentionally
                    throw new \Exception(sprintf('Path %s is not writable.', dirname($targetFile)));
                }

                // filter by extensions
                if ($extensionsFilter) {
                    if (!is_array($extensionsFilter)) {
                        $extensionsFilter = array($extensionsFilter);
                    }
                    if (!empty($srcFiles)){
                        foreach ($srcFiles as $key => $file) {
                            $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            if (!in_array($fileExt, $extensionsFilter)) {
                                unset($srcFiles[$key]);
                            }
                        }
                    }
                }
                if (empty($srcFiles)) {
                    // no translation intentionally
                    throw new \Exception('No files to compile.');
                }

                $data = '';
                foreach ($srcFiles as $file) {
                    if (!file_exists($file)) {
                        continue;
                    }
                    $contents = file_get_contents($file) . "\n";
                    if ($beforeMergeCallback && is_callable($beforeMergeCallback)) {
                        $contents = call_user_func($beforeMergeCallback, $file, $contents);
                    }
                    $data .= $contents;
                }
                if (!$data) {
                    // no translation intentionally
                    throw new \Exception(sprintf("No content found in files:\n%s", implode("\n", $srcFiles)));
                }
                if ($targetFile) {
                    file_put_contents($targetFile, $data, LOCK_EX);
                } else {
                    return $data; // no need to write to file, just return data
                }
            }

            return true; // no need in merger or merged into file successfully
        } catch (\Exception $e) {
            $this->logHelper->processorLog($e->getMessage());
        }
        return false;
    }
}
