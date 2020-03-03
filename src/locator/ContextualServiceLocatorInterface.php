<?php
namespace amocrmtech\base\locator;

use yii\base\InvalidConfigException;

/**
 *
 */
interface ContextualServiceLocatorInterface
{
    /**
     * @param string   $id
     * @param callable $function
     *
     * @return mixed
     */
    public function doWith(string $id, callable $function);

    /**
     * @param string $componentId
     * @param bool   $throwException
     * @param string $id
     *
     * @return object|null
     * @throws InvalidConfigException
     */
    public function get($componentId, $throwException = true, string $id = null);
}