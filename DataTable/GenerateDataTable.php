<?php
/**
 * User: Abdelmaksoud Marwane
 */

namespace Marwen\DataTableBundle\DataTable;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Routing\RouterInterface;

class GenerateDataTable implements GenerateDataTableInterface
{
    private $em;
    private $router;
    private $columnsType = ['string', 'date', 'button'];

    public function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em     = $em;
        $this->router = $router;
    }

    /**
     * @param                                 $request
     * @param                                 $entity
     * @param                                 $columns
     * @param \Doctrine\ORM\QueryBuilder|null $customQuery
     *
     * @return array
     * @throws \Exception
     */
    public function build(array $request, string $entity, array $columns, QueryBuilder $customQuery = null)
    {
        if ($customQuery && !$customQuery instanceof QueryBuilder) {
            throw new \Exception(
                'Custom query builder must be instance of Query Builder but '.gettype($customQuery).' was given'
            );
        }
        $classMetadata          = $this->em->getClassMetadata($entity);
        $entityFieldsList       = $classMetadata->getFieldNames();
        $entityMappedFieldsList = $classMetadata->getAssociationNames();
        foreach ($columns as $column) {
            $this->checkColumnType($column[1]);
            if ($column[1] != 'button') {
                if (strpos($column[0], '.')) {
                    $field                  = explode('.', $column[0]);
                    $associatedEntityClass  = $classMetadata->getAssociationTargetClass($field[0]);
                    $associatedEntityFields = $this->em->getClassMetadata($associatedEntityClass)->getFieldNames();
                    $this->checkEntityField($field[0], $entityMappedFieldsList);
                    $this->checkEntityField($field[1], $associatedEntityFields);
                } else {
                    $this->checkEntityField($column[0], $entityFieldsList);
                }
            }
        }

        $qb                  = $this->getData($this->em, $entity, $customQuery);
        $alias               = $qb->getDQLPart('from')[0]->getAlias();
        $qbData              = clone $qb;
        $qbData              = $this->getFilteredData($qbData, $request, $columns, $alias);
        $qbDataDisplay       = clone $qbData;
        $data                = $qbData->setFirstResult((int)$request['start'])
                                      ->setMaxResults((int)$request['length'])
        ;
        $totalRecords        = $qb->select('COUNT('.$alias.')')->getQuery()->getSingleScalarResult();
        $totalDisplayRecords = $qbDataDisplay->select('COUNT('.$alias.')')->getQuery()->getSingleScalarResult();
        $data                = $data->getQuery()->getResult();
        $output              = [
            "draw"                 => intval($request['draw']),
            "iTotalRecords"        => $totalRecords,
            "iTotalDisplayRecords" => $totalDisplayRecords,
            "aaData"               => [],
        ];


        foreach ($data as $aRow) {
            $row = [];
            foreach ($columns as $key => $column) {
                if ($column[1] != 'button') {
                    $dateFormat = !empty($column[2]) && isset($column[2]['format']) ? $column[2]['format']
                        : 'Y-m-d H:i:s';
                    $column     = $column[0];
                    $field      = explode('.', $column);
                    if (strpos($column, '.')) {
                        if ($aRow->{'get'.ucwords($field[0])}()) {
                            if ($aRow->{'get'.ucwords($field[0])}()->{'get'.ucwords($field[1])}()
                                instanceof
                                \DateTime) {
                                $row[] = $aRow->{'get'.ucwords($field[0])}()->{'get'.ucwords($field[1])}()->format(
                                    $dateFormat
                                )
                                ;
                            } else {
                                $row[] = $aRow->{'get'.ucwords($field[0])}()->{'get'.ucwords($field[1])}();
                            }
                        } else {
                            $row[] = null;
                        }

                    } else {
                        if ($aRow->{'get'.ucwords($field[0])}() instanceof \DateTime) {
                            $row[] = $aRow->{'get'.ucwords($field[0])}()->format($dateFormat);
                        } else {
                            $row[] = $aRow->{'get'.ucwords($field[0])}();
                        }
                    }
                } elseif ($column[1] === 'button') {
                    $button = $column[2];
                    if ($button) {
                        $action = ' ';
                        $class  = isset($button['class']) ? '"class ="'.$button['class'].'"' : '';
                        $title  = isset($button['name']) ? 'title ="'.$button['name'].'"' : '';
                        $name   = isset($button['name']) ? $button['name'] : '';
                        $entypo = isset($button['entypo']) ? '<i class ="'.$button['entypo'].'"></i>' : '';
                        $action .= ' <a href="'.
                                   $this->router->generate(
                                       $button['url'],
                                       [$button['id'] => $aRow->{'get'.ucwords($button['id'])}()]
                                   ).
                                   $class.
                                   $title.
                                   '>'.$name.$entypo.
                                   '</a>';
                        $row[]  = $action;
                    }
                }
            }
            $output['aaData'][] = $row;
        }

        return $output;

    }

    /**
     * @param \Doctrine\ORM\EntityManager              $em
     * @param \Marwen\DataTableBundle\DataTable\string $entity
     * @param \Doctrine\ORM\QueryBuilder               $customQuery
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getData(EntityManager $em, string $entity, QueryBuilder $customQuery = null)
    {
        if ($customQuery) {
            return $customQuery;
        } else {
            return $em->createQueryBuilder()->select('q')->from($entity, 'q');
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $qb
     * @param array                      $get
     * @param array                      $columns
     * @param                            $alias
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getFilteredData(QueryBuilder $qb, array $get, array $columns, $alias)
    {
        /*
        * Ordering
        */
        if (isset($get['order'])) {
            foreach ($columns as $key => $column) {
                if ($column[1] != 'button') {
                    if ($key == $get['order'][0]['column']) {
                        $order = $get['order'][0]['dir'];
                        if (strpos($column[0], '.')) {
                            $field = explode('.', $column[0]);
                            $qb->innerJoin($alias.'.'.$field[0], $field[0].'_order_'.$key);
                            $qb->orderBy($field[0].'_order_'.$key.'.'.$field[1], $order);
                        } else {
                            $qb->orderBy($alias.'.'.$column[0], $order);
                        }
                    }
                }
            }
        }
        /*
         * filtering
         */
        if (isset($get['search'])) {
            $mainSearch = false;
            $aLike      = [];
            foreach ($columns as $key => $column) {
                if ($column[1] != 'button') {
                    if (!empty($get['search']['value'])) {
                        $search = $get['search']['value'];
                        if ($column[1] == 'date' && !preg_match('/^[:\d]+$/', $search)) {
                            $search = 'empty';
                        }
                        if (strpos($column[0], '.')) {
                            $field = explode('.', $column[0]);
                            $qb->innerJoin($alias.'.'.$field[0], $field[0].'_filter_'.$key);
                            $aLike[] = $qb->expr()->like($field[0].'_filter_'.$key.'.'.$field[1], ':Search_'.$field[1]);
                            $qb->setParameter('Search_'.$field[1], "%".$search."%");
                        } else {
                            $aLike[] = $qb->expr()->like($alias.'.'.$column[0], ':Search_'.$column[0]);
                            $qb->setParameter('Search_'.$column[0], "%".$search."%");
                        }
                        $mainSearch = true;
                    } elseif ($key == $get['columns'][$key]['data'] &&
                              !empty($get['columns'][$key]['search']['value'])) {
                        $search = $get['columns'][$key]['search']['value'];
                        if ($column[1] == 'date' && !preg_match('/^[:\d]+$/', $search)) {
                            $search = 'empty';
                        }
                        if (strpos($column[0], '.')) {
                            $field = explode('.', $column[0]);
                            $qb->innerJoin($alias.'.'.$field[0], $field[0].'_filter_'.$key);
                            $aLike[] = $qb->expr()->like($field[0].'_filter_'.$key.'.'.$field[1], ':Search_'.$field[1]);
                            $qb->setParameter('Search_'.$field[1], "%".$search."%");
                        } else {
                            $aLike[] = $qb->expr()->like($alias.'.'.$column[0], ':Search_'.$column[0]);
                            $qb->setParameter('Search_'.$column[0], "%".$search."%");
                        }
                    }
                }
            }
            if (count($aLike) > 0) {
                if ($mainSearch) {
                    $qb->andWhere(new Query\Expr\Orx($aLike));
                } else {
                    $qb->andWhere(new Query\Expr\Andx($aLike));
                }
            } else {
                unset($aLike);
            }
        }

        return $qb;
    }

    /**
     * @param \Marwen\DataTableBundle\DataTable\string $column
     *
     * @return bool
     * @throws \Exception
     */
    public function checkColumnType(string $column)
    {
        if (!in_array($column, $this->columnsType)) {
            throw new \Exception(
                'Column type '.$column.' not found, Available types are : '.implode(', ', $this->columnsType)
            );
        }

        return true;
    }

    /**
     * @param \Marwen\DataTableBundle\DataTable\string $field
     * @param array                                    $list
     *
     * @return bool
     * @throws \Exception
     */
    public function checkEntityField(string $field, array $list)
    {
        if (!in_array($field, $list)) {
            throw new \Exception(
                'Column "'.$field.'" NOT FOUND, Do you mean : '.implode(', ', $list)
            );
        }

        return true;
    }
}
