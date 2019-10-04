<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace Marwen\DataTableBundle\DataTable;


interface DataTableBuilderInterface
{
    public function add($child, $type = null, array $options = []);

    public function getChilds();

}