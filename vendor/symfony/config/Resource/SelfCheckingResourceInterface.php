<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20220117\Symfony\Component\Config\Resource;

/**
 * Interface for Resources that can check for freshness autonomously,
 * without special support from external services.
 *
 * @author Matthias Pigulla <mp@webfactory.de>
 */
interface SelfCheckingResourceInterface extends \RectorPrefix20220117\Symfony\Component\Config\Resource\ResourceInterface
{
    /**
     * Returns true if the resource has not been updated since the given timestamp.
     *
     * @param int $timestamp The last time the resource was loaded
     *
     * @return bool
     */
    public function isFresh(int $timestamp);
}
