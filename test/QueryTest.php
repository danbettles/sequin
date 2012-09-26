<?php
/**
 * @copyright Copyright (c) 2012, Dan Bettles
 * @author Dan Bettles <dan@danbettles.net>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

require_once __DIR__ . '/../src/boot.php';

class QueryTest extends PHPUnit_Framework_TestCase {

    public function testIsATerm() {
        $oReflectionClass = new ReflectionClass('Sequin\\Query');
        $this->assertTrue($oReflectionClass->isSubclassOf('Sequin\\Term'));
    }

    public function testCanBeJoinedWithTerms() {
        $oQuery = new Sequin\Query('foo');
        $returnValue = $oQuery->andTerm('bar');
        $this->assertSame($oQuery, $returnValue);
        $this->assertEquals('foo AND bar', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $returnValue = $oQuery->orTerm('bar');
        $this->assertSame($oQuery, $returnValue);
        $this->assertEquals('foo OR bar', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $returnValue = $oQuery->notTerm('bar');
        $this->assertSame($oQuery, $returnValue);
        $this->assertEquals('foo NOT bar', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oQuery->andTerm('bar')->orTerm('baz');
        $this->assertEquals('foo AND bar OR baz', $oQuery->toString());
    }

    public function testTostring() {
        $oGroup = new Sequin\Query(new Sequin\Term('foo'));
        $oGroup->andRight(new Sequin\Term('bar'));
        $this->assertEquals('(foo) AND bar', $oGroup->toString());

        $oLeftTerm = new Sequin\Term('foo');
        $oLeftTerm->andRight(new Sequin\Term('bar'));
        $oGroup = new Sequin\Query($oLeftTerm);
        $oGroup->orRight(new Sequin\Term('baz'));
        $this->assertEquals('(foo AND bar) OR baz', $oGroup->toString());

        $oLeftTerm = new Sequin\Term('foo');
        $oLeftTerm->andRight(new Sequin\Term('bar'));
        $oGroup = new Sequin\Query($oLeftTerm, 'fieldName', 3);
        $oGroup->orRight(new Sequin\Term('baz'));
        $this->assertEquals('fieldName:(foo AND bar)^3 OR baz', $oGroup->toString());
    }

    public function testCanBeJoinedWithSubQueries() {
        $oQuery = new Sequin\Query('foo');
        $oFirstTerm = new Sequin\Term('bar');
        $oSubQuery = $oQuery->andQuery($oFirstTerm);
        $this->assertTrue($oSubQuery instanceof Sequin\Query);
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND (bar)', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oFirstTerm = new Sequin\Term('bar');
        $oSubQuery = $oQuery->andQuery($oFirstTerm, 'fieldName');
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertEquals('fieldName', $oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND fieldName:(bar)', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oSubQuery = $oQuery->andQuery('bar');
        $this->assertEquals('bar', $oSubQuery->value()->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND (bar)', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oSubQuery = $oQuery->andQuery('bar', 'fieldName');
        $this->assertEquals('bar', $oSubQuery->value()->value());
        $this->assertEquals('fieldName', $oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo AND fieldName:(bar)', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oQuery->andQuery('bar')->orTerm('baz')->notTerm('bip');
        $this->assertEquals('foo AND (bar OR baz NOT bip)', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oFirstTerm = new Sequin\Term('bar');
        $oSubQuery = $oQuery->orQuery($oFirstTerm);
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo OR (bar)', $oQuery->toString());

        $oQuery = new Sequin\Query('foo');
        $oFirstTerm = new Sequin\Term('bar');
        $oSubQuery = $oQuery->notQuery($oFirstTerm);
        $this->assertSame($oFirstTerm, $oSubQuery->value());
        $this->assertNull($oSubQuery->fieldName());
        $this->assertEquals(1, $oSubQuery->boostFactor());
        $this->assertEquals('foo NOT (bar)', $oQuery->toString());
    }

    public function testCanBeResumedAfterASubQuery() {
        $oQuery = new Sequin\Query('foo');
        $oQuery->andQuery('bar')->orTerm('baz')->endQuery()->notTerm('bip');
        $this->assertEquals('foo AND (bar OR baz) NOT bip', $oQuery->toString());
    }

    public function testNewinstanceCreatesANewInstance() {
        $oQuery = Sequin\Query::newInstance('value');
        $this->assertTrue($oQuery instanceof Sequin\Query);
        $this->assertEquals('value', $oQuery->value());
        $this->assertNull($oQuery->fieldName());
        $this->assertEquals(1, $oQuery->boostFactor());

        $oQuery = Sequin\Query::newInstance('value', 'fieldName');
        $this->assertTrue($oQuery instanceof Sequin\Query);
        $this->assertEquals('value', $oQuery->value());
        $this->assertEquals('fieldName', $oQuery->fieldName());
        $this->assertEquals(1, $oQuery->boostFactor());

        $oQuery = Sequin\Query::newInstance('value', 'fieldName', 3);
        $this->assertTrue($oQuery instanceof Sequin\Query);
        $this->assertEquals('value', $oQuery->value());
        $this->assertEquals('fieldName', $oQuery->fieldName());
        $this->assertEquals(3, $oQuery->boostFactor());

        $oQuery = Sequin\Query::newInstance(new Sequin\Term('value'));
        $this->assertTrue($oQuery instanceof Sequin\Query);
        $this->assertEquals('value', $oQuery->value()->value());
        $this->assertNull($oQuery->fieldName());
        $this->assertEquals(1, $oQuery->boostFactor());
    }

    public function testEndqueryReturnsTheCurrentQueryIfThereIsNoSubQueryToClose() {
        $oQuery = new Sequin\Query('foo');
        $this->assertSame($oQuery, $oQuery->endQuery());
    }
}