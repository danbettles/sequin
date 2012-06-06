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
class Query extends Term {

    /**
     * The term onto which we'll be attaching the next term
     * 
     * Initially, the active term will be the query itself
     * 
     * @var object sequin\Term
     */
    private $oActiveTerm;

    /**
     * The query 'containing' this query, or NULL if this is the outermost query
     * 
     * @var object|null sequin\Query
     */
    private $oOwnerQuery = null;

    /**
     * @param string|sequin\Term $p_value
     * @param string|null [$p_fieldName]
     * @param int [$p_boostFactor]
     */
    public function __construct($p_value, $p_fieldName = null, $p_boostFactor = self::DEFAULT_BOOST_FACTOR) {
        parent::__construct($p_value, $p_fieldName, $p_boostFactor);
        $this->setActiveTerm($this);
    }

    /**
     * @param object $p_oActiveTerm sequin\Term
     */
    protected function setActiveTerm(Term $p_oActiveTerm) {
        $this->oActiveTerm = $p_oActiveTerm;
    }

    /**
     * @return object sequin\Term
     */
    protected function activeTerm() {
        return $this->oActiveTerm;
    }

    /**
     * @param string $p_operator
     * @param object $p_oRightTerm sequin\Term
     * @return object sequin\Term
     */
    private function joinWithActiveTerm($p_operator, Term $p_oRightTerm) {
        $joinMethodName = strtolower($p_operator) . 'Right';
        $this->setActiveTerm($this->activeTerm()->$joinMethodName($p_oRightTerm));
        return $this->activeTerm();
    }

    /**
     * @param string $p_operator
     * @param array $p_aArgument
     * @return object sequin\Term
     */
    private function joinNewTermWithActiveTerm($p_operator, array $p_aArgument) {
        $oTermClass = new \ReflectionClass(__NAMESPACE__ . '\\Term');
        $oTerm = $oTermClass->newInstanceArgs($p_aArgument);
        return $this->joinWithActiveTerm($p_operator, $oTerm);
    }

    /**
     * @return object sequin\Term
     */
    public function andTerm() {
        $this->joinNewTermWithActiveTerm(self::OPERATOR_AND, func_get_args());
        return $this;
    }

    /**
     * @return object sequin\Term
     */
    public function orTerm() {
        $this->joinNewTermWithActiveTerm(self::OPERATOR_OR, func_get_args());
        return $this;
    }

    /**
     * @return object sequin\Term
     */
    public function notTerm() {
        $this->joinNewTermWithActiveTerm(self::OPERATOR_NOT, func_get_args());
        return $this;
    }

    /**
     * @return string
     */
    protected function valueToString() {
        return $this->value() instanceof Term ? '(' . $this->value() . ')' : $this->value();
    }

    /**
     * @param object $p_oOwnerQuery sequin\Query
     */
    protected function setOwnerQuery($p_oOwnerQuery) {
        $this->oOwnerQuery = $p_oOwnerQuery;
    }

    /**
     * @return object sequin\Query
     */
    protected function ownerQuery() {
        return $this->oOwnerQuery;
    }

    /**
     * @param string $p_operator
     * @param array $p_aArgument
     * @return object sequin\Term
     */
    private function joinNewQueryWithActiveTerm($p_operator, array $p_aArgument) {
        $aArgument = $p_aArgument;
        $oTerm = array_shift($aArgument);

        if (! ($oTerm instanceof Term)) {
            $oTerm = new Term($oTerm);
        }

        array_unshift($aArgument, $oTerm);

        $oQueryClass = new \ReflectionClass(__CLASS__);
        $oQuery = $oQueryClass->newInstanceArgs($aArgument);

        $this->joinWithActiveTerm($p_operator, $oQuery);
        $oQuery->setActiveTerm($oTerm);
        $oQuery->setOwnerQuery($this);

        return $oQuery;
    }

    /**
     * @return object sequin\Query
     */
    public function andQuery() {
        return $this->joinNewQueryWithActiveTerm(self::OPERATOR_AND, func_get_args());
    }

    /**
     * @return object sequin\Query
     */
    public function orQuery() {
        return $this->joinNewQueryWithActiveTerm(self::OPERATOR_OR, func_get_args());
    }

    /**
     * @return object sequin\Query
     */
    public function notQuery() {
        return $this->joinNewQueryWithActiveTerm(self::OPERATOR_NOT, func_get_args());
    }

    /**
     * Ends the current subquery and returns the owner query (the query 'containing' the subquery)
     * 
     * Returns the current query if there is no subquery to end
     * 
     * @return object sequin\Query
     */
    public function endQuery() {
        if (is_null($this->ownerQuery())) {
            return $this;
        }

        return $this->ownerQuery();
    }
}