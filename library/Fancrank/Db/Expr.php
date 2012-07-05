<?php
class Fancrank_Db_Expr extends Zend_Db_Expr
{
    public function __construct($expression)
    {
        $args = func_get_args();
        $expression = array_shift($args);

        $this->_expression = (string) vsprintf($expression, $args);
    }
}
