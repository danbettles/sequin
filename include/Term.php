<?php
/**
 * @copyright Copyright (c) 2012, Dan Bettles
 * @author Dan Bettles <dan@danbettles.net>
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace sequin;

/**
 * @author Dan Bettles <dan@danbettles.net>
 */
class Term {

    /**
     * @var int
     */
    const DEFAULT_BOOST_FACTOR = 1;

    /**
     * @var string
     */
    const OPERATOR_AND = 'AND';

    /**
     * @var string
     */
    const OPERATOR_OR = 'OR';

    /**
     * @var string
     */
    const OPERATOR_NOT = 'NOT';

    /**
     * @var string
     */
    private $value;

    /**
     * @var string|null
     */
    private $fieldName;

    /**
     * @var int
     */
    private $boostFactor;

    /**
     * @var string|null
     */
    private $operator = null;

    /**
     * @var object|null sequin\Term
     */
    private $oRightTerm = null;

    /**
     * @return string
     */
    protected function valueToString() {
        return $this->value();
    }

    /**
     * @return string
     */
    protected function rightTermsToString() {
        if (! is_null($this->operator()) && ! is_null($this->rightTerm())) {
            return ' ' . $this->operator() . ' ' . $this->rightTerm();
        }

        return '';
    }

    /**
     * @return string
     */
    public function toString() {
        $term = '';
        $term .= is_null($this->fieldName()) ? '' : $this->fieldName() . ':';
        $term .= $this->valueToString();
        $term .= $this->boostFactor() == self::DEFAULT_BOOST_FACTOR ? '' : '^' . $this->boostFactor();
        $term .= $this->rightTermsToString();
        return $term;
    }

    /**
     * @param string $p_value
     * @param string|null [$p_fieldName]
     * @param int [$p_boostFactor]
     */
    public function __construct($p_value, $p_fieldName = null, $p_boostFactor = self::DEFAULT_BOOST_FACTOR) {
        $this->setValue($p_value);
        $this->setFieldName($p_fieldName);
        $this->setBoostFactor($p_boostFactor);
    }

    /**
     * @param string $p_value
     */
    protected function setValue($p_value) {
        $this->value = $p_value;
    }

    /**
     * @return string
     */
    public function value() {
        return $this->value;
    }

    /**
     * @param string $p_fieldName
     */
    public function setFieldName($p_fieldName) {
        $this->fieldName = $p_fieldName;
    }

    /**
     * @return string
     */
    public function fieldName() {
        return $this->fieldName;
    }

    /**
     * @param int $p_boostFactor
     */
    public function setBoostFactor($p_boostFactor) {
        $this->boostFactor = $p_boostFactor;
    }

    /**
     * @return int
     */
    public function boostFactor() {
        return $this->boostFactor;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * @param string $p_operator
     */
    protected function setOperator($p_operator) {
        $this->operator = $p_operator;
    }

    /**
     * @return string|null
     */
    public function operator() {
        return $this->operator;
    }

    /**
     * @param object $p_oRightTerm sequin\Term
     */
    protected function setRightTerm(Term $p_oRightTerm) {
        $this->oRightTerm = $p_oRightTerm;
    }

    /**
     * @return object|null sequin\Term
     */
    public function rightTerm() {
        return $this->oRightTerm;
    }

    /**
     * @param string $p_operator
     * @param object $p_oRightTerm sequin\Term
     * @return object sequin\Term
     */
    private function join($p_operator, Term $p_oRightTerm) {
        $this->setOperator($p_operator);
        $this->setRightTerm($p_oRightTerm);
        return $this->rightTerm();
    }

    /**
     * @param object $p_oRightTerm sequin\Term
     */
    public function andRight(Term $p_oRightTerm) {
        return $this->join(self::OPERATOR_AND, $p_oRightTerm);
    }

    /**
     * @param object $p_oRightTerm sequin\Term
     */
    public function orRight(Term $p_oRightTerm) {
        return $this->join(self::OPERATOR_OR, $p_oRightTerm);
    }

    /**
     * @param object $p_oRightTerm sequin\Term
     */
    public function notRight(Term $p_oRightTerm) {
        return $this->join(self::OPERATOR_NOT, $p_oRightTerm);
    }

    /**
     * @param string $p_string
     * @return string
     * @link http://lucene.apache.org/core/old_versioned_docs/versions/3_0_0/queryparsersyntax.html#Escaping Special Characters
     */
    public static function escape($p_string) {
        return preg_replace('/(\+|-|&&|\|\||!|\(|\)|{|}|\[|\]|\^|"|~|\*|\?|:|\\\\)/', "\\\\$1", $p_string);
    }

    /**
     * @see Term::__construct()
     */
    public static function newInstance() {
        $oTermClass = new \ReflectionClass(get_called_class());
        return $oTermClass->newInstanceArgs(func_get_args());
    }
}