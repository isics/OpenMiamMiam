<?php

namespace Isics\Bundle\OpenMiamMiamBundle\Doctrine\ORM\Query\AST\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Date extends FunctionNode
{
    public $dateTime;

    /**
     * @override
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->dateTime = $parser->ArithmeticPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * @override
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return sprintf(
            'DATE(%s)',
            $this->dateTime->dispatch($sqlWalker)
        );
    }
}
