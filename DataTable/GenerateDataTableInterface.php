<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace DataTableBundle\DataTable;


use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

interface GenerateDataTableInterface
{
    public function build(array $request, string $entity, array $columns, QueryBuilder $customQuery = null);

    public function getData(EntityManager $em, string $entity, QueryBuilder $customQuery = null);

    public function getFilteredData(QueryBuilder $qb, array $get, array $columns, $alias);

    public function checkColumnType(string $column);

    public function checkEntityField(string $field, array $list);


}