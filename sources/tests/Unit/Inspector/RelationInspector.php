<?php
/*
 * This file is part of the Pomm's Foundation package.
 *
 * (c) 2014 - 2015 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\Foundation\Test\Unit\Inspector;

use PommProject\Foundation\Where;
use PommProject\Foundation\ResultIterator;
use PommProject\Foundation\ConvertedResultIterator;
use PommProject\Foundation\Tester\FoundationSessionAtoum;

class RelationInspector extends FoundationSessionAtoum
{
    use InspectorTestTrait;

    protected function getInspector()
    {
        return $this
            ->getSession()
            ->getInspector('relation');
    }

    public function testGetRelations()
    {
        $tables_info = $this
            ->getInspector()
            ->getRelations()
            ;

        $this
            ->array($tables_info->slice('name'))
            ->contains('no_pk')
            ->contains('pg_class')
            ->contains('sql_parts')
            ;

        $tables_info = $this
            ->getInspector()
            ->getRelations(Where::create("cl.relname !~ $*", ['[pk]']))
            ;

        $this
            ->array($tables_info->slice('name'))
            ->notContains('no_pk')
            ->notContains('pg_class')
            ->notContains('sql_parts')
            ->contains('sql_sizing')
            ;
    }

    public function testGetRelationsInSchema()
    {
        $relations = $this->getInspector()
            ->getRelationsInSchema('inspector_test')
            ;
        $this
            ->array($relations->slice('name'))
            ->isIdenticalTo(['no_pk', 'with_complex_pk', 'with_simple_pk'])
            ;

        $relations = $this->getInspector()
            ->getRelationsInSchema('inspector_test', Where::create("cl.relname ~ $*", ['^with']))
            ;
        $this
            ->array($relations->slice('name'))
            ->isIdenticalTo(['with_complex_pk', 'with_simple_pk'])
            ;
    }

    public function testGetDatabaseRelations()
    {
        $relations = $this->getInspector()
            ->getDatabaseRelations()
            ;

        $this
            ->array($relations->slice('name'))
            ->isIdenticalTo(['no_pk', 'with_complex_pk', 'with_simple_pk'])
            ;
        $relations = $this->getInspector()
            ->getDatabaseRelations(Where::create("cl.relname ~ $*", ['^with']))
            ;
        $this
            ->array($relations->slice('name'))
            ->isIdenticalTo(['with_complex_pk', 'with_simple_pk'])
            ;
    }

    public function testGetTableFieldInformation()
    {
        $relation_oid = $this
            ->getInspector()
            ->getRelationsInSchema(
                'inspector_test',
                Where::create("cl.relname = $*", ['with_simple_pk'])
            )
            ->slice('oid')[0]
            ;

        $relation_info = $this
            ->getInspector()
            ->getTableFieldInformation($relation_oid)
            ;

        $this
            ->object($relation_info)
            ->isInstanceOf(ConvertedResultIterator::class)
            ->array($relation_info->slice('name'))
            ->isIdenticalTo(['with_simple_pk_id', 'a_patron', 'some_timestamps'])
            ->array($relation_info->slice('type'))
            ->isIdenticalTo(['int4', 'inspector_test._someone', '_timestamptz'])
            ;
    }

    public function testGetTableFieldInformationName()
    {
        $relation_info = $this
            ->getInspector()
            ->getTableFieldInformationName('inspector_test', 'no_pk')
            ;

        $this
            ->array($relation_info->slice('name'))
            ->isIdenticalTo(['a_boolean', 'varchar_array'])
            ;
    }
}
