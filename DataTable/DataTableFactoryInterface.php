<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace Marwen\DataTableBundle\DataTable;

interface DataTableFactoryInterface
{
    public function create(string $dataTableType, array $request, array $options = null);
}