<?php
/**
 * Created by PhpStorm.
 * User: tux
 * Date: 10/3/19
 * Time: 12:37 PM
 */

namespace Marwen\DataTableBundle\DataTable;


abstract class AbstractDataTable
{
    abstract public function buildDataTable(DataTableBuilderInterface $builder);

    abstract public function getEntity();

}