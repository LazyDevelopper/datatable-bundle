<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace DataTableBundle\DataTable;

interface DataTableFactoryInterface
{
    public function create(string $dataTableType, array $request, array $options = null);
}