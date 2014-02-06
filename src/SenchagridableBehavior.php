<?php

/**
 * @author     Alexandre Mogère
 * @license    MIT License
 */
class SenchagridableBehavior extends Behavior
{
    public function queryMethods($builder)
    {
        $queryClassName = $builder->getStubQueryBuilder()->getClassname();
        $objectClassName = $builder->getStubObjectBuilder()->getFullyQualifiedClassname();
        
        return "
/**
 * Paginate results
 *
 * @param Symfony\Component\HttpFoundation\ParameterBag $params
 * @return $queryClassName
 */
public function paginateGrid(\$params)
{
    return \$this
        ->addGridFiltersQuery(\$params)
        ->addGridSortQuery(\$params)
        ->paginate(
            \$params->get('page'),
            \$params->get('limit')
        );
}

/**
 * Added sort filter on query
 *
 * @param Symfony\Component\HttpFoundation\ParameterBag $params
 * @return $queryClassName
 */
public function addGridSortQuery(\$params)
{
    if (\$params->get('sort')) {
        \$sorts = json_decode(\$params->get('sort'));
        foreach (\$sorts as \$sort) {
            \$orderBy = \"orderBy\".ucfirst(\$sort->property);
            \$this->\$orderBy(strtolower(\$sort->direction));
        }
    }
    
    return \$this;
}

/**
 * Added sort filter on query
 *
 * @param Symfony\Component\HttpFoundation\ParameterBag $params
 * @return $queryClassName
 */
public function addGridFiltersQuery(\$params)
{
    \$i = 0;
    \$fields = \$this->getFields(\$params);
    if (!empty(\$fields)) {
        \$orConds = array();
        foreach (\$fields as \$field) {
            \$this->condition(
                \"cond\$i\",
                '$objectClassName.'.ucfirst(\$field).' LIKE ?',
                \"%{\$params->get('query')}%\"
            );
            
            \$orConds[] = \"cond\$i\";
            \$i++;
        }
        
        \$this->where(\$orConds, 'or');
    }
    
    \$filter = \$this->getFilter(\$params);
    if (!empty(\$filter)) {
        \$andConds = array();
        \$count = count(\$filter);
        for (\$j = 0; \$j < \$count; \$j++) {
            
            switch(\$filter[\$j]['data']['type']) {
                case 'string': {
                    \$this->condition(
                        \"cond\$i\",
                        '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' LIKE ?',
                        \"%{\$filter[\$j]['data']['value']}%\"
                    ); break;
                }
                case 'list': {
                    if (strstr(\$filter[\$j]['data']['value'], ',')) {
                        \$this->condition(
                            \"cond\$i\",
                            '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' IN ?',
                            explode(',', \$filter[\$j]['data']['value'])
                        );
                    } else {
                        \$this->condition(
                            \"cond\$i\",
                            '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' = ?',
                            \$filter[\$j]['data']['value']
                        );
                    } break;
                }
                case 'boolean': {
                    \$this->condition(
                        \"cond\$i\",
                        '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' = ?',
                        \"{\$filter[\$j]['data']['value']}\"
                    ); break;
                }
                case 'numeric': {
                    switch (\$filter[\$j]['data']['comparison']) {
                        case 'ne': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' != ?',
                                \"{\$filter[\$j]['data']['value']}\"
                            ); break;
                        }
                        case 'eq': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' = ?',
                                \"{\$filter[\$j]['data']['value']}\"
                            ); break;
                        }
                        case 'lt': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' < ?',
                                \"{\$filter[\$j]['data']['value']}\"
                            ); break;
                        }
                        case 'gt': {
                             \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' > ?',
                                \"{\$filter[\$j]['data']['value']}\"
                            ); break;
                        }
                    } break;
                }
                case 'date': {
                
                    switch (\$filter[\$j]['data']['comparison']) {
                        case 'ne': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' != ?',
                                date('Y-m-d', strtotime(\$filter[\$j]['data']['value']))
                            ); break;
                        }
                        case 'eq': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' = ?',
                                date('Y-m-d', strtotime(\$filter[\$j]['data']['value']))
                            ); break;
                        }
                        case 'lt': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' < ?',
                                date('Y-m-d', strtotime(\$filter[\$j]['data']['value']))
                            ); break;
                        }
                        case 'gt': {
                            \$this->condition(
                                \"cond\$i\",
                                '$objectClassName.'.ucfirst(\$filter[\$j]['field']).' > ?',
                                date('Y-m-d', strtotime(\$filtrer[\$j]['data']['value']))
                            ); break;
                        }
                    }
                } break;
            }
            \$andConds[] = \"cond\$i\";
            \$i++;
        }
        if (!empty(\$andConds)) \$this->where(\$andConds, 'and');
    }
    
    return \$this;
}

/**
 * Get fields from request
 *
 * @param Symfony\Component\HttpFoundation\ParameterBag $params
 * @return array
 */
protected function getFields(\$params) {
    return json_decode(\$params->get('fields'));
}

/**
 * Get filter from request
 *
 * @param Symfony\Component\HttpFoundation\ParameterBag $params
 * @return array
 */
protected function getFilter(\$params) {
    return \$params->get('filter');
}

        ";
    }

}
