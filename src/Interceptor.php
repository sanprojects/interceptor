<?php

namespace Sanprojects\Interceptor;

use Sanprojects\Interceptor\Hooks\AMQPHook;
use Sanprojects\Interceptor\Hooks\CurlHook;
use Sanprojects\Interceptor\Hooks\FileHook;
use Sanprojects\Interceptor\Hooks\PDOHook;

/**
 * Implementation adapted from:
 * https://github.com/antecedent/patchwork/blob/418a9aae80ca3228d6763a2dc6d9a30ade7a4e7e/lib/Preprocessor/Stream.php.
 *
 * @copyright  2010-2013 Ignas Rudaitis
 * @license    http://www.opensource.org/licenses/mit-license.html
 *
 * @see       http://antecedent.github.com/patchwork
 * @see       https://github.com/php-vcr/php-vcr/blob/master/src/VCR/Util/StreamProcessor.php
 * @see       https://github.com/goaop/ast-manipulator/blob/master/src/Hook/StreamWrapperHook.php
 */
class Interceptor extends \php_user_filter
{
    /**
     * Constant for a stream which was opened while including a file.
     */
    protected const STREAM_OPEN_FOR_INCLUDE = 128;

    /**
     * Stream protocol which is used when registering this wrapper.
     */
    protected const PROTOCOL = 'file';

    protected const STREAM_FILTER = 'php_code_filter';

    /**
     * @var resource|false resource for the currently opened file
     */
    protected $resource;

    /**
     * @see http://www.php.net/manual/en/class.streamwrapper.php#streamwrapper.props.context
     *
     * @var resource the current context, or NULL if no context was passed to the caller function
     */
    public $context;

    /**
     * @var bool
     */
    protected bool $isIntercepting = false;

    /**
     * @var callable[] transformers which have been appended to this stream processor
     */
    protected static array $hooks = [];

    public function __construct()
    {
    }

    /**
     * Registers current class as the PHP file stream wrapper.
     */
    public function intercept(): void
    {
        if (!$this->isIntercepting) {
            ini_set('opcache.enable', '0');
            stream_wrapper_unregister(self::PROTOCOL);
            $this->isIntercepting = stream_wrapper_register(self::PROTOCOL, __CLASS__);
        }
    }

    /**
     * Restores the original file stream wrapper status.
     */
    public function restore(): void
    {
        // stream_wrapper_restore can throw when stream_wrapper was never changed, so we unregister first
        stream_wrapper_unregister(self::PROTOCOL);
        stream_wrapper_restore(self::PROTOCOL);
    }

    /**
     * Determines that the provided uri leads to a PHP file.
     */
    protected function isPhpFile(string $uri): bool
    {
        return 'php' === pathinfo($uri, PATHINFO_EXTENSION);
    }

    protected function shouldProcess(string $uri): bool
    {
        return $this->isPhpFile($uri);
    }

    /**
     * Opens a stream and attaches registered filters.
     *
     * @param string $path       specifies the URL that was passed to the original function
     * @param string $mode       the mode used to open the file, as detailed for fopen()
     * @param int    $options    Holds additional flags set by the streams API.
     *                           It can hold one or more of the following values OR'd together.
     * @param string $openedPath if the path is opened successfully, and STREAM_USE_PATH is set in options,
     *                           opened_path should be set to the full path of the file/resource that was
     *                           actually opened
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        // file_exists catches paths like /dev/urandom that are missed by is_file.
        if ('r' === substr($mode, 0, 1) && !file_exists($path)) {
            return false;
        }

        $this->restore();

        if (isset($this->context)) {
            $this->resource = fopen($path, $mode, (bool) ($options & STREAM_USE_PATH), $this->context);
        } else {
            $this->resource = fopen($path, $mode, (bool) ($options & STREAM_USE_PATH));
        }

        if (false !== $this->resource && $options & self::STREAM_OPEN_FOR_INCLUDE && $this->shouldProcess($path)) {
            $this->appendFiltersToStream($this->resource);
        }

        $this->intercept();

        return false !== $this->resource;
    }

    /**
     * Close an resource.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-close.php
     */
    public function stream_close(): bool
    {
        if (false === $this->resource) {
            return true;
        }

        return fclose($this->resource);
    }

    /**
     * Tests for end-of-file on a file pointer.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-eof.php
     *
     * @return bool should return TRUE if the read/write position is at the end of the stream
     *              and if no more data is available to be read, or FALSE otherwise
     */
    public function stream_eof(): bool
    {
        if (false === $this->resource) {
            return false;
        }

        return feof($this->resource);
    }

    /**
     * Flushes the output.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-flush.php
     */
    public function stream_flush(): bool
    {
        if (false === $this->resource) {
            return false;
        }

        return fflush($this->resource);
    }

    /**
     * Read from stream.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-read.php
     *
     * @param int $count how many bytes of data from the current position should be returned
     *
     * @return string|false If there are less than count bytes available, return as many as are available.
     *                      If no more data is available, return either FALSE or an empty string.
     */
    public function stream_read(int $count)
    {
        if (false === $this->resource) {
            return false;
        }

        return fread($this->resource, $count);
    }

    /**
     * Seeks to specific location in a stream.
     *
     * @param int $offset the stream offset to seek to
     * @param int $whence Possible values:
     *                    SEEK_SET - Set position equal to offset bytes.
     *                    SEEK_CUR - Set position to current location plus offset.
     *                    SEEK_END - Set position to end-of-file plus offset.
     *
     * @return bool return TRUE if the position was updated, FALSE otherwise
     */
    public function stream_seek(int $offset, int $whence = SEEK_SET): bool
    {
        if (false === $this->resource) {
            return false;
        }

        return 0 === fseek($this->resource, $offset, $whence);
    }

    /**
     * Retrieve information about a file resource.
     *
     * Do not return the stat since we don't know the resulting size that the file will have
     * after having all transformations applied. When including files, PHP 7.4 and newer are sensitive
     * to file size reported by stat.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-stat.php
     *
     * @return array<int|string, int>|false see stat()
     */
    public function stream_stat()
    {
        if (false === $this->resource) {
            return false;
        }

        if (!$this->shouldProcess(stream_get_meta_data($this->resource)['uri'])) {
            return fstat($this->resource);
        }

        return false;
    }

    /**
     * Retrieve the current position of a stream.
     *
     * This method is called in response to fseek() to determine the current position.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-tell.php
     *
     * @return int|false should return the current position of the stream
     */
    public function stream_tell()
    {
        if (false === $this->resource) {
            return false;
        }

        return ftell($this->resource);
    }

    /**
     * Retrieve information about a file.
     *
     * @see http://www.php.net/manual/en/streamwrapper.url-stat.php
     *
     * @param string $path  the file path or URL to stat
     * @param int    $flags holds additional flags set by the streams API
     *
     * @return array<int|string, int>|false should return as many elements as stat() does
     */
    public function url_stat(string $path, int $flags)
    {
        $this->restore();
        if ($flags & STREAM_URL_STAT_QUIET) {
            set_error_handler(function () {
                // Use native error handler
                return false;
            });
            try {
                $result = @stat($path);
            } catch (\Exception $e) {
            }
            restore_error_handler();
        } else {
            $result = stat($path);
        }
        $this->intercept();

        return $result;
    }

    /**
     * Close directory handle.
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-closedir.php
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function dir_closedir(): bool
    {
        if (false === $this->resource) {
            return false;
        }

        closedir($this->resource);

        return true;
    }

    /**
     * Open directory handle.
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-opendir.php
     *
     * @param string $path the file path or URL to stat
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function dir_opendir(string $path): bool
    {
        $this->restore();
        if (isset($this->context)) {
            $this->resource = opendir($path, $this->context);
        } else {
            $this->resource = opendir($path);
        }
        $this->intercept();

        return false !== $this->resource;
    }

    /**
     * Read entry from directory handle.
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-readdir.php
     *
     * @return mixed should return string representing the next filename, or FALSE if there is no next file
     */
    public function dir_readdir()
    {
        if (false === $this->resource) {
            return false;
        }

        return readdir($this->resource);
    }

    /**
     * Rewind directory handle.
     *
     * @see http://www.php.net/manual/en/streamwrapper.dir-rewinddir.php
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function dir_rewinddir(): bool
    {
        if (false === $this->resource) {
            return false;
        }

        rewinddir($this->resource);

        return true;
    }

    /**
     * Create a directory.
     *
     * @see http://www.php.net/manual/en/streamwrapper.mkdir.php
     *
     * @param string $path    directory which should be created
     * @param int    $mode    the value passed to mkdir()
     * @param int    $options a bitwise mask of values, such as STREAM_MKDIR_RECURSIVE
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function mkdir(string $path, int $mode, int $options): bool
    {
        $this->restore();
        if (isset($this->context)) {
            $result = mkdir($path, $mode, (bool) ($options & STREAM_MKDIR_RECURSIVE), $this->context);
        } else {
            $result = mkdir($path, $mode, (bool) ($options & STREAM_MKDIR_RECURSIVE));
        }
        $this->intercept();

        return $result;
    }

    /**
     * Renames a file or directory.
     *
     * @see http://www.php.net/manual/en/streamwrapper.rename.php
     *
     * @param string $path_from the URL to the current file
     * @param string $path_to   the URL which the path_from should be renamed to
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function rename(string $path_from, string $path_to): bool
    {
        $this->restore();
        if (isset($this->context)) {
            $result = rename($path_from, $path_to, $this->context);
        } else {
            $result = rename($path_from, $path_to);
        }
        $this->intercept();

        return $result;
    }

    /**
     * Removes a directory.
     *
     * @see http://www.php.net/manual/en/streamwrapper.rmdir.php
     *
     * @param string $path the directory URL which should be removed
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function rmdir(string $path): bool
    {
        $this->restore();
        if (isset($this->context)) {
            $result = rmdir($path, $this->context);
        } else {
            $result = rmdir($path);
        }
        $this->intercept();

        return $result;
    }

    /**
     * Retrieve the underlaying resource.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-cast.php
     *
     * @param int $cast_as can be STREAM_CAST_FOR_SELECT when stream_select() is calling stream_cast() or
     *                     STREAM_CAST_AS_STREAM when stream_cast() is called for other uses
     *
     * @return resource|false should return the underlying stream resource used by the wrapper, or FALSE
     */
    public function stream_cast(int $cast_as)
    {
        return $this->resource;
    }

    /**
     * Advisory file locking.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-lock.php
     *
     * @param int $operation one of the operation constantes
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function stream_lock(int $operation): bool
    {
        if (false === $this->resource) {
            return false;
        }

        $operation = (0 === $operation ? LOCK_EX : $operation);

        return flock($this->resource, $operation);
    }

    /**
     * Change stream options.
     *
     * @codeCoverageIgnore
     *
     * @param int $option one of STREAM_OPTION_BLOCKING, STREAM_OPTION_READ_TIMEOUT, STREAM_OPTION_WRITE_BUFFER
     * @param int $arg1   depending on option
     * @param int $arg2   depending on option
     *
     * @return bool Returns TRUE on success or FALSE on failure. If option is not implemented,
     *              FALSE should be returned.
     */
    public function stream_set_option(int $option, int $arg1, int $arg2): bool
    {
        if (false === $this->resource) {
            return false;
        }

        switch ($option) {
            case STREAM_OPTION_BLOCKING:
                return stream_set_blocking($this->resource, (bool) $arg1);
            case STREAM_OPTION_READ_TIMEOUT:
                return stream_set_timeout($this->resource, $arg1, $arg2);
            case STREAM_OPTION_WRITE_BUFFER:
                // stream_set_write_buffer returns 0 in case of success
                return 0 === stream_set_write_buffer($this->resource, $arg1);
            case STREAM_OPTION_READ_BUFFER:
                // stream_set_read_buffer returns 0 in case of success
                return 0 === stream_set_read_buffer($this->resource, $arg1);
            // STREAM_OPTION_CHUNK_SIZE does not exist at all in PHP 7
            /*case STREAM_OPTION_CHUNK_SIZE:
                return stream_set_chunk_size($this->resource, $arg1);*/
        }

        return false;
    }

    /**
     * Write to stream.
     *
     * @throws \BadMethodCallException if called, because this method is not applicable for this stream
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-write.php
     *
     * @param string $data should be stored into the underlying stream
     *
     * @return int|false
     */
    public function stream_write(string $data)
    {
        if (false === $this->resource) {
            return false;
        }

        return fwrite($this->resource, $data);
    }

    /**
     * Delete a file.
     *
     * @see http://www.php.net/manual/en/streamwrapper.unlink.php
     *
     * @param string $path the file URL which should be deleted
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function unlink(string $path): bool
    {
        $this->restore();
        if (isset($this->context)) {
            $result = unlink($path, $this->context);
        } else {
            $result = unlink($path);
        }
        $this->intercept();

        return $result;
    }

    /**
     * Change stream options.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-metadata.php
     *
     * @param string $path   the file path or URL to set metadata
     * @param int    $option one of the stream options
     * @param mixed  $value  value depending on the option
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function stream_metadata(string $path, int $option, $value): bool
    {
        $this->restore();
        $result = false;

        switch ($option) {
            case STREAM_META_TOUCH:
                if (empty($value)) {
                    $result = touch($path);
                } else {
                    $result = touch($path, $value[0], $value[1]);
                }
                break;
            case STREAM_META_OWNER_NAME:
            case STREAM_META_OWNER:
                $result = chown($path, $value);
                break;
            case STREAM_META_GROUP_NAME:
            case STREAM_META_GROUP:
                $result = chgrp($path, $value);
                break;
            case STREAM_META_ACCESS:
                $result = chmod($path, $value);
                break;
        }
        $this->intercept();

        return $result;
    }

    /**
     * Truncate stream.
     *
     * @see http://www.php.net/manual/en/streamwrapper.stream-truncate.php
     *
     * @param int $new_size the new size
     *
     * @return bool returns TRUE on success or FALSE on failure
     */
    public function stream_truncate(int $new_size): bool
    {
        if (false === $this->resource) {
            return false;
        }

        return ftruncate($this->resource, $new_size);
    }

    /**
     * Appends the current set of php_user_filter to the provided stream.
     *
     * @param resource $stream
     */
    protected function appendFiltersToStream($stream): void
    {
        if (!\in_array(static::STREAM_FILTER, stream_get_filters(), true)) {
            $isRegistered = stream_filter_register(static::STREAM_FILTER, static::class);
            assert($isRegistered, sprintf('Failed registering stream filter "%s" on stream "%s"', static::class, static::STREAM_FILTER));
        }

        stream_filter_append($stream, self::STREAM_FILTER, STREAM_FILTER_READ);
    }

    /**
     * Applies the current filter to a provided stream.
     *
     * @param resource $in
     * @param resource $out
     * @param int      $consumed
     * @param bool     $closing
     *
     * @return int PSFS_PASS_ON
     *
     * @see http://www.php.net/manual/en/php-user-filter.filter.php
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        $bufferHandle = fopen('php://temp', 'w+');
        $outBucket = stream_bucket_new($bufferHandle, '');

        if (false === $outBucket) {
            return PSFS_ERR_FATAL;
        }

        while ($bucket = stream_bucket_make_writeable($in)) {
            $outBucket->data .= $bucket->data;
            $consumed += $bucket->datalen;
        }

        foreach (static::$hooks as $codeTransformer) {
            $outBucket->data = $codeTransformer($outBucket->data);
        }

        stream_bucket_append($out, $outBucket);

        return PSFS_PASS_ON;
    }

    /**
     * Adds code transformer to the stream processor.
     * @param callable $hook
     * @return self
     */
    public function addHook(callable $hook): self
    {
        static::$hooks[] = $hook;

        return $this;
    }

    public function addAllHooks(): self
    {
        return $this
            ->addHook([new CurlHook(), 'filter'])
            ->addHook([new FileHook(), 'filter'])
            ->addHook([new PDOHook(), 'filter']);
    }

    public static function interceptAll(): self
    {
        $interceptor = new Interceptor();
        $interceptor->addAllHooks();
        $interceptor->intercept();

        return $interceptor;
    }
}
