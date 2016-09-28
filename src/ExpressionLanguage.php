<?php

namespace ECL;

use \Symfony\Component\ExpressionLanguage as SEL;

/**
 * Class ExpressionLanguage
 * Base expression language class with commonly used functions enabled.
 * @package ECL
 */
class ExpressionLanguage extends SEL\ExpressionLanguage {
    /** @var Whitelisted functions available in ExpressionLanguage. */
    private static $FUNCTIONS = [
        'explode', 'implode', 'trim', 'substr', 'str_replace', 'strlen', 'json_encode', 'json_decode', 'strcmp', 'count',
    ];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();

        foreach(self::$FUNCTIONS as $function) {
            $this->register($function, [__CLASS__, 'compileStub'], $this->evaluateWrapper($function));
        }
    }

    /**
     * Wraps a function so that it's correctly called by an SEL expression.
     * @param string|string[] $function The function name.
     */
    public static function evaluateWrapper($function) {
        return function() use($function) {
            $args = func_get_args();
            array_shift($args);
            return call_user_func_array($function, $args);
        };
    }

    public static function compileStub() {
        throw new \BadMethodCallException();
    }

    /**
     * Evaluate an expression.
     * Overridden to allow passing in a SymbolTable.
     * @param Expression|string $expression
     * @param SymbolTable|array $values
     * @return mixed
     */
    public function evaluate($expression, $values) {
        if(is_array($values)) {
            return parent::evaluate($expression, $values);
        } else {
            return $this->parse($expression, $values->getKeys())->getNodes()->evaluate($this->functions, $values);
        }
    }
}
