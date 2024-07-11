<?php
declare(strict_types=1);

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * ConvertFromFunction ::=
 *  "CONVERT_FROM" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class ConvertFrom extends FunctionNode
{
    public $field = null;
    public $encoding = null;

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->encoding = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $walker)
    {
        return "CONVERT_FROM(" .
            $this->field->dispatch($walker) . ", " .
            $this->encoding->dispatch($walker) .
            ")";
    }
}