<?php

namespace App\Traits;

/**
 * Trait HasCustomShortflakePrimary
 *
 * This trait is used to generate unique IDs similar to Twitter's Snowflake IDs.
 * It replaces the need for the kra8/laravel-snowflake library and provides methods
 * for generating, parsing, and working with Snowflake IDs.
 *
 * @package App\Traits
 */
trait HasCustomShortflakePrimary
{
    // Replace constants with private static variables
    /** @var string Default epoch datetime */
    private static $DEFAULT_EPOCH_DATETIME = '2023-08-28 00:00:00';
    /** @var int Total bits of the ID */
    private static $ID_BITS = 63;
    /** @var int Bits for timestamp */
    private static $TIMESTAMP_BITS = 41;
    /** @var int Bits for worker ID */
    private static $WORKER_ID_BITS = 5;
    /** @var int Bits for datacenter ID */
    private static $DATACENTER_ID_BITS = 5;
    /** @var int Bits for sequence */
    private static $SEQUENCE_BITS = 12;
    /** @var int Timeout in milliseconds */
    private static $TIMEOUT = 1000;
    /** @var int Maximum sequence value */
    private static $MAX_SEQUENCE = 4095;

    // Properties from the Snowflake class
    /** @var int Epoch timestamp in milliseconds */
    protected static $epoch;
    /** @var int Last timestamp when an ID was generated */
    private static $lastTimestamp;
    /** @var int Sequence number */
    private static $sequence = 0;
    /** @var int Datacenter ID */
    private static $datacenterId;
    /** @var int Worker ID */
    private static $workerId;

    /**
     * Initialize Snowflake properties from configuration.
     */
    public static function initializeSnowflakeProperties()
    {
        $timestamp = strtotime(config('snowflake.epoch'));
        self::$epoch = $timestamp * 1000;
        self::$workerId = config('snowflake.worker_id');
        self::$datacenterId = config('snowflake.datacenter_id');
        self::$lastTimestamp = self::$epoch;
    }

    /**
     * Boot the trait.
     *
     * This method is automatically called by Laravel when the model is being booted.
     * It sets up the model's events and initializes the Snowflake properties.
     */
    public static function bootHasCustomShortflakePrimary()
    {
        if (config('snowflake.use_snowflake', true)) {
            static::creating(function ($model) {
                $model->{$model->getKeyName()} = $model->short();
            });
        }
    
        self::initializeSnowflakeProperties();
    }
    
    /**
     * Make a sequence ID for the current timestamp.
     *
     * @param int $currentTime Current timestamp in milliseconds.
     * @return int Sequence ID.
     */
    public function makeSequenceId(int $currentTime): int
    {
        if (self::$lastTimestamp === $currentTime) {
            self::$sequence = (self::$sequence + 1) & self::$MAX_SEQUENCE;
            if (self::$sequence == 0) {
                // Sequence overflow, wait for the next millisecond
                do {
                    $currentTime = $this->timestamp();
                } while ($currentTime <= self::$lastTimestamp);
            }
        } else {
            self::$sequence = 0;
        }

        self::$lastTimestamp = $currentTime;
        return self::$sequence;
    }

    /**
     * Generate a new Snowflake ID.
     *
     * @return int Snowflake ID.
     * @throws \Exception If the clock moves backward.
     */
    public function generateId(): int
    {
        $currentTime = $this->timestamp();
        if ($currentTime < self::$lastTimestamp) {
            // Clock moved backward, throw an exception
            throw new \Exception('Clock moved backward');
        }

        $sequenceId = $this->makeSequenceId($currentTime);
        return $this->toSnowflakeId($currentTime - self::$epoch, $sequenceId);
    }

    /**
     * Generate the next Snowflake ID.
     *
     * @return int Next Snowflake ID.
     */
    public function next(): int
    {
        return $this->generateId();
    }

    /**
     * Generate a short Snowflake ID.
     *
     * This method generates a shorter version of the Snowflake ID that only includes
     * the timestamp and sequence number.
     *
     * @return int Short Snowflake ID.
     * @throws \Exception If the clock moves backward.
     */
    public function short(): int
    {
        $currentTime = $this->timestamp();
        if ($currentTime < self::$lastTimestamp) {
            // Clock moved backward, throw an exception
            throw new \Exception('Clock moved backward');
        }

        $sequenceId = $this->makeSequenceId($currentTime);
        return $this->toShortflakeId($currentTime - self::$epoch, $sequenceId);
    }

    /**
     * Convert timestamp and sequence number to a short Snowflake ID.
     *
     * @param int $currentTime Current timestamp in milliseconds since the epoch.
     * @param int $sequenceId Sequence number.
     * @return int Short Snowflake ID.
     */
    public function toShortflakeId(int $currentTime, int $sequenceId)
    {
        return ($currentTime << self::$SEQUENCE_BITS) | ($sequenceId);
    }

    /**
     * Convert timestamp, datacenter ID, worker ID, and sequence number to a Snowflake ID.
     *
     * @param int $currentTime Current timestamp in milliseconds since the epoch.
     * @param int $sequenceId Sequence number.
     * @return int Snowflake ID.
     */
    public function toSnowflakeId(int $currentTime, int $sequenceId)
    {
        $workerIdLeftShift = self::$SEQUENCE_BITS;
        $datacenterIdLeftShift = self::$WORKER_ID_BITS + self::$SEQUENCE_BITS;
        $timestampLeftShift = self::$DATACENTER_ID_BITS + self::$WORKER_ID_BITS + self::$SEQUENCE_BITS;

        return ($currentTime << $timestampLeftShift)
            | (self::$datacenterId << $datacenterIdLeftShift)
            | (self::$workerId << $workerIdLeftShift)
            | ($sequenceId);
    }

    /**
     * Get the current timestamp in milliseconds.
     *
     * @return int Current timestamp in milliseconds.
     */
    public function timestamp(): int
    {
        return (int) floor(microtime(true) * 1000);
    }

    /**
     * Parse a Snowflake ID into its components.
     *
     * @param int $id Snowflake ID.
     * @return array Parsed components of the Snowflake ID.
     */
    public function parse(int $id): array
    {
        $id = decbin($id);

        $datacenterIdLeftShift = self::$WORKER_ID_BITS + self::$SEQUENCE_BITS;
        $timestampLeftShift = self::$DATACENTER_ID_BITS + self::$WORKER_ID_BITS + self::$SEQUENCE_BITS;

        $binaryTimestamp = substr($id, 0, -$timestampLeftShift);
        $binarySequence = substr($id, -self::$SEQUENCE_BITS);
        $binaryWorkerId = substr($id, -$datacenterIdLeftShift, self::$WORKER_ID_BITS);
        $binaryDatacenterId = substr($id, -$timestampLeftShift, self::$DATACENTER_ID_BITS);
        $timestamp = bindec($binaryTimestamp);
        $datetime = date('Y-m-d H:i:s', ((int) (($timestamp + self::$epoch) / 1000) | 0));

        return [
            'binary_length' => strlen($id),
            'binary' => $id,
            'binary_timestamp' => $binaryTimestamp,
            'binary_sequence' => $binarySequence,
            'binary_worker_id' => $binaryWorkerId,
            'binary_datacenter_id' => $binaryDatacenterId,
            'timestamp' => $timestamp,
            'sequence' => bindec($binarySequence),
            'worker_id' => bindec($binaryWorkerId),
            'datacenter_id' => bindec($binaryDatacenterId),
            'epoch' => self::$epoch,
            'datetime' => $datetime,
        ];
    }

    /**
     * Get the incrementing status of the model's primary key.
     *
     * @return bool False, because the primary key is not auto-incrementing.
     */
    public function getIncrementing()
    {
        return (config('snowflake.use_snowflake', true)) ? false : true;
    }

    /**
     * Get the data type of the model's primary key.
     *
     * @return string Data type of the primary key.
     */
    public function getKeyType()
    {
        return 'bigint';
    }
}
