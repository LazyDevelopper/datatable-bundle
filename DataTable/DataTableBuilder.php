<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace DataTableBundle\DataTable;


class DataTableBuilder implements DataTableBuilderInterface
{
    private $childs;
    public function add($child, $type = null, array $options = [])
    {
        $this->childs[] = [$child, $type, $options];
        return $this;
    }

    public function getChilds()
    {
        return $this->childs;
    }

}