<?php

namespace ColissimoLabel\EventListeners;


use Picking\Event\GenerateLabelEvent;
use Picking\Picking;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Controller\Admin\BaseAdminController;

/**
 * Class GenerateLabelListener
 *
 * This class is used only when you have the Picking module
 *
 * @package ColissimoLabel\EventListeners
 */
class GenerateLabelListener extends BaseAdminController implements EventSubscriberInterface
{
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param GenerateLabelEvent $event
     */
    public function generateLabel(GenerateLabelEvent $event)
    {
        $deliveryModuleCode = $event->getOrder()->getModuleRelatedByDeliveryModuleId()->getCode();
        if ($deliveryModuleCode === "ColissimoHomeDelivery" || $deliveryModuleCode === "ColissimoPickupPoint") {
            $data = [];
            $orderId = $event->getOrder()->getId();
            $data['new_status'] = '';
            $data['order_id'][$orderId] = $orderId;
            $data['weight'][$orderId] = $event->getWeight();
            $data['signed'][$orderId] = $event->isSignedDelivery();
            $service = $this->container->get('colissimolabel.generate.label.service');
            $event->setResponse($service->generateLabel($data, true));
        }
    }

    public static function getSubscribedEvents()
    {
        $events = [];
        if (class_exists('Picking\Event\GenerateLabelEvent')){
            $events[GenerateLabelEvent::PICKING_GENERATE_LABEL] = ['generateLabel', 65];
        }
        return $events;
    }
}