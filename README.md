SenchagridableBehavior
====================

Installation
------------

Download the SenchagridableBehavior.php file in src/, put it somewhere on your project, then add the following line to your propel.ini:

``` ini
propel.behavior.senchagridable.class = path.to.SenchagridableBehavior
```

Or use composer adding the requirement below:

``` js
{
    "require": {
        "heristop/propel-senchagridable-behavior": "*"
    }
}
```

Usage
-----

Add this line to your schema.xml:

``` xml
<behavior name="senchagridable" />
```

The Behavior will add several methods to the Query class:

``` php
public function paginateGrid($params)
public function addGridSortQuery($params)
public function addGridFiltersQuery($params)
```

The variable $params contains the parameters retrieved from the request:

``` php
$request = $this->get('request');
$posts = PostQuery::create()->paginateGrid($request->query);
```
