<?php
/**
 * @copyright Copyright (c) 2012, Dan Bettles
 * @author Dan Bettles <dan@danbettles.net>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

require_once dirname(__DIR__) . '/include/boot.php';

class QueryTest extends PHPUnit_Framework_TestCase {

    public function testIsATerm() {
        $oReflectionClass = new ReflectionClass('sequin\\Query');
        $this->assertTrue($oReflectionClass->isSubclassOf('sequin\\Term'));
    }

    public function testCanBeJoinedWithTerms() {
        $oQuery = new sequin\Query('foo');
        $returnValue = $oQuery->andTerm('bar');
        $this->assertSame($oQuery, $returnValue);
        $this->assertEquals('foo AND bar', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $returnValue = $oQuery->orTerm('bar');
        $this->assertSame($oQuery, $returnValue);
        $this->assertEquals('foo OR bar', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $returnValue = $oQuery->notTerm('bar');
        $this->assertSame($oQuery, $returnValue);
        $this->assertEquals('foo NOT bar', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oQuery->andTerm('bar')->orTerm('baz');
        $this->assertEquals('foo AND bar OR baz', $oQuery->toString());
    }

    public function testTostring() {
        $oGroup = new sequin\Query(new sequin\Term('foo'));
        $oGroup->andRight(new sequin\Term('bar'));
        $this->assertEquals('(foo) AND bar', $oGroup->toString());

        $oLeftTerm = new sequin\Term('foo');
        $oLeftTerm->andRight(new sequin\Term('bar'));
        $oGroup = new sequin\Query($oLeftTerm);
        $oGroup->orRight(new sequin\Term('baz'));
        $this->assertEquals('(foo AND bar) OR baz', $oGroup->toString());

        $oLeftTerm = new sequin\Term('foo');
        $oLeftTerm->andRight(new sequin\Term('bar'));
        $oGroup = new sequin\Query($oLeftTerm, 'fieldName', 3);
        $oGroup->orRight(new sequin\Term('baz'));
        $this->assertEquals('fieldName:(foo AND bar)^3 OR baz', $oGroup->toString());
    }

    public function testCanBeJoinedWithSubQueries() {
        $oQuery = new sequin\Query('foo');
        $oFirstTerm = new sequin\Term('bar');
        $oSubQuery = $oQuery->andQuery($oFirstTerm);
        $this->assertTrue($oSubQuery instanceof sequin\Query);
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND (bar)', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oFirstTerm = new sequin\Term('bar');
        $oSubQuery = $oQuery->andQuery($oFirstTerm, 'fieldName');
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertEquals('fieldName', $oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND fieldName:(bar)', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oSubQuery = $oQuery->andQuery('bar');
        $this->assertEquals('bar', $oSubQuery->value()->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND (bar)', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oSubQuery = $oQuery->andQuery('bar', 'fieldName');
        $this->assertEquals('bar', $oSubQuery->value()->value());
        $this->assertEquals('fieldName', $oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND fieldName:(bar)', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oQuery->andQuery('bar')->orTerm('baz')->notTerm('bip');
        $this->assertEquals('foo AND (bar OR baz NOT bip)', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oFirstTerm = new sequin\Term('bar');
        $oSubQuery = $oQuery->orQuery($oFirstTerm);
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo OR (bar)', $oQuery->toString());

        $oQuery = new sequin\Query('foo');
        $oFirstTerm = new sequin\Term('bar');
        $oSubQuery = $oQuery->notQuery($oFirstTerm);
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo NOT (bar)', $oQuery->toString());
    }

    public function testCanBeResumedAfterASubQuery() {
        $oQuery = new sequin\Query('foo');
        $oQuery->andQuery('bar')->orTerm('baz')->endQuery()->notTerm('bip');
        $this->assertEquals('foo AND (bar OR baz) NOT bip', $oQuery->toString());
    }

    public function testNewinstanceCreatesANewInstance() {
        $oQuery = sequin\Query::newInstance('value');
        $this->assertTrue($oQuery instanceof sequin\Query);
        $this->assertEquals('value', $oQuery->value());
        $this->assertNull($oQuery->fieldName());
        $this->assertEquals(1, $oQuery->boostFactor());

        $oQuery = sequin\Query::newInstance('value', 'fieldName');
        $this->assertTrue($oQuery instanceof sequin\Query);
        $this->assertEquals('value', $oQuery->value());
        $this->assertEquals('fieldName', $oQuery->fieldName());
        $this->assertEquals(1, $oQuery->boostFactor());

        $oQuery = sequin\Query::newInstance('value', 'fieldName', 3);
        $this->assertTrue($oQuery instanceof sequin\Query);
        $this->assertEquals('value', $oQuery->value());
        $this->assertEquals('fieldName', $oQuery->fieldName());
        $this->assertEquals(3, $oQuery->boostFactor());

        $oQuery = sequin\Query::newInstance(new sequin\Term('value'));
        $this->assertTrue($oQuery instanceof sequin\Query);
        $this->assertEquals('value', $oQuery->value()->value());
        $this->assertNull($oQuery->fieldName());
        $this->assertEquals(1, $oQuery->boostFactor());
    }

    public function testEndqueryReturnsTheCurrentQueryIfThereIsNoSubQueryToClose() {
        $oQuery = new sequin\Query('foo');
        $this->assertSame($oQuery, $oQuery->endQuery());
    }
}