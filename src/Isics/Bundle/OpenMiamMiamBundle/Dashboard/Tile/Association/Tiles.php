<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Association;

use Isics\Bundle\OpenMiamMiamBundle\Dashboard\Tile\Tile;
use Isics\Bundle\OpenMiamMiamBundle\Entity\Repository\BranchOccurrenceRepository;
use Isics\Bundle\OpenMiamMiamBundle\Model\BranchOccurrence\BranchOccurrenceProducersAttendances;
use Sonata\IntlBundle\Templating\Helper\DateTimeHelper;
use Sonata\IntlBundle\Templating\Helper\NumberHelper;
use Symfony\Component\Routing\RouterInterface;

class Tiles
{
    /**
     * @var BranchOccurrenceRepository
     */
    protected $branchOccurrenceRepository;

    /**
     * @var BranchOccurrenceProducersAttendances
     */
    protected $branchOccurrenceProducersAttendances;

    /**
     * @var NumberHelper
     */
    protected $numberHelper;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var DateTimeHelper
     */
    protected $dateTimeHelper;

    /**
     * @param BranchOccurrenceRepository           $branchOccurrenceRepository
     * @param BranchOccurrenceProducersAttendances $branchOccurrenceProducersAttendances
     * @param NumberHelper                         $numberHelper
     * @param string                               $currency
     * @param RouterInterface                      $router
     * @param DateTimeHelper                       $dateTimeHelper
     */
    public function __construct(BranchOccurrenceRepository $branchOccurrenceRepository,
                                BranchOccurrenceProducersAttendances $branchOccurrenceProducersAttendances,
                                NumberHelper $numberHelper,
                                $currency,
                                RouterInterface $router,
                                DateTimeHelper $dateTimeHelper)
    {
        $this->branchOccurrenceRepository           = $branchOccurrenceRepository;
        $this->branchOccurrenceProducersAttendances = $branchOccurrenceProducersAttendances;
        $this->numberHelper                         = $numberHelper;
        $this->currency                             = $currency;
        $this->router                               = $router;
        $this->dateTimeHelper                       = $dateTimeHelper;
    }

    /**
     * Add branch planned dates tile
     *
     * @param AssociationTileCollectorEvent $event
     */
    public function addBranchPlannedDatesTile(AssociationTileCollectorEvent $event)
    {
        $association      = $event->getAssociation();
        $branchOccurrence = $event->getBranchOccurrence();

        $nextOccurrences   = $this->branchOccurrenceRepository->findAllNextForBranch(
            $branchOccurrence->getBranch(),
            true,
            null
        );
        $nbNextOccurrences = count($nextOccurrences);

        $tileClass = 'success';
        if ($nbNextOccurrences <= 3) {
            $tileClass = 'warning';
        }
        if ($nbNextOccurrences <= 1) {
            $tileClass = 'danger';
        }

        $tile = new Tile();

        $tile->setIconClass('calendar');
        $tile->setValue($nbNextOccurrences);
        $tile->setDescription('admin.association.dashboard.branch_planned_dates');
        $tile->setTileClass($tileClass);
        $tile->setLink($this->router->generate('open_miam_miam.admin.association.branch.edit_calendar', array(
            'id'       => $association->getId(),
            'branchId' => $branchOccurrence->getBranch()->getId()
        )));

        $event->addTile($tile);
    }

    /**
     * Add producers to call tile
     *
     * @param AssociationTileCollectorEvent $event
     */
    public function addProducersToCallTile(AssociationTileCollectorEvent $event)
    {
        $association      = $event->getAssociation();
        $branchOccurrence = $event->getBranchOccurrence();

        $branchOccurrenceProducersAttendances = $this->branchOccurrenceProducersAttendances;
        $branchOccurrenceProducersAttendances->setBranchOccurrence($branchOccurrence);
        $nb = count($branchOccurrenceProducersAttendances->getProducersAttendanceUnknown());

        $tile = new Tile();

        $tile->setIconClass('time');
        $tile->setDescription('admin.association.dashboard.producer_to_call');

        $tile->setTileClass(($nb === 0 ? 'success' : 'danger'));
        $tile->setHeader($this->dateTimeHelper->format($branchOccurrence->getDate(), 'dd MMMM'));
        $tile->setValue($nb);
        $tile->setLink($this->router->generate('open_miam_miam.admin.association.branch.occurrence.list_attendances', array(
            'id'                 => $association->getId(),
            'branchOccurrenceId' => $branchOccurrence->getId()
        )));

        if ($nb === 0) {
            $nextBranchOccurrence = $this->branchOccurrenceRepository->findOneNextForBranchOccurrence($branchOccurrence);

            if ($nextBranchOccurrence) {
                $branchOccurrenceProducersAttendances->setBranchOccurrence($nextBranchOccurrence);
                $nb = count($branchOccurrenceProducersAttendances->getProducersAttendanceUnknown());

                if ($nb > 0) {
                    $tile->setTileClass('warning');
                    $tile->setHeader($this->dateTimeHelper->format($nextBranchOccurrence->getDate(), 'dd MMMM'));
                    $tile->setValue($nb);
                    $tile->setLink($this->router->generate('open_miam_miam.admin.association.branch.occurrence.list_attendances', array(
                        'id'                 => $association->getId(),
                        'branchOccurrenceId' => $nextBranchOccurrence->getId()
                    )));
                }
            }
        }

        $event->addTile($tile);
    }

    /**
     * Add sales orders tile
     *
     * @param AssociationTileCollectorEvent $event
     */
    public function addSalesOrdersTile(AssociationTileCollectorEvent $event)
    {
        $association      = $event->getAssociation();
        $branchOccurrence = $event->getBranchOccurrence();

        $tile = new Tile();

        $salesOrders = $branchOccurrence->getSalesOrders();

        $nbSalesOrders          = count($salesOrders);
        $totalAmountSalesOrders = 0;

        foreach ($salesOrders as $salesOrder) {
            $totalAmountSalesOrders += $salesOrder->getTotal();
        }

        $tile->setIconClass('shopping-cart');
        $tile->setHeader($this->numberHelper->formatCurrency($totalAmountSalesOrders, $this->currency));
        $tile->setValue($nbSalesOrders);
        $tile->setDescription('admin.association.dashboard.sales_orders');
        $tile->setTileClass($nbSalesOrders > 0 ? 'success' : 'danger');
        $tile->setLink($this->router->generate('open_miam_miam.admin.association.sales_order.list_for_branch_occurrence', array(
            'id'                 => $association->getId(),
            'branchOccurrenceId' => $branchOccurrence->getId()
        )));

        $event->addTile($tile);
    }
}