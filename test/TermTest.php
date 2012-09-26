<?php
/**
 * @copyright Copyright (c) 2012, Dan Bettles
 * @author Dan Bettles <dan@danbettles.net>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

require_once __DIR__ . '/../src/boot.php';

class TermTest extends PHPUnit_Framework_TestCase {

    public function testIsConstructedWithAValueAndOptionallyTheFieldName() {
        $oTerm = new Sequin\Term('value');
        $this->assertEquals('value', $oTerm->value());
        $this->assertNull($oTerm->fieldName());

        $oTerm = new Sequin\Term('value', 'fieldName');
        $this->assertEquals('value', $oTerm->value());
        $this->assertEquals('fieldName', $oTerm->fieldName());
    }

    public function testBoostfactorReturnsTheBoostFactorSetWithSetboostfactor() {
        $oTerm = new Sequin\Term('value');
        $oTerm->setBoostFactor(3);
        $this->assertEquals(3, $oTerm->boostFactor());
    }

    public function testTheDefaultBoostFactorIsOne() {
        $oTerm = new Sequin\Term('value');
        $this->assertEquals(1, $oTerm->boostFactor());
    }

    public function testTostring() {
        $oTerm = new Sequin\Term('value');
        $this->assertEquals('value', $oTerm->toString());

        $oTerm = new Sequin\Term('value', 'fieldName');
        $this->assertEquals('fieldName:value', $oTerm->toString());

        $oTerm = new Sequin\Term('value');
        $oTerm->setBoostFactor(3);
        $this->assertEquals('value^3', $oTerm->toString());

        $oTerm = new Sequin\Term('value', 'fieldName');
        $oTerm->setBoostFactor(3);
        $this->assertEquals('fieldName:value^3', $oTerm->toString());
    }

    public function testValueCanBeAPhrase() {
        $oTerm = new Sequin\Term('"phrase"');
        $this->assertEquals('"phrase"', $oTerm->value());
        $this->assertEquals('"phrase"', $oTerm->toString());
    }

    public function testImplementsTostringMagicMethod() {
        $oTerm = new Sequin\Term('foo');
        $this->assertEquals('foo', (string)$oTerm);
    }

    public function testCanBeConstructedWithABoostFactor() {
        $oTerm = new Sequin\Term('value', 'fieldName', 3);
        $this->assertEquals(3, $oTerm->boostFactor());
    }

    public function testNewInstanceDoesNotHaveARightTerm() {
        $oTerm = new Sequin\Term('foo');
        $this->assertNull($oTerm->operator());
        $this->assertNull($oTerm->rightTerm());
    }

    public function testCanBeJoinedWithAnotherTerm() {
        $oLeftTerm = new Sequin\Term('foo');
        $oRightTerm = new Sequin\Term('bar');
        $oLeftTerm->andRight($oRightTerm);

        $this->assertEquals('AND', $oLeftTerm->operator());
        $this->assertSame($oRightTerm, $oLeftTerm->rightTerm());
        $this->assertEquals('foo AND bar', $oLeftTerm->toString());

        $oLeftTerm = new Sequin\Term('foo');
        $oRightTerm = new Sequin\Term('bar');
        $oLeftTerm->orRight($oRightTerm);

        $this->assertEquals('OR', $oLeftTerm->operator());
        $this->assertSame($oRightTerm, $oLeftTerm->rightTerm());
        $this->assertEquals('foo OR bar', $oLeftTerm->toString());

        $oLeftTerm = new Sequin\Term('foo');
        $oRightTerm = new Sequin\Term('bar');
        $oLeftTerm->notRight($oRightTerm);

        $this->assertEquals('NOT', $oLeftTerm->operator());
        $this->assertSame($oRightTerm, $oLeftTerm->rightTerm());
        $this->assertEquals('foo NOT bar', $oLeftTerm->toString());
    }

    public function testTermsCanBeJoinedByChainingMethodCalls() {
        $oTerm = new Sequin\Term('foo');
        $oTerm->andRight(new Sequin\Term('bar'))->orRight(new Sequin\Term('baz'))->notRight(new Sequin\Term('bip'));
        $this->assertEquals('foo AND bar OR baz NOT bip', $oTerm->toString());
    }

    /**
     * @param string $p_escapedString Expected escaped string
     * @param string $p_string String containing characters to escape
     * @dataProvider stringsWithAndWithoutEscapeCharacters
     */
    public function testEscape($p_escapedString, $p_string) {
        $this->assertEquals($p_escapedString, Sequin\Term::escape($p_string));
    }

    public function stringsWithAndWithoutEscapeCharacters() {
        return array(
            array('\\+', '+'),
            array('\\-', '-'),
            array('\\&&', '&&'),
            array('\\||', '||'),
            array('\\!', '!'),
            array('\\(', '('),
            array('\\)', ')'),
            array('\\{', '{'),
            array('\\}', '}'),
            array('\\[', '['),
            array('\\]', ']'),
            array('\\^', '^'),
            array('\\"', '"'),
            array('\\~', '~'),
            array('\\*', '*'),
            array('\\?', '?'),
            array('\\:', ':'),
            array('\\\\', '\\'),
        );
    }

    public function testNewinstanceCreatesANewInstance() {
        $oTerm = Sequin\Term::newInstance('value');
        $this->assertTrue($oTerm instanceof Sequin\Term);
        $this->assertEquals('value', $oTerm->value());
        $this->assertNull($oTerm->fieldName());
        $this->assertEquals(1, $oTerm->boostFactor());

        $oTerm = Sequin\Term::newInstance('value', 'fieldName');
        $this->assertTrue($oTerm instanceof Sequin\Term);
        $this->assertEquals('value', $oTerm->value());
        $this->assertEquals('fieldName', $oTerm->fieldName());
        $this->assertEquals(1, $oTerm->boostFactor());

        $oTerm = Sequin\Term::newInstance('value', 'fieldName', 3);
        $this->assertTrue($oTerm instanceof Sequin\Term);
        $this->assertEquals('value', $oTerm->value());
        $this->assertEquals('fieldName', $oTerm->fieldName());
        $this->assertEquals(3, $oTerm->boostFactor());
    }
}