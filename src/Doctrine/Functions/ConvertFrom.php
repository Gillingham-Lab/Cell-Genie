<?php
declare(strict_types=1);

namespace App\Doctrine\Functions;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\TokenType;

/**
 * ConvertFromFunction ::=
 *  "CONVERT_FROM" "(" ArithmeticPrimary "," ArithmeticPrimary ")"
 */
class ConvertFrom extends FunctionNode
{
    public ?Node $field = null;
    public ?Node $encoding = null;

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
        return "CONVERT_FROM(" .
            $this->field->dispatch($sqlWalker) . ", " .
            $this->encoding->dispatch($sqlWalker) .
            ")";
    }
}