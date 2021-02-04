<?php /** @noinspection PhpUnused */

namespace amocrmtech\base\locator;

use Closure;
use Yii;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\di\ServiceLocator;

class ContextualServiceLocator extends ServiceLocator implements ContextualServiceLocatorInterface
{
    /** @var string|null */
    private $context;

    /**
     * @param string $componentId
     * @param bool   $throwException
     * @param string $id
     *
     * @return object|null
     * @throws InvalidConfigException
     */
    public function get($componentId, $throwException = true, string $id = null)
    {
        $serviceId = $this->generateServiceId($componentId, $id);
        if (!$this->has($serviceId)) {
            $this->set($serviceId, $this->buildDefinition($componentId, $id));
        }

        return parent::get($serviceId, $throwException);
    }

    /**
     * @param string $componentId
     * @param string $id
     */
    public function clear($componentId, string $id = null): void
    {
        parent::clear($componentId);
        parent::clear($this->generateServiceId($componentId, $id));
    }

    /**
     * @param string   $id
     * @param callable $function
     *
     * @return mixed
     */
    public function doWith(string $id, callable $function)
    {
        $previous = $this->getCurrentId($id);

        $this->context = $id;
        $result        = $function($this);
        $this->context = $previous;

        return $result;
    }

    /**
     * @param $componentId
     * @param $id
     *
     * @return string
     */
    private function generateServiceId($componentId, $id): string
    {
        return "{$componentId}_{$this->getCurrentId($id)}";
    }

    /**
     * @param $componentId
     * @param $id
     *
     * @return array|mixed|null
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    private function buildDefinition($componentId, $id)
    {
        $definitions = $this->getComponents();
        if (!array_key_exists($componentId, $definitions)) {
            return null;
        }

        $definition = $definitions[$componentId];
        if (is_array($definition)) {
            $currentId = $this->getCurrentId($id);
            array_walk_recursive($definition, static function (&$value) use ($currentId) {
                if (is_string($value)) {
                    $value = str_replace('{id}', $currentId, $value);
                }
            });

            return $definition;
        }

        if (is_object($definition) && $definition instanceof Closure) {
            return Yii::$container->invoke($definition, [$this->getCurrentId($id)]);
        }

        return $definition;
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function getCurrentId(?string $id): string
    {
        if ($id !== null) {
            return $id;
        }

        if ($this->context !== null) {
            return $this->context;
        }

        throw new InvalidCallException('id not set');
    }
}