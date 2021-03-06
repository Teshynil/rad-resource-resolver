<?php

namespace Knp\Rad\ResourceResolver\ResourceContainer;

use Knp\Rad\ResourceResolver\Event\ResourceEvent\ResourceEvent;
use Knp\Rad\ResourceResolver\Events;
use Knp\Rad\ResourceResolver\ResourceContainer as ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DispatcherProxyContainer implements ContainerInterface
{
    /**
     * @var ContainerInterface
     */
    private $wrapped;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(ContainerInterface $wrapped, EventDispatcherInterface $dispatcher)
    {
        $this->wrapped    = $wrapped;
        $this->dispatcher = $dispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getResources()
    {
        return $this->wrapped->getResources();
    }

    /**
     * {@inheritdoc}
     */
    public function getResource($key)
    {
        return $this->wrapped->getResource($key);
    }

    /**
     * {@inheritdoc}
     */
    public function hasResource($key)
    {
        return $this->wrapped->hasResource($key);
    }

    /**
     * {@inheritdoc}
     */
    public function addResource($key, $resource)
    {
        $this->wrapped->addResource($key, $resource);

        $this
            ->dispatcher
            ->dispatch(new ResourceEvent($key, $resource, $this->wrapped),Events::RESOURCES_ADDED)
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeResource($key)
    {
        $resource = $this->getResource($key);
        $this->wrapped->removeResource($key);

        $this
            ->dispatcher
            ->dispatch(new ResourceEvent($key, $resource, $this->wrapped), Events::RESOURCES_REMOVED)
        ;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return $this->hasResource($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
        return $this->getResource($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
        return $this->addResource($offset, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
        return $this->removeResource($offset);
    }
}
