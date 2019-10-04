<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace DataTableBundle\DataTable;


interface DataTableBuilderInterface
{
    public function add($child, $type = null, array $options = []);

    public function getChilds();

}