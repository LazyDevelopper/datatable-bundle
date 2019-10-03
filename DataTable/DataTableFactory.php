<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace DataTableBundle\DataTable;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;

class DataTableFactory implements DataTableFactoryInterface
{
    private $em;
    private $router;

    public function __construct(EntityManager $em, Router $router)
    {
        $this->em     = $em;
        $this->router = $router;
    }

    /**
     * @param \Marwen\DataTableBundle\DataTable\string $dataTableType
     * @param array                                    $request
     * @param array|null                               $options
     *
     * @return array
     * @throws \Exception
     */
    public function create(string $dataTableType, array $request, array $options = null)
    {
        $dataTable            = new $dataTableType;
        $dataTableTypeBuilder = $dataTable->buildDataTable(new DataTableBuilder());
        $columns              = $dataTableTypeBuilder->getChilds();
        $customQuery          = null;
        if (!method_exists($dataTable, 'getEntity')) {
            throw new \Exception('entity is missing');
        }
        if (method_exists($dataTable, 'getCustomQuery')) {
            try {
                $customQuery = $this->em->getRepository($dataTable->getEntity())->{$dataTable->getCustomQuery()}($options);
            } catch (\Exception $e) {
                throw new \Exception('error in your custom repository '.$dataTable->getCustomQuery());
            }
        }
        $dt     = new GenerateDataTable($this->em, $this->router);
        $output = $dt->build(
            $request,
            $dataTable->getEntity(),
            $columns,
            $customQuery
        );

        return $output;
    }
}
