<?php

namespace Glip;

use Generator;

/**
 * Class GitStreamWrapper
 * @package Glip
 */
class GitStreamWrapper {

	/**
	 * @param string $path
	 * @return array
	 */
	protected static function getPathParts($path) {
		$path = preg_replace("#[\\\\]#", '/', $path);
		if (preg_match('#^(\w+)://(.*\.git/)([^/]+)/(.*)$#Dsiu', $path, $matches)) {
			$parts = [
				'scheme'        => $matches[1],
				'repository'    => $matches[2],
				'branch'        => $matches[3],
				'path'          => $matches[4],
				'full'          => $matches[0],
			];
			return $parts;
		} else
			; // TODO throw exception
	}

	/** @var string */
	protected $dirPath;
	/** @var Git */
	protected $repository;
	/** @var GitObject */
	protected $branch;
	/** @var GitTree */
	protected $branchTree;
	/** @var GitTree */
	protected $pathTree;
	/** @var Generator */
	protected $filesListGenerator = null;

	/** @var GitBlob */
	protected $file;
	/** @var int */
	protected $fileOffset = 0;

	/**
	 * @param string $path
	 * @param int $options
	 * @return bool
	 */
	function dir_opendir($path, /** @noinspection PhpUnusedParameterInspection */ $options) {
		$pathParts = self::getPathParts($this->dirPath = $path);
		$repository = $this->repository = new Git($pathParts['repository']);
		$this->branch = $repository->getObject($repository->getTip($pathParts['branch']));
		$this->branchTree = $repository->getObject($this->branch->tree);
		$this->pathTree = $repository->getObject($this->branchTree->find($pathParts['path']));
		return true;
	}

	/**
	 * @return bool
	 */
	function dir_closedir() {
		$this->repository = null;
		$this->branch = null;
		$this->branchTree = null;
		$this->pathTree = null;
		$this->filesListGenerator = null;
		return true;
	}

	/**
	 * @return string
	 */
	function dir_readdir() {
		if (is_null($this->filesListGenerator)) {
			$files = $this->filesListGenerator = $this->pathTree->getFiles();
		} else {
			$files = $this->filesListGenerator;
		}
		if ($files->valid()) {
			$filePath = $files->key();
			$files->next();
		} else {
			$filePath = false;
		}
		return $filePath;
	}

	/**
	 * @return bool
	 */
	function dir_rewinddir() {
		$this->filesListGenerator = null;
		return true;
	}

	/**
	 * @param string $path
	 * @param string $mode
	 * @param int $options
	 * @param string $opened_path
	 * @return bool
	 */
	function stream_open($path, $mode, $options, &$opened_path) {
		$pathParts = self::getPathParts($path);
		$repository = $this->repository = new Git($pathParts['repository']);
		$this->branch = $repository->getObject($repository->getTip($pathParts['branch']));
		$this->branchTree = $repository->getObject($this->branch->tree);
		$this->file = $repository->getObject($this->branchTree->find($pathParts['path']));
		return true;
	}

	function stream_close() {
		$this->repository = null;
		$this->branch = null;
		$this->branchTree = null;
		$this->file = null;
	}

	/**
	 * @return array
	 */
	function stream_stat() {
		return [
			0           => 0, // dev device number
			1           => 0, // inode number *
			2           => 33206, // inode protection mode
			3           => 0, // number of links
			4           => 0, // userid of owner *
			5           => 0, // groupid of owner *
			6           => 0, // device type, if inode device
			7           => 0, // size in bytes
			8           => 0, // time of last access (Unix timestamp)
			9           => 0, // time of last modification (Unix timestamp)
			10          => 0, // time of last inode change (Unix timestamp)
			11          => -1, // blocksize of filesystem IO **
			12          => -1, // number of 512-byte blocks allocated **

			'dev'       => 0, // device number
			'ino'       => 0, // inode number *
			'mode'      => 33206, // inode protection mode
			'nlink'     => 0, // number of links
			'uid'       => 0, // userid of owner *
			'gid'       => 0, // groupid of owner *
			'rdev'      => 0, // device type, if inode device
			'size'      => 0, // size in bytes
			'atime'     => 0, // time of last access (Unix timestamp)
			'mtime'     => 0, // time of last modification (Unix timestamp)
			'ctime'     => 0, // time of last inode change (Unix timestamp)
			'blksize'   => -1, // blocksize of filesystem IO **
			'blocks'    => -1, // number of 512-byte blocks allocated **
		];
	}

	function stream_read($count) {
		$content = substr($this->file->data, $this->fileOffset, $count);
		$this->fileOffset += $count;
		return $content;
	}

	function stream_write($data) {
		// TODO Throw exception
		return 0;
	}

	function stream_tell() {
		// TODO Throw exception
		return 0;
	}

	function stream_eof() {
		return $this->fileOffset >= strlen($this->file->data);
	}

	function stream_seek($offset, $whence) {
		$this->fileOffset = 0;
		return true;
	}

	/**
	 * @param string $path
	 * @param int $option
	 * @param mixed $value
	 * @return bool
	 */
	function stream_metadata($path, $option, $value) {
		// TODO Throw exception
		return false;
	}

	/**
	 * @return array
	 */
	function url_stat() {
		return [
			0           => 0, // dev device number
			1           => 0, // inode number *
			2           => 33206, // inode protection mode
			3           => 0, // number of links
			4           => 0, // userid of owner *
			5           => 0, // groupid of owner *
			6           => 0, // device type, if inode device
			7           => 0, // size in bytes
			8           => 0, // time of last access (Unix timestamp)
			9           => 0, // time of last modification (Unix timestamp)
			10          => 0, // time of last inode change (Unix timestamp)
			11          => -1, // blocksize of filesystem IO **
			12          => -1, // number of 512-byte blocks allocated **

			'dev'       => 0, // device number
			'ino'       => 0, // inode number *
			'mode'      => 33206, // inode protection mode
			'nlink'     => 0, // number of links
			'uid'       => 0, // userid of owner *
			'gid'       => 0, // groupid of owner *
			'rdev'      => 0, // device type, if inode device
			'size'      => 0, // size in bytes
			'atime'     => 0, // time of last access (Unix timestamp)
			'mtime'     => 0, // time of last modification (Unix timestamp)
			'ctime'     => 0, // time of last inode change (Unix timestamp)
			'blksize'   => -1, // blocksize of filesystem IO **
			'blocks'    => -1, // number of 512-byte blocks allocated **
		];
	}

}