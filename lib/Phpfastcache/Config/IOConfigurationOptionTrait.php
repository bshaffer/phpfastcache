<?php

/**
 *
 * This file is part of Phpfastcache.
 *
 * @license MIT License (MIT)
 *
 * For full copyright and license information, please see the docs/CREDITS.txt and LICENCE files.
 *
 * @author Georges.L (Geolim4)  <contact@geolim4.com>
 * @author Contributors  https://github.com/PHPSocialNetwork/phpfastcache/graphs/contributors
 */
declare(strict_types=1);

namespace Phpfastcache\Config;

use Phpfastcache\Exceptions\PhpfastcacheInvalidConfigurationException;

trait IOConfigurationOptionTrait
{
    protected bool $secureFileManipulation = false;

    protected bool $autoHtaccessCreationEnabled = true;

    protected string $securityKey = '';

    protected string $cacheFileExtension = 'txt';

    protected int $defaultChmod = 0777;

    /**
     * @return string
     */
    public function getSecurityKey(): string
    {
        return $this->securityKey;
    }

    /**
     * @param string $securityKey
     * @return static
     */
    public function setSecurityKey(string $securityKey): static
    {
        $this->securityKey = $securityKey;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAutoHtaccessCreationEnabled(): bool
    {
        return $this->autoHtaccessCreationEnabled;
    }

    /**
     * @param bool $autoHtaccessCreationEnabled
     * @return static
     */
    public function setAutoHtaccessCreationEnabled(bool $autoHtaccessCreationEnabled): static
    {
        $this->autoHtaccessCreationEnabled = $autoHtaccessCreationEnabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecureFileManipulation(): bool
    {
        return $this->secureFileManipulation;
    }

    /**
     * @param bool $secureFileManipulation
     * @return self
     */
    public function setSecureFileManipulation(bool $secureFileManipulation): static
    {
        $this->secureFileManipulation = $secureFileManipulation;
        return $this;
    }


    /**
     * @return string
     */
    public function getCacheFileExtension(): string
    {
        return $this->cacheFileExtension;
    }

    /**
     * @param string $cacheFileExtension
     * @return static
     * @throws PhpfastcacheInvalidConfigurationException
     */
    public function setCacheFileExtension(string $cacheFileExtension): static
    {
        $safeFileExtensions = \explode('|', IOConfigurationOptionInterface::SAFE_FILE_EXTENSIONS);

        if (str_contains($cacheFileExtension, '.')) {
            throw new PhpfastcacheInvalidConfigurationException('cacheFileExtension cannot contain a dot "."');
        }
        if (!\in_array($cacheFileExtension, $safeFileExtensions, true)) {
            throw new PhpfastcacheInvalidConfigurationException(
                "Extension \"$cacheFileExtension\" is not safe, currently allowed extension names: " . \implode(', ', $safeFileExtensions)
            );
        }

        $this->cacheFileExtension = $cacheFileExtension;
        return $this;
    }

    /**
     * @return int
     */
    public function getDefaultChmod(): int
    {
        return $this->defaultChmod;
    }

    /**
     * @param int $defaultChmod
     * @return self
     */
    public function setDefaultChmod(int $defaultChmod): static
    {
        $this->defaultChmod = $defaultChmod;
        return $this;
    }
}
