<?php
declare(strict_types=1);

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * ConvertFromFunction ::=
 *  "DECODE" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class Decode extends FunctionNode
{
    public $field = null;
    public $encoding = null;

    public function parse(Parser $parser): void
    {
        $parser->match(TokenType::T_IDENTIFIER);
        $parser->match(TokenType::T_OPEN_PARENTHESIS);
        $this->field = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_COMMA);
        $this->encoding = $parser->ArithmeticPrimary();
        $parser->match(TokenType::T_CLOSE_PARENTHESIS);
    }

    public function getSql(SqlWalker $sqlWalker): string
    {
        return "DECODE(" .
            $this->field->dispatch($sqlWalker) . ", " .
            $this->encoding->dispatch($sqlWalker) .
            ")";
    }
}