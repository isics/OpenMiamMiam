<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Producer;

use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Tile;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\ProductRepository;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProducerAttendancesManager;
use Isics\Bundle\OpenMiamMiamBundle\Manager\ProducerSalesOrderManager;
use Symfony\Component\Routing\RouterInterface;

class Tiles
{
    /**
     * @var ProducerSalesOrderManager
     */
    protected $producerSalesOrderManager;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var ProducerAttendancesManager
     */
    protected $producerAttendancesManager;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(ProducerSalesOrderManager $producerSalesOrderManager,
                                ProductRepository $productRepository,
                                ProducerAttendancesManager $producerAttendancesManager,
                                RouterInterface $router)
    {
        $this->producerSalesOrderManager  = $producerSalesOrderManager;
        $this->productRepository          = $productRepository;
        $this->producerAttendancesManager = $producerAttendancesManager;
        $this->router                     = $router;
    }

    /**
     * Add orders to prepare tile
     *
     * @param ProducerTileCollectorEvent $event
     */
    public function addOrdersToPrepare(ProducerTileCollectorEvent $event)
    {
        $producer = $event->getProducer();

        $nbSalesOrderToPrepare = $this->producerSalesOrderManager
            ->getForNextBranchOccurrences($producer)
            ->countSalesOrders();

        $tile = new Tile();

        $tile->setTileClass('success');
        $tile->setIconClass('shopping-cart');
        $tile->setValue($nbSalesOrderToPrepare);
        $tile->setDescription('admin.producer.dashboard.orders_to_prepare');

        $tile->setLink($this->router->generate('open_miam_miam.admin.producer.sales_order.list', array(
            'id' => $producer->getId()
        )));

        $event->addTile($tile);
    }

    /**
     * Add products out of stock tile
     *
     * @param ProducerTileCollectorEvent $event
     */
    public function addProductsOutOfStock(ProducerTileCollectorEvent $event)
    {
        $producer = $event->getProducer();

        $nbOutOfStockProducts = $this->productRepository->countOutOfStockProductsForProducer($producer);

        $tile = new Tile();

        $tile->setTileClass($nbOutOfStockProducts > 0 ? 'danger' : 'success');
        $tile->setIconClass('list');
        $tile->setValue($nbOutOfStockProducts);
        $tile->setDescription('admin.producer.dashboard.products_out_of_stock');

        $tile->setLink($this->router->generate('open_miam_miam.admin.producer.product.list', array(
            'id' => $producer->getId()
        )));

        $event->addTile($tile);
    }

    /**
     * Add orders to prepare tile
     *
     * @param ProducerTileCollectorEvent $event
     */
    public function addAttendancesToConfirm(ProducerTileCollectorEvent $event)
    {
        $producer         = $event->getProducer();

        $nbUnknownAttendances = $this->producerAttendancesManager->getNbUnknownAttendances(
            $this->producerAttendancesManager->getNextAttendancesOf($producer)
        );

        $tile = new Tile();

        $tile->setTileClass($nbUnknownAttendances > 0 ? 'danger' : 'success');
        $tile->setIconClass('time');
        $tile->setValue($nbUnknownAttendances);
        $tile->setDescription('admin.producer.dashboard.attendances_to_confirm');

        $tile->setLink($this->router->generate('open_miam_miam.admin.producer.calendar.edit', array(
            'id' => $producer->getId()
        )));

        $event->addTile($tile);
    }
}